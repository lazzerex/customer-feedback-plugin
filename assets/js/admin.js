/**
 * File: assets/js/admin.js
 * Admin JavaScript for Customer Feedback plugin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeAdminFeatures();
    });

    function initializeAdminFeatures() {
        // Initialize data tables
        initializeDataTables();
        
        // Initialize form validation
        initializeFormValidation();
        
        // Initialize confirmation dialogs
        initializeConfirmationDialogs();
        
        // Initialize statistics
        //initializeStatistics();
        
        // Show admin notices
        showAdminNotices();
    }

    function initializeDataTables() {
        // Add sorting capabilities to tables
        $('.wp-list-table').each(function() {
            var $table = $(this);
            
            // Add click handlers for sortable columns
            $table.find('th').on('click', function() {
                var $th = $(this);
                var column = $th.index();
                var sortDirection = $th.hasClass('asc') ? 'desc' : 'asc';
                
                // Remove previous sort classes
                $table.find('th').removeClass('asc desc');
                $th.addClass(sortDirection);
                
                sortTable($table, column, sortDirection);
            });
        });
    }

    function sortTable($table, column, direction) {
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').get();
        
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(column).text().trim();
            var bVal = $(b).find('td').eq(column).text().trim();
            
            // Try to parse as numbers if possible
            var aNum = parseFloat(aVal);
            var bNum = parseFloat(bVal);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return direction === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // String comparison
            if (direction === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
        
        $tbody.empty().append(rows);
    }

    function initializeFormValidation() {
        // Real-time validation for question forms
        $('#title').on('input', function() {
            var $input = $(this);
            var value = $input.val().trim();
            
            if (value.length < 3) {
                showFieldError($input, 'Question title must be at least 3 characters long.');
            } else if (value.length > 255) {
                showFieldError($input, 'Question title must not exceed 255 characters.');
            } else {
                hideFieldError($input);
            }
        });

        // Form submission validation
        $('form').on('submit', function(e) {
            var $form = $(this);
            var isValid = true;
            
            // Validate required fields
            $form.find('input[required], textarea[required]').each(function() {
                var $field = $(this);
                if (!$field.val().trim()) {
                    showFieldError($field, 'This field is required.');
                    isValid = false;
                } else {
                    hideFieldError($field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAdminNotice('error', 'Please fix the errors below before submitting.');
            }
        });
    }

    function showFieldError($field, message) {
        var $errorDiv = $field.siblings('.field-error');
        
        if ($errorDiv.length === 0) {
            $errorDiv = $('<div class="field-error"></div>');
            $field.after($errorDiv);
        }
        
        $errorDiv.text(message).show();
        $field.addClass('error');
    }

    function hideFieldError($field) {
        $field.removeClass('error');
        $field.siblings('.field-error').hide();
    }

    function initializeConfirmationDialogs() {
        // Enhanced delete confirmation
        $('a[onclick*="confirm"]').on('click', function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var url = $link.attr('href');
            var type = $link.closest('tr').find('td:first').text();
            
            showDeleteConfirmation(function(confirmed) {
                if (confirmed) {
                    window.location.href = url;
                }
            }, type);
        });
    }

    function showDeleteConfirmation(callback, itemType) {
        var message = 'Are you sure you want to delete this ' + (itemType || 'item') + '? This action cannot be undone.';
        
        // Create custom confirmation dialog
        var $dialog = $('<div class="delete-confirmation-dialog">')
            .html(
                '<div class="dialog-content">' +
                '<div class="dialog-icon">⚠️</div>' +
                '<div class="dialog-message">' + message + '</div>' +
                '<div class="dialog-buttons">' +
                '<button class="button button-secondary dialog-cancel">Cancel</button>' +
                '<button class="button button-primary button-delete">Delete</button>' +
                '</div>' +
                '</div>'
            );
        
        $('body').append($dialog);
        
        // Handle button clicks
        $dialog.find('.dialog-cancel').on('click', function() {
            $dialog.remove();
            callback(false);
        });
        
        $dialog.find('.button-delete').on('click', function() {
            $dialog.remove();
            callback(true);
        });
        
        // Show dialog
        $dialog.fadeIn(200);
    }

    // function initializeStatistics() {
    //     // Load dashboard statistics if we're on the main page
    //     if (window.location.href.indexOf('page=customer-feedback') !== -1 && 
    //         window.location.href.indexOf('action=') === -1) {
    //         loadDashboardStats();
    //     }
    // }

    // function loadDashboardStats() {
    //     // This would typically load via AJAX, but for now we'll add placeholder
    //     var $statsContainer = $('.customer-feedback-stats');
        
    //     if ($statsContainer.length === 0) {
    //         var statsHtml = '<div class="customer-feedback-stats">' +
    //             '<div class="stat-box">' +
    //             '<span class="stat-number" id="total-questions">-</span>' +
    //             '<span class="stat-label">Total Questions</span>' +
    //             '</div>' +
    //             '<div class="stat-box">' +
    //             '<span class="stat-number" id="total-reviews">-</span>' +
    //             '<span class="stat-label">Total Reviews</span>' +
    //             '</div>' +
    //             '<div class="stat-box">' +
    //             '<span class="stat-number" id="avg-score">-</span>' +
    //             '<span class="stat-label">Average Score</span>' +
    //             '</div>' +
    //             '<div class="stat-box">' +
    //             '<span class="stat-number" id="this-month">-</span>' +
    //             '<span class="stat-label">This Month</span>' +
    //             '</div>' +
    //             '</div>';
            
    //         $('.wrap h1').after(statsHtml);
    //     }
    // }

    function showAdminNotices() {
        // Handle URL parameters for success/error messages
        var urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('success') === '1') {
            showAdminNotice('success', 'Operation completed successfully!');
        } else if (urlParams.get('error') === '1') {
            showAdminNotice('error', 'An error occurred. Please try again.');
        }
        
        // Auto-hide notices after 5 seconds
        setTimeout(function() {
            $('.notice').fadeOut();
        }, 5000);
    }

    function showAdminNotice(type, message) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible">')
            .html('<p>' + message + '</p>');
        
        $('.wrap h1').after($notice);
        
        // Add dismiss functionality
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut();
        });
    }

    // Add enhanced styles for admin interface
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .field-error {
                color: #d63638;
                font-size: 12px;
                margin-top: 5px;
                display: none;
            }
            
            .customer-feedback-form input.error,
            .customer-feedback-form textarea.error {
                border-color: #d63638 !important;
                box-shadow: 0 0 0 1px #d63638 !important;
            }
            
            .delete-confirmation-dialog {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 100000;
                display: none;
                justify-content: center;
                align-items: center;
            }
            
            .dialog-content {
                background: white;
                padding: 30px;
                border-radius: 5px;
                max-width: 400px;
                text-align: center;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            }
            
            .dialog-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            
            .dialog-message {
                margin-bottom: 20px;
                font-size: 16px;
                line-height: 1.5;
            }
            
            .dialog-buttons {
                display: flex;
                gap: 10px;
                justify-content: center;
            }
            
            .button-delete {
                background: #d63638 !important;
                border-color: #d63638 !important;
            }
            
            .button-delete:hover {
                background: #b32d2e !important;
                border-color: #b32d2e !important;
            }
            
            .wp-list-table th {
                cursor: pointer;
                user-select: none;
            }
            
            .wp-list-table th:hover {
                background: #e8e8e8;
            }
            
            .wp-list-table th.asc:after {
                content: " ↑";
            }
            
            .wp-list-table th.desc:after {
                content: " ↓";
            }
        `)
        .appendTo('head');

})(jQuery);