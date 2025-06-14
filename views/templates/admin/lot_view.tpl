{*
* Lot Manager - Lot View Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-folder-open"></i>
        {l s='Détails du Lot' mod='lotmanager'} - {$lot->name}
    </div>
    
    <div class="panel-body">
        <!-- Informations générales -->
        <div class="row">
            <div class="col-md-6">
                <h4>{l s='Informations Générales' mod='lotmanager'}</h4>
                <table class="table">
                    <tr>
                        <td><strong>{l s='Numéro de Lot:' mod='lotmanager'}</strong></td>
                        <td>{$lot->lot_number}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Nom:' mod='lotmanager'}</strong></td>
                        <td>{$lot->name}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Fournisseur:' mod='lotmanager'}</strong></td>
                        <td>{if $supplier}{$supplier->name}{else}N/A{/if}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Statut:' mod='lotmanager'}</strong></td>
                        <td>
                            {if $lot->status == 'completed'}
                                <span class="label label-success">{l s='Terminé' mod='lotmanager'}</span>
                            {elseif $lot->status == 'processing'}
                                <span class="label label-warning">{l s='En cours' mod='lotmanager'}</span>
                            {elseif $lot->status == 'pending'}
                                <span class="label label-default">{l s='En attente' mod='lotmanager'}</span>
                            {else}
                                <span class="label label-info">{l s='Archivé' mod='lotmanager'}</span>
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Date de création:' mod='lotmanager'}</strong></td>
                        <td>{dateFormat date=$lot->date_add full=true}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h4>{l s='Statistiques' mod='lotmanager'}</h4>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h3>{$stats.total_products}</h3>
                                <p>{l s='Produits Total' mod='lotmanager'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h3>{$stats.pending_products}</h3>
                                <p>{l s='En Attente' mod='lotmanager'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h3>{$stats.functional_products}</h3>
                                <p>{l s='Fonctionnels' mod='lotmanager'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-danger">
                            <div class="panel-body text-center">
                                <h3>{$stats.defective_products}</h3>
                                <p>{l s='Défectueux' mod='lotmanager'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de progression -->
        {if $stats.total_products > 0}
            <div class="progress" style="height: 30px;">
                <div class="progress-bar progress-bar-success" style="width: {($stats.functional_products / $stats.total_products) * 100}%">
                    {l s='Fonctionnels' mod='lotmanager'} ({$stats.functional_products})
                </div>
                <div class="progress-bar progress-bar-danger" style="width: {($stats.defective_products / $stats.total_products) * 100}%">
                    {l s='Défectueux' mod='lotmanager'} ({$stats.defective_products})
                </div>
                <div class="progress-bar progress-bar-warning" style="width: {($stats.pending_products / $stats.total_products) * 100}%">
                    {l s='En attente' mod='lotmanager'} ({$stats.pending_products})
                </div>
            </div>
        {/if}

        <!-- Actions rapides -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>{l s='Actions Rapides' mod='lotmanager'}</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    {if $lot->file_path && $stats.pending_products > 0}
                        <div class="col-md-4">
                            <a href="{$import_link}" class="btn btn-primary btn-lg btn-block">
                                <i class="icon-upload"></i><br>
                                {l s='Configurer Import' mod='lotmanager'}
                            </a>
                        </div>
                    {/if}
                    {if $stats.total_products > 0}
                        <div class="col-md-4">
                            <a href="{$qualification_link}" class="btn btn-success btn-lg btn-block">
                                <i class="icon-cogs"></i><br>
                                {l s='Qualifier Produits' mod='lotmanager'}
                            </a>
                        </div>
                    {/if}
                    <div class="col-md-4">
                        <a href="{$link->getAdminLink('AdminLotManagerLots')}" class="btn btn-default btn-lg btn-block">
                            <i class="icon-list"></i><br>
                            {l s='Retour à la Liste' mod='lotmanager'}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des produits -->
        {if $products}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>{l s='Produits du Lot' mod='lotmanager'}</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{l s='Produit' mod='lotmanager'}</th>
                                    <th>{l s='N° Série' mod='lotmanager'}</th>
                                    <th>{l s='Prix' mod='lotmanager'}</th>
                                    <th>{l s='Statut' mod='lotmanager'}</th>
                                    <th>{l s='SKU' mod='lotmanager'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $products as $product}
                                    <tr>
                                        <td>
                                            <strong>{$product.raw_name}</strong>
                                            {if $product.supplier_reference}<br><small>Réf: {$product.supplier_reference}</small>{/if}
                                        </td>
                                        <td>{$product.serial_number|default:'-'}</td>
                                        <td>{displayPrice price=$product.unit_price}</td>
                                        <td>
                                            {if $product.status == 'functional'}
                                                <span class="label label-success">{l s='Fonctionnel' mod='lotmanager'}</span>
                                            {elseif $product.status == 'defective'}
                                                <span class="label label-danger">{l s='Défectueux' mod='lotmanager'}</span>
                                            {else}
                                                <span class="label label-warning">{l s='En attente' mod='lotmanager'}</span>
                                            {/if}
                                        </td>
                                        <td>{$product.sku|default:'-'}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>