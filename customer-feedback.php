<?php
/**
 * Plugin Name: Customer Feedback
 * Plugin URI: https://yourwebsite.com
 * Description: Plugin quản lý phản hồi khách hàng với câu hỏi và đánh giá
 * Version: 1.0.0
 * Author: lazzerex
 * License: GPL v2 or later
 * Text Domain: customer-feedback
 * Requires at least: 5.0
 * Tested up to: 6.3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CUSTOMER_FEEDBACK_VERSION', '1.0.0');
define('CUSTOMER_FEEDBACK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOMER_FEEDBACK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CUSTOMER_FEEDBACK_PLUGIN_FILE', __FILE__);

// Require Composer autoloader with error handling
if (file_exists(CUSTOMER_FEEDBACK_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CUSTOMER_FEEDBACK_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Show admin notice if autoloader is missing
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Customer Feedback Plugin: Missing vendor/autoload.php. Please run composer install.</p></div>';
    });
    return;
}

// Initialize plugin
add_action('plugins_loaded', function() {
    if (class_exists('CustomerFeedback\Core\Plugin')) {
        CustomerFeedback\Core\Plugin::getInstance();
    }
});

// Activation hook
register_activation_hook(__FILE__, function() {
    if (class_exists('CustomerFeedback\Core\Activator')) {
        CustomerFeedback\Core\Activator::activate();
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    if (class_exists('CustomerFeedback\Core\Deactivator')) {
        CustomerFeedback\Core\Deactivator::deactivate();
    }
});

// XÓA DÒNG NÀY - WordPress sẽ tự động tìm file uninstall.php
// register_uninstall_hook(__FILE__, array('CustomerFeedback\Core\Uninstaller', 'uninstall'));