<?php

/**
 * Docker project ajax helpers.
 *
 * @category   apps
 * @package    docker
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2018 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/docker/
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

///////////////////////////////////////////////////////////////////////////
// M A I N
///////////////////////////////////////////////////////////////////////////

$(document).ready(function() {
    var clearos_project_name = $('#clearos_project_name').val();
    var clearos_app_name = $('#clearos_app_name').val();

    if (clearos_project_name)
        clearosProject(clearos_project_name, clearos_app_name);

    $('#sidebar_daemon_status').show();
    $('#sidebar_daemon_action').show();

    // Click Events
    //-------------

    $('#clearos_daemon_action').click(function(e) {
        e.preventDefault();
        var service_status = $("#clearos_daemon_action").html();
        $("#clearos_daemon_action").html(clearos_loading());

        var options = new Object();
        options.classes = 'theme-daemon-start-stop-status';

        if ($('#clearos_project_status_lock').val() == 'on') {
            // Prevent double click
        } else if (service_status == lang_stop) {
            $('#clearos_project_status_lock').val('on');
            $('#clearos_daemon_status').html(clearos_loading(options) + lang_stopping + '...');
            clearosStopProject(clearos_project_name);
        } else {
            $('#clearos_project_status_lock').val('on');
            $('#clearos_daemon_status').html(clearos_loading(options) + lang_starting + '...');
            clearosStartProject(clearos_project_name);
        }
    });

    // Main
    //-----

});

///////////////////////////////////////////////////////////////////////////
// D A E M O N
///////////////////////////////////////////////////////////////////////////

function clearosProject(daemon, app_name) {

    // Translations
    //-------------

    lang_busy = '<?php echo lang("base_busy"); ?>';
    lang_restart = '<?php echo lang("base_restart"); ?>';
    lang_restarting = '<?php echo lang("base_restarting"); ?>';
    lang_running = '<?php echo lang("base_running"); ?>';
    lang_start = '<?php echo lang("base_start"); ?>';
    lang_starting = '<?php echo lang("base_starting"); ?>';
    lang_stop = '<?php echo lang("base_stop"); ?>';
    lang_stopping = '<?php echo lang("base_stopping"); ?>';
    lang_stopped = '<?php echo lang("base_stopped"); ?>';
    lang_dead = '<?php echo lang("base_dead"); ?>';
    lang_no_entries = '<?php echo lang("base_no_entries"); ?>';
    basename = '/app/' + app_name + '/project';

    $('#clearos_daemon_status').html('');

    clearosGetProjectStatus();
}

// Functions
//----------

function clearosStartProject(daemon) {
    $.ajax({
        url: basename + '/start/' + daemon, 
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            $('#clearos_project_status_lock').val('off');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#clearos_project_status_lock').val('off');
            console.log(errorThrown);
        }
    });
}

function clearosStopProject(project) {
    $.ajax({
        url: basename + '/stop/' + project, 
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            $('#clearos_project_status_lock').val('off');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#clearos_project_status_lock').val('off');
            console.log(errorThrown);
        }
    });
}

function clearosGetProjectStatus() {

    var clearos_project_name = $('#clearos_project_name').val();

    $.ajax({
        url: basename + '/status/' + clearos_project_name, 
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            var clearos_project_status_lock = $('#clearos_project_status_lock').val();
            if (clearos_project_status_lock == 'off')
                clearosShowProjectStatus(payload);

            window.setTimeout(clearosGetProjectStatus, 3000);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(clearosGetProjectStatus, 1000);
        }
    });
}

function clearosShowProjectStatus(payload) {
    if (payload.status == 'running') {
        $("#clearos_daemon_status").html(lang_running);
        $("#clearos_daemon_action").html(lang_stop);
    } else if (payload.status == 'stopped') {
        $("#clearos_daemon_status").html("<span class='theme-stopped-daemon'>" + lang_stopped + "</span>");
        $("#clearos_daemon_action").html(lang_start);
    } else if (payload.status == 'dead') {
        $("#clearos_daemon_status").html("<span class='theme-dead-daemon'>" + lang_dead + "</span>");
        $("#clearos_daemon_action").html(lang_start);
    } else if (payload.status == 'starting') {
        $('#clearos_daemon_status').html(lang_starting + '<span class="theme-loading"></span>');
        $("#clearos_daemon_action").html('<span class="theme-loading-small" style="padding-right: 5px; height: 15px; margin-bottom: -5px;"></span>');
        $(".theme-loading-small").css('background-position', '5 0');
    } else if (payload.status == 'stopping') {
        $('#clearos_daemon_status').html(lang_stopping + '<span class="theme-loading"></span>');
        $("#clearos_daemon_action").html('<span class="theme-loading-small" style="padding-right: 5px; height: 15px; margin-bottom: -5px;"></span>');
        $(".theme-loading-small").css('background-position', '5 0');
    } else if (payload.status == 'restarting') {
        $('#clearos_daemon_status').html(lang_restarting + '<span class="theme-loading"></span>');
        $("#clearos_daemon_action").html('<span class="theme-loading-small" style="padding-right: 5px; height: 15px; margin-bottom: -5px;"></span>');
        $(".theme-loading-small").css('background-position', '5 0');
    } else if (payload.status == 'busy') {
        $('#clearos_daemon_status').html(lang_busy + '<span class="theme-loading"></span>');
        $("#clearos_daemon_action").html(lang_stop);
        $(".theme-loading-small").css('background-position', '5 0');
    } else if (payload.status == 'no_entries') {
        $("#clearos_daemon_status").html(lang_no_entries);
        $("#clearos_daemon_action_row").css('display', 'none');
    }
}

// vim: syntax=javascript
