<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('tms_wbt_primary_menu_id');
delete_option('tms_wbt_record_map');
delete_option('tms_wbt_openai_key');
delete_option('tms_wbt_page_mode');
