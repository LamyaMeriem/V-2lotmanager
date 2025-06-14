<?php
/**
 * Lot Manager - Lot Class
 */

class LotManagerLot extends ObjectModel
{
    public $id_lot;
    public $lot_number;
    public $name;
    public $id_supplier;
    public $status;
    public $total_cost;
    public $estimated_value;
    public $total_products;
    public $processed_products;
    public $functional_products;
    public $defective_products;
    public $file_name;
    public $file_path;
    public $mapping_profile;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'lot_manager_lots',
        'primary' => 'id_lot',
        'fields' => [
            'lot_number' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'values' => ['pending', 'processing', 'completed', 'archived']],
            'total_cost' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'estimated_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_products' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'processed_products' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'functional_products' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'defective_products' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'file_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'file_path' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'size' => 500],
            'mapping_profile' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        // Initialiser les valeurs par défaut pour éviter les erreurs de formatage
        if (!$this->total_cost)
            $this->total_cost = 0.0;
        if (!$this->estimated_value)
            $this->estimated_value = 0.0;
        if (!$this->total_products)
            $this->total_products = 0;
        if (!$this->processed_products)
            $this->processed_products = 0;
        if (!$this->functional_products)
            $this->functional_products = 0;
        if (!$this->defective_products)
            $this->defective_products = 0;
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->lot_number)) {
            $this->lot_number = $this->generateLotNumber();
        }

        // Initialiser les valeurs par défaut
        if (!isset($this->total_cost))
            $this->total_cost = 0.0;
        if (!isset($this->estimated_value))
            $this->estimated_value = 0.0;
        if (!isset($this->total_products))
            $this->total_products = 0;
        if (!isset($this->processed_products))
            $this->processed_products = 0;
        if (!isset($this->functional_products))
            $this->functional_products = 0;
        if (!isset($this->defective_products))
            $this->defective_products = 0;

        return parent::add($auto_date, $null_values);
    }

    public function generateLotNumber()
    {
        $date = date('Y-m-d');
        $count = (int) Db::getInstance()->getValue('
            SELECT COUNT(*) 
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE DATE(date_add) = "' . pSQL($date) . '"
        ');

        return 'LOT-' . date('Y-m-d') . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function getProducts()
    {
        return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_products` 
            WHERE `id_lot` = ' . (int) $this->id
        );
    }

    public function getSupplier()
    {
        if ($this->id_supplier > 0) {
            return new LotManagerSupplier($this->id_supplier);
        }
        return null;
    }

    public function updateStats()
    {
        $stats = Db::getInstance()->getRow('
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN status != "pending" THEN 1 ELSE 0 END) as processed_products,
                SUM(CASE WHEN status = "functional" THEN 1 ELSE 0 END) as functional_products,
                SUM(CASE WHEN status = "defective" THEN 1 ELSE 0 END) as defective_products,
                SUM(CASE WHEN unit_price IS NOT NULL THEN unit_price * quantity ELSE 0 END) as total_cost,
                SUM(CASE WHEN sale_price > 0 THEN sale_price ELSE 0 END) as estimated_value
            FROM `' . _DB_PREFIX_ . 'lot_manager_products` 
            WHERE `id_lot` = ' . (int) $this->id
        );

        if ($stats) {
            $this->total_products = (int) ($stats['total_products'] ?: 0);
            $this->processed_products = (int) ($stats['processed_products'] ?: 0);
            $this->functional_products = (int) ($stats['functional_products'] ?: 0);
            $this->defective_products = (int) ($stats['defective_products'] ?: 0);
            $this->total_cost = (float) ($stats['total_cost'] ?: 0.0);
            $this->estimated_value = (float) ($stats['estimated_value'] ?: 0.0);

            // Mise à jour du statut
            if ($this->processed_products == $this->total_products && $this->total_products > 0) {
                $this->status = 'completed';
            } elseif ($this->processed_products > 0) {
                $this->status = 'processing';
            }

            $this->update();
        }
    }

    public static function getRecentLots($limit = 10)
    {
        $lots = Db::getInstance()->executeS('
            SELECT l.*, s.name as supplier_name
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` l
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_suppliers` s ON l.id_supplier = s.id_lot_supplier
            ORDER BY l.date_add DESC
            LIMIT ' . (int) $limit
        );

        // Sécuriser les valeurs pour éviter les erreurs de formatage
        foreach ($lots as &$lot) {
            $lot['total_cost'] = (float) ($lot['total_cost'] ?: 0.0);
            $lot['estimated_value'] = (float) ($lot['estimated_value'] ?: 0.0);
            $lot['total_products'] = (int) ($lot['total_products'] ?: 0);
            $lot['processed_products'] = (int) ($lot['processed_products'] ?: 0);
            $lot['functional_products'] = (int) ($lot['functional_products'] ?: 0);
            $lot['defective_products'] = (int) ($lot['defective_products'] ?: 0);
        }

        return $lots;
    }

    public static function getStatistics($dateFrom = null, $dateTo = null)
    {
        $whereClause = '';
        if ($dateFrom && $dateTo) {
            $whereClause = 'WHERE l.date_add BETWEEN "' . pSQL($dateFrom) . '" AND "' . pSQL($dateTo) . '"';
        }

        $result = Db::getInstance()->getRow('
            SELECT 
                COUNT(*) as total_lots,
                COALESCE(SUM(total_products), 0) as total_products,
                COALESCE(SUM(functional_products), 0) as functional_products,
                COALESCE(SUM(defective_products), 0) as defective_products,
                COALESCE(SUM(total_cost), 0) as total_cost,
                COALESCE(SUM(estimated_value), 0) as estimated_value,
                COALESCE(AVG(CASE WHEN total_products > 0 THEN (functional_products / total_products) * 100 ELSE 0 END), 0) as avg_functional_rate
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` l
            ' . $whereClause
        );

        // Sécuriser les valeurs
        if (!$result) {
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
            'total_lots' => (int) ($result['total_lots'] ?: 0),
            'total_products' => (int) ($result['total_products'] ?: 0),
            'functional_products' => (int) ($result['functional_products'] ?: 0),
            'defective_products' => (int) ($result['defective_products'] ?: 0),
            'total_cost' => (float) ($result['total_cost'] ?: 0.0),
            'estimated_value' => (float) ($result['estimated_value'] ?: 0.0),
            'avg_functional_rate' => (float) ($result['avg_functional_rate'] ?: 0.0)
        ];
    }

    public function addAuditLog($action, $details = null, $id_employee = null)
    {
        if (!$id_employee) {
            $id_employee = Context::getContext()->employee->id;
        }

        return Db::getInstance()->insert('lot_manager_audit', [
            'id_lot' => (int) $this->id,
            'action' => pSQL($action),
            'details' => pSQL($details),
            'id_employee' => (int) $id_employee,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
}