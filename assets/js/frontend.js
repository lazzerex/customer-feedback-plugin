/**
 * File: assets/js/frontend.js
 * Frontend JavaScript for Customer Feedback forms
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeCustomerFeedback();
    });

    function initializeCustomerFeedback() {
        // Star rating functionality
        $('.rating-stars').on('click', '.star', function() {
            var $this = $(this);
            var value = $this.data('value');
            var $container = $this.parent();
            var questionId = $container.data('question-id');
            
            // Update visual state
            updateStarRating($container, value);
            
            // Update hidden input
            $('input[name="questions[' + questionId + ']"]').val(value);
            
            // Remove error styling if present
            $container.removeClass('error');
        });

        // Hover effect for stars
        $('.rating-stars').on('mouseenter', '.star', function() {
            var $this = $(this);
            var value = $this.data('value');
            var $container = $this.parent();
            
            updateStarHover($container, value);
        });

        $('.rating-stars').on('mouseleave', function() {
            $(this).find('.star').removeClass('hover');
        });

        // Form submission
        $('#customer-feedback-form').on('submit', handleFormSubmission);

        // Input validation on blur
        $('input[required]').on('blur', function() {
            validateField($(this));
        });

        // Phone number formatting
        $('input[type="tel"]').on('input', function() {
            formatPhoneNumber($(this));
        });
    }

    function updateStarRating($container, value) {
        $container.find('.star').removeClass('active');
        $container.find('.star').each(function(index) {
            if (index < value) {
                $(this).addClass('active');
            }
        });
    }

    function updateStarHover($container, value) {
        $container.find('.star').removeClass('hover');
        $container.find('.star').each(function(index) {
            if (index < value) {
                $(this).addClass('hover');
            }
        });
    }

    function validateField($field) {
        var isValid = true;
        var value = $field.val().trim();

        if ($field.prop('required') && !value) {
            isValid = false;
        }

        if ($field.attr('type') === 'tel' && value) {
            var phoneRegex = /^[0-9+\-\s\(\)]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
            }
        }

        if (isValid) {
            $field.removeClass('error').addClass('valid');
        } else {
            $field.removeClass('valid').addClass('error');
        }

        return isValid;
    }

    function formatPhoneNumber($input) {
        var value = $input.val().replace(/\D/g, '');
        var formattedValue = value;

        // Simple formatting for Vietnamese phone numbers
        if (value.length >= 10) {
            if (value.startsWith('84')) {
                // International format: +84 xxx xxx xxx
                formattedValue = '+84 ' + value.substring(2, 5) + ' ' + 
                               value.substring(5, 8) + ' ' + value.substring(8);
            } else if (value.startsWith('0')) {
                // Domestic format: 0xxx xxx xxx
                formattedValue = value.substring(0, 4) + ' ' + 
                               value.substring(4, 7) + ' ' + value.substring(7);
            }
        }

        $input.val(formattedValue);
    }

    function handleFormSubmission(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $container = $('.customer-feedback-form-container');
        var $submitButton = $('.submit-button');
        
        // Prevent multiple submissions
        if ($submitButton.prop('disabled') || $container.hasClass('loading')) {
            return false;
        }
        
        // Validate all fields
        if (!validateForm($form)) {
            showMessage('error', customer_feedback_text.validation_error || 'Please fill in all required fields correctly.');
            return;
        }

        // Show loading state immediately
        showLoadingState($container, true);

        // Prepare form data
        var formData = {
            action: 'submit_feedback',
            nonce: customer_feedback_ajax.nonce,
            customer_name: $('#customer_name').val().trim(),
            phone: $('#phone').val().trim(),
            notes: $('#notes').val().trim(),
            questions: getQuestionScores(),
            timestamp: Date.now() // Add timestamp for additional duplicate prevention
        };

        // Submit form data with additional safeguards
        $.ajax({
            url: customer_feedback_ajax.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000, // 30 seconds timeout
            cache: false, // Prevent caching
            beforeSend: function(xhr) {
                // Additional header to prevent caching
                xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
                xhr.setRequestHeader('Pragma', 'no-cache');
                xhr.setRequestHeader('Expires', '0');
            },
            success: function(response) {
                handleSubmissionSuccess(response, $form);
            },
            error: function(xhr, status, error) {
                handleSubmissionError(xhr, status, error);
            },
            complete: function() {
                showLoadingState($container, false);
            }
        });
    }

    function validateForm($form) {
        var isValid = true;
        
        // Validate text inputs
        $form.find('input[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        // Validate question ratings
        $form.find('.question-score').each(function() {
            var $input = $(this);
            var $container = $input.closest('.question-group');
            
            if (!$input.val()) {
                $container.find('.rating-stars').addClass('error');
                isValid = false;
            } else {
                $container.find('.rating-stars').removeClass('error');
            }
        });

        return isValid;
    }

    function getQuestionScores() {
        var scores = {};
        $('.question-score').each(function() {
            var $input = $(this);
            var name = $input.attr('name');
            var match = name.match(/questions\[(\d+)\]/);
            
            if (match && $input.val()) {
                scores[match[1]] = parseInt($input.val());
            }
        });
        return scores;
    }

    function handleSubmissionSuccess(response, $form) {
        if (response.success) {
            showMessage('success', response.data.message || 'Thank you for your feedback!');
            resetForm($form);
            
            // Scroll to success message
            $('html, body').animate({
                scrollTop: $('.feedback-success').offset().top - 100
            }, 500);
            
        } else {
            showMessage('error', response.data || 'An error occurred. Please try again.');
        }
    }

    function handleSubmissionError(xhr, status, error) {
        var message = 'An error occurred. Please try again.';
        
        if (status === 'timeout') {
            message = 'Request timed out. Please check your connection and try again.';
        } else if (xhr.responseJSON && xhr.responseJSON.data) {
            message = xhr.responseJSON.data;
        }
        
        showMessage('error', message);
    }

    function showMessage(type, message) {
        var $successMsg = $('.feedback-success');
        var $errorMsg = $('.feedback-error');
        
        if (type === 'success') {
            $successMsg.html(message).show();
            $errorMsg.hide();
        } else {
            $errorMsg.html(message).show();
            $successMsg.hide();
        }
    }

    function showLoadingState($container, loading) {
        var $button = $('.submit-button');
        var originalText = $button.data('original-text') || $button.text();
        
        if (!$button.data('original-text')) {
            $button.data('original-text', originalText);
        }
        
        if (loading) {
            $container.addClass('loading');
            $button.prop('disabled', true).html('Submitting...');
            
            // Disable the entire form to prevent any interaction
            $container.find('input, textarea, button').prop('disabled', true);
            
            // Add visual indicator
            $container.css('pointer-events', 'none');
        } else {
            $container.removeClass('loading');
            $button.prop('disabled', false).html(originalText);
            
            // Re-enable form elements
            $container.find('input, textarea').prop('disabled', false);
            $container.css('pointer-events', 'auto');
        }
    }

    function resetForm($form) {
        $form[0].reset();
        $('.star').removeClass('active');
        $('.question-score').val('');
        $form.find('.error').removeClass('error');
        $form.find('.valid').removeClass('valid');
        
        // Re-enable submit button after reset
        $('.submit-button').prop('disabled', false);
    }

    // Add debouncing for form submission
    var submitTimeout;
    function debounceSubmit(callback, delay) {
        clearTimeout(submitTimeout);
        submitTimeout = setTimeout(callback, delay);
    }

    // Modify form submission to include debouncing
    $('#customer-feedback-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        
        // Debounce submission by 300ms to prevent accidental double-clicks
        debounceSubmit(function() {
            handleFormSubmission.call($form[0], e);
        }, 300);
    });

    // Add CSS for validation states
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .customer-feedback-form input.error,
            .customer-feedback-form textarea.error {
                border-color: #e74c3c !important;
                box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.2) !important;
            }
            
            .customer-feedback-form input.valid,
            .customer-feedback-form textarea.valid {
                border-color: #27ae60 !important;
                box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.2) !important;
            }
            
            .rating-stars.error .star {
                color: #e74c3c !important;
                animation: shake 0.5s ease-in-out;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-2px); }
                75% { transform: translateX(2px); }
            }
        `)
        .appendTo('head');

})(jQuery);