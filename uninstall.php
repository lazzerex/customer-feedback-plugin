<?php
/**
 * File: uninstall.php
 * Đặt file này trong thư mục gốc plugin: /wp-content/plugins/customer-feedback/uninstall.php
 */

// Kiểm tra WordPress có gọi uninstall không
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Tắt foreign key checks để tránh lỗi khi xóa bảng
$wpdb->query("SET FOREIGN_KEY_CHECKS = 0");

// Xóa các bảng theo thứ tự đúng (bảng con trước, bảng cha sau)
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cf_review_questions");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cf_reviews");  
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cf_questions");

// Bật lại foreign key checks
$wpdb->query("SET FOREIGN_KEY_CHECKS = 1");

// Xóa tất cả options của plugin
delete_option('customer_feedback_version');

// Xóa tất cả options có chứa customer_feedback
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%customer_feedback%'));

// Xóa tất cả transients của plugin
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%_transient_%customer_feedback%'));
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%_transient_timeout_%customer_feedback%'));

// Xóa các scheduled hooks
wp_clear_scheduled_hook('customer_feedback_cleanup');

// Clear cache
wp_cache_flush();