<?php
/**
 * Settings URL maps
 *
 * @category   GadgetMaps
 * @package    Settings
 */
$maps[] = array('Settings', 'settings');
$maps[] = array('HealthStatus', 'settings/health-status');
$maps[] = array('Offline', 'offline');
$maps[] = array(
    'ServiceWorker',
    'service-worker',
    array(),
    'js'
);
$maps[] = array(
    'Manifest',
    'manifest',
    array(),
    'json'
);
$maps[] = array(
    'CleanupExpiredCache',
    'cleanup-expired-cache'
);
