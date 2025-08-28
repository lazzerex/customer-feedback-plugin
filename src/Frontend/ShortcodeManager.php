<?php

/**
 * File: src/Frontend/ShortcodeManager.php
 */

namespace CustomerFeedback\Frontend;

use CustomerFeedback\Database\DatabaseManager;

class ShortcodeManager
{
    private $database_manager;

    public function __construct()
    {
        $this->database_manager = new DatabaseManager();
        $this->init();
    }

    private function init()
    {
        add_shortcode('customer_feedback_form', [$this, 'renderFeedbackForm']);
        add_action('wp_ajax_submit_feedback', [$this, 'handleFeedbackSubmission']);
        add_action('wp_ajax_nopriv_submit_feedback', [$this, 'handleFeedbackSubmission']);

        // Enqueue assets when shortcode is used
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets()
    {
        // Only enqueue when shortcode is actually used on the page
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'customer_feedback_form')) {
            return;
        }

        // Enqueue CSS
        // wp_enqueue_style(
        //     'customer-feedback-form',
        //     CUSTOMER_FEEDBACK_PLUGIN_URL . 'src/Frontend/assets/css/feedback-form.css',
        //     [],
        //     CUSTOMER_FEEDBACK_VERSION
        // );

        // Enqueue JS with jQuery dependency
        wp_enqueue_script(
            'customer-feedback-form',
            CUSTOMER_FEEDBACK_PLUGIN_URL . 'src/Frontend/assets/js/feedback-form.js',
            ['jquery'],
            CUSTOMER_FEEDBACK_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('customer-feedback-form', 'customer_feedback_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('customer_feedback_nonce')
        ]);
    }

    public function renderFeedbackForm($atts = [])
    {
        // Parse shortcode attributes
        $atts = shortcode_atts([
            'title' => 'PHIẾU ĐÁNH GIÁ DỊCH VỤ',
            'submit_text' => 'GỬI ĐÁNH GIÁ',
            'success_message' => 'Thú Y Tên Lửa đã ghi nhận phản hồi của Quý khách. Xin chân thành cảm ơn!'
        ], $atts);

        // Get all questions
        $questions = $this->database_manager->getQuestions(100, 0);

        if (empty($questions)) {
            return '<p>Hiện tại chưa có câu hỏi nào.</p>';
        }

        // Define rating labels for mobile
        $rating_labels = [
            5 => 'Tuyệt vời',
            4 => 'Rất hài lòng',
            3 => 'Hài lòng',
            2 => 'Bình thường',
            1 => 'Thất vọng'
        ];

        ob_start();
?>

        <div class="vet-feedback-wrapper">
            <div class="vet-feedback-container">
                <h2 class="feedback-title"><?php echo esc_html($atts['title']); ?></h2>
                <p class="feedback-subtitle">
                    Thú Y Tên Lửa luôn nỗ lực để mang đến dịch vụ tốt nhất cho bạn và thú cưng.<br>
                    Hãy giúp chúng tôi hoàn thiện hơn nữa bằng cách chia sẻ những trải nghiệm của bạn tại phòng khám nhé!
                </p>

                <div class="feedback-messages">
                    <div class="feedback-success" style="display: none;">
                        <?php echo esc_html($atts['success_message']); ?>
                    </div>
                    <div class="feedback-error" style="display: none;"></div>
                </div>

                <form id="vet-feedback-form" class="vet-feedback-form" novalidate>
                    <?php wp_nonce_field('customer_feedback_nonce', 'feedback_nonce'); ?>

                    <!-- Customer Information Section with Floating Labels -->
                    <div class="customer-info-section">
                        <div class="form-row">
                            <div class="form-group half">
                                <input type="text" id="customer_name" name="customer_name" placeholder=" " required>
                                <label for="customer_name" class="floating-label">Tên khách hàng:</label>
                            </div>
                            <div class="form-group half">
                                <input type="text" id="pet_name" name="pet_name" placeholder=" " required>
                                <label for="pet_name" class="floating-label">Tên thú cưng:</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder=" " required>
                            <label for="phone" class="floating-label">Số điện thoại:</label>
                        </div>
                    </div>

                    <!-- Overall Rating Section -->
                    <div class="overall-rating-section">
                        <h3>QUÝ KHÁCH VUI LÒNG ĐÁNH GIÁ VỀ TRẢI NGHIỆM CHUNG TẠI PHÒNG KHÁM</h3>
                        <p class="rating-subtitle">(Vui lòng chọn vào biểu tượng tương ứng với mức độ hài lòng của bạn dưới đây)</p>

                        <div class="emotion-rating">
                            <div class="emotion-option" data-value="5">
                                <div class="emotion-icon excellent">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/smile-with-stars.png" alt="Tuyệt vời" class="emotion-image">
                                </div>
                                <span>Tuyệt vời</span>
                            </div>
                            <div class="emotion-option" data-value="4">
                                <div class="emotion-icon very-good">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/very-happy.png" alt="Rất hài lòng" class="emotion-image">
                                </div>
                                <span>Rất hài lòng</span>
                            </div>
                            <div class="emotion-option" data-value="3">
                                <div class="emotion-icon good">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/happy.png" alt="Hài lòng" class="emotion-image">
                                </div>
                                <span>Hài lòng</span>
                            </div>
                            <div class="emotion-option" data-value="2">
                                <div class="emotion-icon normal">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/neutral.png" alt="Bình thường" class="emotion-image">
                                </div>
                                <span>Bình thường</span>
                            </div>
                            <div class="emotion-option" data-value="1">
                                <div class="emotion-icon poor">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/sad.png" alt="Thất vọng" class="emotion-image">
                                </div>
                                <span>Thất vọng</span>
                            </div>
                        </div>
                        <input type="hidden" name="overall_rating" id="overall_rating" required>
                    </div>

                    <!-- Detailed Questions Section -->
                    <div class="detailed-questions-section">
                        <h3>CHIA SẺ THÊM VỀ TRẢI NGHIỆM CỦA QUÝ KHÁCH</h3>
                        <p class="questions-subtitle">(Vui lòng chọn vào ô tương ứng với trải nghiệm của bạn dưới đây)</p>

                        <div class="questions-container">
                            <!-- Heart icon - replaced with image -->
                            <div class="heart-icon">
                                <div class="heart-circle">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/heart-icon.png" alt="Heart Icon" class="heart-image">
                                </div>
                            </div>

                            <!-- Main grid container -->
                            <div class="questions-grid">
                                <!-- Header row -->
                                <!-- <div class="grid-header">
                                    <div class="header-spacer"></div>
                                    <div class="rating-header star-header">★</div>
                                    <div class="rating-header star-header">★★</div>
                                    <div class="rating-header star-header">★★★</div>
                                    <div class="rating-header star-header">★★★★</div>
                                    <div class="rating-header star-header">★★★★★</div>
                                </div> -->


                                <!-- Question rows -->
                                <?php foreach ($questions as $question): ?>
                                    <div class="grid-row">
                                        <div class="question-cell"><?php echo esc_html($question->title); ?></div>
                                        <div class="rating-cell-wrapper star-rating-wrapper">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <div class="rating-cell star-rating-cell" data-value="<?php echo $i; ?>">
                                                    <input type="radio"
                                                        name="questions[<?php echo esc_attr($question->id); ?>]"
                                                        value="<?php echo $i; ?>"
                                                        id="q<?php echo $question->id; ?>_<?php echo $i; ?>"
                                                        required>
                                                    <label for="q<?php echo $question->id; ?>_<?php echo $i; ?>" class="star-rating-label">
                                                        <span class="star"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg></span>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Feedback icon - replaced with image -->
                            <div class="feedback-icon">
                                <div class="feedback-bubble">
                                    <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/chat-icon.png" alt="Chat Icon" class="chat-image">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section with Floating Label -->
                    <div class="comments-section">
                        <div class="form-group">
                            <textarea id="notes" name="notes" rows="4" placeholder=" "></textarea>
                            <label for="notes" class="floating-label">Đánh giá, phản hồi chi tiết:</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="submit-section">
                        <div class="submit-layout">
                            <div class="submit-image">
                                <img src="<?php echo CUSTOMER_FEEDBACK_PLUGIN_URL; ?>assets/images/submit-illustration.png" alt="Submit Feedback" class="submit-illustration">
                            </div>
                            <div class="submit-button-wrapper">
                                <button type="submit" class="submit-button">
                                    <?php echo esc_html($atts['submit_text']); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Success message positioned right after submit button -->
                        <div class="submit-success-message" style="display: none;">
                            <div class="success-icon">✓</div>
                            <div class="success-text"><?php echo esc_html($atts['success_message']); ?></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


<?php

        return ob_get_clean();
    }

    public function handleFeedbackSubmission()
    {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Phương thức request không hợp lệ.');
        }

        // Verify nonce - FIXED: Check both POST and REQUEST
        $nonce = '';
        if (isset($_POST['nonce'])) {
            $nonce = $_POST['nonce'];
        } elseif (isset($_POST['feedback_nonce'])) {
            $nonce = $_POST['feedback_nonce'];
        } elseif (isset($_REQUEST['nonce'])) {
            $nonce = $_REQUEST['nonce'];
        }

        if (empty($nonce) || !wp_verify_nonce($nonce, 'customer_feedback_nonce')) {
            wp_send_json_error('Xác thực bảo mật thất bại. Nonce: ' . $nonce);
        }

        // Sanitize and validate input
        $customer_name = sanitize_text_field($_POST['customer_name'] ?? '');
        $pet_name = sanitize_text_field($_POST['pet_name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $overall_rating = intval($_POST['overall_rating'] ?? 0);
        $questions = $_POST['questions'] ?? [];

        // Debug log
        error_log('Feedback submission data: ' . print_r([
            'customer_name' => $customer_name,
            'pet_name' => $pet_name,
            'phone' => $phone,
            'overall_rating' => $overall_rating,
            'questions_count' => count($questions)
        ], true));

        // Validate required fields
        if (empty($customer_name) || empty($pet_name) || empty($phone) || $overall_rating < 1 || $overall_rating > 5) {
            wp_send_json_error('Vui lòng điền đầy đủ thông tin bắt buộc.');
        }

        // Validate customer name
        if (strlen($customer_name) < 2) {
            wp_send_json_error('Tên khách hàng phải có ít nhất 2 ký tự.');
        }

        if (!preg_match('/^[a-zA-ZÀ-ỹ\s]+$/u', $customer_name)) {
            wp_send_json_error('Tên khách hàng chỉ được chứa chữ cái và khoảng trắng.');
        }

        // Validate pet name
        if (strlen($pet_name) < 1) {
            wp_send_json_error('Tên thú cưng không được để trống.');
        }

        // Validate phone number format (Vietnamese phone numbers)
        $phone_digits = preg_replace('/\D/', '', $phone);
        if (strlen($phone_digits) !== 10) {
            wp_send_json_error('Số điện thoại phải có đúng 10 số.');
        }

        if (!preg_match('/^0[3-9]\d{8}$/', $phone_digits)) {
            wp_send_json_error('Số điện thoại không hợp lệ. Phải bắt đầu bằng 03, 05, 07, 08, hoặc 09.');
        }

        // Validate question scores
        $question_scores = [];
        foreach ($questions as $question_id => $score) {
            $question_id = intval($question_id);
            $score = intval($score);

            if ($question_id <= 0 || $score < 1 || $score > 5) {
                wp_send_json_error('Điểm đánh giá không hợp lệ.');
            }

            // Verify question exists
            $question = $this->database_manager->getQuestion($question_id);
            if (!$question) {
                wp_send_json_error('Câu hỏi không hợp lệ.');
            }

            $question_scores[$question_id] = $score;
        }

        // Insert review with separate fields
        $review_id = $this->database_manager->insertReview($customer_name, $phone, $notes, $question_scores, $pet_name, $overall_rating);

        if ($review_id) {
            wp_send_json_success([
                'message' => 'Cảm ơn bạn đã gửi phản hồi!',
                'review_id' => $review_id
            ]);
        } else {
            wp_send_json_error('Không thể lưu phản hồi. Vui lòng thử lại.');
        }
    }
}
