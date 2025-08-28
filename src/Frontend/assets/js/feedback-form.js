// FIXED: Floating Labels - Keep Original Position During Validation
jQuery(document).ready(function ($) {

    // Enhanced function to check if SPECIFIC input has value and add class for floating labels
    function checkInputValue($input) {
        var inputId = $input.attr('id');
        if ($input.val().trim() !== '') {
            $input.addClass('has-value');
        } else {
            $input.removeClass('has-value');
            // IMPORTANT: Don't add 'has-value' if input is empty, even during validation
        }
    }

    // Initialize floating labels on page load - Check each input independently
    $('#customer_name, #pet_name, #phone, #notes').each(function () {
        checkInputValue($(this));
    });

    // Handle input events for floating labels - FIXED: Individual handlers
    $('#customer_name').on('input blur', function () {
        checkInputValue($(this));
    });

    $('#pet_name').on('input blur', function () {
        checkInputValue($(this));
    });

    $('#phone').on('input blur', function () {
        checkInputValue($(this));
    });

    $('#notes').on('input blur', function () {
        checkInputValue($(this));
    });

    // Handle focus events for floating labels - FIXED: Only add was-focused on actual user focus
    $('#customer_name').on('focus', function () {
        $(this).parent().addClass('focused');
        $(this).addClass('was-focused'); // Add permanent class once focused by USER
    });

    $('#customer_name').on('blur', function () {
        $(this).parent().removeClass('focused');
        checkInputValue($(this));
        // Don't remove 'was-focused' class - keep label up permanently
    });

    $('#pet_name').on('focus', function () {
        $(this).parent().addClass('focused');
        $(this).addClass('was-focused'); // Add permanent class once focused by USER
    });

    $('#pet_name').on('blur', function () {
        $(this).parent().removeClass('focused');
        checkInputValue($(this));
        // Don't remove 'was-focused' class - keep label up permanently
    });

    $('#phone').on('focus', function () {
        $(this).parent().addClass('focused');
        $(this).addClass('was-focused'); // Add permanent class once focused by USER
    });

    $('#phone').on('blur', function () {
        $(this).parent().removeClass('focused');
        checkInputValue($(this));
        // Don't remove 'was-focused' class - keep label up permanently
    });

    $('#notes').on('focus', function () {
        $(this).parent().addClass('focused');
        $(this).addClass('was-focused'); // Add permanent class once focused by USER
    });

    $('#notes').on('blur', function () {
        $(this).parent().removeClass('focused');
        checkInputValue($(this));
        // Don't remove 'was-focused' class - keep label up permanently
    });

    // Disable browser's default validation messages
    $('input[required]').on('invalid', function (e) {
        e.preventDefault();
        this.setCustomValidity('');
    });

    // Clear custom validity on input - FIXED: Individual handlers
    $('#customer_name').on('input', function () {
        this.setCustomValidity('');
        $(this).removeClass('error');
        checkInputValue($(this));
    });

    $('#pet_name').on('input', function () {
        this.setCustomValidity('');
        $(this).removeClass('error');
        checkInputValue($(this));
    });

    $('#phone').on('input', function () {
        this.setCustomValidity('');
        $(this).removeClass('error');
        checkInputValue($(this));
    });

    // Overall rating selection
    $('.emotion-option').on('click', function () {
        $('.emotion-option').removeClass('selected');
        $(this).addClass('selected');
        $('#overall_rating').val($(this).data('value'));

        // Remove error styling when user selects
        $('.overall-rating-section').removeClass('error');
        clearErrorMessage();
    });

    // Question rating selection - allow clicking on rating cell
    $('.rating-cell').on('click', function () {
        var $radio = $(this).find('input[type="radio"]');
        $radio.prop('checked', true);

        // Remove error styling from the question row
        $(this).closest('.grid-row').removeClass('error');
        clearErrorMessage();
    });

    $('input[name^="questions"]').on('change', function () {
        // Remove error styling from the question row
        $(this).closest('.grid-row').removeClass('error');
        clearErrorMessage();
    });

    // Enhanced field validation with floating labels - FIXED: Don't trigger label movement
    function validateField($field) {
        var fieldName = $field.attr('id');
        var value = $field.val().trim();
        var isValid = true;
        var errorMessage = '';

        // Remove previous error styling for this specific field
        $field.removeClass('error');

        if ($field.prop('required') && !value) {
            isValid = false;
            errorMessage = 'Vui lòng điền thông tin vào ô còn thiếu';
        } else if (fieldName === 'phone' && value) {
            // Phone validation
            var phoneDigits = value.replace(/\D/g, '');
            if (phoneDigits.length !== 10) {
                isValid = false;
                errorMessage = 'Số điện thoại phải có đúng 10 số';
            } else if (!phoneDigits.match(/^0[3-9]\d{8}$/)) {
                isValid = false;
                errorMessage = 'Số điện thoại không hợp lệ (phải bắt đầu bằng 03, 05, 07, 08, 09)';
            }
        } else if (fieldName === 'customer_name' && value) {
            // Name validation
            if (value.length < 2) {
                isValid = false;
                errorMessage = 'Tên phải có ít nhất 2 ký tự';
            } else if (!value.match(/^[a-zA-ZÀ-ỹ\s]+$/)) {
                isValid = false;
                errorMessage = 'Tên chỉ được chứa chữ cái và khoảng trắng';
            }
        } else if (fieldName === 'pet_name' && value) {
            // Pet name validation
            if (value.length < 1) {
                isValid = false;
                errorMessage = 'Tên thú cưng không được để trống';
            }
        }

        if (!isValid) {
            $field.addClass('error');
            showFieldError($field, errorMessage);
        } else {
            $field.removeClass('error');
            hideFieldError($field);
        }

        // FIXED: Update floating label state for this specific field - but don't force movement
        checkInputValue($field);

        return isValid;
    }

    // Enhanced phone number formatting - FIXED: Only affects phone field
    $('#phone').on('input', function () {
        var value = $(this).val().replace(/\D/g, '');

        // Limit to 10 digits for Vietnamese phone numbers
        if (value.length > 10) {
            value = value.substring(0, 10);
        }

        // Format phone number
        if (value.length >= 3) {
            if (value.length <= 6) {
                value = value.replace(/(\d{3})(\d+)/, '$1 $2');
            } else {
                value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1 $2 $3');
            }
        }

        $(this).val(value);
        validateField($(this));
        checkInputValue($(this)); // Update floating label for phone only
    });

    // Real-time validation for text inputs - FIXED: Individual handlers
    $('#customer_name').on('input', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    $('#pet_name').on('input', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    $('#notes').on('input', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    // Input field validation on blur - FIXED: Individual handlers
    $('#customer_name').on('blur', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    $('#pet_name').on('blur', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    $('#phone').on('blur', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    $('#notes').on('blur', function () {
        validateField($(this));
        checkInputValue($(this));
    });

    function showFieldError($field, message) {
        hideFieldError($field); // Remove existing error first

        var $errorDiv = $('<div class="field-error-message">' + message + '</div>');
        $field.parent().append($errorDiv);
    }

    function hideFieldError($field) {
        $field.parent().find('.field-error-message').remove();
    }

    function clearErrorMessage() {
        $('.feedback-error').hide();
    }

    // FIXED: Enhanced form submission - DON'T trigger floating labels during validation
    $('#vet-feedback-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $container = $('.vet-feedback-container');
        var $submitButton = $('.submit-button');
        var validationErrors = [];
        var isValid = true;
        var firstErrorElement = null;

        // Clear previous error messages
        $('.feedback-error').hide();
        $('.feedback-success').hide();
        $('.error').removeClass('error');
        $('.field-error-message').remove();

        // FIXED: Validate each input field individually WITHOUT triggering label movement
        var fieldsToValidate = [
            { selector: '#customer_name', name: 'Tên khách hàng' },
            { selector: '#pet_name', name: 'Tên thú cưng' },
            { selector: '#phone', name: 'Số điện thoại' }
        ];

        fieldsToValidate.forEach(function (field) {
            var $field = $(field.selector);
            var value = $field.val().trim();

            // IMPORTANT: Only check input value, don't force label state
            if ($field.prop('required') && !value) {
                // Add error but DON'T trigger checkInputValue() that might move label
                $field.addClass('error');
                validationErrors.push('Vui lòng điền ' + field.name);
                if (!firstErrorElement) firstErrorElement = $field;
                isValid = false;
            } else {
                // Field has value, validate normally
                if (!validateField($field)) {
                    if (!firstErrorElement) firstErrorElement = $field;
                    isValid = false;
                }
            }
        });

        // Validate overall rating
        var overallRating = $('#overall_rating').val();
        if (!overallRating) {
            validationErrors.push('Vui lòng chọn đánh giá tổng thể');
            $('.overall-rating-section').addClass('error');
            if (!firstErrorElement) firstErrorElement = $('.overall-rating-section');
            isValid = false;
        }

        // Validate question ratings
        var questionCount = $('.grid-row').length;
        var answeredQuestions = 0;
        var unansweredQuestions = [];

        $('.grid-row').each(function (index) {
            var $row = $(this);
            var hasAnswer = $row.find('input[name^="questions"]:checked').length > 0;

            if (hasAnswer) {
                answeredQuestions++;
                $row.removeClass('error');
            } else {
                $row.addClass('error');
                unansweredQuestions.push(index + 1);
                if (!firstErrorElement) firstErrorElement = $row;
                isValid = false;
            }
        });

        if (unansweredQuestions.length > 0) {
            if (unansweredQuestions.length === questionCount) {
                validationErrors.push('Vui lòng trả lời tất cả các câu hỏi đánh giá');
            } else {
                validationErrors.push('Vui lòng trả lời câu hỏi số: ' + unansweredQuestions.join(', '));
            }
        }

        // Show validation errors
        if (!isValid) {
            var errorMessage = validationErrors.length === 1 ?
                validationErrors[0] :
                'Vui lòng điền thông tin vào ô còn thiếu:<br>• ' + validationErrors.join('<br>• ');

            $('.feedback-error').html(errorMessage).show();

            // Scroll to first error element
            if (firstErrorElement) {
                $('html, body').animate({
                    scrollTop: firstErrorElement.offset().top - 100
                }, 500);
            }

            return;
        }

        // Show loading state
        $container.addClass('loading');
        $submitButton.addClass('loading').prop('disabled', true);
        var originalText = $submitButton.text();
        $submitButton.data('original-text', originalText);

        // Prepare form data
        var formData = {
            action: 'submit_feedback',
            nonce: customer_feedback_ajax.nonce,
            customer_name: $('#customer_name').val().trim(),
            pet_name: $('#pet_name').val().trim(),
            phone: $('#phone').val().trim(),
            notes: $('#notes').val().trim(),
            overall_rating: overallRating,
            questions: getQuestionScores()
        };

        // Submit form data
        $.ajax({
            url: customer_feedback_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Hide the general feedback messages
                    $('.feedback-success').hide();
                    $('.feedback-error').hide();

                    // Show success message right below submit button
                    $('.submit-success-message').show();

                    // Reset form but keep labels up (sticky behavior)
                    $form[0].reset();
                    $('.emotion-option').removeClass('selected');
                    $('input[name^="questions"]').prop('checked', false);
                    $('.error').removeClass('error');
                    $('.field-error-message').remove();

                    // Reset star ratings
                    $('.star-rating-cell').removeClass('filled');
                    $('.star').css('color', '#ddd');

                    $('.error').removeClass('error');
                    $('.field-error-message').remove();

                    // Reset floating label states but keep 'was-focused' for sticky behavior
                    $('#customer_name, #pet_name, #phone, #notes').removeClass('has-value');
                    // Note: We keep 'was-focused' class so labels stay up if they were previously focused

                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: $('.submit-success-message').offset().top - 400
                    }, 500);
                } else {
                    $('.feedback-error').html(response.data || 'Có lỗi xảy ra. Vui lòng thử lại.').show();
                    $('.feedback-success').hide();
                    $('.submit-success-message').hide();
                }
            },
            error: function () {
                $('.feedback-error').html('Có lỗi xảy ra. Vui lòng thử lại.').show();
                $('.feedback-success').hide();
                $('.submit-success-message').hide();
            },
            complete: function () {
                // Restore button state
                $container.removeClass('loading');
                $submitButton.removeClass('loading').prop('disabled', false);
                var originalText = $submitButton.data('original-text');
                if (originalText) {
                    $submitButton.text(originalText);
                }
            }
        });
    });

    function getQuestionScores() {
        var scores = {};
        $('input[name^="questions"]:checked').each(function () {
            var name = $(this).attr('name');
            var match = name.match(/questions\[(\d+)\]/);
            if (match) {
                scores[match[1]] = $(this).val();
            }
        });
        return scores;
    }

    // Star Rating Functionality
    function updateStarRating($wrapper, selectedValue) {
        $wrapper.find('.star-rating-cell').each(function (index) {
            const starValue = index + 1;
            if (starValue <= selectedValue) {
                $(this).addClass('filled');
            } else {
                $(this).removeClass('filled');
            }
        });
    }

    // Star rating click handler
    $('.star-rating-cell').on('click', function () {
        const $wrapper = $(this).closest('.star-rating-wrapper');
        const selectedValue = parseInt($(this).data('value'));
        const $radio = $(this).find('input[type="radio"]');

        // Check the radio button
        $radio.prop('checked', true);

        // Update visual stars
        updateStarRating($wrapper, selectedValue);

        // Remove error styling
        $(this).closest('.grid-row').removeClass('error');
        clearErrorMessage();
    });

    // Enhanced hover effect that properly handles mobile
    $('.star-rating-cell').on('mouseenter', function () {
        const $wrapper = $(this).closest('.star-rating-wrapper');
        const hoverValue = parseInt($(this).data('value'));

        // Only apply hover effects on non-touch devices
        if (!('ontouchstart' in window)) {
            // Reset all stars in this wrapper only
            $wrapper.find('.star').css('color', '#ddd');

            // Apply hover color from left to hover position
            $wrapper.find('.star-rating-cell').each(function (index) {
                const starValue = index + 1;
                if (starValue <= hoverValue) {
                    $(this).find('.star').css('color', 'rgba(255, 193, 7, 0.5)');
                }
            });
        }
    });

    $('.star-rating-wrapper').on('mouseleave', function () {
        // Only apply mouseleave effects on non-touch devices
        if (!('ontouchstart' in window)) {
            const $wrapper = $(this);
            // Reset all stars to default
            $wrapper.find('.star').css('color', '#ddd');
            // Restore selected stars to full color
            $wrapper.find('.star-rating-cell.filled .star').css('color', '#ffb300');
        }
    });

    // Enhanced star rating click handler
    $('.star-rating-cell').on('click', function () {
        const $wrapper = $(this).closest('.star-rating-wrapper');
        const selectedValue = parseInt($(this).data('value'));
        const $radio = $(this).find('input[type="radio"]');

        // Check the radio button
        $radio.prop('checked', true);

        // Update visual stars for this wrapper only
        updateStarRating($wrapper, selectedValue);

        // Remove error styling
        $(this).closest('.grid-row').removeClass('error');
        clearErrorMessage();

        // Force restore proper colors for all wrappers to fix the mobile issue
        $('.star-rating-wrapper').each(function () {
            const $currentWrapper = $(this);
            const $checkedRadio = $currentWrapper.find('input[type="radio"]:checked');

            if ($checkedRadio.length > 0) {
                const checkedValue = parseInt($checkedRadio.val());
                updateStarRating($currentWrapper, checkedValue);
            } else {
                // No selection, reset to default
                $currentWrapper.find('.star').css('color', '#ddd');
                $currentWrapper.find('.star-rating-cell').removeClass('filled');
            }
        });
    });

    // Enhanced updateStarRating function
    function updateStarRating($wrapper, selectedValue) {
        $wrapper.find('.star-rating-cell').each(function (index) {
            const starValue = index + 1;
            const $star = $(this).find('.star');

            if (starValue <= selectedValue) {
                $(this).addClass('filled');
                $star.css('color', '#ffb300'); // Solid gold color for selected
            } else {
                $(this).removeClass('filled');
                $star.css('color', '#ddd'); // Gray for unselected
            }
        });
    }
});