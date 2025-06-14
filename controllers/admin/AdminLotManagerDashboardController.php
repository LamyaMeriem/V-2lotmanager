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

        $this->meta_title = $this->l('Dashboard');
    }

    public function initContent()
    {
        parent::initContent();

        // Récupération des statistiques
        $stats = $this->getDashboardKpis();
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

    private function getDashboardKpis()
    {
        // --- Date ranges --- //
        $dateFromCurrentMonth = date('Y-m-01 00:00:00');
        $dateToCurrentMonth = date('Y-m-t 23:59:59');

        $dateFromPreviousMonth = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $dateToPreviousMonth = date('Y-m-t 23:59:59', strtotime('-1 month'));

        // --- KPI Calculations --- //
        $currentMonthStats = $this->fetchStatsForPeriod($dateFromCurrentMonth, $dateToCurrentMonth);
        $previousMonthStats = $this->fetchStatsForPeriod($dateFromPreviousMonth, $dateToPreviousMonth);

        $lotsInProcessing = Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE status IN ("pending", "processing")'
        );

        // --- Build the final array --- //
        return [
            'lots_processing' => (int) $lotsInProcessing,
            'products_processed' => (int) $currentMonthStats['total_products'],
            'total_value' => (float) $currentMonthStats['total_cost'],
            'functional_rate' => ($currentMonthStats['total_products'] > 0) ? round(($currentMonthStats['functional_products'] / $currentMonthStats['total_products']) * 100, 1) : 0,

            'products_change' => $this->calculateChange($currentMonthStats['total_products'], $previousMonthStats['total_products']),
            'value_change' => $this->calculateChange($currentMonthStats['total_cost'], $previousMonthStats['total_cost']),
            'rate_change' => $this->calculateChange(
                ($currentMonthStats['total_products'] > 0) ? ($currentMonthStats['functional_products'] / $currentMonthStats['total_products']) : 0,
                ($previousMonthStats['total_products'] > 0) ? ($previousMonthStats['functional_products'] / $previousMonthStats['total_products']) : 0
            )
        ];
    }

    /**
     * Fetches statistics for a given period from the database.
     * This method now resides within the controller that needs it.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    private function fetchStatsForPeriod($dateFrom, $dateTo)
    {
        $query = new DbQuery();
        $query->select('
            COALESCE(SUM(total_products), 0) as total_products,
            COALESCE(SUM(functional_products), 0) as functional_products,
            COALESCE(SUM(total_cost), 0) as total_cost
        ');
        $query->from('lot_manager_lots');
        $query->where('date_add BETWEEN \'' . pSQL($dateFrom) . '\' AND \'' . pSQL($dateTo) . '\'');

        $result = Db::getInstance()->getRow($query);
        return $result ?: [
            'total_products' => 0,
            'functional_products' => 0,
            'total_cost' => 0.0,
        ];
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

    /**
     * Calculates the percentage change between two values.
     * @param float $current
     * @param float $previous
     * @return float
     */
    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return ($current > 0) ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}