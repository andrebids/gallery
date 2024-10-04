<?php
/*
Plugin Name: Custom Gallery with Sidebar Filters
Description: Displays a custom gallery with advanced filters for tags and dimensions, available in a sidebar with pagination.
Version: 1.8
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'shortcode.php';
include_once plugin_dir_path(__FILE__) . 'ajax-handlers.php';
include_once plugin_dir_path(__FILE__) . 'assets.php';
