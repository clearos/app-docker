<?php

/**
 * Docker project install view.
 *
 * @category   apps
 * @package    docker
 * @subpackage views
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
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('docker');
$this->lang->load('base');

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo "<input id='docker_install_app_name' value='$app_name' type='hidden'>\n";

echo "<div id='docker_install_wrapper' style='display:none;'>";

$progress = 
    "<div id='docker_install_progress_bar_wrapper'>" .
        "<p>" . lang('docker_install_progress_help') . "</p>" .
        "<h3>" . lang('base_progress') . "</h3>" .
        progress_bar('docker_install_progress_bar', array('input' => 'docker_install_progress_bar')) .
        "<h3 style='clear: both;'>" . lang('base_details') . "</h3>" .
        "<div id='docker_install_progress_details'>...</div>\n" .
    "</div>" .
    "<div id='docker_install_button_wrapper'>" .
        "<p>" . lang('docker_install_help') . "</p>" .
        anchor_javascript('docker_install_button', lang('docker_start_download')) .
    "</div>";


echo infobox_highlight(lang('base_download'), $progress);

echo "</div>";
