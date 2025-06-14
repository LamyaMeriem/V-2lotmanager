<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class LotManager extends Module
{
    public function __construct()
    {
        $this->name = 'lotmanager';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Mr-dev';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Lot Manager');
        $this->description = $this->l('Import, qualify, and track products purchased in lots.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        return parent::install() &&
            $this->installDb() &&
            $this->installTabs() &&
            $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall()
    {
        return $this->uninstallDb() &&
            $this->uninstallTabs() &&
            parent::uninstall();
    }

    private function installDb()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_lots` (
            `id_lot` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `lot_number` VARCHAR(50) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `id_supplier` INT(11) UNSIGNED NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT \'pending\',
            `total_cost` DECIMAL(20, 6) DEFAULT 0.00,
            `estimated_value` DECIMAL(20, 6) DEFAULT 0.00,
            `total_products` INT(11) DEFAULT 0,
            `processed_products` INT(11) DEFAULT 0,
            `functional_products` INT(11) DEFAULT 0,
            `defective_products` INT(11) DEFAULT 0,
            `file_name` VARCHAR(255) DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_lot`),
            UNIQUE KEY `lot_number` (`lot_number`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_products` (
            `id_lot_product` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lot` INT(11) UNSIGNED NOT NULL,
            `raw_name` VARCHAR(255) NOT NULL,
            `imei` VARCHAR(100) DEFAULT NULL,
            `quantity` INT(11) UNSIGNED NOT NULL DEFAULT 1,
            `unit_price` DECIMAL(20, 6) NOT NULL,
            `supplier_reference` VARCHAR(100) DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT \'pending\',
            `id_product` INT(11) UNSIGNED DEFAULT NULL,
            `id_product_attribute` INT(11) UNSIGNED DEFAULT NULL,
            `sku` VARCHAR(100) DEFAULT NULL,
            `sale_price` DECIMAL(20, 6) DEFAULT NULL,
            `cancellation_reason` TEXT,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_lot_product`),
            KEY `id_lot` (`id_lot`),
            KEY `imei` (`imei`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';



        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_suppliers` (
            `id_supplier` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `contact_name` VARCHAR(255) DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `phone` VARCHAR(50) DEFAULT NULL,
            `address` TEXT,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_supplier`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_defects` (
            `id_defect` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `frequency` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_defect`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles` (
            `id_mapping_profile` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_supplier` INT(11) UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `mapping` TEXT NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_mapping_profile`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_product_defects` (
            `id_lot_product` INT(11) UNSIGNED NOT NULL,
            `id_defect` INT(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_lot_product`, `id_defect`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';


        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_audit` (
            `id_audit` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` INT(11) UNSIGNED NOT NULL,
            `id_lot` INT(11) UNSIGNED DEFAULT NULL,
            `id_lot_product` INT(11) UNSIGNED DEFAULT NULL,
            `action` VARCHAR(100) NOT NULL,
            `details` TEXT,
            `imei` VARCHAR(100) DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_audit`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_dictionary` (
            `id_dictionary` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `category` VARCHAR(100) NOT NULL,
            `pattern` TEXT NOT NULL,
            `replacement` VARCHAR(255) NOT NULL,
            `priority` INT(11) NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_dictionary`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';


        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }

    private function uninstallDb()
    {
        $tables = [
            'lot_manager_lots',
            'lot_manager_products',
            'lot_manager_suppliers',
            'lot_manager_defects',
            'lot_manager_product_defects',
            'lot_manager_mapping_profiles',
            'lot_manager_audit',
            'lot_manager_dictionary',
        ];
        foreach ($tables as $table) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`');
        }
        return true;
    }

    private function installTabs()
    {
        $parentTab = new Tab();
        $parentTab->active = 1;
        $parentTab->class_name = 'AdminLotManager';
        $parentTab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $parentTab->name[$lang['id_lang']] = 'Lot Manager';
        }
        $parentTab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $parentTab->module = $this->name;
        $parentTab->icon = 'inventory';
        if (!$parentTab->add()) {
            return false;
        }

        $subTabs = [
            'AdminLotManagerDashboard' => 'Dashboard',
            'AdminLotManagerLots' => 'Lots',
            'AdminLotManagerProducts' => 'Products',
            'AdminLotManagerStatistics' => 'Statistics',
            'AdminLotManagerConfiguration' => 'Configuration',
            // Hidden tab for import process
            'AdminLotManagerImport' => 'Import',
        ];

        foreach ($subTabs as $className => $name) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $className;
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $name;
            }
            $tab->id_parent = $parentTab->id;
            $tab->module = $this->name;
            if ($className === 'AdminLotManagerImport') {
                $tab->active = 0; // Hide from menu
            }
            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    private function uninstallTabs()
    {
        $tabClasses = [
            'AdminLotManagerDashboard',
            'AdminLotManagerLots',
            'AdminLotManagerProducts',
            'AdminLotManagerStatistics',
            'AdminLotManagerConfiguration',
            'AdminLotManagerImport',
            'AdminLotManager',
        ];

        foreach ($tabClasses as $className) {
            $id_tab = (int) Tab::getIdFromClassName($className);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminLotManagerDashboard'));
    }

    public function hookActionAdminControllerSetMedia()
    {
        $controllerName = Tools::getValue('controller');
        if (strpos($controllerName, 'AdminLotManager') === 0) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }
}