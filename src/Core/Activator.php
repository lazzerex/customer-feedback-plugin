<?php
/**
 * File: src/Core/Activator.php
 */

namespace CustomerFeedback\Core;

use CustomerFeedback\Database\DatabaseManager;

class Activator
{
    public static function activate()
    {
        $database_manager = new DatabaseManager();
        $database_manager->createTables();
        
        // Set plugin version
        update_option('customer_feedback_version', CUSTOMER_FEEDBACK_VERSION);
        
        // Create default questions if none exist
        self::createDefaultQuestions($database_manager);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private static function createDefaultQuestions($database_manager)
    {
        $existing_questions = $database_manager->getQuestionsCount();
        
        if ($existing_questions == 0) {
            $default_questions = [
                'Sự tận tâm và chuyên môn của bác sĩ điều trị?',
                'Sự thân thiện và nhiệt tình của trợ lý bác sĩ?',
                'Sự thân thiện và nhiệt tình của nhân viên lễ tân?',
                'Mức độ sạch sẽ và tiện nghi của phòng khám?'
            ];
            
            $admin_user_id = 1; // Default to admin user ID 1
            $admin_users = get_users(['role' => 'administrator', 'number' => 1]);
            if (!empty($admin_users)) {
                $admin_user_id = $admin_users[0]->ID;
            }
            
            foreach ($default_questions as $question) {
                $database_manager->insertQuestion($question, $admin_user_id);
            }
        }
    }
}