{*
* Lot Manager - Configuration Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Configuration - Gestionnaire de Lots' mod='lotmanager'}
    </div>
    
    <div class="panel-body">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="{if $current_tab == 'suppliers'}active{/if}">
                <a href="#suppliers" role="tab" data-toggle="tab">
                    <i class="icon-users"></i>
                    {l s='Fournisseurs' mod='lotmanager'}
                </a>
            </li>
            <li class="{if $current_tab == 'defects'}active{/if}">
                <a href="#defects" role="tab" data-toggle="tab">
                    <i class="icon-warning"></i>
                    {l s='Pannes' mod='lotmanager'}
                </a>
            </li>
            <li class="{if $current_tab == 'dictionary'}active{/if}">
                <a href="#dictionary" role="tab" data-toggle="tab">
                    <i class="icon-book"></i>
                    {l s='Dictionnaire' mod='lotmanager'}
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Suppliers Tab -->
            <div class="tab-pane {if $current_tab == 'suppliers'}active{/if}" id="suppliers">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-plus"></i>
                                {l s='Ajouter un Fournisseur' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Nom *' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="supplier_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Contact' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="supplier_contact" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Email' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="email" name="supplier_email" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Téléphone' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="tel" name="supplier_phone" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Adresse' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <textarea name="supplier_address" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-offset-3 col-lg-9">
                                            <button type="submit" name="submitSupplier" class="btn btn-primary">
                                                <i class="icon-save"></i>
                                                {l s='Ajouter' mod='lotmanager'}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-list"></i>
                                {l s='Liste des Fournisseurs' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                {if $suppliers}
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{l s='Nom' mod='lotmanager'}</th>
                                                    <th>{l s='Contact' mod='lotmanager'}</th>
                                                    <th>{l s='Lots' mod='lotmanager'}</th>
                                                    <th>{l s='Actions' mod='lotmanager'}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {foreach $suppliers as $supplier}
                                                    <tr>
                                                        <td>
                                                            <strong>{$supplier.name}</strong>
                                                            {if $supplier.email}<br><small>{$supplier.email}</small>{/if}
                                                        </td>
                                                        <td>
                                                            {$supplier.contact_name}
                                                            {if $supplier.phone}<br><small>{$supplier.phone}</small>{/if}
                                                        </td>
                                                        <td>{$supplier.total_lots}</td>
                                                        <td>
                                                            <button class="btn btn-danger btn-xs delete-supplier" data-id="{$supplier.id_supplier}">
                                                                <i class="icon-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                {else}
                                    <p class="text-center text-muted">{l s='Aucun fournisseur configuré' mod='lotmanager'}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Defects Tab -->
            <div class="tab-pane {if $current_tab == 'defects'}active{/if}" id="defects">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-plus"></i>
                                {l s='Ajouter une Panne' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Nom *' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="defect_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Description' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <textarea name="defect_description" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-offset-3 col-lg-9">
                                            <button type="submit" name="submitDefect" class="btn btn-primary">
                                                <i class="icon-save"></i>
                                                {l s='Ajouter' mod='lotmanager'}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-list"></i>
                                {l s='Liste des Pannes' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                {if $defects}
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{l s='Nom' mod='lotmanager'}</th>
                                                    <th>{l s='Fréquence' mod='lotmanager'}</th>
                                                    <th>{l s='Statut' mod='lotmanager'}</th>
                                                    <th>{l s='Actions' mod='lotmanager'}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {foreach $defects as $defect}
                                                    <tr>
                                                        <td>
                                                            <strong>{$defect.name}</strong>
                                                            {if $defect.description}<br><small>{$defect.description}</small>{/if}
                                                        </td>
                                                        <td>{$defect.frequency}</td>
                                                        <td>
                                                            <label class="switch">
                                                                <input type="checkbox" class="toggle-defect" 
                                                                       data-id="{$defect.id_defect}" 
                                                                       {if $defect.active}checked{/if}>
                                                                <span class="slider"></span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-danger btn-xs delete-defect" data-id="{$defect.id_defect}">
                                                                <i class="icon-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                {else}
                                    <p class="text-center text-muted">{l s='Aucune panne configurée' mod='lotmanager'}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dictionary Tab -->
            <div class="tab-pane {if $current_tab == 'dictionary'}active{/if}" id="dictionary">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-plus"></i>
                                {l s='Ajouter une Règle' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Catégorie *' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <select name="dict_category" class="form-control" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="Stockage">Stockage</option>
                                                <option value="Couleur">Couleur</option>
                                                <option value="État">État</option>
                                                <option value="Marque">Marque</option>
                                                <option value="Modèle">Modèle</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Variations *' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="dict_pattern" class="form-control" required
                                                   placeholder="Ex: Go,gb,giga,GB">
                                            <small class="help-block">{l s='Séparez les variations par des virgules' mod='lotmanager'}</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Valeur Normalisée *' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="dict_replacement" class="form-control" required
                                                   placeholder="Ex: GB">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3">{l s='Priorité' mod='lotmanager'}</label>
                                        <div class="col-lg-9">
                                            <input type="number" name="dict_priority" class="form-control" value="0" min="0" max="100">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-offset-3 col-lg-9">
                                            <button type="submit" name="submitDictionary" class="btn btn-primary">
                                                <i class="icon-save"></i>
                                                {l s='Ajouter' mod='lotmanager'}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="panel">
                            <div class="panel-heading">
                                <i class="icon-list"></i>
                                {l s='Règles de Dictionnaire' mod='lotmanager'}
                            </div>
                            <div class="panel-body">
                                {if $dictionary}
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{l s='Catégorie' mod='lotmanager'}</th>
                                                    <th>{l s='Variations' mod='lotmanager'}</th>
                                                    <th>{l s='Normalisé' mod='lotmanager'}</th>
                                                    <th>{l s='Actions' mod='lotmanager'}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {foreach $dictionary as $rule}
                                                    <tr>
                                                        <td>
                                                            <span class="label label-info">{$rule.category}</span>
                                                        </td>
                                                        <td>
                                                            <small>{$rule.pattern}</small>
                                                        </td>
                                                        <td>
                                                            <strong>{$rule.replacement}</strong>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-danger btn-xs delete-rule" data-id="{$rule.id_dictionary}">
                                                                <i class="icon-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                {else}
                                    <p class="text-center text-muted">{l s='Aucune règle configurée' mod='lotmanager'}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

<script>
$(document).ready(function() {
    // Toggle defect status
    $('.toggle-defect').on('change', function() {
        var id = $(this).data('id');
        var active = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{$link->getAdminLink('AdminLotManagerConfiguration')}',
            type: 'POST',
            data: {
                ajax: true,
                action: 'toggleDefect',
                id_defect: id,
                active: active
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (!data.success) {
                    alert(data.message);
                }
            }
        });
    });
    
    // Delete supplier
    $('.delete-supplier').on('click', function() {
        if (confirm('{l s='Êtes-vous sûr de vouloir supprimer ce fournisseur ?' mod='lotmanager'}')) {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: '{$link->getAdminLink('AdminLotManagerConfiguration')}',
                type: 'POST',
                data: {
                    ajax: true,
                    action: 'deleteSupplier',
                    id_supplier: id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        row.fadeOut();
                    } else {
                        alert(data.message);
                    }
                }
            });
        }
    });
    
    // Delete defect
    $('.delete-defect').on('click', function() {
        if (confirm('{l s='Êtes-vous sûr de vouloir supprimer cette panne ?' mod='lotmanager'}')) {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: '{$link->getAdminLink('AdminLotManagerConfiguration')}',
                type: 'POST',
                data: {
                    ajax: true,
                    action: 'deleteDefect',
                    id_defect: id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        row.fadeOut();
                    } else {
                        alert(data.message);
                    }
                }
            });
        }
    });
});
</script>