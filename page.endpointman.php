<?php
global $active_modules;

if (!empty($active_modules[\ENDPOINT][\RAWNAME])) {
	if (FreePBX::Endpointman()->configmod->get("disable_endpoint_warning") !== "1") {
		include('page.epm_warning.php');  
	}
}
?>
<?PHP

/**
 * Endpoint Manager Master Page File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Endpoint Manager
 */

require_once __DIR__.'/config.php';

match ($page) {
    'advanced' => include LOCAL_PATH . 'includes/advanced.inc',
    'epm_oss' => include LOCAL_PATH . 'includes/advanced.inc',
    'template_manager' => include LOCAL_PATH . 'includes/template_manager.inc',
    'devices_manager' => include LOCAL_PATH . 'includes/devices_manager.inc',
    'brand_model_manager' => include LOCAL_PATH . 'includes/brand_model_manager.inc',
    'installer' => include LOCAL_PATH . 'install.inc',
    default => include LOCAL_PATH . 'includes/devices_manager.inc',
};