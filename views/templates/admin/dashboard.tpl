{*
* Lot Manager - Dashboard Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-dashboard"></i>
        {l s='Tableau de Bord - Gestionnaire de Lots' mod='lotmanager'}
    </div>
    
    <div class="panel-body">
        <!-- KPIs -->
        <div class="row">
            <div class="col-lg-3">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="icon-clock-o icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$stats.lots_processing}</div>
                                <div>{l s='Lots en traitement' mod='lotmanager'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="panel panel-success">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="icon-check-circle icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$stats.products_processed}</div>
                                <div>{l s='Produits traités ce mois' mod='lotmanager'}</div>
                                {if $stats.products_change > 0}
                                    <small class="text-success">+{$stats.products_change}%</small>
                                {elseif $stats.products_change < 0}
                                    <small class="text-danger">{$stats.products_change}%</small>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="icon-euro icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{displayPrice price=$stats.total_value}</div>
                                <div>{l s='Valeur des achats' mod='lotmanager'}</div>
                                {if $stats.value_change > 0}
                                    <small class="text-success">+{$stats.value_change}%</small>
                                {elseif $stats.value_change < 0}
                                    <small class="text-danger">{$stats.value_change}%</small>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="panel panel-warning">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="icon-bar-chart icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$stats.functional_rate|string_format:"%.1f"}%</div>
                                <div>{l s='Taux de fonctionnalité' mod='lotmanager'}</div>
                                {if $stats.rate_change > 0}
                                    <small class="text-success">+{$stats.rate_change}%</small>
                                {elseif $stats.rate_change < 0}
                                    <small class="text-danger">{$stats.rate_change}%</small>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lots récents et pannes -->
        <div class="row">
            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-list"></i>
                        {l s='Lots Récents' mod='lotmanager'}
                        <a href="{$lots_link}" class="btn btn-default btn-sm pull-right">
                            {l s='Voir tous' mod='lotmanager'}
                        </a>
                    </div>
                    <div class="panel-body">
                        {if $recent_lots}
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{l s='Lot' mod='lotmanager'}</th>
                                            <th>{l s='Fournisseur' mod='lotmanager'}</th>
                                            <th>{l s='Statut' mod='lotmanager'}</th>
                                            <th>{l s='Produits' mod='lotmanager'}</th>
                                            <th>{l s='Valeur' mod='lotmanager'}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $recent_lots as $lot}
                                            <tr>
                                                <td>
                                                    <strong>{$lot.name}</strong><br>
                                                    <small class="text-muted">{$lot.lot_number}</small>
                                                </td>
                                                <td>{$lot.supplier_name}</td>
                                                <td>
                                                    {if $lot.status == 'completed'}
                                                        <span class="label label-success">{l s='Terminé' mod='lotmanager'}</span>
                                                    {elseif $lot.status == 'processing'}
                                                        <span class="label label-warning">{l s='En cours' mod='lotmanager'}</span>
                                                    {else}
                                                        <span class="label label-default">{l s='En attente' mod='lotmanager'}</span>
                                                    {/if}
                                                </td>
                                                <td>{$lot.processed_products}/{$lot.total_products}</td>
                                                <td>{displayPrice price=$lot.total_cost}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <p class="text-center text-muted">{l s='Aucun lot récent' mod='lotmanager'}</p>
                        {/if}
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-warning"></i>
                        {l s='Top Pannes' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        {if $top_defects}
                            {foreach $top_defects as $defect}
                                <div class="progress-group">
                                    <span class="progress-text">{$defect.name}</span>
                                    <span class="float-right"><b>{$defect.current_frequency}</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar progress-bar-danger" style="width: {if $defect.current_frequency > 0}{($defect.current_frequency / $top_defects[0].current_frequency) * 100}{else}0{/if}%"></div>
                                    </div>
                                </div>
                            {/foreach}
                        {else}
                            <p class="text-center text-muted">{l s='Aucune panne enregistrée' mod='lotmanager'}</p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-flash"></i>
                        {l s='Actions Rapides' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{$create_lot_link}" class="btn btn-primary btn-lg btn-block">
                                    <i class="icon-plus"></i><br>
                                    {l s='Créer un Nouveau Lot' mod='lotmanager'}
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{$statistics_link}" class="btn btn-success btn-lg btn-block">
                                    <i class="icon-bar-chart"></i><br>
                                    {l s='Voir les Statistiques' mod='lotmanager'}
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{$configuration_link}" class="btn btn-info btn-lg btn-block">
                                    <i class="icon-cogs"></i><br>
                                    {l s='Configuration' mod='lotmanager'}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.huge {
    font-size: 40px;
}

.progress-group {
    margin-bottom: 10px;
}

.progress-text {
    font-weight: 600;
}

.panel-primary .panel-body {
    color: #fff;
    background-color: #337ab7;
}

.panel-success .panel-body {
    color: #fff;
    background-color: #5cb85c;
}

.panel-info .panel-body {
    color: #fff;
    background-color: #5bc0de;
}

.panel-warning .panel-body {
    color: #fff;
    background-color: #f0ad4e;
}
</style>