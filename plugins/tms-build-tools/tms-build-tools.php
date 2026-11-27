<?php
/**
 * Plugin Name: TMS Build Tools
 * Description: WordPress build tools for theme generation, page importing, and isolated ACF page generation.
 * Version: 1.7.5
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Adam Do
 * Author Email: do.hieu@thomasnet.com
 * Author URI: https://thomasnet.com
 * Plugin URI: https://thomasnet.com
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('TMS_WBT_VERSION', '1.7.5');
define('TMS_WBT_FILE', __FILE__);
define('TMS_WBT_DIR', plugin_dir_path(__FILE__));

require_once TMS_WBT_DIR . 'includes/class-tms-wbt-xlsx-reader.php';
require_once TMS_WBT_DIR . 'includes/class-tms-wbt-importer.php';
require_once TMS_WBT_DIR . 'includes/class-tms-wbt-admin.php';

register_activation_hook(__FILE__, ['TMS_WBT_Importer', 'activate']);

add_action('plugins_loaded', static function (): void {
    TMS_WBT_Importer::init();
    TMS_WBT_Admin::init();
});
