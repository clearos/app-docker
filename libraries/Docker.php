<?php

/**
 * Docker server class.
 *
 * @category   apps
 * @package    docker
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
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

clearos_load_language('docker');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Daemon as Daemon;

clearos_load_library('base/Daemon');

// FIXME
require('httpful.phar');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Docker server class.
 *
 * @category   apps
 * @package    docker
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

class Docker extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const STATE_DEAD = 'dead';
    const STATE_EXITED = 'exited';
    const STATE_PAUSED = 'paused';
    const STATE_CREATED = 'created';
    const STATE_RUNNING = 'running';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    var $status_mapping = [];

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Docker constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->state_mapping = [
            self::STATE_DEAD => lang('docker_dead'),
            self::STATE_EXITED => lang('docker_exited'),
            self::STATE_PAUSED => lang('docker_paused'),
            self::STATE_CREATED => lang('docker_created'),
            self::STATE_RUNNING => lang('docker_running')
        ];

        parent::__construct('docker');
    }

    /**
     * Returns list of containers.
     *
     * @param string $project project name
     *
     * @return void
     * @throws Exception
     */

    public function get_containers($project = '')
    {
        clearos_profile(__METHOD__, __LINE__);

        $url = 'http://127.0.0.1:2375/containers/json?all=1';

        $response = \Httpful\Request::get($url)
            ->expectsJson()
            ->send();

        $raw_result = $response->body;

        $result = [];

        foreach ($raw_result as $container) {
            $result[$container->Id]['id'] = $container->Id;
            $result[$container->Id]['status'] = $container->Status;

            if (array_key_exists($container->State, $this->state_mapping))
                $result[$container->Id]['state'] = $this->state_mapping[$container->State];
            else
                $result[$container->Id]['state'] = $container->State;

            if ($container->Labels->{'com.docker.compose.project'})
                $result[$container->Id]['project'] = $container->Labels->{'com.docker.compose.project'};
            else
                $result[$container->Id]['project'] = '';

            if ($container->Labels->{'com.docker.compose.service'})
                $result[$container->Id]['service'] = $container->Labels->{'com.docker.compose.service'};
            else
                $result[$container->Id]['service'] = '';
        }

        // Filter result by project
        //-------------------------

        if (empty($project))
            return $result;

        $filtered_result = [];

        foreach ($result as $container) {
            if ($container['project'] == $project)
                $filtered_result[] = $container;
        }

        return $filtered_result;
    }

    /**
     * Stops a container.
     *
     * @param string $id container ID
     *
     * @return void
     * @throws Exception
     */

    public function stop_container($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        // FIXME: validate
        $url = 'http://127.0.0.1:2375/containers/' . $id . '/stop';
    }
}
