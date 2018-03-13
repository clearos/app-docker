<?php

/**
 * Docker project class.
 *
 * @category   apps
 * @package    docker
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\docker;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');
clearos_load_language('docker');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\docker\Docker as Docker;
use \clearos\apps\docker\Project as Project;
use \clearos\apps\firewall\Firewall as Firewall;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('docker/Docker');
clearos_load_library('docker/Project');
clearos_load_library('firewall/Firewall');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Docker project class.
 *
 * @category   apps
 * @package    docker
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

class Project extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_COMPOSE = '/usr/bin/docker-compose';
    const COMMAND_CLEAROS_COMPOSE = '/usr/sbin/clearos-compose';
    const STATE_RUNNING = 'Running';
    const PATH_CONFIGLET = '/var/clearos/docker/project';
    const STATUS_BUSY = 'busy';
    const STATUS_RUNNING = 'running';
    const STATUS_STARTING = 'starting';
    const STATUS_STOPPED = 'stopped';
    const STATUS_STOPPING = 'stopping';
    const STATUS_RESTARTING = 'restarting';
    const STATUS_DEAD = 'dead';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $project;
    protected $details;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Docker project constructor.
     *
     * @param string $project Docker project name
     */

    public function __construct($project)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->project = $project;

        $configlet_file = self::PATH_CONFIGLET . '/' . $project . '.php';

        $file = new File($configlet_file);

        if (file_exists($configlet_file)) {
            include $configlet_file;
            $this->details = $configlet;
        }
    }

    /**
     * Returns the app name associated with project.
     *
     * @return string app name
     * @throws Engine_Exception
     */

    public function get_app_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->details['app_name'];
    }

    /**
     * Returns install status details.
     *
     * @return array status information
     * @throws Exception
     */

    public function get_install_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Get docker image status
        //------------------------

        $docker = new Docker();
        $installed_images = $docker->get_images($this->details['base_project']);
        $required_images = $this->details['images'];

        $latest_timestamp = 0;
        $latest_image = '';
        $installed_count = 0;
// FIXME div by 0
        $required_count = count($required_images);

        foreach ($required_images as $tag => $translation) {
            // TODO: there must be a better way to detect a tag with a default registry
            if (!preg_match('/.*\..*\//', $tag))
                $tag = 'docker.io/' . $tag;

            if (array_key_exists($tag, $installed_images)) {
                $installed_count++;
                if ($installed_images[$tag]['created'] > $latest_timestamp) {
                    $latest_timestamp = $installed_images[$tag]['created'];
                }
            }
        }

        $progress = ($installed_count / $required_count) * 100;

        if ($progress == 100) {
            $result = array(
                'code' => 1000,
                'progress' => 100,
                'details' => lang('base_complete')
            );
        } else {
            if ($this->is_pull_running()) {
                $result = array(
                    'code' => 2000,
                    'progress' => $progress,
                    'details' => lang('docker_download_help')
                );
            } else {
                $result = array(
                    'code' => 3000,
                    'progress' => 0,
                    'details' => '',
                );
            }
        }

        return $result;
    }

    /**
     * Returns list of containers.
     *
     * @return array list of containers
     * @throws Exception
     */

    public function get_listing()
    {
        clearos_profile(__METHOD__, __LINE__);

        $docker = new Docker();
        $containers = $docker->get_containers($this->details['base_project'], $this->details['ignore_list']);

        return $containers;
    }

    /**
     * Returns the running state of the project.
     *
     * @return boolean TRUE if the project is running
     * @throws Engine_Exception
     */

    public function get_running_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $status = $this->get_status();

        if ($status == self::STATUS_RUNNING)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns the status of the project.
     *
     * Status codes:
     * - stopped
     * - running
     * - stopping
     * - starting
     *
     * @return string status code
     * @throws Engine_Exception
     */

    public function get_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        $docker = new Docker();

        // Bail if Docker is not running
        //------------------------------

        $is_running = $docker->get_running_state();

        if (!$is_running)
            return self::STATUS_STOPPED;

        // Grab list of containers
        //------------------------

        $containers = $docker->get_containers($this->details['base_project'], $this->details['ignore_list']);

        $count = 0;
        $busy_count = 0;

        foreach ($containers as $details) {
            if ($details['state'] == self::STATE_RUNNING)
                $count++;
            else
                $busy_count++;
        }

        if ($busy_count > 0)
            $status = self::STATUS_BUSY;
        else if ($count == 0)
            $status = self::STATUS_STOPPED;
        else if ($count < $this->details['container_count'])
            $status = self::STATUS_BUSY;
        else
            $status = self::STATUS_RUNNING;

        return $status;
    }

    /**
     * Returns install status.
     *
     * @return array status information
     * @throws Exception
     */

    public function is_installed()
    {
        clearos_profile(__METHOD__, __LINE__);

        $status = $this->get_install_status();

        if ($status['progress'] == 100)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns status of Docker pull.
     *
     * @return array status information
     * @throws Exception
     */

    public function is_pull_running()
    {
        clearos_profile(__METHOD__, __LINE__);

        $output = [];

        try {
            $shell = new Shell();
            $shell->execute('/usr/bin/ps', 'ax');
            $output = $shell->get_output();
        } catch (Exception $e) {
            // Not fatal
        }

        foreach ($output as $line) {
            if (preg_match('/docker/', $line) && preg_match('/pull/', $line))
                return TRUE;
        }

        return FALSE;
    }

    /**
     * Starts docker pull for project.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function pull()
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['background'] = TRUE;

        $shell = new Shell();
        $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' pull', TRUE, $options);

        // Lame, but we need to give docker-compose some time
        sleep(5);
    }

    /**
     * Restarts the project if (and only if) it is already running.
     *
     * @param boolean $background run in background
     *
     * @return void
     * @throws Engine_Exception
     */

    public function reset($background = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($background));

        $options['background'] = $background;

        $shell = new Shell();
        $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' restart', TRUE, $options);

        // Lame, but we need to give docker-compose some time to tear down
        if ($background)
            sleep(5);
    }

    /**
     * Restarts a container.
     *
     * @param string $container container name or ID
     * @param string $compose_file docker compose file
     *
     * @see Project::reset()
     * @return void
     * @throws Engine_Exception
     */

    public static function restart_container($container, $compose_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $shell->execute(self::COMMAND_COMPOSE, '-f ' . $compose_file . ' restart ' . $container, TRUE);
        } catch (Exception $e) {
            // Not fatal
        }
    }

    /**
     * Restarts the project.
     *
     * @param boolean $background run in background
     *
     * @see Project::reset()
     * @return void
     * @throws Engine_Exception
     */

    public function restart($background = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($background));

        $options['background'] = $background;

        $shell = new Shell();
        $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' down', TRUE);
        $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' up -d', TRUE, $options);

        // Lame, but we need to give docker-compose some time to tear down
        if ($background)
            sleep(5);
    }

    /**
     * Sets running state of the project.
     *
     * @param boolean $state desired running state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_running_state($state, $background = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['background'] = $background;
        $command = ($state) ? 'up -d' : 'down';

        $shell = new Shell();
        $shell->execute(self::COMMAND_CLEAROS_COMPOSE, '-p ' . $this->project . ' -a ' . $command, TRUE, $options);

        if ($background)
            sleep(5);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validate state variable.
     *
     * @param boolean $state state
     *
     * @return string error message if state is invalid.
     */
    
    public function validate_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! is_bool($state))
            return lang('base_parameter_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Sets running state of the project via wrapper.
     *
     * This should only be called by the 
     *
     * @param boolean $state desired running state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function _set_running_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($state));

        // Start Docker
        //-------------

        $docker = new Docker();

        if (!$docker->get_running_state()) {
            $docker->set_running_state(TRUE);
            $docker->set_boot_state(TRUE);
            sleep(5);
        }

        // Docker login
        //-------------

        // FIXME


        // Docker compose start/stop
        //--------------------------

        $command = ($state) ? 'up -d' : 'down';

        $shell = new Shell();

        if ($state) {
            try {
                // On start, make sure everything is really shutdown.
                $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' down', TRUE);
            } catch (Exception $e) {
                // Not fatal
            }
        }

        $shell->execute(self::COMMAND_COMPOSE, '-f ' . $this->details['docker_compose_file'] . ' ' . $command, TRUE);

        // Lame, but we need to give docker-compose some time to tear down
        sleep(5);

        // Firewall
        //---------
        // TODO: we need to do a full restart for now.  Hopefully this will change in future Docker versions.

        if ($state) {
            $firewall = new Firewall();
            $firewall->restart();
        }
    }
}
