<?php
/**
 * Lot Manager - Product Class
 */

class LotManagerProduct extends ObjectModel
{
    public $id_lot_product;
    public $id_lot;
    public $raw_name;
    public $serial_number;
    public $imei;
    public $unit_price;
    public $quantity;
    public $status;
    public $id_product;
    public $id_product_attribute;
    public $sku;
    public $sale_price;
    public $margin;
    public $supplier_reference;
    public $notes;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'lot_manager_products',
        'primary' => 'id_lot_product',
        'fields' => [
            'id_lot' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'raw_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 500],
            'serial_number' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 100],
            'imei' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50],
            'unit_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'values' => ['pending', 'functional', 'defective', 'cancelled']],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'sku' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 100],
            'sale_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'margin' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'supplier_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 100],
            'notes' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function update($null_values = false)
    {
        $result = parent::update($null_values);
        
        if ($result) {
            // Mise à jour des statistiques du lot
            $lot = new LotManagerLot($this->id_lot);
            $lot->updateStats();
            
            // Log d'audit
            $this->addAuditLog('product_updated', 'Produit mis à jour');
        }
        
        return $result;
    }

    public function getLot()
    {
        return new LotManagerLot($this->id_lot);
    }

    public function getDefects()
    {
        return Db::getInstance()->executeS('
            SELECT d.* 
            FROM `' . _DB_PREFIX_ . 'lot_manager_defects` d
            INNER JOIN `' . _DB_PREFIX_ . 'lot_manager_product_defects` pd ON d.id_defect = pd.id_defect
            WHERE pd.id_lot_product = ' . (int)$this->id
        );
    }

    public function addDefect($id_defect)
    {
        return Db::getInstance()->insert('lot_manager_product_defects', [
            'id_lot_product' => (int)$this->id,
            'id_defect' => (int)$id_defect,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeDefect($id_defect)
    {
        return Db::getInstance()->delete('lot_manager_product_defects', 
            'id_lot_product = ' . (int)$this->id . ' AND id_defect = ' . (int)$id_defect
        );
    }

    public function setDefects($defects)
    {
        // Suppression des pannes existantes
        Db::getInstance()->delete('lot_manager_product_defects', 'id_lot_product = ' . (int)$this->id);
        
        // Ajout des nouvelles pannes
        foreach ($defects as $id_defect) {
            $this->addDefect($id_defect);
        }
    }

    public function assignToProduct($id_product, $id_product_attribute = null, $sale_price = null)
    {
        $this->id_product = (int)$id_product;
        $this->id_product_attribute = $id_product_attribute ? (int)$id_product_attribute : null;
        $this->status = 'functional';
        
        if ($sale_price) {
            $this->sale_price = (float)$sale_price;
            $this->margin = $this->sale_price - $this->unit_price;
        }
        
        // Génération du SKU
        $this->sku = $this->generateSku();
        
        $result = $this->update();
        
        if ($result) {
            // Mise à jour du stock
            $this->updateStock();
            
            // Log d'audit
            $this->addAuditLog('product_assigned', 'Produit assigné au SKU: ' . $this->sku);
        }
        
        return $result;
    }

    private function generateSku()
    {
        if ($this->id_product) {
            $product = new Product($this->id_product);
            $sku = strtoupper($product->reference);
            
            if ($this->id_product_attribute) {
                $combination = new Combination($this->id_product_attribute);
                $sku .= '-' . $combination->reference;
            }
            
            return $sku;
        }
        
        return null;
    }

    private function updateStock()
    {
        if ($this->id_product && $this->status == 'functional') {
            StockAvailable::updateQuantity(
                $this->id_product,
                $this->id_product_attribute,
                1, // Quantité à ajouter
                Context::getContext()->shop->id
            );
        }
    }

    public static function searchProducts($query, $limit = 10)
    {
        $query = pSQL($query);
        
        return Db::getInstance()->executeS('
            SELECT p.id_product, pl.name, p.reference, p.price
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON p.id_product = pl.id_product AND pl.id_lang = ' . (int)Context::getContext()->language->id . '
            WHERE p.active = 1 
            AND (pl.name LIKE "%' . $query . '%" OR p.reference LIKE "%' . $query . '%")
            ORDER BY pl.name
            LIMIT ' . (int)$limit
        );
    }

    public static function getProductCombinations($id_product)
    {
        return Db::getInstance()->executeS('
            SELECT pa.id_product_attribute, pa.reference, pa.price, 
                   GROUP_CONCAT(CONCAT(agl.name, ": ", al.name) SEPARATOR ", ") as attributes
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pa.id_product_attribute = pac.id_product_attribute
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON pac.id_attribute = a.id_attribute
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . (int)Context::getContext()->language->id . '
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int)Context::getContext()->language->id . '
            WHERE pa.id_product = ' . (int)$id_product . '
            GROUP BY pa.id_product_attribute
            ORDER BY pa.id_product_attribute
        ');
    }

    public function addAuditLog($action, $details = null, $id_employee = null)
    {
        if (!$id_employee) {
            $id_employee = Context::getContext()->employee->id;
        }

        return Db::getInstance()->insert('lot_manager_audit', [
            'id_lot' => (int)$this->id_lot,
            'id_lot_product' => (int)$this->id,
            'action' => pSQL($action),
            'details' => pSQL($details),
            'id_employee' => (int)$id_employee,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
}