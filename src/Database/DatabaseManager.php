<?php
/**
 * File: src/Database/DatabaseManager.php
 */

namespace CustomerFeedback\Database;

class DatabaseManager
{
    private $wpdb;
    private $questions_table;
    private $reviews_table;
    private $review_questions_table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->questions_table = $wpdb->prefix . 'cf_questions';
        $this->reviews_table = $wpdb->prefix . 'cf_reviews';
        $this->review_questions_table = $wpdb->prefix . 'cf_review_questions';
    }

    public function createTables()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        // Questions table
        $questions_sql = "CREATE TABLE {$this->questions_table} (
            id int(11) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            created_by int(11) NOT NULL,
            updated_by int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Reviews table - updated to include pet_name and overall_rating
        $reviews_sql = "CREATE TABLE {$this->reviews_table} (
            id int(11) NOT NULL AUTO_INCREMENT,
            customer_name varchar(255) NOT NULL,
            pet_name varchar(255) DEFAULT NULL,
            phone varchar(20) NOT NULL,
            notes text,
            overall_rating int(11) DEFAULT NULL,
            total_score int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Review questions junction table
        $review_questions_sql = "CREATE TABLE {$this->review_questions_table} (
            id int(11) NOT NULL AUTO_INCREMENT,
            review_id int(11) NOT NULL,
            question_id int(11) NOT NULL,
            score int(11) NOT NULL,
            PRIMARY KEY (id),
            KEY review_id (review_id),
            KEY question_id (question_id),
            FOREIGN KEY (review_id) REFERENCES {$this->reviews_table} (id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES {$this->questions_table} (id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($questions_sql);
        dbDelta($reviews_sql);
        dbDelta($review_questions_sql);
        
        // Check if we need to add new columns to existing reviews table
        $this->updateReviewsTable();
    }

    private function updateReviewsTable()
    {
        // Check if pet_name column exists
        $pet_name_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->reviews_table} LIKE 'pet_name'");
        if (empty($pet_name_exists)) {
            $this->wpdb->query("ALTER TABLE {$this->reviews_table} ADD COLUMN pet_name varchar(255) DEFAULT NULL AFTER customer_name");
        }

        // Check if overall_rating column exists
        $overall_rating_exists = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->reviews_table} LIKE 'overall_rating'");
        if (empty($overall_rating_exists)) {
            $this->wpdb->query("ALTER TABLE {$this->reviews_table} ADD COLUMN overall_rating int(11) DEFAULT NULL AFTER notes");
        }
    }

    public function dropTables()
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->review_questions_table}");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->reviews_table}");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->questions_table}");
    }

    // Questions CRUD
    public function insertQuestion($title, $created_by)
    {
        return $this->wpdb->insert(
            $this->questions_table,
            [
                'title' => sanitize_text_field($title),
                'created_by' => intval($created_by)
            ],
            ['%s', '%d']
        );
    }

    public function updateQuestion($id, $title, $updated_by)
    {
        return $this->wpdb->update(
            $this->questions_table,
            [
                'title' => sanitize_text_field($title),
                'updated_by' => intval($updated_by)
            ],
            ['id' => intval($id)],
            ['%s', '%d'],
            ['%d']
        );
    }

    public function deleteQuestion($id)
    {
        return $this->wpdb->delete(
            $this->questions_table,
            ['id' => intval($id)],
            ['%d']
        );
    }

    public function getQuestion($id)
    {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->questions_table} WHERE id = %d", $id)
        );
    }

    public function getQuestions($limit = 20, $offset = 0)
    {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT q.*, u1.display_name as created_by_name, u2.display_name as updated_by_name 
                FROM {$this->questions_table} q 
                LEFT JOIN {$this->wpdb->users} u1 ON q.created_by = u1.ID 
                LEFT JOIN {$this->wpdb->users} u2 ON q.updated_by = u2.ID 
                ORDER BY q.created_at DESC 
                LIMIT %d OFFSET %d",
                $limit, $offset
            )
        );
    }

    public function getQuestionsCount()
    {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->questions_table}");
    }

    // Reviews CRUD - Updated to handle pet_name and overall_rating
    public function insertReview($customer_name, $phone, $notes, $questions_scores, $pet_name = '', $overall_rating = null)
    {
        // Start transaction for data consistency
        $this->wpdb->query('START TRANSACTION');

        try {
            // Check for recent duplicate submissions (last 5 minutes)
            $recent_duplicate = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT id FROM {$this->reviews_table} 
                    WHERE customer_name = %s AND phone = %s 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    ORDER BY created_at DESC LIMIT 1",
                    $customer_name, $phone
                )
            );

            if ($recent_duplicate) {
                throw new \Exception('Duplicate submission detected within 5 minutes');
            }

            // Calculate total score
            $total_score = array_sum($questions_scores);

            // Insert review with new fields
            $result = $this->wpdb->insert(
                $this->reviews_table,
                [
                    'customer_name' => sanitize_text_field($customer_name),
                    'pet_name' => sanitize_text_field($pet_name),
                    'phone' => sanitize_text_field($phone),
                    'notes' => sanitize_textarea_field($notes),
                    'overall_rating' => $overall_rating ? intval($overall_rating) : null,
                    'total_score' => intval($total_score)
                ],
                ['%s', '%s', '%s', '%s', '%d', '%d']
            );

            if (!$result) {
                throw new \Exception('Failed to insert review: ' . $this->wpdb->last_error);
            }

            $review_id = $this->wpdb->insert_id;

            // Validate all questions exist before inserting scores
            $question_ids = array_keys($questions_scores);
            $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
            $existing_questions = $this->wpdb->get_col(
                $this->wpdb->prepare(
                    "SELECT id FROM {$this->questions_table} WHERE id IN ($placeholders)",
                    ...$question_ids
                )
            );

            // Check if all questions exist
            $missing_questions = array_diff($question_ids, $existing_questions);
            if (!empty($missing_questions)) {
                throw new \Exception('Invalid question IDs: ' . implode(', ', $missing_questions));
            }

            // Insert question scores
            foreach ($questions_scores as $question_id => $score) {
                $result = $this->wpdb->insert(
                    $this->review_questions_table,
                    [
                        'review_id' => intval($review_id),
                        'question_id' => intval($question_id),
                        'score' => intval($score)
                    ],
                    ['%d', '%d', '%d']
                );

                if (!$result) {
                    throw new \Exception('Failed to insert question score for question ID: ' . $question_id);
                }
            }

            // Commit transaction
            $this->wpdb->query('COMMIT');
            
            // Log successful submission
            error_log("Customer Feedback: Successfully inserted review ID {$review_id} for {$customer_name}");
            
            return $review_id;

        } catch (\Exception $e) {
            // Rollback transaction on any error
            $this->wpdb->query('ROLLBACK');
            
            // Log the error
            error_log("Customer Feedback Error: " . $e->getMessage());
            
            return false;
        }
    }

    // Backward compatibility wrapper for old insertReview calls
    public function insertReviewLegacy($customer_name, $phone, $notes, $questions_scores)
    {
        return $this->insertReview($customer_name, $phone, $notes, $questions_scores);
    }

    public function getReview($id)
    {
        $review = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->reviews_table} WHERE id = %d", $id)
        );

        if ($review) {
            $review->questions = $this->wpdb->get_results(
                $this->wpdb->prepare(
                    "SELECT rq.*, q.title 
                    FROM {$this->review_questions_table} rq 
                    JOIN {$this->questions_table} q ON rq.question_id = q.id 
                    WHERE rq.review_id = %d",
                    $id
                )
            );
        }

        return $review;
    }

    public function getReviews($limit = 20, $offset = 0)
    {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->reviews_table} 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d",
                $limit, $offset
            )
        );
    }

    public function getReviewsCount()
    {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->reviews_table}");
    }

    public function deleteReview($id)
    {
        return $this->wpdb->delete(
            $this->reviews_table,
            ['id' => intval($id)],
            ['%d']
        );
    }

    // Getter methods for table names
    public function getQuestionsTable()
    {
        return $this->questions_table;
    }

    public function getReviewsTable()
    {
        return $this->reviews_table;
    }

    public function getReviewQuestionsTable()
    {
        return $this->review_questions_table;
    }

    // Additional methods for Excel export
    public function getAllReviewsWithDetails()
    {
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->reviews_table} 
            ORDER BY created_at DESC"
        );
    }

    public function getReviewQuestions($review_id)
    {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT rq.*, q.title as question_title
                FROM {$this->review_questions_table} rq 
                JOIN {$this->questions_table} q ON rq.question_id = q.id 
                WHERE rq.review_id = %d
                ORDER BY q.id",
                $review_id
            )
        );
    }
}