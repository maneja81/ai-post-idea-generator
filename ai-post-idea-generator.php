<?php
/*
Plugin Name: AI Post Idea Generator
Plugin URI: https://mohitaneja.com
Description: A WordPress plugin that generates new post ideas based on existing posts using AI.
Version: 1.0.0
Author: Mohit Aneja
Author URI: https://mohitaneja.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ai-post-idea-generator
Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

ob_start();

/**
 * The current version of the AI Post Idea Generator plugin.
 */
define('AI_POST_IDEA_GENERATOR_VERSION', '1.0.0');

/**
 * The directory path of the AI Post Idea Generator plugin.
 */
define('AI_POST_IDEA_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * The directory URL of the AI Post Idea Generator plugin.
 */
define('AI_POST_IDEA_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin activation hook.
 *
 * This function is triggered when the plugin is activated. It is used to perform
 * any setup tasks required for the plugin to function correctly, such as creating
 * database tables, setting default options, or scheduling events.
 *
 * @return void
 */
register_activation_hook(__FILE__, 'ai_post_idea_generator_activate');
function ai_post_idea_generator_activate()
{
    // Code to run on activation
}

/**
 * Registers the deactivation hook.
 *
 * This function will be called when the plugin is deactivated.
 *
 * @return void
 */
register_deactivation_hook(__FILE__, 'ai_post_idea_generator_deactivate');
function ai_post_idea_generator_deactivate()
{
    // Code to run on deactivation
}

/**
 * Checks if the class 'ai_post_idea_generator_autoload' exists.
 * If the class does not exist, it requires the file 'class-ai-post-idea-generator-autoload.php'
 * from the 'classes' directory within the plugin directory.
 *
 * @package AI_Post_Idea_Generator
 */
if (!class_exists('ai_post_idea_generator_autoload')) {
    require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . '/classes/class-ai-post-idea-generator-autoload.php';
    $ai_post_idea_generator_autoload = ai_post_idea_generator_autoload::getInstance();
    $ai_post_idea_generator_autoload->init();
}
