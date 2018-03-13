<?php

/**
 * Docker rpoject install ajax helpers.
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
    var install_app_name = $('#docker_install_app_name').val();

    // Click Events
    //-------------

    $('#docker_install_button').click(function(e) {
        e.preventDefault();

        // FIXME: add locking to avoid jumpiness
        var options = new Object();
        options.text = '...';
        $('#docker_install_progress_details').html(clearos_loading(options));
        $('#docker_install_progress_bar_wrapper').show();
        $('#docker_install_button_wrapper').hide();
        $('#docker_install_wrapper').show();

        dockerStart(install_app_name);
    });

    if (install_app_name)
        dockerInstallStatus(install_app_name);
});

///////////////////////////////////////////////////////////////////////////
// F U N C T I O N S
///////////////////////////////////////////////////////////////////////////

function dockerStart(app_name) {
    $.ajax({
        url: '/app/' + app_name + '/install/pull',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
        }
    });
}


function dockerInstallStatus(app_name) {
    $.ajax({
        url: '/app/' + app_name + '/install/status',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            dockerShowStatus(payload);
            if (payload.code == 1000)
                window.location = '/app/' + app_name;
            else
                window.setTimeout(dockerInstallStatus, 3000, app_name);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(dockerInstallStatus, 3000, app_name);
        }
    });
}

function dockerShowStatus(payload) {
    clearos_set_progress_bar('docker_install_progress_bar', payload.progress, null);

    // Installed and complete
    if (payload.code == 1000) {
        $('#docker_install_wrapper').hide();

    // Pull is running
    } else if (payload.code == 2000) {
        var options = new Object();
        options.text = payload.details;
        $('#docker_install_progress_details').html(clearos_loading(options));
        $('#docker_install_progress_bar_wrapper').show();
        $('#docker_install_button_wrapper').hide();

        $('#docker_install_wrapper').show();

    // Not installed
    } else if (payload.code == 3000) {
        $('#docker_install_progress_bar_wrapper').hide();
        $('#docker_install_button_wrapper').show();
        $('#docker_install_wrapper').show();
    }
}

// vim: syntax=javascript
