<?php
/**
 * Lot Manager - Lots Controller with Enhanced Import
 */
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';
class AdminLotManagerLotsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'lot_manager_lots';
        $this->className = 'LotManagerLot';
        $this->identifier = 'id_lot';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->meta_title = $this->l('Gestion des Lots');

        $this->fields_list = [
            'id_lot' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'lot_number' => [
                'title' => $this->l('Numéro de Lot'),
                'width' => 140
            ],
            'name' => [
                'title' => $this->l('Nom du Lot')
            ],
            'supplier_name' => [
                'title' => $this->l('Fournisseur'),
                'width' => 120
            ],
            'status' => [
                'title' => $this->l('Statut'),
                'width' => 100,
                'type' => 'select',
                'list' => [
                    'pending' => $this->l('En attente'),
                    'processing' => $this->l('En cours'),
                    'completed' => $this->l('Terminé'),
                    'archived' => $this->l('Archivé')
                ],
                'filter_key' => 'a!status'
            ],
            'total_products' => [
                'title' => $this->l('Produits'),
                'align' => 'center',
                'width' => 80
            ],
            'processed_products' => [
                'title' => $this->l('Traités'),
                'align' => 'center',
                'width' => 80
            ],
            'total_cost' => [
                'title' => $this->l('Coût Total'),
                'align' => 'right',
                'type' => 'price',
                'currency' => true
            ],
            'date_add' => [
                'title' => $this->l('Date de création'),
                'align' => 'right',
                'type' => 'datetime'
            ]
        ];

        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // Action personnalisée pour traiter les produits
        $this->addRowAction('process');

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Supprimer la sélection'),
                'confirm' => $this->l('Supprimer les éléments sélectionnés ?'),
                'icon' => 'icon-trash'
            ]
        ];
    }

    public function renderList()
    {
        $this->_select = 's.name as supplier_name';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_suppliers` s ON a.id_supplier = s.id_lot_supplier';

        return parent::renderList();
    }

    public function renderForm()
    {
        $suppliers = LotManagerSupplier::getActiveSuppliers();
        $supplier_options = [];
        foreach ($suppliers as $supplier) {
            $supplier_options[] = [
                'id' => $supplier['id_lot_supplier'],
                'name' => $supplier['name']
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Lot'),
                'icon' => 'icon-folder-open'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Numéro de Lot'),
                    'name' => 'lot_number',
                    'required' => false,
                    'hint' => $this->l('Laissez vide pour génération automatique')
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Nom du Lot'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => false
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Fournisseur'),
                    'name' => 'id_supplier',
                    'required' => true,
                    'options' => [
                        'query' => $supplier_options,
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Statut'),
                    'name' => 'status',
                    'options' => [
                        'query' => [
                            ['id' => 'pending', 'name' => $this->l('En attente')],
                            ['id' => 'processing', 'name' => $this->l('En cours')],
                            ['id' => 'completed', 'name' => $this->l('Terminé')],
                            ['id' => 'archived', 'name' => $this->l('Archivé')]
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Fichier d\'import'),
                    'name' => 'import_file',
                    'desc' => $this->l('Formats supportés: .xlsx, .csv, .pdf (max 10MB)')
                ]
            ],
            'submit' => [
                'title' => $this->l('Sauvegarder')
            ]
        ];

        return parent::renderForm();
    }

    public function processAdd()
    {
        $lot = new LotManagerLot();
        $lot->name = Tools::getValue('name');
        $lot->lot_number = Tools::getValue('lot_number');
        $lot->id_supplier = (int) Tools::getValue('id_supplier');
        $lot->status = Tools::getValue('status', 'pending');

        if ($lot->add()) {
            // Traitement du fichier d'import si présent
            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                $uploadResult = $this->processImportFile($lot);
                if ($uploadResult['success']) {
                    // Redirection vers l'interface de mapping
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminLotManagerImport') .
                        '&id_lot=' . $lot->id
                    );
                    return;
                } else {
                    $this->errors[] = $uploadResult['message'];
                }
            } else {
                // Créer quelques produits d'exemple pour tester
                $this->createSampleProducts($lot);
            }

            $lot->addAuditLog('lot_created', 'Lot créé');

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminLotManagerLots'));
        } else {
            $this->errors[] = $this->l('Erreur lors de la création du lot');
        }
    }

    private function processImportFile($lot)
    {
        $uploadDir = _PS_MODULE_DIR_ . 'lotmanager/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validation du fichier
        $allowedExtensions = ['xlsx', 'xls', 'csv', 'pdf'];
        $fileExtension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => $this->l('Format de fichier non supporté. Formats acceptés: .xlsx, .csv, .pdf')
            ];
        }

        // Validation de la taille (max 10MB)
        if ($_FILES['import_file']['size'] > 10 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => $this->l('Fichier trop volumineux. Taille maximum: 10MB')
            ];
        }

        $fileName = $lot->lot_number . '_' . date('Y-m-d_H-i-s') . '_' . $_FILES['import_file']['name'];
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['import_file']['tmp_name'], $filePath)) {
            $lot->file_name = $_FILES['import_file']['name'];
            $lot->file_path = $filePath;
            $lot->update();

            return [
                'success' => true,
                'message' => $this->l('Fichier uploadé avec succès'),
                'file_path' => $filePath
            ];
        } else {
            return [
                'success' => false,
                'message' => $this->l('Erreur lors de l\'upload du fichier')
            ];
        }
    }

    private function createSampleProducts($lot)
    {
        $sampleProducts = [
            [
                'name' => 'iPhone 11 64GB Black Grade B',
                'serial' => '351234567890123',
                'price' => 450.00,
                'ref' => 'REF-IPHONE11-001'
            ],
            [
                'name' => 'Samsung Galaxy S20 128GB Blue',
                'serial' => '351987654321098',
                'price' => 380.00,
                'ref' => 'REF-SAMSUNG-002'
            ],
            [
                'name' => 'iPhone 12 Pro 256GB Space Gray',
                'serial' => '351456789012345',
                'price' => 680.00,
                'ref' => 'REF-IPHONE12-003'
            ],
            [
                'name' => 'Samsung Galaxy S21 écran cassé',
                'serial' => '351789012345678',
                'price' => 200.00,
                'ref' => 'REF-SAMSUNG-004'
            ],
            [
                'name' => 'iPad Air 64GB WiFi Silver',
                'serial' => '351123456789012',
                'price' => 320.00,
                'ref' => 'REF-IPAD-005'
            ]
        ];

        $productCount = 0;
        foreach ($sampleProducts as $sampleProduct) {
            $product = new LotManagerProduct();
            $product->id_lot = $lot->id;
            $product->raw_name = $sampleProduct['name'];
            $product->serial_number = $sampleProduct['serial'];
            $product->unit_price = $sampleProduct['price'];
            $product->supplier_reference = $sampleProduct['ref'];
            $product->quantity = 1;
            $product->status = 'pending';

            if ($product->add()) {
                $productCount++;
            }
        }

        // Mettre à jour les statistiques du lot
        $lot->updateStats();

        $this->confirmations[] = sprintf($this->l('%d produits d\'exemple créés'), $productCount);
    }

    public function displayProcessLink($token, $id)
    {
        return '<a class="btn btn-default" href="' . $this->context->link->getAdminLink('AdminLotManagerProducts') . '&id_lot=' . $id . '&qualification=1">
            <i class="icon-cogs"></i> ' . $this->l('Traiter') . '
        </a>';
    }

    public function displayViewLink($token, $id)
    {
        return '<a class="btn btn-default" href="' . $this->context->link->getAdminLink('AdminLotManagerLots') . '&id_lot=' . $id . '&viewlot_manager_lot">
            <i class="icon-eye"></i>
        </a>';
    }

    public function renderView()
    {
        $id_lot = (int) Tools::getValue('id_lot');
        $lot = new LotManagerLot($id_lot);

        if (!Validate::isLoadedObject($lot)) {
            $this->errors[] = $this->l('Lot introuvable');
            return;
        }

        $products = $lot->getProducts();
        $supplier = $lot->getSupplier();

        // Statistiques du lot
        $stats = [
            'total_products' => count($products),
            'pending_products' => count(array_filter($products, function ($p) {
                return $p['status'] == 'pending'; })),
            'functional_products' => count(array_filter($products, function ($p) {
                return $p['status'] == 'functional'; })),
            'defective_products' => count(array_filter($products, function ($p) {
                return $p['status'] == 'defective'; })),
            'total_cost' => array_sum(array_map(function ($p) {
                return $p['unit_price'] * $p['quantity']; }, $products)),
            'estimated_value' => array_sum(array_map(function ($p) {
                return $p['sale_price'] ?: 0; }, $products))
        ];

        $this->context->smarty->assign([
            'lot' => $lot,
            'products' => $products,
            'supplier' => $supplier,
            'stats' => $stats,
            'import_link' => $this->context->link->getAdminLink('AdminLotManagerImport') . '&id_lot=' . $lot->id,
            'qualification_link' => $this->context->link->getAdminLink('AdminLotManagerProducts') . '&id_lot=' . $lot->id . '&qualification=1'
        ]);

        return $this->createTemplate('lot_view.tpl')->fetch();
    }
}