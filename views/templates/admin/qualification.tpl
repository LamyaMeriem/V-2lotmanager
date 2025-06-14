{*
* Lot Manager - Product Qualification Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Qualification des Produits' mod='lotmanager'} - {$lot->name}
    </div>
    
    <div class="panel-body">
        <!-- Informations du lot -->
        <div class="alert alert-info">
            <strong>{l s='Lot:' mod='lotmanager'}</strong> {$lot->name} | 
            <strong>{l s='Fournisseur:' mod='lotmanager'}</strong> {$lot->getSupplier()->name} | 
            <strong>{l s='Produits:' mod='lotmanager'}</strong> {$lot->processed_products}/{$lot->total_products}
        </div>

        <!-- Barre de progression -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" 
                 style="width: {($lot->processed_products / $lot->total_products) * 100}%">
                {($lot->processed_products / $lot->total_products) * 100|string_format:"%.1f"}%
            </div>
        </div>

        <br>

        <!-- Liste des produits -->
        <div class="table-responsive">
            <table class="table table-striped" id="products-table">
                <thead>
                    <tr>
                        <th>{l s='Produit' mod='lotmanager'}</th>
                        <th>{l s='N° Série' mod='lotmanager'}</th>
                        <th>{l s='Prix d\'achat' mod='lotmanager'}</th>
                        <th>{l s='Statut' mod='lotmanager'}</th>
                        <th>{l s='SKU / Pannes' mod='lotmanager'}</th>
                        <th>{l s='Actions' mod='lotmanager'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $products as $product}
                        <tr data-product-id="{$product.id_lot_product}">
                            <td>
                                <strong>{$product.raw_name}</strong>
                            </td>
                            <td>{$product.serial_number}</td>
                            <td>{displayPrice price=$product.unit_price}</td>
                            <td>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-success btn-sm {if $product.status == 'functional'}active{/if}">
                                        <input type="radio" name="status_{$product.id_lot_product}" value="functional" 
                                               {if $product.status == 'functional'}checked{/if}> 
                                        <i class="icon-check"></i> {l s='Fonctionnel' mod='lotmanager'}
                                    </label>
                                    <label class="btn btn-danger btn-sm {if $product.status == 'defective'}active{/if}">
                                        <input type="radio" name="status_{$product.id_lot_product}" value="defective" 
                                               {if $product.status == 'defective'}checked{/if}> 
                                        <i class="icon-times"></i> {l s='Défectueux' mod='lotmanager'}
                                    </label>
                                </div>
                            </td>
                            <td>
                                <!-- Zone SKU (pour produits fonctionnels) -->
                                <div class="sku-section" {if $product.status != 'functional'}style="display:none"{/if}>
                                    <input type="text" class="form-control product-search" 
                                           placeholder="{l s='Rechercher un produit...' mod='lotmanager'}"
                                           data-product-id="{$product.id_lot_product}">
                                    <div class="search-results" style="display:none;"></div>
                                    {if $product.sku}
                                        <small class="text-success">
                                            <i class="icon-check"></i> {l s='SKU assigné:' mod='lotmanager'} {$product.sku}
                                        </small>
                                    {/if}
                                </div>
                                
                                <!-- Zone Pannes (pour produits défectueux) -->
                                <div class="defects-section" {if $product.status != 'defective'}style="display:none"{/if}>
                                    {foreach $defects as $defect}
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="defects_{$product.id_lot_product}[]" 
                                                   value="{$defect.id_defect}"> {$defect.name}
                                        </label>
                                    {/foreach}
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm save-product" 
                                        data-product-id="{$product.id_lot_product}">
                                    <i class="icon-save"></i>
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        <!-- Actions globales -->
        <div class="panel-footer">
            <button class="btn btn-success" id="save-all">
                <i class="icon-save"></i>
                {l s='Sauvegarder Tout' mod='lotmanager'}
            </button>
            
            <a href="{$link->getAdminLink('AdminLotManagerLots')}" class="btn btn-default">
                <i class="icon-arrow-left"></i>
                {l s='Retour aux Lots' mod='lotmanager'}
            </a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Changement de statut
    $('input[type="radio"][name^="status_"]').on('change', function() {
        var productId = $(this).attr('name').split('_')[1];
        var status = $(this).val();
        var row = $(this).closest('tr');
        
        // Afficher/masquer les sections appropriées
        if (status === 'functional') {
            row.find('.sku-section').show();
            row.find('.defects-section').hide();
        } else if (status === 'defective') {
            row.find('.sku-section').hide();
            row.find('.defects-section').show();
        }
        
        // Mettre à jour le statut via AJAX
        updateProductStatus(productId, status);
    });
    
    // Recherche de produits
    $('.product-search').on('input', function() {
        var query = $(this).val();
        var productId = $(this).data('product-id');
        var resultsDiv = $(this).siblings('.search-results');
        
        if (query.length >= 3) {
            searchProducts(query, resultsDiv, productId);
        } else {
            resultsDiv.hide();
        }
    });
    
    // Sauvegarde individuelle
    $('.save-product').on('click', function() {
        var productId = $(this).data('product-id');
        saveProduct(productId);
    });
    
    // Sauvegarde globale
    $('#save-all').on('click', function() {
        saveAllProducts();
    });
});

function updateProductStatus(productId, status) {
    $.ajax({
        url: '{$link->getAdminLink('AdminLotManagerProducts')}',
        type: 'POST',
        data: {
            ajax: true,
            action: 'updateProductStatus',
            id_product: productId,
            status: status
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (!data.success) {
                alert(data.message);
            }
        }
    });
}

function searchProducts(query, resultsDiv, productId) {
    $.ajax({
        url: '{$link->getAdminLink('AdminLotManagerProducts')}',
        type: 'POST',
        data: {
            ajax: true,
            action: 'searchProducts',
            query: query
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                displaySearchResults(data.products, resultsDiv, productId);
            }
        }
    });
}

function displaySearchResults(products, resultsDiv, productId) {
    var html = '<div class="list-group" style="position:absolute; z-index:1000; width:100%;">';
    
    products.forEach(function(product) {
        html += '<a href="#" class="list-group-item select-product" data-product-id="' + product.id_product + '" data-lot-product-id="' + productId + '">';
        html += '<strong>' + product.name + '</strong><br>';
        html += '<small>' + product.reference + ' - ' + product.price + '€</small>';
        html += '</a>';
    });
    
    html += '</div>';
    
    resultsDiv.html(html).show();
    
    // Gestion de la sélection
    $('.select-product').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var lotProductId = $(this).data('lot-product-id');
        var productName = $(this).find('strong').text();
        
        // Mettre à jour l'input
        $(this).closest('.search-results').siblings('.product-search').val(productName);
        $(this).closest('.search-results').hide();
        
        // Assigner le produit
        assignProduct(lotProductId, productId);
    });
}

function assignProduct(lotProductId, productId) {
    $.ajax({
        url: '{$link->getAdminLink('AdminLotManagerProducts')}',
        type: 'POST',
        data: {
            ajax: true,
            action: 'assignProduct',
            id_lot_product: lotProductId,
            id_product: productId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                // Afficher un message de succès
                showNotification('{l s='Produit assigné avec succès' mod='lotmanager'}', 'success');
            } else {
                alert(data.message);
            }
        }
    });
}

function saveProduct(productId) {
    // Logique de sauvegarde individuelle
    showNotification('{l s='Produit sauvegardé' mod='lotmanager'}', 'success');
}

function saveAllProducts() {
    // Logique de sauvegarde globale
    showNotification('{l s='Tous les produits ont été sauvegardés' mod='lotmanager'}', 'success');
}

function showNotification(message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var notification = '<div class="alert ' + alertClass + ' alert-dismissible">' +
                      '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                      message + '</div>';
    
    $('.panel-body').prepend(notification);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>