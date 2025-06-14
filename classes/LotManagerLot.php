<?php

class LotManagerLot extends ObjectModel
{
    // --- PROPERTIES --- //

    public $id;
    public $lot_number;
    public $name;
    public $id_supplier;
    public $status = 'pending'; // Default status
    public $total_cost = 0.0;
    public $estimated_value = 0.0;
    public $total_products = 0;
    public $processed_products = 0;
    public $functional_products = 0;
    public $defective_products = 0;
    public $file_name;
    public $file_path; // Propriété restaurée
    public $mapping_profile; // Propriété restaurée
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
            'file_path' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 500], // Restauré
            'mapping_profile' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'], // Restauré
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ],
    ];

    // --- METHODS --- //

    /**
     * @see ObjectModel::add()
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->lot_number)) {
            $this->lot_number = self::generateLotNumber();
        }
        $this->addAuditLog('lot_created', 'Lot created with name: ' . $this->name);
        return parent::add($auto_date, $null_values);
    }

    /**
     * Generates a unique lot number.
     * @return string
     */
    public static function generateLotNumber()
    {
        $date = date('Y-m-d');
        $count = (int) Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE DATE(date_add) = \'' . pSQL($date) . '\''
        );
        return 'LOT-' . date('Y-m-d') . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get all products associated with this lot.
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function getProducts()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('lot_manager_products');
        $query->where('id_lot = ' . (int) $this->id);
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get the supplier object for this lot.
     * @return LotManagerSupplier|null
     */
    public function getSupplier()
    {
        if (!$this->id_supplier) {
            return null;
        }
        $supplier = new LotManagerSupplier($this->id_supplier);
        if (Validate::isLoadedObject($supplier)) {
            return $supplier;
        }
        return null;
    }

    /**
     * Recalculates all statistics for the lot based on its products.
     */
    public function updateStats()
    {
        $stats = Db::getInstance()->getRow('
            SELECT 
                COALESCE(SUM(quantity), 0) as total_products,
                COALESCE(SUM(CASE WHEN status != \'pending\' THEN quantity ELSE 0 END), 0) as processed_products,
                COALESCE(SUM(CASE WHEN status = \'functional\' THEN quantity ELSE 0 END), 0) as functional_products,
                COALESCE(SUM(CASE WHEN status = \'defective\' THEN quantity ELSE 0 END), 0) as defective_products,
                COALESCE(SUM(unit_price * quantity), 0) as total_cost,
                COALESCE(SUM(sale_price * quantity), 0) as estimated_value
            FROM `' . _DB_PREFIX_ . 'lot_manager_products` 
            WHERE `id_lot` = ' . (int) $this->id
        );

        if ($stats) {
            $this->total_products = (int) $stats['total_products'];
            $this->processed_products = (int) $stats['processed_products'];
            $this->functional_products = (int) $stats['functional_products'];
            $this->defective_products = (int) $stats['defective_products'];
            $this->total_cost = (float) $stats['total_cost'];
            $this->estimated_value = (float) $stats['estimated_value'];

            // Update status based on progress
            if ($this->total_products > 0 && $this->processed_products >= $this->total_products) {
                $this->status = 'completed';
            } elseif ($this->processed_products > 0) {
                $this->status = 'processing';
            }

            return $this->update();
        }
        return false;
    }

    /**
     * Adds an entry to the audit log for this lot.
     * @param string $action
     * @param string|null $details
     * @param int|null $id_employee
     * @return bool
     */
    public function addAuditLog($action, $details = null, $id_employee = null)
    {
        if (!class_exists('LotManagerAudit')) {
            require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerAudit.php';
        }

        if (!$id_employee && Context::getContext()->employee) {
            $id_employee = Context::getContext()->employee->id;
        }

        $audit = new LotManagerAudit();
        $audit->id_lot = (int) $this->id;
        $audit->action = $action;
        $audit->details = $details;
        $audit->id_employee = (int) $id_employee;
        return $audit->add();
    }

    // --- STATIC METHODS (for fetching collections) --- //

    /**
     * Get recent lots for the dashboard.
     * @param int $limit
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public static function getRecentLots($limit = 5)
    {
        $query = new DbQuery();
        $query->select('l.*, s.name as supplier_name');
        $query->from('lot_manager_lots', 'l');
        $query->leftJoin('lot_manager_suppliers', 's', 'l.id_supplier = s.id_supplier');
        $query->orderBy('l.date_add DESC');
        $query->limit($limit);

        return Db::getInstance()->executeS($query);
    }
}