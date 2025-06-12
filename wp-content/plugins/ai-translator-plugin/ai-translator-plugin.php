<?php
/*
Plugin Name: AI Translator Plugin
Description: Bu eklenti, verilen İngilizce makaleyi Türkçeye çevirir ve WordPress'te yeni bir yazı oluşturur.
Version: 1.0
Author: Samet047
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/translation.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

// Hook for adding admin menus
add_action('admin_menu', 'ai_translator_plugin_menu');

// Action function for the above hook
function ai_translator_plugin_menu() {
    add_menu_page('AI Translator', 'AI Translator', 'manage_options', 'ai-translator-plugin', 'ai_translator_plugin_options');
}

// Register and define the settings
add_action('admin_init', 'ai_translator_plugin_settings');

function ai_translator_admin_styles() {
    wp_enqueue_style('ai-translator-admin', plugins_url('assets/admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'ai_translator_admin_styles');

?>