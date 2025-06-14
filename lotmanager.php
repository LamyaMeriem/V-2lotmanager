<?php
/**
 * Lot Manager Module for PrestaShop
 * 
 * @author    Your Name
 * @copyright 2025 Your Company
 * @license   Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LotManager extends Module
{
    public function __construct()
    {
        $this->name = 'lotmanager';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Your Company';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Gestionnaire de Lots');
        $this->description = $this->l('Module de gestion et traçabilité des lots d\'achat de produits reconditionnés');
        $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');
    }

    public function install()
    {
        return parent::install() &&
            $this->installDb() &&
            $this->installTabs() &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallDb() &&
            $this->uninstallTabs();
    }

    private function installDb()
    {
        $sql = [];

        // Table des lots
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_lots` (
            `id_lot` int(11) NOT NULL AUTO_INCREMENT,
            `lot_number` varchar(50) NOT NULL,
            `name` varchar(255) NOT NULL,
            `id_supplier` int(11) NOT NULL,
            `status` enum("pending","processing","completed","archived") DEFAULT "pending",
            `total_cost` decimal(20,6) DEFAULT 0,
            `estimated_value` decimal(20,6) DEFAULT 0,
            `total_products` int(11) DEFAULT 0,
            `processed_products` int(11) DEFAULT 0,
            `functional_products` int(11) DEFAULT 0,
            `defective_products` int(11) DEFAULT 0,
            `file_name` varchar(255) DEFAULT NULL,
            `file_path` varchar(500) DEFAULT NULL,
            `mapping_profile` text DEFAULT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_lot`),
            UNIQUE KEY `lot_number` (`lot_number`),
            KEY `id_supplier` (`id_supplier`),
            KEY `status` (`status`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table des produits du lot
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_products` (
            `id_lot_product` int(11) NOT NULL AUTO_INCREMENT,
            `id_lot` int(11) NOT NULL,
            `raw_name` varchar(500) NOT NULL,
            `serial_number` varchar(100) DEFAULT NULL,
            `imei` varchar(50) DEFAULT NULL,
            `unit_price` decimal(20,6) NOT NULL,
            `quantity` int(11) DEFAULT 1,
            `status` enum("pending","functional","defective","cancelled") DEFAULT "pending",
            `id_product` int(11) DEFAULT NULL,
            `id_product_attribute` int(11) DEFAULT NULL,
            `sku` varchar(100) DEFAULT NULL,
            `sale_price` decimal(20,6) DEFAULT NULL,
            `margin` decimal(20,6) DEFAULT NULL,
            `supplier_reference` varchar(100) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_lot_product`),
            KEY `id_lot` (`id_lot`),
            KEY `serial_number` (`serial_number`),
            KEY `imei` (`imei`),
            KEY `status` (`status`),
            KEY `id_product` (`id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table des pannes
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_defects` (
            `id_defect` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `frequency` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_defect`),
            KEY `active` (`active`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table de liaison produits-pannes
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_product_defects` (
            `id_lot_product` int(11) NOT NULL,
            `id_defect` int(11) NOT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_lot_product`, `id_defect`),
            KEY `id_defect` (`id_defect`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table des fournisseurs personnalisés
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_suppliers` (
            `id_lot_supplier` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `contact_name` varchar(255) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `phone` varchar(50) DEFAULT NULL,
            `address` text DEFAULT NULL,
            `total_lots` int(11) DEFAULT 0,
            `average_functional_rate` decimal(5,2) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_lot_supplier`),
            KEY `active` (`active`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table du dictionnaire de correspondances
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_dictionary` (
            `id_dictionary` int(11) NOT NULL AUTO_INCREMENT,
            `category` varchar(100) NOT NULL,
            `pattern` text NOT NULL,
            `replacement` varchar(255) NOT NULL,
            `priority` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_dictionary`),
            KEY `category` (`category`),
            KEY `active` (`active`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table des profils de mapping
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles` (
            `id_mapping_profile` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `id_supplier` int(11) DEFAULT NULL,
            `mapping_config` text NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_mapping_profile`),
            KEY `id_supplier` (`id_supplier`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        // Table d'audit
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lot_manager_audit` (
            `id_audit` int(11) NOT NULL AUTO_INCREMENT,
            `id_lot` int(11) DEFAULT NULL,
            `id_lot_product` int(11) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `details` text DEFAULT NULL,
            `id_employee` int(11) NOT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_audit`),
            KEY `id_lot` (`id_lot`),
            KEY `id_lot_product` (`id_lot_product`),
            KEY `id_employee` (`id_employee`),
            KEY `action` (`action`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        // Insertion des données par défaut
        $this->insertDefaultData();

        return true;
    }

    private function insertDefaultData()
    {
        // Pannes par défaut
        $defects = [
            'Écran cassé',
            'Batterie HS',
            'Ne s\'allume pas',
            'Problème caméra',
            'Bouton défaillant',
            'Problème audio',
            'Rayures importantes',
            'Oxydation',
            'Problème tactile',
            'Problème réseau'
        ];

        foreach ($defects as $defect) {
            Db::getInstance()->insert('lot_manager_defects', [
                'name' => pSQL($defect),
                'active' => 1,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s')
            ]);
        }

        // Dictionnaire par défaut
        $dictionary = [
            ['category' => 'Stockage', 'pattern' => 'Go,gb,giga,GB', 'replacement' => 'GB'],
            ['category' => 'Stockage', 'pattern' => 'To,tb,tera,TB', 'replacement' => 'TB'],
            ['category' => 'Couleur', 'pattern' => 'Black,noir,jet black,space black', 'replacement' => 'Noir'],
            ['category' => 'Couleur', 'pattern' => 'White,blanc,silver white', 'replacement' => 'Blanc'],
            ['category' => 'Couleur', 'pattern' => 'Space Gray,Gris Sidéral,gray,grey', 'replacement' => 'Gris Sidéral'],
            ['category' => 'Couleur', 'pattern' => 'Blue,bleu,ocean blue,pacific blue', 'replacement' => 'Bleu'],
            ['category' => 'Couleur', 'pattern' => 'Red,rouge,product red', 'replacement' => 'Rouge'],
            ['category' => 'État', 'pattern' => 'Grade A,excellent,parfait,neuf', 'replacement' => 'Grade A'],
            ['category' => 'État', 'pattern' => 'Grade B,bon,good,très bon', 'replacement' => 'Grade B'],
            ['category' => 'État', 'pattern' => 'Grade C,correct,fair,moyen', 'replacement' => 'Grade C']
        ];

        foreach ($dictionary as $rule) {
            Db::getInstance()->insert('lot_manager_dictionary', [
                'category' => pSQL($rule['category']),
                'pattern' => pSQL($rule['pattern']),
                'replacement' => pSQL($rule['replacement']),
                'active' => 1,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function uninstallDb()
    {
        $sql = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_audit`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_dictionary`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_suppliers`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_product_defects`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_defects`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_products`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lot_manager_lots`'
        ];

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function installTabs()
    {
        // Onglet principal
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminLotManager';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Gestionnaire de Lots';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $this->name;
        $tab->icon = 'inventory_2';

        if (!$tab->add()) {
            return false;
        }

        // Sous-onglets
        $subTabs = [
            [
                'class_name' => 'AdminLotManagerDashboard',
                'name' => 'Tableau de Bord',
                'parent' => 'AdminLotManager'
            ],
            [
                'class_name' => 'AdminLotManagerLots',
                'name' => 'Lots',
                'parent' => 'AdminLotManager'
            ],
            [
                'class_name' => 'AdminLotManagerProducts',
                'name' => 'Produits',
                'parent' => 'AdminLotManager'
            ],
            [
                'class_name' => 'AdminLotManagerStatistics',
                'name' => 'Statistiques',
                'parent' => 'AdminLotManager'
            ],
            [
                'class_name' => 'AdminLotManagerConfiguration',
                'name' => 'Configuration',
                'parent' => 'AdminLotManager'
            ]
        ];

        foreach ($subTabs as $subTab) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $subTab['class_name'];
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $subTab['name'];
            }
            $tab->id_parent = (int)Tab::getIdFromClassName($subTab['parent']);
            $tab->module = $this->name;

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    private function uninstallTabs()
    {
        $tabClasses = [
            'AdminLotManagerConfiguration',
            'AdminLotManagerStatistics',
            'AdminLotManagerProducts',
            'AdminLotManagerLots',
            'AdminLotManagerDashboard',
            'AdminLotManager'
        ];

        foreach ($tabClasses as $tabClass) {
            $idTab = (int)Tab::getIdFromClassName($tabClass);
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        return true;
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (strpos($this->context->controller->php_self, 'AdminLotManager') !== false) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (strpos($this->context->controller->php_self, 'AdminLotManager') !== false) {
            return '<script>var lotManagerAjaxUrl = "' . $this->context->link->getAdminLink('AdminLotManagerAjax') . '";</script>';
        }
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitLotManagerConfig')) {
            Configuration::updateValue('LOT_MANAGER_AUTO_INCREMENT', (int)Tools::getValue('auto_increment'));
            Configuration::updateValue('LOT_MANAGER_DEFAULT_MARGIN', (float)Tools::getValue('default_margin'));
            Configuration::updateValue('LOT_MANAGER_UPLOAD_PATH', pSQL(Tools::getValue('upload_path')));
            
            $output .= $this->displayConfirmation($this->l('Configuration mise à jour avec succès.'));
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Configuration du module'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Auto-incrément des lots'),
                    'name' => 'auto_increment',
                    'is_bool' => true,
                    'desc' => $this->l('Génération automatique des numéros de lots'),
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Activé')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Désactivé')
                        ]
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Marge par défaut (%)'),
                    'name' => 'default_margin',
                    'suffix' => '%',
                    'desc' => $this->l('Marge appliquée par défaut lors du calcul des prix de vente'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Dossier d\'upload'),
                    'name' => 'upload_path',
                    'desc' => $this->l('Chemin relatif pour stocker les fichiers importés'),
                ]
            ],
            'submit' => [
                'title' => $this->l('Sauvegarder'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitLotManagerConfig';

        $helper->fields_value['auto_increment'] = Configuration::get('LOT_MANAGER_AUTO_INCREMENT', true);
        $helper->fields_value['default_margin'] = Configuration::get('LOT_MANAGER_DEFAULT_MARGIN', 30);
        $helper->fields_value['upload_path'] = Configuration::get('LOT_MANAGER_UPLOAD_PATH', 'modules/lotmanager/uploads/');

        return $helper->generateForm($fieldsForm);
    }
}