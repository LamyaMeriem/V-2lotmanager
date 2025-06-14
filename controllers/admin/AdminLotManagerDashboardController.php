<?php
/**
 * Lot Manager - Dashboard Controller
 */

class AdminLotManagerDashboardController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        
        parent::__construct();
        
        $this->meta_title = $this->l('Tableau de Bord - Gestionnaire de Lots');
    }

    public function initContent()
    {
        parent::initContent();
        
        // Récupération des statistiques
        $stats = $this->getStatistics();
        $recentLots = LotManagerLot::getRecentLots(5);
        $topDefects = LotManagerDefect::getTopDefects(5);
        
        $this->context->smarty->assign([
            'stats' => $stats,
            'recent_lots' => $recentLots,
            'top_defects' => $topDefects,
            'create_lot_link' => $this->context->link->getAdminLink('AdminLotManagerLots') . '&addlot_manager_lot',
            'lots_link' => $this->context->link->getAdminLink('AdminLotManagerLots'),
            'statistics_link' => $this->context->link->getAdminLink('AdminLotManagerStatistics'),
            'configuration_link' => $this->context->link->getAdminLink('AdminLotManagerConfiguration')
        ]);
        
        $this->setTemplate('dashboard.tpl');
    }

    private function getStatistics()
    {
        // Statistiques du mois en cours
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');
        
        $currentMonth = LotManagerLot::getStatistics($dateFrom, $dateTo);
        
        // Statistiques du mois précédent pour comparaison
        $prevDateFrom = date('Y-m-01', strtotime('-1 month'));
        $prevDateTo = date('Y-m-t', strtotime('-1 month'));
        
        $previousMonth = LotManagerLot::getStatistics($prevDateFrom, $prevDateTo);
        
        // Calcul des variations
        $stats = [
            'lots_processing' => Db::getInstance()->getValue('
                SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
                WHERE status IN ("pending", "processing")
            '),
            'products_processed' => $currentMonth['total_products'] ?: 0,
            'total_value' => $currentMonth['total_cost'] ?: 0,
            'functional_rate' => $currentMonth['avg_functional_rate'] ?: 0,
            'products_change' => $this->calculateChange(
                $currentMonth['total_products'] ?: 0, 
                $previousMonth['total_products'] ?: 0
            ),
            'value_change' => $this->calculateChange(
                $currentMonth['total_cost'] ?: 0, 
                $previousMonth['total_cost'] ?: 0
            ),
            'rate_change' => $this->calculateChange(
                $currentMonth['avg_functional_rate'] ?: 0, 
                $previousMonth['avg_functional_rate'] ?: 0
            )
        ];
        
        return $stats;
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}