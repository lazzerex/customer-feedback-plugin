<?php
/**
 * File: src/Core/Deactivator.php
 */

namespace CustomerFeedback\Core;

class Deactivator
{
    public static function deactivate()
    {
        // Clear any scheduled events
        wp_clear_scheduled_hook('customer_feedback_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Note: We don't delete data on deactivation, only on uninstall
    }
}