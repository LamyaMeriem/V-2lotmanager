<?php
/**
 * Lot Manager - Supplier Class
 */

class LotManagerSupplier extends ObjectModel
{
    public $id_lot_supplier;
    public $name;
    public $contact_name;
    public $email;
    public $phone;
    public $address;
    public $total_lots;
    public $average_functional_rate;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'lot_manager_suppliers',
        'primary' => 'id_lot_supplier',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'contact_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 255],
            'phone' => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 50],
            'address' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'total_lots' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'average_functional_rate' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function getLots()
    {
        return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE `id_supplier` = ' . (int)$this->id . '
            ORDER BY date_add DESC
        ');
    }

    public function updateStats()
    {
        $stats = Db::getInstance()->getRow('
            SELECT 
                COUNT(*) as total_lots,
                AVG(CASE WHEN total_products > 0 THEN (functional_products / total_products) * 100 ELSE 0 END) as avg_functional_rate
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE `id_supplier` = ' . (int)$this->id
        );

        if ($stats) {
            $this->total_lots = (int)$stats['total_lots'];
            $this->average_functional_rate = (float)$stats['avg_functional_rate'];
            $this->update();
        }
    }

    public static function getActiveSuppliers()
    {
        return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_suppliers` 
            WHERE active = 1 
            ORDER BY name
        ');
    }
}