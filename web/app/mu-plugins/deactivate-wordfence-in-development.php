<?php
/*
Plugin Name:  Deactivate WordFence Plugin in Development
Description:  Disallow activation of WordFence in Development environments
Version:      1.0.0
Author:       Common Knowledge
Author URI:   https://commonknowledge.coop/
Text Domain:  commonknowledge
License:      MIT License
*/


add_action('admin_init', function () {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

         // Add an admin notice to explain why WordFence is not activated
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p>Wordfence has been automatically deactivated because this is a development environment.</p></div>';
        });
   
        // Deactivate Wordfence plugin if it's active
        if (is_plugin_active('wordfence/wordfence.php')) {
            deactivate_plugins('wordfence/wordfence.php');
        }
    }
});

// Prevent activation of Wordfence in development environment
add_filter('plugin_action_links', function ($actions, $plugin_file, $plugin_data, $context) {
    if (defined('WP_ENV') && WP_ENV === 'development' && $plugin_file == 'wordfence/wordfence.php') {
        if (isset($actions['activate'])) {
            unset($actions['activate']);
            $actions['cannot_activate'] = 'Cannot activate in development environment.';
        }
    }
    return $actions;
}, 10, 4);
