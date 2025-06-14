<?php
/**
 * Lot Manager - Statistics Controller
 */
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';
class AdminLotManagerStatisticsController extends ModuleAdminController
{
  public function __construct()
  {
    $this->bootstrap = true;
    $this->context = Context::getContext();

    parent::__construct();

    $this->meta_title = $this->l('Statistiques - Gestionnaire de Lots');
  }

  public function initContent()
  {
    parent::initContent();

    $dateFrom = Tools::getValue('date_from', date('Y-m-01'));
    $dateTo = Tools::getValue('date_to', date('Y-m-t'));

    // Statistiques générales
    $generalStats = $this->getGeneralStatistics($dateFrom, $dateTo);

    // Statistiques par fournisseur
    $supplierStats = $this->getSupplierStatistics($dateFrom, $dateTo);

    // Top des pannes
    $topDefects = $this->getTopDefects($dateFrom, $dateTo);

    // Évolution mensuelle
    $monthlyEvolution = $this->getMonthlyEvolution();

    $this->context->smarty->assign([
      'general_stats' => $generalStats,
      'supplier_stats' => $supplierStats,
      'top_defects' => $topDefects,
      'monthly_evolution' => $monthlyEvolution,
      'date_from' => $dateFrom,
      'date_to' => $dateTo
    ]);

    $this->setTemplate('statistics.tpl');
  }

  private function getGeneralStatistics($dateFrom, $dateTo)
  {
    $query = new DbQuery();
    $query->select('
            COUNT(DISTINCT l.id_lot) as total_lots,
            COALESCE(SUM(p.quantity), 0) as total_products,
            COALESCE(SUM(CASE WHEN p.status = "functional" THEN p.quantity ELSE 0 END), 0) as functional_products,
            COALESCE(SUM(CASE WHEN p.status = "defective" THEN p.quantity ELSE 0 END), 0) as defective_products,
            COALESCE(SUM(p.unit_price * p.quantity), 0) as total_cost,
            COALESCE(SUM(p.sale_price * p.quantity), 0) as total_revenue
        ');
    $query->from('lot_manager_lots', 'l');
    $query->leftJoin('lot_manager_products', 'p', 'l.id_lot = p.id_lot');
    $query->where('l.date_add BETWEEN \'' . pSQL($dateFrom) . ' 00:00:00\' AND \'' . pSQL($dateTo) . ' 23:59:59\'');

    $stats = Db::getInstance()->getRow($query);

    // --- AJOUT DE LA CORRECTION ICI --- //
    // On calcule le taux en PHP pour éviter les divisions par zéro en SQL
    if ($stats['total_products'] > 0) {
      $stats['avg_functional_rate'] = ($stats['functional_products'] / $stats['total_products']) * 100;
    } else {
      $stats['avg_functional_rate'] = 0;
    }

    return $stats;
  }

  private function getSupplierStatistics($dateFrom, $dateTo)
  {
    return Db::getInstance()->executeS('
            SELECT 
                s.name as supplier_name,
                COUNT(DISTINCT l.id_lot) as total_lots,
                COUNT(p.id_lot_product) as total_products,
                SUM(CASE WHEN p.status = "functional" THEN 1 ELSE 0 END) as functional_products,
                SUM(CASE WHEN p.status = "defective" THEN 1 ELSE 0 END) as defective_products,
                SUM(p.unit_price * p.quantity) as total_cost,
                AVG(CASE WHEN l.total_products > 0 THEN (l.functional_products / l.total_products) * 100 ELSE 0 END) as avg_functional_rate
            FROM `' . _DB_PREFIX_ . 'lot_manager_suppliers` s
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_lots` l ON s.id_supplier = l.id_supplier
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_products` p ON l.id_lot = p.id_lot
            WHERE l.date_add BETWEEN "' . pSQL($dateFrom) . '" AND "' . pSQL($dateTo) . '"
            GROUP BY s.id_supplier
            ORDER BY total_products DESC
        ');
  }

  private function getTopDefects($dateFrom, $dateTo)
  {
    return Db::getInstance()->executeS('
            SELECT 
                d.name as defect_name,
                COUNT(pd.id_defect) as frequency,
                (COUNT(pd.id_defect) * 100.0 / (
                    SELECT COUNT(*) 
                    FROM `' . _DB_PREFIX_ . 'lot_manager_product_defects` pd2
                    INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_products` p2 ON pd2.id_lot_product = p2.id_lot_product
                    INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_lots` l2 ON p2.id_lot = l2.id_lot
                    WHERE l2.date_add BETWEEN "' . pSQL($dateFrom) . '" AND "' . pSQL($dateTo) . '"
                )) as percentage
            FROM `' . _DB_PREFIX_ . 'lot_manager_defects` d
            INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_product_defects` pd ON d.id_defect = pd.id_defect
            INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_products` p ON pd.id_lot_product = p.id_lot_product
            INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_lots` l ON p.id_lot = l.id_lot
            WHERE l.date_add BETWEEN "' . pSQL($dateFrom) . '" AND "' . pSQL($dateTo) . '"
            GROUP BY d.id_defect
            ORDER BY frequency DESC
            LIMIT 10
        ');
  }

  private function getMonthlyEvolution()
  {
    return Db::getInstance()->executeS('
            SELECT 
                DATE_FORMAT(l.date_add, "%Y-%m") as month,
                COUNT(DISTINCT l.id_lot) as total_lots,
                COUNT(p.id_lot_product) as total_products,
                SUM(p.unit_price * p.quantity) as total_cost,
                SUM(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE 0 END) as total_revenue
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` l
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_products` p ON l.id_lot = p.id_lot
            WHERE l.date_add >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(l.date_add, "%Y-%m")
            ORDER BY month ASC
        ');
  }

  public function ajaxProcessExportStatistics()
  {
    $dateFrom = Tools::getValue('date_from');
    $dateTo = Tools::getValue('date_to');

    // Générer un fichier CSV avec les statistiques
    $stats = $this->getGeneralStatistics($dateFrom, $dateTo);
    $supplierStats = $this->getSupplierStatistics($dateFrom, $dateTo);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="statistiques_lots_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // En-têtes
    fputcsv($output, ['Période', $dateFrom . ' - ' . $dateTo]);
    fputcsv($output, []);
    fputcsv($output, ['Statistiques Générales']);
    fputcsv($output, ['Total Lots', $stats['total_lots']]);
    fputcsv($output, ['Total Produits', $stats['total_products']]);
    fputcsv($output, ['Produits Fonctionnels', $stats['functional_products']]);
    fputcsv($output, ['Produits Défectueux', $stats['defective_products']]);
    fputcsv($output, ['Coût Total', number_format($stats['total_cost'], 2) . '€']);
    fputcsv($output, ['Revenus Total', number_format($stats['total_revenue'], 2) . '€']);

    fputcsv($output, []);
    fputcsv($output, ['Statistiques par Fournisseur']);
    fputcsv($output, ['Fournisseur', 'Lots', 'Produits', 'Fonctionnels', 'Défectueux', 'Coût', 'Taux Fonctionnel']);

    foreach ($supplierStats as $supplier) {
      fputcsv($output, [
        $supplier['supplier_name'],
        $supplier['total_lots'],
        $supplier['total_products'],
        $supplier['functional_products'],
        $supplier['defective_products'],
        number_format($supplier['total_cost'], 2) . '€',
        number_format($supplier['avg_functional_rate'], 1) . '%'
      ]);
    }

    fclose($output);
    exit;
  }
}