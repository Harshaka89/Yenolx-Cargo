/**
 * Frontend JavaScript for Yenolx Cargo Service
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize form validation
        initFormValidation();
        
        // Initialize animations
        initAnimations();
        
        // Initialize responsive behavior
        initResponsiveBehavior();
    });

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('.yenolx-tooltip').each(function() {
            var $tooltip = $(this);
            var $tooltipText = $tooltip.find('.yenolx-tooltip-text');
            
            $tooltip.on('mouseenter', function() {
                $tooltipText.css('visibility', 'visible').css('opacity', '1');
            });
            
            $tooltip.on('mouseleave', function() {
                $tooltipText.css('visibility', 'hidden').css('opacity', '0');
            });
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // Add real-time validation
        $('input[required], select[required], textarea[required]').on('blur', function() {
            validateField($(this));
        });
        
        // Validate form on submit
        $('form').on('submit', function(e) {
            var $form = $(this);
            var isValid = true;
            
            $form.find('[required]').each(function() {
                var $field = $(this);
                if (!validateField($field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                scrollToFirstError();
            }
        });
    }

    /**
     * Validate individual field
     */
    function validateField($field) {
        var value = $field.val().trim();
        var fieldType = $field.attr('type');
        var isValid = true;
        var errorMessage = '';
        
        // Remove previous error state
        $field.closest('.yenolx-form-group').removeClass('error');
        $field.closest('.yenolx-form-group').find('.yenolx-form-error').remove();
        
        // Check if field is empty
        if (!value) {
            isValid = false;
            errorMessage = 'This field is required.';
        } else {
            // Field-specific validation
            switch (fieldType) {
                case 'email':
                    if (!isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                    break;
                case 'tel':
                    if (!isValidPhone(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number.';
                    }
                    break;
                case 'number':
                    if (!isValidNumber(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid number.';
                    }
                    break;
            }
        }
        
        // Show error if not valid
        if (!isValid) {
            $field.closest('.yenolx-form-group').addClass('error');
            $field.closest('.yenolx-form-group').append('<div class="yenolx-form-error">' + errorMessage + '</div>');
        }
        
        return isValid;
    }

    /**
     * Check if email is valid
     */
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Check if phone is valid
     */
    function isValidPhone(phone) {
        // Remove all non-digit characters
        var phoneDigits = phone.replace(/\D/g, '');
        return phoneDigits.length >= 10;
    }

    /**
     * Check if number is valid
     */
    function isValidNumber(number) {
        return !isNaN(number) && number > 0;
    }

    /**
     * Scroll to first error
     */
    function scrollToFirstError() {
        var $firstError = $('.yenolx-form-group.error').first();
        if ($firstError.length) {
            $('html, body').animate({
                scrollTop: $firstError.offset().top - 100
            }, 500);
        }
    }

    /**
     * Initialize animations
     */
    function initAnimations() {
        // Fade in elements on scroll
        $(window).on('scroll', function() {
            var windowHeight = $(window).height();
            var scrollTop = $(window).scrollTop();
            
            $('.yenolx-fade-in').each(function() {
                var $element = $(this);
                var elementTop = $element.offset().top;
                
                if (elementTop < scrollTop + windowHeight - 100) {
                    $element.addClass('animated');
                }
            });
        });
        
        // Trigger scroll event on load
        $(window).trigger('scroll');
    }

    /**
     * Initialize responsive behavior
     */
    function initResponsiveBehavior() {
        // Mobile menu toggle
        $('.yenolx-mobile-menu-toggle').on('click', function() {
            $('.yenolx-mobile-menu').slideToggle();
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            // Reset mobile menu on desktop
            if ($(window).width() > 768) {
                $('.yenolx-mobile-menu').hide();
            }
        });
    }

    /**
     * Show loading state
     */
    function showLoading($element) {
        $element.prop('disabled', true);
        $element.html('<span class="yenolx-loading"></span> Loading...');
    }

    /**
     * Hide loading state
     */
    function hideLoading($element, originalText) {
        $element.prop('disabled', false);
        $element.html(originalText);
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'success') {
        var notificationHtml = '<div class="yenolx-notice ' + type + ' yenolx-fade-in">' + message + '</div>';
        
        // Insert notification at the top of the container
        $('.yenolx-container').prepend(notificationHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.yenolx-notice').first().fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount, currencySymbol = '€') {
        return currencySymbol + parseFloat(amount).toFixed(2);
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        var timeout;
        return function executedFunction(...args) {
            var later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Throttle function
     */
    function throttle(func, limit) {
        var inThrottle;
        return function() {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() {
                    inThrottle = false;
                }, limit);
            }
        };
    }

    // Make functions available globally
    window.yenolx = {
        showLoading: showLoading,
        hideLoading: hideLoading,
        showNotification: showNotification,
        formatCurrency: formatCurrency,
        formatDate: formatDate,
        debounce: debounce,
        throttle: throttle
    };

})(jQuery);