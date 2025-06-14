<?php
/**
 * Lot Manager - Defect Class
 */

class LotManagerDefect extends ObjectModel
{
    public $id_defect;
    public $name;
    public $description;
    public $frequency;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'lot_manager_defects',
        'primary' => 'id_defect',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'frequency' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getActiveDefects()
    {
        return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_defects` 
            WHERE active = 1 
            ORDER BY frequency DESC, name
        ');
    }

    public static function getTopDefects($limit = 10)
    {
        return Db::getInstance()->executeS('
            SELECT d.*, COUNT(pd.id_defect) as current_frequency
            FROM `' . _DB_PREFIX_ . 'lot_manager_defects` d
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_product_defects` pd ON d.id_defect = pd.id_defect
            WHERE d.active = 1
            GROUP BY d.id_defect
            ORDER BY current_frequency DESC, d.name
            LIMIT ' . (int)$limit
        );
    }

    public function updateFrequency()
    {
        $frequency = Db::getInstance()->getValue('
            SELECT COUNT(*) 
            FROM `' . _DB_PREFIX_ . 'lot_manager_product_defects` 
            WHERE id_defect = ' . (int)$this->id
        );

        $this->frequency = (int)$frequency;
        return $this->update();
    }
}