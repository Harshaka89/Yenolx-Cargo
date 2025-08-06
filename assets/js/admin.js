/**
 * Admin JavaScript for Yenolx Cargo Service
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        
        // Initialize date pickers
        initDatePickers();
        
        // Initialize confirmations
        initConfirmations();
        
        // Initialize AJAX forms
        initAjaxForms();
        
        // Initialize tabs
        initTabs();
        
        // Initialize charts
        initCharts();
        
        // Initialize bulk actions
        initBulkActions();
        
        // Initialize search/filter
        initSearchFilter();
    });

    /**
     * Initialize date pickers
     */
    function initDatePickers() {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true
        });
        
        // Date range validation
        $('.date-range-start').on('change', function() {
            var startDate = $(this).val();
            var $endDate = $('.date-range-end');
            
            if (startDate) {
                $endDate.datepicker('option', 'minDate', startDate);
            }
        });
        
        $('.date-range-end').on('change', function() {
            var endDate = $(this).val();
            var $startDate = $('.date-range-start');
            
            if (endDate) {
                $startDate.datepicker('option', 'maxDate', endDate);
            }
        });
    }

    /**
     * Initialize confirmations
     */
    function initConfirmations() {
        // Delete confirmations
        $('.yenolx-delete-confirm').on('click', function(e) {
            var message = $(this).data('confirm-message') || 'Are you sure you want to delete this item?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
        
        // Status change confirmations
        $('.yenolx-status-confirm').on('change', function() {
            var newStatus = $(this).val();
            var $form = $(this).closest('form');
            
            if (newStatus && newStatus !== '') {
                var message = 'Are you sure you want to change the status to "' + newStatus + '"?';
                
                if (!confirm(message)) {
                    $(this).val($form.find('input[name="original_status"]').val());
                }
            }
        });
    }

    /**
     * Initialize AJAX forms
     */
    function initAjaxForms() {
        $('.yenolx-ajax-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
            var originalText = $submitBtn.html();
            
            // Show loading state
            showLoading($submitBtn);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=' + $form.data('ajax-action'),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showNotification(response.data.message || 'Action completed successfully.', 'success');
                        
                        // Reload or redirect if needed
                        if (response.data.reload) {
                            location.reload();
                        } else if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                        
                        // Reset form if needed
                        if ($form.data('reset-on-success')) {
                            $form[0].reset();
                        }
                    } else {
                        // Show error message
                        showNotification(response.data.message || 'An error occurred.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    showNotification('An error occurred. Please try again.', 'error');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    // Hide loading state
                    hideLoading($submitBtn, originalText);
                }
            });
        });
    }

    /**
     * Initialize tabs
     */
    function initTabs() {
        $('.yenolx-tabs').each(function() {
            var $tabs = $(this);
            var $tabContents = $tabs.next('.yenolx-tab-contents');
            
            // Tab click handler
            $tabs.find('.yenolx-tab').on('click', function() {
                var $tab = $(this);
                var tabId = $tab.data('tab');
                
                // Remove active class from all tabs and contents
                $tabs.find('.yenolx-tab').removeClass('active');
                $tabContents.find('.yenolx-tab-content').removeClass('active');
                
                // Add active class to clicked tab and corresponding content
                $tab.addClass('active');
                $tabContents.find('[data-tab-content="' + tabId + '"]').addClass('active');
            });
            
            // Activate first tab by default
            $tabs.find('.yenolx-tab').first().trigger('click');
        });
    }

    /**
     * Initialize charts
     */
    function initCharts() {
        // Order status chart
        if ($('#yenolx-order-status-chart').length) {
            var statusData = $('#yenolx-order-status-chart').data('status-data');
            
            if (statusData && typeof Chart !== 'undefined') {
                var ctx = document.getElementById('yenolx-order-status-chart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusData),
                        datasets: [{
                            data: Object.values(statusData),
                            backgroundColor: [
                                '#17a2b8',
                                '#ffc107',
                                '#28a745',
                                '#007bff',
                                '#6610f2',
                                '#fd7e14',
                                '#e83e8c',
                                '#20c997'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }
        
        // Revenue chart
        if ($('#yenolx-revenue-chart').length) {
            var revenueData = $('#yenolx-revenue-chart').data('revenue-data');
            
            if (revenueData && typeof Chart !== 'undefined') {
                var ctx = document.getElementById('yenolx-revenue-chart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: revenueData.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData.values,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '€' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        // Bulk action select change
        $('#bulk-action-selector-top').on('change', function() {
            var action = $(this).val();
            
            if (action === '-1') {
                $('#doaction').prop('disabled', true);
            } else {
                $('#doaction').prop('disabled', false);
            }
        });
        
        // Bulk action submit
        $('#doaction').on('click', function(e) {
            var action = $('#bulk-action-selector-top').val();
            var selectedItems = [];
            
            // Get selected items
            $('input[name="item[]"]:checked').each(function() {
                selectedItems.push($(this).val());
            });
            
            if (action === '-1') {
                e.preventDefault();
                return;
            }
            
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item.');
                return;
            }
            
            // Confirm destructive actions
            if (action.includes('delete')) {
                if (!confirm('Are you sure you want to delete the selected items?')) {
                    e.preventDefault();
                    return;
                }
            }
        });
        
        // Select all checkbox
        $('#cb-select-all-1').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('input[name="item[]"]').prop('checked', isChecked);
        });
    }

    /**
     * Initialize search/filter
     */
    function initSearchFilter() {
        // Live search
        var searchTimeout;
        $('.yenolx-live-search').on('input', function() {
            var $search = $(this);
            var searchTerm = $search.val();
            var $form = $search.closest('form');
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout
            searchTimeout = setTimeout(function() {
                if (searchTerm.length >= 2 || searchTerm.length === 0) {
                    $form.submit();
                }
            }, 500);
        });
        
        // Filter change
        $('.yenolx-filter-select').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Reset filters
        $('.yenolx-reset-filters').on('click', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            
            // Reset all form fields
            $form[0].reset();
            
            // Submit form
            $form.submit();
        });
    }

    /**
     * Show loading state
     */
    function showLoading($element) {
        $element.prop('disabled', true);
        var originalText = $element.html();
        $element.data('original-text', originalText);
        $element.html('<span class="yenolx-loading"></span> Loading...');
    }

    /**
     * Hide loading state
     */
    function hideLoading($element) {
        var originalText = $element.data('original-text');
        $element.prop('disabled', false);
        $element.html(originalText);
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'success') {
        var notificationHtml = '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>';
        
        // Insert notification at the top of the page
        $('.wrap h1').after(notificationHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Make dismissible
        $('.notice-dismiss').on('click', function() {
            $(this).closest('.notice').fadeOut(function() {
                $(this).remove();
            });
        });
    }

    /**
     * Export data
     */
    function exportData(type, filters) {
        var exportUrl = ajaxurl + '?action=yenolx_export_' + type + '&nonce=' + yenolx_cargo_admin.nonce;
        
        // Add filters
        if (filters) {
            $.each(filters, function(key, value) {
                if (value) {
                    exportUrl += '&' + key + '=' + encodeURIComponent(value);
                }
            });
        }
        
        // Create and click download link
        var $link = $('<a>').attr('href', exportUrl).attr('download', type + '-export.csv').appendTo('body');
        $link[0].click();
        $link.remove();
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

    // Make functions available globally
    window.yenolxAdmin = {
        showLoading: showLoading,
        hideLoading: hideLoading,
        showNotification: showNotification,
        exportData: exportData,
        formatCurrency: formatCurrency,
        formatDate: formatDate
    };

})(jQuery);