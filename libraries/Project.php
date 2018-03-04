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

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('docker/Docker');
clearos_load_library('docker/Project');

// Exceptions
//-----------

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
        } else {
            $this->details['ignore_list'] = [];
            $this->details['app_name'] = $project;
            $this->details['base_project'] = $project;
        }
    }

    /**
     * Returns the boot state of the project.
     *
     * @return boolean TRUE if project is set to run at boot
     * @throws Engine_Exception
     */

    public function get_boot_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        return FALSE;
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

        return TRUE;
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
        else
            $status = self::STATUS_RUNNING;

        return $status;
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

        $options['stdin'] = "use_popen";
        $options['background'] = $background;

        $shell = new Shell();
    }

    /**
     * Sets the boot state of the project.
     *
     * @param boolean $state desired boot state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_boot_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($state));
    }

    /**
     * Sets running state of the project.
     *
     * @param boolean $state desired running state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_running_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($state));
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
}
