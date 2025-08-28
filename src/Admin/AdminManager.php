<?php

/**
 * File: src/Admin/AdminManager.php
 */

namespace CustomerFeedback\Admin;

use CustomerFeedback\Database\DatabaseManager;

class AdminManager
{
    private $database_manager;

    public function __construct()
    {
        $this->database_manager = new DatabaseManager();
        $this->init();
    }

    private function init()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_post_cf_add_question', [$this, 'handleAddQuestion']);
        add_action('admin_post_cf_edit_question', [$this, 'handleEditQuestion']);
        add_action('admin_post_cf_delete_question', [$this, 'handleDeleteQuestion']);
        add_action('admin_post_cf_delete_review', [$this, 'handleDeleteReview']);
        add_action('admin_post_cf_export_excel', [$this, 'handleExportExcel']);
    }

    public function addAdminMenu()
    {
        add_menu_page(
            'Phản hồi khách hàng',
            'Phản hồi khách hàng',
            'manage_options',
            'customer-feedback',
            [$this, 'questionsPage'],
            'dashicons-star-filled',
            30
        );

        add_submenu_page(
            'customer-feedback',
            'Câu hỏi đánh giá',
            'Câu hỏi đánh giá',
            'manage_options',
            'customer-feedback',
            [$this, 'questionsPage']
        );

        add_submenu_page(
            'customer-feedback',
            'Phản hồi của khách',
            'Phản hồi của khách',
            'manage_options',
            'customer-feedback-reviews',
            [$this, 'reviewsPage']
        );
    }

    public function questionsPage()
    {
        $action = $_GET['action'] ?? 'list';
        $question_id = $_GET['id'] ?? null;

        switch ($action) {
            case 'add':
                $this->renderAddQuestionForm();
                break;
            case 'edit':
                $this->renderEditQuestionForm($question_id);
                break;
            default:
                $this->renderQuestionsListPage();
                break;
        }
    }

    public function reviewsPage()
    {
        $action = $_GET['action'] ?? 'list';
        $review_id = $_GET['id'] ?? null;

        switch ($action) {
            case 'view':
                $this->renderReviewDetail($review_id);
                break;
            default:
                $this->renderReviewsListPage();
                break;
        }
    }

    private function renderQuestionsListPage()
    {
        $page = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $questions = $this->database_manager->getQuestions($per_page, $offset);
        $total = $this->database_manager->getQuestionsCount();
        $pages = ceil($total / $per_page);

        echo '<div class="wrap">';
        echo '<h1>Quản lý câu hỏi đánh giá</h1>';

        echo '<a href="' . admin_url('admin.php?page=customer-feedback&action=add') . '" class="button button-primary">Thêm câu hỏi mới</a>';

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>Nội dung câu hỏi</th>';
        echo '<th>Người tạo</th>';
        echo '<th>Ngày tạo</th>';
        echo '<th>Thao tác</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($questions as $question) {
            echo '<tr>';
            echo '<td>' . esc_html($question->id) . '</td>';
            echo '<td>' . esc_html($question->title) . '</td>';
            echo '<td>' . esc_html($question->created_by_name) . '</td>';
            echo '<td>' . esc_html($question->created_at) . '</td>';
            echo '<td>';
            echo '<a href="' . admin_url('admin.php?page=customer-feedback&action=edit&id=' . $question->id) . '" class="button button-small">Sửa</a> ';
            echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=cf_delete_question&id=' . $question->id), 'delete_question') . '" class="button button-small" onclick="return confirm(\'Bạn có chắc muốn xóa?\')">Xóa</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        if ($pages > 1) {
            echo '<div class="tablenav">';
            echo '<div class="tablenav-pages">';
            echo paginate_links([
                'current' => $page,
                'total' => $pages,
                'base' => admin_url('admin.php?page=customer-feedback&paged=%#%')
            ]);
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    private function renderAddQuestionForm()
    {
        echo '<div class="wrap">';
        echo '<h1>Thêm câu hỏi mới</h1>';
        echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
        wp_nonce_field('add_question');
        echo '<input type="hidden" name="action" value="cf_add_question">';
        echo '<table class="form-table">';
        echo '<tr><th scope="row"><label for="title">Nội dung câu hỏi</label></th>';
        echo '<td><input name="title" type="text" id="title" class="regular-text" required /></td></tr>';
        echo '</table>';
        echo '<p class="submit"><input type="submit" class="button button-primary" value="Thêm câu hỏi" /></p>';
        echo '</form>';
        echo '</div>';
    }

    private function renderEditQuestionForm($question_id)
    {
        $question = $this->database_manager->getQuestion($question_id);

        if (!$question) {
            wp_die('Không tìm thấy câu hỏi');
        }

        echo '<div class="wrap">';
        echo '<h1>Sửa câu hỏi</h1>';
        echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
        wp_nonce_field('edit_question');
        echo '<input type="hidden" name="action" value="cf_edit_question">';
        echo '<input type="hidden" name="id" value="' . esc_attr($question->id) . '">';
        echo '<table class="form-table">';
        echo '<tr><th scope="row"><label for="title">Nội dung câu hỏi</label></th>';
        echo '<td><input name="title" type="text" id="title" value="' . esc_attr($question->title) . '" class="regular-text" required /></td></tr>';
        echo '</table>';
        echo '<p class="submit"><input type="submit" class="button button-primary" value="Cập nhật câu hỏi" /></p>';
        echo '</form>';
        echo '</div>';
    }

    private function renderReviewsListPage()
    {
        $page = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $reviews = $this->database_manager->getReviews($per_page, $offset);
        $total = $this->database_manager->getReviewsCount();
        $pages = ceil($total / $per_page);

        echo '<div class="wrap">';
        echo '<h1>Quản lý phản hồi khách hàng</h1>';

        // Add export button
        echo '<div style="margin-bottom: 20px;">';
        echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=cf_export_excel'), 'export_excel') . '" class="button button-secondary" style="margin-right: 10px;">📊 Xuất Excel</a>';
        echo '</div>';

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>Tên khách hàng</th>';
        echo '<th>Tên thú cưng</th>';
        echo '<th>Số điện thoại</th>';
        echo '<th>Đánh giá tổng thể</th>';
        echo '<th>Tổng điểm</th>';
        echo '<th>Ngày tạo</th>';
        echo '<th>Thao tác</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($reviews as $review) {
            echo '<tr>';
            echo '<td>' . esc_html($review->id) . '</td>';
            echo '<td>' . esc_html($review->customer_name) . '</td>';
            echo '<td>' . esc_html($review->pet_name ?: 'N/A') . '</td>';
            echo '<td>' . esc_html($review->phone) . '</td>';
            echo '<td>';
            if ($review->overall_rating) {
                echo $this->renderStars($review->overall_rating) . ' (' . $review->overall_rating . '/5)';
            } else {
                echo 'N/A';
            }
            echo '</td>';
            echo '<td>' . esc_html($review->total_score) . '/' . (count($this->database_manager->getQuestions(100, 0)) * 5) . '</td>';
            echo '<td>' . esc_html($review->created_at) . '</td>';
            echo '<td>';
            echo '<a href="' . admin_url('admin.php?page=customer-feedback-reviews&action=view&id=' . $review->id) . '" class="button button-small">Xem</a> ';
            echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=cf_delete_review&id=' . $review->id), 'delete_review') . '" class="button button-small" onclick="return confirm(\'Bạn có chắc muốn xóa?\')">Xóa</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        if ($pages > 1) {
            echo '<div class="tablenav">';
            echo '<div class="tablenav-pages">';
            echo paginate_links([
                'current' => $page,
                'total' => $pages,
                'base' => admin_url('admin.php?page=customer-feedback-reviews&paged=%#%')
            ]);
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    private function renderReviewDetail($review_id)
    {
        $review = $this->database_manager->getReview($review_id);

        if (!$review) {
            wp_die('Không tìm thấy phản hồi');
        }

        echo '<div class="wrap">';
        echo '<h1>Chi tiết phản hồi</h1>';
        echo '<table class="form-table">';
        echo '<tr><th>Tên khách hàng</th><td>' . esc_html($review->customer_name) . '</td></tr>';
        if ($review->pet_name) {
            echo '<tr><th>Tên thú cưng</th><td>' . esc_html($review->pet_name) . '</td></tr>';
        }
        echo '<tr><th>Số điện thoại</th><td>' . esc_html($review->phone) . '</td></tr>';
        if ($review->overall_rating) {
            echo '<tr><th>Đánh giá tổng thể</th><td>' . $this->renderStars($review->overall_rating) . ' (' . $review->overall_rating . '/5)</td></tr>';
        }
        echo '<tr><th>Ghi chú</th><td>' . esc_html($review->notes) . '</td></tr>';
        $max_score = count($review->questions) * 5;
        echo '<tr><th>Tổng điểm</th><td>' . esc_html($review->total_score) . '/' . $max_score . '</td></tr>';
        echo '<tr><th>Ngày tạo</th><td>' . esc_html($review->created_at) . '</td></tr>';
        echo '</table>';

        echo '<h2>Chi tiết đánh giá từng tiêu chí</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Câu hỏi</th><th>Điểm</th><th>Đánh giá</th></tr></thead>';
        echo '<tbody>';

        foreach ($review->questions as $question) {
            echo '<tr>';
            echo '<td>' . esc_html($question->title) . '</td>';
            echo '<td>' . esc_html($question->score) . '/5</td>';
            echo '<td>' . $this->renderStars($question->score) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    private function renderStars($rating)
    {
        $output = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $output .= '<span style="color: #ffc107;">★</span>';
            } else {
                $output .= '<span style="color: #ddd;">★</span>';
            }
        }
        return $output;
    }

    // Handle form submissions
    public function handleAddQuestion()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'add_question')) {
            wp_die('Không có quyền thực hiện');
        }

        $title = $_POST['title'] ?? '';

        if (empty($title)) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&action=add&error=1'));
            exit;
        }

        $result = $this->database_manager->insertQuestion($title, get_current_user_id());

        if ($result) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&success=1'));
        } else {
            wp_redirect(admin_url('admin.php?page=customer-feedback&error=1'));
        }
        exit;
    }

    public function handleEditQuestion()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'edit_question')) {
            wp_die('Không có quyền thực hiện');
        }

        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';

        if (empty($id) || empty($title)) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&error=1'));
            exit;
        }

        $result = $this->database_manager->updateQuestion($id, $title, get_current_user_id());

        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&success=1'));
        } else {
            wp_redirect(admin_url('admin.php?page=customer-feedback&error=1'));
        }
        exit;
    }

    public function handleDeleteQuestion()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'delete_question')) {
            wp_die('Không có quyền thực hiện');
        }

        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&error=1'));
            exit;
        }

        $result = $this->database_manager->deleteQuestion($id);

        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=customer-feedback&success=1'));
        } else {
            wp_redirect(admin_url('admin.php?page=customer-feedback&error=1'));
        }
        exit;
    }

    public function handleDeleteReview()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'delete_review')) {
            wp_die('Không có quyền thực hiện');
        }

        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            wp_redirect(admin_url('admin.php?page=customer-feedback-reviews&error=1'));
            exit;
        }

        $result = $this->database_manager->deleteReview($id);

        if ($result !== false) {
            wp_redirect(admin_url('admin.php?page=customer-feedback-reviews&success=1'));
        } else {
            wp_redirect(admin_url('admin.php?page=customer-feedback-reviews&error=1'));
        }
        exit;
    }

    public function handleExportExcel()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'export_excel')) {
            wp_die('Không có quyền thực hiện');
        }

        // Get all reviews with their question details
        $reviews = $this->database_manager->getAllReviewsWithDetails();
        $questions = $this->database_manager->getQuestions(100, 0);

        if (empty($reviews)) {
            wp_redirect(admin_url('admin.php?page=customer-feedback-reviews&error=2'));
            exit;
        }

        // Set headers for Excel download
        $filename = 'customer-feedback-' . date('Y-m-d-H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Create header row
        $headers = [
            'ID',
            'Tên khách hàng',
            'Tên thú cưng',
            'Số điện thoại',
            'Đánh giá tổng thể',
            'Tổng điểm'
        ];

        // Add question headers
        foreach ($questions as $question) {
            $headers[] = $question->title;
        }

        $headers[] = 'Ghi chú';
        $headers[] = 'Ngày tạo';

        // Write header
        fputcsv($output, $headers);

        // Write data rows
        foreach ($reviews as $review) {
            $row = [
                $review->id,
                $review->customer_name,
                $review->pet_name ?: '',
                $review->phone,
                $review->overall_rating ?: '',
                $review->total_score
            ];

            // Add question scores
            $review_questions = $this->database_manager->getReviewQuestions($review->id);
            $question_scores = [];
            foreach ($review_questions as $rq) {
                $question_scores[$rq->question_id] = $rq->score;
            }

            foreach ($questions as $question) {
                $row[] = isset($question_scores[$question->id]) ? $question_scores[$question->id] : '';
            }

            $row[] = $review->notes;
            $row[] = $review->created_at;

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
