{*
* Lot Manager - Advanced Import Mapping Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-upload"></i>
        {l s='Configuration du Mapping - Import de Produits' mod='lotmanager'}
    </div>
    
    <div class="panel-body">
        <!-- Informations du fichier -->
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>{l s='Lot:' mod='lotmanager'}</strong> {$lot->name}<br>
                    <strong>{l s='Fichier:' mod='lotmanager'}</strong> {$lot->file_name}
                </div>
                <div class="col-md-6">
                    {if $file_analysis.success}
                        <strong>{l s='Type:' mod='lotmanager'}</strong> {$file_analysis.file_type}<br>
                        <strong>{l s='Colonnes détectées:' mod='lotmanager'}</strong> {count($file_analysis.columns)}<br>
                        <strong>{l s='Lignes estimées:' mod='lotmanager'}</strong> {$file_analysis.total_rows|default:0}
                    {else}
                        <span class="text-danger">{$file_analysis.message}</span>
                    {/if}
                </div>
            </div>
        </div>

        {if $file_analysis.success}
            <!-- Détection automatique -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-magic"></i>
                    {l s='Détection Automatique' mod='lotmanager'}
                    <button class="btn btn-primary btn-sm pull-right" id="auto-detect-btn">
                        <i class="icon-magic"></i>
                        {l s='Détecter Automatiquement' mod='lotmanager'}
                    </button>
                </div>
                <div class="panel-body">
                    <p class="text-muted">
                        {l s='Cliquez sur "Détecter Automatiquement" pour que le système essaie de faire correspondre automatiquement les colonnes de votre fichier avec les champs requis.' mod='lotmanager'}
                    </p>
                </div>
            </div>

            <!-- Profils existants -->
            {if $existing_profiles}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-flash"></i>
                        {l s='Profils de Mapping Existants' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            {foreach $existing_profiles as $profile}
                                <div class="col-md-4">
                                    <button class="btn btn-info btn-block load-profile" data-profile-id="{$profile.id_mapping_profile}">
                                        <i class="icon-download"></i>
                                        <strong>{$profile.name}</strong>
                                        {if $profile.supplier_name}<br><small>{$profile.supplier_name}</small>{/if}
                                    </button>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/if}

            <!-- Formulaire de mapping -->
            <form method="post" class="form-horizontal" id="mapping-form">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-cogs"></i>
                        {l s='Configuration des Correspondances' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            <i class="icon-info-circle"></i>
                            {l s='Associez chaque champ requis à une colonne de votre fichier. Les champs marqués d\'un * sont obligatoires.' mod='lotmanager'}
                        </div>

                        {foreach $mapping_fields as $field_key => $field_info}
                            <div class="form-group mapping-row">
                                <label class="control-label col-lg-3">
                                    <strong>{$field_info.label}</strong>
                                    {if $field_info.required}<span class="text-danger">*</span>{/if}
                                    <br><small class="text-muted">{$field_info.description}</small>
                                </label>
                                <div class="col-lg-6">
                                    <select name="mapping_{$field_key}" class="form-control mapping-select" 
                                            data-field="{$field_key}"
                                            {if $field_info.required}required{/if}>
                                        <option value="">{l s='-- Sélectionner une colonne --' mod='lotmanager'}</option>
                                        {foreach $file_analysis.columns as $column}
                                            <option value="{$column|escape:'html':'UTF-8'}">{$column|escape:'html':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mapping-preview" style="display:none;">
                                        <small class="text-success">
                                            <i class="icon-check"></i>
                                            <span class="preview-text"></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>

                <!-- Aperçu des données -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-eye"></i>
                        {l s='Aperçu des Données' mod='lotmanager'}
                        <span class="badge pull-right">{count($sample_data)} {l s='lignes d\'exemple' mod='lotmanager'}</span>
                    </div>
                    <div class="panel-body">
                        {if $sample_data}
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            {foreach $file_analysis.columns as $column}
                                                <th class="text-center">
                                                    <strong>{$column|escape:'html':'UTF-8'}</strong>
                                                    <div class="mapping-indicator" data-column="{$column|escape:'html':'UTF-8'}" style="display:none;">
                                                        <span class="label label-success mapping-label"></span>
                                                    </div>
                                                </th>
                                            {/foreach}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $sample_data as $row}
                                            <tr>
                                                {foreach $file_analysis.columns as $column}
                                                    <td>{$row[$column]|escape:'html':'UTF-8'|default:'-'}</td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <p class="text-center text-muted">{l s='Aucune donnée d\'aperçu disponible' mod='lotmanager'}</p>
                        {/if}
                    </div>
                </div>

                <!-- Validation du mapping -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-check-circle"></i>
                        {l s='Validation du Mapping' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <div id="mapping-validation">
                            <div class="alert alert-warning">
                                <i class="icon-warning"></i>
                                {l s='Veuillez configurer le mapping pour voir la validation' mod='lotmanager'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sauvegarde du profil -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="icon-save"></i>
                        {l s='Sauvegarde du Profil' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="save_profile" id="save_profile">
                                    {l s='Sauvegarder ce mapping comme profil réutilisable' mod='lotmanager'}
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="profile-name-group" style="display:none;">
                            <label class="control-label col-lg-3">{l s='Nom du profil' mod='lotmanager'}</label>
                            <div class="col-lg-9">
                                <input type="text" name="profile_name" class="form-control" 
                                       placeholder="{l s='Ex: Format Excel Fournisseur XYZ' mod='lotmanager'}">
                                <small class="help-block">{l s='Ce profil pourra être réutilisé pour les futurs imports du même fournisseur' mod='lotmanager'}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{$link->getAdminLink('AdminLotManagerLots')}" class="btn btn-default">
                                <i class="icon-arrow-left"></i>
                                {l s='Retour aux Lots' mod='lotmanager'}
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-info" id="preview-import">
                                <i class="icon-eye"></i>
                                {l s='Prévisualiser l\'Import' mod='lotmanager'}
                            </button>
                            <button type="submit" name="submitMapping" class="btn btn-primary" id="submit-import">
                                <i class="icon-upload"></i>
                                {l s='Importer les Produits' mod='lotmanager'}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            <div class="alert alert-danger">
                <i class="icon-exclamation-triangle"></i>
                {l s='Impossible d\'analyser le fichier. Veuillez vérifier le format et réessayer.' mod='lotmanager'}
            </div>
        {/if}
    </div>
</div>

<script>
$(document).ready(function() {
    var fileColumns = {$file_analysis.columns|json_encode};
    var mappingFields = {$mapping_fields|json_encode};
    
    // Détection automatique
    $('#auto-detect-btn').on('click', function() {
        autoDetectMapping();
    });
    
    // Charger un profil existant
    $('.load-profile').on('click', function() {
        var profileId = $(this).data('profile-id');
        loadMappingProfile(profileId);
    });
    
    // Changement de mapping
    $('.mapping-select').on('change', function() {
        updateMappingPreview();
        validateMapping();
        updateColumnIndicators();
    });
    
    // Afficher/masquer le nom du profil
    $('#save_profile').on('change', function() {
        if ($(this).is(':checked')) {
            $('#profile-name-group').slideDown();
        } else {
            $('#profile-name-group').slideUp();
        }
    });
    
    // Prévisualisation
    $('#preview-import').on('click', function() {
        previewImport();
    });
    
    // Validation du formulaire
    $('#mapping-form').on('submit', function(e) {
        if (!validateMapping()) {
            e.preventDefault();
            return false;
        }
        
        // Confirmation avant import
        var mappedFields = $('.mapping-select').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        if (!confirm('{l s='Êtes-vous sûr de vouloir importer les produits avec ce mapping ?' mod='lotmanager'}\n\n' + 
                    mappedFields + ' {l s='champs mappés' mod='lotmanager'}')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Initialisation
    updateMappingPreview();
    validateMapping();
});

function autoDetectMapping() {
    $.ajax({
        url: '{$link->getAdminLink('AdminLotManagerImport')}',
        type: 'POST',
        data: {
            ajax: true,
            action: 'autoDetectMapping',
            columns: fileColumns
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                // Appliquer le mapping automatique
                $.each(data.mapping, function(field, column) {
                    $('select[name="mapping_' + field + '"]').val(column);
                });
                
                updateMappingPreview();
                validateMapping();
                updateColumnIndicators();
                
                showNotification('{l s='Mapping automatique appliqué avec succès' mod='lotmanager'}', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        },
        error: function() {
            showNotification('{l s='Erreur lors de la détection automatique' mod='lotmanager'}', 'error');
        }
    });
}

function loadMappingProfile(profileId) {
    $.ajax({
        url: '{$link->getAdminLink('AdminLotManagerImport')}',
        type: 'POST',
        data: {
            ajax: true,
            action: 'loadMappingProfile',
            profile_id: profileId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                // Appliquer le mapping
                $.each(data.mapping, function(field, column) {
                    $('select[name="mapping_' + field + '"]').val(column);
                });
                
                updateMappingPreview();
                validateMapping();
                updateColumnIndicators();
                
                showNotification('{l s='Profil chargé avec succès' mod='lotmanager'}', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        },
        error: function() {
            showNotification('{l s='Erreur lors du chargement du profil' mod='lotmanager'}', 'error');
        }
    });
}

function updateMappingPreview() {
    $('.mapping-select').each(function() {
        var $select = $(this);
        var $preview = $select.closest('.mapping-row').find('.mapping-preview');
        var selectedColumn = $select.val();
        
        if (selectedColumn) {
            $preview.find('.preview-text').text(selectedColumn);
            $preview.show();
        } else {
            $preview.hide();
        }
    });
}

function updateColumnIndicators() {
    // Réinitialiser tous les indicateurs
    $('.mapping-indicator').hide();
    
    // Afficher les indicateurs pour les colonnes mappées
    $('.mapping-select').each(function() {
        var $select = $(this);
        var selectedColumn = $select.val();
        var fieldKey = $select.data('field');
        
        if (selectedColumn && mappingFields[fieldKey]) {
            var $indicator = $('.mapping-indicator[data-column="' + selectedColumn + '"]');
            $indicator.find('.mapping-label').text(mappingFields[fieldKey].label);
            $indicator.show();
        }
    });
}

function validateMapping() {
    var requiredFields = ['product_name', 'quantity', 'unit_price'];
    var mappedRequired = 0;
    var totalMapped = 0;
    var errors = [];
    
    // Vérifier les champs obligatoires
    requiredFields.forEach(function(field) {
        var $select = $('select[name="mapping_' + field + '"]');
        if ($select.val()) {
            mappedRequired++;
            $select.removeClass('error');
        } else {
            $select.addClass('error');
            errors.push(mappingFields[field].label + ' {l s='est obligatoire' mod='lotmanager'}');
        }
    });
    
    // Compter le total des champs mappés
    $('.mapping-select').each(function() {
        if ($(this).val()) {
            totalMapped++;
        }
    });
    
    // Mettre à jour l'affichage de validation
    var $validation = $('#mapping-validation');
    
    if (errors.length === 0) {
        $validation.html(
            '<div class="alert alert-success">' +
            '<i class="icon-check-circle"></i> ' +
            '{l s='Mapping valide' mod='lotmanager'} - ' + totalMapped + ' {l s='champs mappés' mod='lotmanager'}' +
            '</div>'
        );
        $('#submit-import').prop('disabled', false);
        return true;
    } else {
        $validation.html(
            '<div class="alert alert-danger">' +
            '<i class="icon-exclamation-triangle"></i> ' +
            '<strong>{l s='Erreurs de mapping:' mod='lotmanager'}</strong><br>' +
            errors.join('<br>') +
            '</div>'
        );
        $('#submit-import').prop('disabled', true);
        return false;
    }
}

function previewImport() {
    if (!validateMapping()) {
        showNotification('{l s='Veuillez corriger les erreurs de mapping avant la prévisualisation' mod='lotmanager'}', 'error');
        return;
    }
    
    // Afficher un modal avec un aperçu de l'import
    var previewHtml = '<div class="modal fade" id="import-preview-modal" tabindex="-1">' +
                     '<div class="modal-dialog modal-lg">' +
                     '<div class="modal-content">' +
                     '<div class="modal-header">' +
                     '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
                     '<h4 class="modal-title">{l s='Aperçu de l\'Import' mod='lotmanager'}</h4>' +
                     '</div>' +
                     '<div class="modal-body">' +
                     '<p><strong>{l s='Résumé:' mod='lotmanager'}</strong></p>' +
                     '<ul>' +
                     '<li>{l s='Fichier:' mod='lotmanager'} {$lot->file_name}</li>' +
                     '<li>{l s='Lignes à traiter:' mod='lotmanager'} {$file_analysis.total_rows|default:0}</li>' +
                     '<li>{l s='Champs mappés:' mod='lotmanager'} <span id="mapped-count"></span></li>' +
                     '</ul>' +
                     '<div class="alert alert-info">' +
                     '<i class="icon-info-circle"></i> ' +
                     '{l s='L\'import créera des produits en statut "En attente" que vous pourrez ensuite qualifier.' mod='lotmanager'}' +
                     '</div>' +
                     '</div>' +
                     '<div class="modal-footer">' +
                     '<button type="button" class="btn btn-default" data-dismiss="modal">{l s='Fermer' mod='lotmanager'}</button>' +
                     '</div>' +
                     '</div>' +
                     '</div>' +
                     '</div>';
    
    $('body').append(previewHtml);
    
    // Compter les champs mappés
    var mappedCount = $('.mapping-select').filter(function() {
        return $(this).val() !== '';
    }).length;
    
    $('#mapped-count').text(mappedCount);
    $('#import-preview-modal').modal('show');
    
    // Nettoyer le modal après fermeture
    $('#import-preview-modal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function showNotification(message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'icon-check-circle' : 'icon-exclamation-triangle';
    
    var notification = '<div class="alert ' + alertClass + ' alert-dismissible notification-alert">' +
                      '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                      '<i class="' + icon + '"></i> ' + message +
                      '</div>';
    
    $('.panel-body').first().prepend(notification);
    
    setTimeout(function() {
        $('.notification-alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}
</script>

<style>
.mapping-select.error {
    border-color: #d9534f;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 6px #ce8483;
}

.mapping-row {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.mapping-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.mapping-indicator {
    margin-top: 5px;
}

.mapping-label {
    font-size: 10px;
    padding: 2px 6px;
}

.load-profile {
    margin-bottom: 10px;
    min-height: 60px;
}

.notification-alert {
    margin-bottom: 15px;
}

#submit-import:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.table th {
    position: relative;
}

.form-control-static small {
    font-style: italic;
}

.panel-footer {
    background-color: #f5f5f5;
    border-top: 1px solid #ddd;
}
</style>