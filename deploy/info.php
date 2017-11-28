<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'docker';
$app['version'] = '2.4.0';
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

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['docker']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['policy']['title'] = lang('base_app_policy');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-network',
);

$app['core_requires'] = array(
    'app-network-core >= 1:2.4.2',
    'docker',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/docker' => array(),
    '/var/clearos/docker/backup' => array(),
);

$app['core_file_manifest'] = array(
    'docker.php'=> array('target' => '/var/clearos/base/daemon/docker.php'),
);

$app['delete_dependency'] = array(
    'app-docker-core',
    'docker',
);
