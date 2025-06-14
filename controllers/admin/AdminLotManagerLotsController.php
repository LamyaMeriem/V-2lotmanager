<?php
/**
 * Lot Manager - Lots Controller
 */

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
                    'required' => true,
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
                    'desc' => $this->l('Formats supportés: .xlsx, .csv, .pdf')
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
        $lot->id_supplier = (int)Tools::getValue('id_supplier');
        $lot->status = Tools::getValue('status', 'pending');
        
        if ($lot->add()) {
            // Traitement du fichier d'import si présent
            if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == 0) {
                $this->processImportFile($lot);
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
        
        $fileName = $lot->lot_number . '_' . $_FILES['import_file']['name'];
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['import_file']['tmp_name'], $filePath)) {
            $lot->file_name = $_FILES['import_file']['name'];
            $lot->file_path = $filePath;
            $lot->update();
            
            // Redirection vers l'interface de mapping
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminLotManagerLots') . '&mapping&id_lot=' . $lot->id);
        }
    }

    public function displayProcessLink($token, $id)
    {
        return '<a class="btn btn-default" href="' . $this->context->link->getAdminLink('AdminLotManagerProducts') . '&id_lot=' . $id . '">
            <i class="icon-cogs"></i> ' . $this->l('Traiter') . '
        </a>';
    }
}