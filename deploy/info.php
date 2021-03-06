<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'docker';
$app['version'] = '2.5.20';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('docker_app_description');
$app['powered_by'] = array(
    'vendor' => array(
        'name' => 'Docker',
        'url' => 'https://www.docker.com/',
    ),
    'packages' => array(
        'docker' => array(
            'name' => 'Docker',
            'version' => '---',
        ),
    ),
);

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('docker_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_virtualization');
$app['menu_enabled'] = FALSE;

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-network',
);

$app['core_requires'] = array(
    'app-network-core >= 1:2.4.6',
    'app-mail-routing-core',
    'app-firewall-core >= 1:2.4.17',
    'app-suva-core',
    'curl',
    'docker',
    'docker-compose',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/docker' => array(),
    '/var/clearos/docker/backup' => array(),
    '/var/clearos/docker/project' => array(),
);

$app['core_file_manifest'] = array(
    'docker.php'=> array('target' => '/var/clearos/base/daemon/docker.php'),
    '10-docker' => array(
        'target' => '/etc/clearos/firewall.d/10-docker',
    ),
    'clearos-compose'=> array(
        'target' => '/usr/sbin/clearos-compose',
        'mode' => '0755'
    ),
    'docker.conf' => array(
        'target' => '/etc/clearos/docker.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);

$app['delete_dependency'] = array(
    'app-docker-core',
    'docker',
    'docker-common',
    'docker-compose'
);
