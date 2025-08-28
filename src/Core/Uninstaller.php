<?php
/**
 * File: src/Core/Uninstaller.php
 */

namespace CustomerFeedback\Core;

use CustomerFeedback\Database\DatabaseManager;

class Uninstaller
{
    public static function uninstall()
    {
        // Check if user really wants to delete all data
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            exit;
        }
        
        // Remove database tables
        $database_manager = new DatabaseManager();
        $database_manager->dropTables();
        
        // Remove plugin options
        delete_option('customer_feedback_version');
        
        // Remove any transients or cached data
        delete_transient('customer_feedback_cache');
        
        // Clear any scheduled events
        wp_clear_scheduled_hook('customer_feedback_cleanup');
    }
}