<?php

/**
 * Docker ajax helper.
 *
 * @category   apps
 * @package    docker
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2015 ClearFoundation
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

$(document).ready(function() {
    $('a').click(function (e) {
console.log('wah');
        e.preventDefault();
        if (e.target.href == undefined)
            return;

        var service = e.target.href.substring((e.target.href.lastIndexOf('/') + 1));
console(service);
    });

    if ($('#docker_containers').length != 0)
        getContainerStatus();
});

/**
 * Ajax call to get container status.
 */

function getContainerStatus() {
    var app = $('#docker_app').text();
    var project = $('#docker_project').text();

    $.ajax({
        url: '/app/' + app + '/containers/listing',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            showContainerStatus(app, payload);
        //    window.setTimeout(getContainerStatus, 3000);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(getContainerStatus, 3000);
        }
    });
}

/**
 * Shows container status.
 */

function showContainerStatus(app, payload) {

    // Translations
    //-------------

    lang_stop = '<?php echo lang("base_stop"); ?>';
    lang_start = '<?php echo lang("base_start"); ?>';

    // Load data
    //----------

    $('#docker_containers').show();

    var anchor = '';
    var table_updates_list = get_table_containers_list();
    table_updates_list.fnClearTable();

    for (var index = 0 ; index < payload.length; index++) {
        if (payload[index].state == 'Running')
            anchor = theme_anchor('/app/' + app + '/containers/stop/' + payload[index].id, lang_stop, {})
        else
            anchor = theme_anchor('/app/' + app + '/containers/start/' + payload[index].id, lang_start, {})

        table_updates_list.fnAddData([
            payload[index].service,
            payload[index].state,
            payload[index].status,
            anchor
        ]);
    }

    table_updates_list.fnAdjustColumnSizing();
}

// vim: ts=4 syntax=javascript
