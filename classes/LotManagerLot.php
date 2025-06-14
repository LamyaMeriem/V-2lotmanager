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
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->lot_number)) {
            $this->lot_number = $this->generateLotNumber();
        }

        return parent::add($auto_date, $null_values);
    }

    public function generateLotNumber()
    {
        $date = date('Y-m-d');
        $count = Db::getInstance()->getValue('
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
            WHERE `id_lot` = ' . (int)$this->id
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
                SUM(unit_price * quantity) as total_cost,
                SUM(CASE WHEN sale_price > 0 THEN sale_price ELSE 0 END) as estimated_value
            FROM `' . _DB_PREFIX_ . 'lot_manager_products` 
            WHERE `id_lot` = ' . (int)$this->id
        );

        if ($stats) {
            $this->total_products = (int)$stats['total_products'];
            $this->processed_products = (int)$stats['processed_products'];
            $this->functional_products = (int)$stats['functional_products'];
            $this->defective_products = (int)$stats['defective_products'];
            $this->total_cost = (float)$stats['total_cost'];
            $this->estimated_value = (float)$stats['estimated_value'];

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
        return Db::getInstance()->executeS('
            SELECT l.*, s.name as supplier_name
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` l
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_suppliers` s ON l.id_supplier = s.id_lot_supplier
            ORDER BY l.date_add DESC
            LIMIT ' . (int)$limit
        );
    }

    public static function getStatistics($dateFrom = null, $dateTo = null)
    {
        $whereClause = '';
        if ($dateFrom && $dateTo) {
            $whereClause = 'WHERE l.date_add BETWEEN "' . pSQL($dateFrom) . '" AND "' . pSQL($dateTo) . '"';
        }

        return Db::getInstance()->getRow('
            SELECT 
                COUNT(*) as total_lots,
                SUM(total_products) as total_products,
                SUM(functional_products) as functional_products,
                SUM(defective_products) as defective_products,
                SUM(total_cost) as total_cost,
                SUM(estimated_value) as estimated_value,
                AVG(CASE WHEN total_products > 0 THEN (functional_products / total_products) * 100 ELSE 0 END) as avg_functional_rate
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` l
            ' . $whereClause
        );
    }

    public function addAuditLog($action, $details = null, $id_employee = null)
    {
        if (!$id_employee) {
            $id_employee = Context::getContext()->employee->id;
        }

        return Db::getInstance()->insert('lot_manager_audit', [
            'id_lot' => (int)$this->id,
            'action' => pSQL($action),
            'details' => pSQL($details),
            'id_employee' => (int)$id_employee,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
}