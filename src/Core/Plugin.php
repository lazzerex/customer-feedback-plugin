<?php
/**
 * File: src/Core/Plugin.php
 */

namespace CustomerFeedback\Core;

use CustomerFeedback\Database\DatabaseManager;
use CustomerFeedback\Admin\AdminManager;
use CustomerFeedback\Frontend\ShortcodeManager;

class Plugin
{
    private static $instance = null;
    private $database_manager;
    private $admin_manager;
    private $shortcode_manager;

    private function __construct()
    {
        $this->init();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init()
    {
        // Initialize database manager
        $this->database_manager = new DatabaseManager();

        // Initialize admin manager
        if (is_admin()) {
            $this->admin_manager = new AdminManager();
        }

        // Initialize shortcode manager
        $this->shortcode_manager = new ShortcodeManager();

        // Load text domain
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    public function loadTextDomain()
    {
        load_plugin_textdomain(
            'customer-feedback',
            false,
            dirname(plugin_basename(CUSTOMER_FEEDBACK_PLUGIN_FILE)) . '/languages/'
        );
    }

    public function enqueueScripts()
    {
        wp_enqueue_style(
            'customer-feedback-frontend',
            CUSTOMER_FEEDBACK_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            filemtime(CUSTOMER_FEEDBACK_PLUGIN_DIR . 'assets/css/frontend.css') // Version lấy theo giờ cập nhật file -> user không cần xoá cache trình duyệt
        );

        wp_enqueue_script(
            'customer-feedback-frontend',
            CUSTOMER_FEEDBACK_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            filemtime(CUSTOMER_FEEDBACK_PLUGIN_DIR . 'assets/js/frontend.js'), // Version lấy theo giờ cập nhật file -> user không cần xoá cache trình duyệt
            true
        );

        wp_localize_script('customer-feedback-frontend', 'customer_feedback_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('customer_feedback_nonce')
        ]);
    }

    public function enqueueAdminScripts()
    {
        wp_enqueue_style(
            'customer-feedback-admin',
            CUSTOMER_FEEDBACK_PLUGIN_URL . 'assets/css/admin.css',
            [],
            CUSTOMER_FEEDBACK_VERSION
        );

        wp_enqueue_script(
            'customer-feedback-admin',
            CUSTOMER_FEEDBACK_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            CUSTOMER_FEEDBACK_VERSION,
            true
        );
    }

    public function getDatabaseManager()
    {
        return $this->database_manager;
    }

    public function getAdminManager()
    {
        return $this->admin_manager;
    }

    public function getShortcodeManager()
    {
        return $this->shortcode_manager;
    }
}