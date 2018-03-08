<?php

/**
 * Docker project controller.
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
 * Docker project controller.
 *
 * @category   apps
 * @package    docker
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/docker/
 */

class Project_Controller extends ClearOS_Controller
{
    protected $project_name = NULL;

    /**
     * Docker project constructor.
     *
     * @param string $project_name project name
     *
     * @return view
     */

    function __construct($project_name)
    {
        $this->project_name = $project_name;
    }

    /**
     * Docker project controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('docker/Project', $this->project_name);
        $this->lang->load('base');

        // Load data
        //----------

        try {
            $data['app_name'] = $this->project->get_app_name();
            $data['project_name'] = $this->project_name;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $options['javascript'] = array(clearos_app_htdocs('docker') . '/project.js.php');

        $this->page->view_form('docker/project', $data, lang('base_server_status'), $options);
    }

    /**
     * Project status.
     *
     * @return view
     */

    function status()
    {

        $this->load->library('docker/Project', $this->project_name);

        $status['status'] = $this->project->get_status();

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        echo json_encode($status);
    }

    /**
     * Project start.
     *
     * @return view
     */

    function start()
    {
        $this->load->library('docker/Project', $this->project_name);

        try {
            $this->project->set_running_state(TRUE);
        } catch (Exception $e) {
            //
        }

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        echo json_encode('ok');
    }

    /**
     * Project stop.
     *
     * @return view
     */

    function stop()
    {
        $this->load->library('docker/Project', $this->project_name);

        try {
            $this->project->set_running_state(FALSE);
        } catch (Exception $e) {
            //
        }

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        echo json_encode('ok');
    }
}
