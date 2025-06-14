{*
* Lot Manager - Statistics Template
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-bar-chart"></i>
        {l s='Statistiques - Gestionnaire de Lots' mod='lotmanager'}
    </div>
    
    <div class="panel-body">
        <!-- Filtres de période -->
        <div class="row">
            <div class="col-lg-12">
                <form method="get" class="form-inline">
                    <input type="hidden" name="controller" value="AdminLotManagerStatistics">
                    <input type="hidden" name="token" value="{$token}">
                    
                    <div class="form-group">
                        <label>{l s='Du' mod='lotmanager'}</label>
                        <input type="date" name="date_from" value="{$date_from}" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>{l s='Au' mod='lotmanager'}</label>
                        <input type="date" name="date_to" value="{$date_to}" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-search"></i>
                        {l s='Filtrer' mod='lotmanager'}
                    </button>
                    
                    <a href="#" class="btn btn-success export-stats">
                        <i class="icon-download"></i>
                        {l s='Exporter CSV' mod='lotmanager'}
                    </a>
                </form>
            </div>
        </div>

        <hr>

        <!-- KPIs -->
        <div class="row">
            <div class="col-lg-3">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="icon-folder-open icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$general_stats.total_lots}</div>
                                <div>{l s='Lots traités' mod='lotmanager'}</div>
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
                                <i class="icon-mobile icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$general_stats.total_products}</div>
                                <div>{l s='Produits traités' mod='lotmanager'}</div>
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
                                <div class="huge">{displayPrice price=$general_stats.total_cost}</div>
                                <div>{l s='Coût total' mod='lotmanager'}</div>
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
                                <i class="icon-check-circle icon-5x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge">{$general_stats.avg_functional_rate|string_format:"%.1f"}%</div>
                                <div>{l s='Taux fonctionnel' mod='lotmanager'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques et tableaux -->
        <div class="row">
            <!-- Évolution mensuelle -->
            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-line-chart"></i>
                        {l s='Évolution Mensuelle' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        <canvas id="monthlyChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Top pannes -->
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
                                    <span class="progress-text">{$defect.defect_name}</span>
                                    <span class="float-right"><b>{$defect.frequency}</b> ({$defect.percentage|string_format:"%.1f"}%)</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar progress-bar-danger" style="width: {$defect.percentage}%"></div>
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

        <!-- Statistiques par fournisseur -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-users"></i>
                        {l s='Analyse par Fournisseur' mod='lotmanager'}
                    </div>
                    <div class="panel-body">
                        {if $supplier_stats}
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{l s='Fournisseur' mod='lotmanager'}</th>
                                            <th>{l s='Lots' mod='lotmanager'}</th>
                                            <th>{l s='Produits' mod='lotmanager'}</th>
                                            <th>{l s='Fonctionnels' mod='lotmanager'}</th>
                                            <th>{l s='Défectueux' mod='lotmanager'}</th>
                                            <th>{l s='Coût Total' mod='lotmanager'}</th>
                                            <th>{l s='Taux Fonctionnel' mod='lotmanager'}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $supplier_stats as $supplier}
                                            <tr>
                                                <td><strong>{$supplier.supplier_name}</strong></td>
                                                <td>{$supplier.total_lots}</td>
                                                <td>{$supplier.total_products}</td>
                                                <td class="text-success">{$supplier.functional_products}</td>
                                                <td class="text-danger">{$supplier.defective_products}</td>
                                                <td>{displayPrice price=$supplier.total_cost}</td>
                                                <td>
                                                    <span class="label {if $supplier.avg_functional_rate >= 80}label-success{elseif $supplier.avg_functional_rate >= 60}label-warning{else}label-danger{/if}">
                                                        {$supplier.avg_functional_rate|string_format:"%.1f"}%
                                                    </span>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <p class="text-center text-muted">{l s='Aucune donnée disponible' mod='lotmanager'}</p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Graphique d'évolution mensuelle
    var ctx = document.getElementById('monthlyChart').getContext('2d');
    var monthlyData = {$monthly_evolution|json_encode};
    
    var labels = [];
    var productsData = [];
    var costData = [];
    var revenueData = [];
    
    monthlyData.forEach(function(item) {
        labels.push(item.month);
        productsData.push(item.total_products);
        costData.push(item.total_cost);
        revenueData.push(item.total_revenue);
    });
    
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{l s='Produits' mod='lotmanager'}',
                data: productsData,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: '{l s='Coût (€)' mod='lotmanager'}',
                data: costData,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1,
                yAxisID: 'y1'
            }, {
                label: '{l s='Revenus (€)' mod='lotmanager'}',
                data: revenueData,
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // Export CSV
    $('.export-stats').on('click', function(e) {
        e.preventDefault();
        
        var url = '{$link->getAdminLink('AdminLotManagerStatistics')}';
        url += '&ajax=1&action=exportStatistics';
        url += '&date_from=' + $('input[name="date_from"]').val();
        url += '&date_to=' + $('input[name="date_to"]').val();
        
        window.location.href = url;
    });
});
</script>

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