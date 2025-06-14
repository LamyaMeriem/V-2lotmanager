/**
 * Lot Manager - Admin JavaScript
 */

$(document).ready(function() {
    
    // File Upload Handler
    initFileUpload();
    
    // Mapping Interface
    initMappingInterface();
    
    // Product Qualification
    initProductQualification();
    
    // Statistics Charts
    initStatisticsCharts();
    
    // Auto-refresh for dashboard
    if ($('.lot-manager-dashboard').length) {
        setInterval(refreshDashboard, 30000); // Refresh every 30 seconds
    }
});

/**
 * Initialize File Upload
 */
function initFileUpload() {
    var $uploadArea = $('.file-upload-area');
    
    if ($uploadArea.length) {
        // Drag and drop events
        $uploadArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        $uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        $uploadArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileUpload(files[0]);
            }
        });
        
        // Click to upload
        $uploadArea.on('click', function() {
            $('#file-input').click();
        });
        
        $('#file-input').on('change', function() {
            if (this.files.length > 0) {
                handleFileUpload(this.files[0]);
            }
        });
    }
}

/**
 * Handle File Upload
 */
function handleFileUpload(file) {
    // Validate file type
    var allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                       'application/vnd.ms-excel', 
                       'text/csv', 
                       'application/pdf'];
    
    if (allowedTypes.indexOf(file.type) === -1) {
        showNotification('Type de fichier non supporté', 'error');
        return;
    }
    
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showNotification('Fichier trop volumineux (max 10MB)', 'error');
        return;
    }
    
    // Show upload progress
    showUploadProgress();
    
    // Create FormData
    var formData = new FormData();
    formData.append('import_file', file);
    formData.append('action', 'uploadFile');
    
    // Upload file
    $.ajax({
        url: lotManagerAjaxUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideUploadProgress();
            if (response.success) {
                showNotification('Fichier uploadé avec succès', 'success');
                // Redirect to mapping interface
                window.location.href = response.redirect_url;
            } else {
                showNotification(response.message || 'Erreur lors de l\'upload', 'error');
            }
        },
        error: function() {
            hideUploadProgress();
            showNotification('Erreur lors de l\'upload du fichier', 'error');
        }
    });
}

/**
 * Initialize Mapping Interface
 */
function initMappingInterface() {
    // Auto-detect columns
    $('.mapping-select').each(function() {
        var $select = $(this);
        var fieldName = $select.data('field');
        
        // Try to auto-match columns based on field name
        autoMatchColumn($select, fieldName);
    });
    
    // Save mapping profile
    $('#save-mapping-profile').on('click', function() {
        saveMappingProfile();
    });
    
    // Load mapping profile
    $('.load-mapping-profile').on('click', function() {
        var profileId = $(this).data('profile-id');
        loadMappingProfile(profileId);
    });
    
    // Validate mapping before proceeding
    $('#proceed-import').on('click', function() {
        if (validateMapping()) {
            proceedWithImport();
        }
    });
}

/**
 * Auto-match columns based on field names
 */
function autoMatchColumn($select, fieldName) {
    var $options = $select.find('option');
    var patterns = {
        'product_name': ['nom', 'produit', 'designation', 'libelle', 'name', 'product'],
        'serial_number': ['serie', 'serial', 'imei', 'sn'],
        'quantity': ['qte', 'quantite', 'quantity', 'qty'],
        'unit_price': ['prix', 'price', 'cout', 'cost', 'unitaire'],
        'supplier_reference': ['reference', 'ref', 'sku', 'code']
    };
    
    if (patterns[fieldName]) {
        $options.each(function() {
            var optionText = $(this).text().toLowerCase();
            for (var i = 0; i < patterns[fieldName].length; i++) {
                if (optionText.indexOf(patterns[fieldName][i]) !== -1) {
                    $select.val($(this).val());
                    return false;
                }
            }
        });
    }
}

/**
 * Validate mapping configuration
 */
function validateMapping() {
    var requiredFields = ['product_name', 'quantity', 'unit_price'];
    var isValid = true;
    
    requiredFields.forEach(function(field) {
        var $select = $('[data-field="' + field + '"]');
        if (!$select.val()) {
            $select.addClass('error');
            isValid = false;
        } else {
            $select.removeClass('error');
        }
    });
    
    if (!isValid) {
        showNotification('Veuillez mapper tous les champs obligatoires', 'error');
    }
    
    return isValid;
}

/**
 * Initialize Product Qualification
 */
function initProductQualification() {
    // Product status change
    $('.product-status-btn').on('click', function() {
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var status = $btn.data('status');
        
        updateProductStatus(productId, status);
    });
    
    // Product search
    $('.product-search').on('input', function() {
        var query = $(this).val();
        var $results = $(this).siblings('.search-results');
        
        if (query.length >= 3) {
            searchProducts(query, $results);
        } else {
            $results.hide();
        }
    });
    
    // Defect selection
    $('.defect-checkbox').on('change', function() {
        var productId = $(this).data('product-id');
        var defectId = $(this).data('defect-id');
        var isChecked = $(this).is(':checked');
        
        updateProductDefects(productId, defectId, isChecked);
    });
    
    // Bulk actions
    $('#bulk-functional').on('click', function() {
        bulkUpdateStatus('functional');
    });
    
    $('#bulk-defective').on('click', function() {
        bulkUpdateStatus('defective');
    });
}

/**
 * Update product status
 */
function updateProductStatus(productId, status) {
    $.ajax({
        url: lotManagerAjaxUrl,
        type: 'POST',
        data: {
            action: 'updateProductStatus',
            product_id: productId,
            status: status
        },
        success: function(response) {
            if (response.success) {
                // Update UI
                updateProductUI(productId, status);
                showNotification('Statut mis à jour', 'success');
            } else {
                showNotification(response.message || 'Erreur lors de la mise à jour', 'error');
            }
        },
        error: function() {
            showNotification('Erreur lors de la mise à jour du statut', 'error');
        }
    });
}

/**
 * Search products
 */
function searchProducts(query, $results) {
    $.ajax({
        url: lotManagerAjaxUrl,
        type: 'POST',
        data: {
            action: 'searchProducts',
            query: query
        },
        success: function(response) {
            if (response.success) {
                displaySearchResults(response.products, $results);
            }
        }
    });
}

/**
 * Display search results
 */
function displaySearchResults(products, $results) {
    var html = '';
    
    products.forEach(function(product) {
        html += '<div class="search-result-item" data-product-id="' + product.id_product + '">';
        html += '<strong>' + product.name + '</strong>';
        html += '<span class="price">' + product.price + '€</span>';
        html += '</div>';
    });
    
    $results.html(html).show();
    
    // Handle result selection
    $results.find('.search-result-item').on('click', function() {
        var productId = $(this).data('product-id');
        var productName = $(this).find('strong').text();
        
        // Update the input field
        $results.siblings('.product-search').val(productName);
        $results.hide();
        
        // Load product combinations
        loadProductCombinations(productId);
    });
}

/**
 * Initialize Statistics Charts
 */
function initStatisticsCharts() {
    if (typeof Chart !== 'undefined') {
        // Monthly evolution chart
        if ($('#monthly-chart').length) {
            createMonthlyChart();
        }
        
        // Defects distribution chart
        if ($('#defects-chart').length) {
            createDefectsChart();
        }
        
        // Supplier performance chart
        if ($('#supplier-chart').length) {
            createSupplierChart();
        }
    }
}

/**
 * Show notification
 */
function showNotification(message, type) {
    var $notification = $('<div class="notification ' + type + '">' + message + '</div>');
    
    $('body').prepend($notification);
    
    setTimeout(function() {
        $notification.fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Show upload progress
 */
function showUploadProgress() {
    var $progress = $('<div class="upload-progress"><div class="progress-bar"></div></div>');
    $('.file-upload-area').append($progress);
}

/**
 * Hide upload progress
 */
function hideUploadProgress() {
    $('.upload-progress').remove();
}

/**
 * Refresh dashboard data
 */
function refreshDashboard() {
    $.ajax({
        url: lotManagerAjaxUrl,
        type: 'POST',
        data: {
            action: 'refreshDashboard'
        },
        success: function(response) {
            if (response.success) {
                // Update KPIs
                updateDashboardKPIs(response.stats);
                
                // Update recent lots
                updateRecentLots(response.recent_lots);
                
                // Update top defects
                updateTopDefects(response.top_defects);
            }
        }
    });
}

/**
 * Update dashboard KPIs
 */
function updateDashboardKPIs(stats) {
    $('.kpi-lots-processing .kpi-value').text(stats.lots_processing);
    $('.kpi-products-processed .kpi-value').text(stats.products_processed);
    $('.kpi-total-value .kpi-value').text(stats.total_value + '€');
    $('.kpi-functional-rate .kpi-value').text(stats.functional_rate + '%');
}

/**
 * Utility function to format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

/**
 * Utility function to format percentage
 */
function formatPercentage(value) {
    return value.toFixed(1) + '%';
}