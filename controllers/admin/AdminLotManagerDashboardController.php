<?php
/**
 * Lot Manager - Dashboard Controller
 */
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';
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

        // Sécurisation des valeurs pour éviter les erreurs de formatage
        $currentMonth = $this->sanitizeStats($currentMonth);
        $previousMonth = $this->sanitizeStats($previousMonth);

        // Calcul des variations
        $stats = [
            'lots_processing' => (int) Db::getInstance()->getValue('
                SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
                WHERE status IN ("pending", "processing")
            ') ?: 0,
            'products_processed' => $currentMonth['total_products'],
            'total_value' => $currentMonth['total_cost'],
            'functional_rate' => $currentMonth['avg_functional_rate'],
            'products_change' => $this->calculateChange(
                $currentMonth['total_products'],
                $previousMonth['total_products']
            ),
            'value_change' => $this->calculateChange(
                $currentMonth['total_cost'],
                $previousMonth['total_cost']
            ),
            'rate_change' => $this->calculateChange(
                $currentMonth['avg_functional_rate'],
                $previousMonth['avg_functional_rate']
            )
        ];

        return $stats;
    }

    private function sanitizeStats($stats)
    {
        if (!$stats || !is_array($stats)) {
            return [
                'total_lots' => 0,
                'total_products' => 0,
                'functional_products' => 0,
                'defective_products' => 0,
                'total_cost' => 0.0,
                'estimated_value' => 0.0,
                'avg_functional_rate' => 0.0
            ];
        }

        return [
            'total_lots' => (int) ($stats['total_lots'] ?: 0),
            'total_products' => (int) ($stats['total_products'] ?: 0),
            'functional_products' => (int) ($stats['functional_products'] ?: 0),
            'defective_products' => (int) ($stats['defective_products'] ?: 0),
            'total_cost' => (float) ($stats['total_cost'] ?: 0.0),
            'estimated_value' => (float) ($stats['estimated_value'] ?: 0.0),
            'avg_functional_rate' => (float) ($stats['avg_functional_rate'] ?: 0.0)
        ];
    }

    private function calculateChange($current, $previous)
    {
        $current = (float) ($current ?: 0);
        $previous = (float) ($previous ?: 0);

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}