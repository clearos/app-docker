<?php

/**
 * Docker containers controller.
 *
 * @category   apps
 * @package    docker
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Docker containers controller.
 *
 * @category   apps
 * @package    docker
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

class Containers_Controller extends ClearOS_Controller
{
    protected $app = NULL;
    protected $project = NULL;

    /**
     * Docker containers constructor.
     *
     * @param string $group group name app name
     *
     * @return view
     */

    function __construct($app, $project)
    {
        $this->app = $app;
        $this->project = $project;
    }

    /**
     * Docker containers controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('docker/Docker');
        $this->lang->load('docker');

        // Load views
        //-----------

        $data['app'] = $this->app;
        $data['project'] = $this->project;

        $this->page->view_form('docker/containers', $data, lang('docker_containers'), $options);
    }

    /**
     * Returns list of containers.
     */

    function listing()
    {
        // Load dependencies
        //------------------

        $this->load->library('docker/Docker');
        $this->lang->load('docker');

        // Load data
        //----------

        try {
            $containers = $this->docker->get_containers($this->project);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        echo json_encode($containers);
    }

    /**
     * Stops a container.
     */

    function stop($id)
    {
        // Load dependencies
        //------------------

        $this->load->library('docker/Docker');
        $this->lang->load('docker');
    }
}
