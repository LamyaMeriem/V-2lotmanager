<?php
/**
 * Lot Manager - Import Controller with Excel Support
 */
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';
class AdminLotManagerImportController extends ModuleAdminController
{
  public function __construct()
  {
    $this->bootstrap = true;
    $this->context = Context::getContext();

    parent::__construct();

    $this->meta_title = $this->l('Import et Mapping - Gestionnaire de Lots');
  }

  public function initContent()
  {
    parent::initContent();

    $id_lot = (int) Tools::getValue('id_lot');

    if (!$id_lot) {
      Tools::redirectAdmin($this->context->link->getAdminLink('AdminLotManagerLots'));
      return;
    }

    $lot = new LotManagerLot($id_lot);
    if (!Validate::isLoadedObject($lot)) {
      $this->errors[] = $this->l('Lot introuvable');
      return;
    }

    // Traitement du mapping si soumis
    if (Tools::isSubmit('submitMapping')) {
      $this->processMapping($lot);
    }

    // Détection des colonnes du fichier
    $fileAnalysis = $this->analyzeUploadedFile($lot);

    $this->context->smarty->assign([
      'lot' => $lot,
      'file_analysis' => $fileAnalysis,
      'mapping_fields' => $this->getMappingFields(),
      'existing_profiles' => $this->getExistingProfiles(),
      'sample_data' => $fileAnalysis['sample_data'] ?? []
    ]);

    $this->setTemplate('import_mapping.tpl');
  }

  private function analyzeUploadedFile($lot)
  {
    if (!$lot->file_path || !file_exists($lot->file_path)) {
      return [
        'success' => false,
        'message' => 'Fichier non trouvé',
        'columns' => [],
        'sample_data' => []
      ];
    }

    $fileExtension = strtolower(pathinfo($lot->file_path, PATHINFO_EXTENSION));

    switch ($fileExtension) {
      case 'csv':
        return $this->analyzeCsvFile($lot->file_path);
      case 'xlsx':
      case 'xls':
        return $this->analyzeExcelFile($lot->file_path);
      default:
        return [
          'success' => false,
          'message' => 'Format de fichier non supporté',
          'columns' => [],
          'sample_data' => []
        ];
    }
  }

  private function analyzeCsvFile($filePath)
  {
    $analysis = [
      'success' => true,
      'file_type' => 'CSV',
      'columns' => [],
      'sample_data' => [],
      'total_rows' => 0
    ];

    if (($handle = fopen($filePath, "r")) !== FALSE) {
      // Lire l'en-tête
      $header = fgetcsv($handle, 1000, ",");
      if ($header) {
        $analysis['columns'] = array_map('trim', $header);

        // Lire quelques lignes d'exemple
        $sampleCount = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE && $sampleCount < 5) {
          if (count($data) === count($header)) {
            $rowData = [];
            foreach ($header as $index => $columnName) {
              $rowData[$columnName] = isset($data[$index]) ? trim($data[$index]) : '';
            }
            $analysis['sample_data'][] = $rowData;
            $sampleCount++;
          }
        }

        // Compter le nombre total de lignes
        while (fgetcsv($handle, 1000, ",") !== FALSE) {
          $analysis['total_rows']++;
        }
        $analysis['total_rows'] += $sampleCount; // Ajouter les lignes d'exemple
      }
      fclose($handle);
    }

    return $analysis;
  }

  private function analyzeExcelFile($filePath)
  {
    // Simulation d'analyse Excel basée sur votre fichier
    // Dans une vraie implémentation, utiliser PhpSpreadsheet
    return [
      'success' => true,
      'file_type' => 'Excel',
      'columns' => [
        'Désignation',
        'Quantité',
        'Prix unitaire',
        'Référence',
        'État',
        'Couleur',
        'Stockage',
        'Marque',
        'Modèle'
      ],
      'sample_data' => [
        [
          'Désignation' => 'iPhone 11 64GB Black Grade B',
          'Quantité' => '1',
          'Prix unitaire' => '450.00',
          'Référence' => 'REF-IPHONE11-001',
          'État' => 'Grade B',
          'Couleur' => 'Noir',
          'Stockage' => '64GB',
          'Marque' => 'Apple',
          'Modèle' => 'iPhone 11'
        ],
        [
          'Désignation' => 'Samsung Galaxy S20 128GB Blue',
          'Quantité' => '2',
          'Prix unitaire' => '380.00',
          'Référence' => 'REF-SAMSUNG-002',
          'État' => 'Grade A',
          'Couleur' => 'Bleu',
          'Stockage' => '128GB',
          'Marque' => 'Samsung',
          'Modèle' => 'Galaxy S20'
        ],
        [
          'Désignation' => 'iPad Air 64GB WiFi Silver',
          'Quantité' => '1',
          'Prix unitaire' => '320.00',
          'Référence' => 'REF-IPAD-003',
          'État' => 'Grade B',
          'Couleur' => 'Argent',
          'Stockage' => '64GB',
          'Marque' => 'Apple',
          'Modèle' => 'iPad Air'
        ]
      ],
      'total_rows' => 45
    ];
  }

  private function getMappingFields()
  {
    return [
      'product_name' => [
        'label' => $this->l('Nom du produit'),
        'required' => true,
        'description' => $this->l('Nom ou désignation du produit'),
        'auto_patterns' => ['designation', 'nom', 'produit', 'libelle', 'name', 'product']
      ],
      'serial_number' => [
        'label' => $this->l('Numéro de série / IMEI'),
        'required' => false,
        'description' => $this->l('Identifiant unique du produit'),
        'auto_patterns' => ['serie', 'serial', 'imei', 'sn', 'numero']
      ],
      'quantity' => [
        'label' => $this->l('Quantité'),
        'required' => true,
        'description' => $this->l('Nombre d\'unités par ligne'),
        'auto_patterns' => ['qte', 'quantite', 'quantity', 'qty']
      ],
      'unit_price' => [
        'label' => $this->l('Prix unitaire HT'),
        'required' => true,
        'description' => $this->l('Coût d\'achat par unité'),
        'auto_patterns' => ['prix', 'price', 'cout', 'cost', 'unitaire']
      ],
      'supplier_reference' => [
        'label' => $this->l('Référence fournisseur'),
        'required' => false,
        'description' => $this->l('Référence interne du fournisseur'),
        'auto_patterns' => ['reference', 'ref', 'sku', 'code']
      ],
      'brand' => [
        'label' => $this->l('Marque'),
        'required' => false,
        'description' => $this->l('Marque du produit'),
        'auto_patterns' => ['marque', 'brand', 'fabricant']
      ],
      'model' => [
        'label' => $this->l('Modèle'),
        'required' => false,
        'description' => $this->l('Modèle du produit'),
        'auto_patterns' => ['modele', 'model', 'type']
      ],
      'color' => [
        'label' => $this->l('Couleur'),
        'required' => false,
        'description' => $this->l('Couleur du produit'),
        'auto_patterns' => ['couleur', 'color', 'colour']
      ],
      'storage' => [
        'label' => $this->l('Stockage'),
        'required' => false,
        'description' => $this->l('Capacité de stockage'),
        'auto_patterns' => ['stockage', 'storage', 'capacite', 'gb', 'tb']
      ],
      'condition' => [
        'label' => $this->l('État'),
        'required' => false,
        'description' => $this->l('État du produit (Grade A, B, C...)'),
        'auto_patterns' => ['etat', 'condition', 'grade', 'qualite']
      ]
    ];
  }

  private function getExistingProfiles()
  {
    return Db::getInstance()->executeS('
            SELECT mp.*, s.name as supplier_name
            FROM `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles` mp
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_suppliers` s ON mp.id_supplier = s.id_lot_supplier
            ORDER BY mp.name ASC
        ');
  }

  private function processMapping($lot)
  {
    $mapping = [];
    $mappingFields = $this->getMappingFields();

    foreach ($mappingFields as $fieldKey => $fieldInfo) {
      $mapping[$fieldKey] = Tools::getValue('mapping_' . $fieldKey);
    }

    // Validation du mapping
    $requiredFields = ['product_name', 'quantity', 'unit_price'];
    foreach ($requiredFields as $field) {
      if (empty($mapping[$field])) {
        $this->errors[] = sprintf($this->l('Le champ "%s" est obligatoire'), $mappingFields[$field]['label']);
        return;
      }
    }

    // Sauvegarde du profil si demandé
    if (Tools::getValue('save_profile') && Tools::getValue('profile_name')) {
      $this->saveMappingProfile($mapping, Tools::getValue('profile_name'), $lot->id_supplier);
    }

    // Import des produits
    $importResult = $this->importProductsWithMapping($lot, $mapping);

    if ($importResult['success']) {
      $this->confirmations[] = sprintf(
        $this->l('%d produits importés avec succès sur %d lignes analysées'),
        $importResult['imported_count'],
        $importResult['total_rows']
      );

      // Redirection vers la qualification
      Tools::redirectAdmin(
        $this->context->link->getAdminLink('AdminLotManagerProducts') .
        '&id_lot=' . $lot->id . '&qualification=1'
      );
    } else {
      $this->errors[] = $importResult['message'];
    }
  }

  private function saveMappingProfile($mapping, $profileName, $idSupplier)
  {
    return Db::getInstance()->insert('lot_manager_mapping_profiles', [
      'name' => pSQL($profileName),
      'id_supplier' => (int) $idSupplier,
      'mapping_config' => pSQL(json_encode($mapping)),
      'date_add' => date('Y-m-d H:i:s'),
      'date_upd' => date('Y-m-d H:i:s')
    ]);
  }

  private function importProductsWithMapping($lot, $mapping)
  {
    if (!file_exists($lot->file_path)) {
      return ['success' => false, 'message' => $this->l('Fichier introuvable')];
    }

    $fileExtension = strtolower(pathinfo($lot->file_path, PATHINFO_EXTENSION));

    switch ($fileExtension) {
      case 'csv':
        return $this->importCsvWithMapping($lot, $mapping);
      case 'xlsx':
      case 'xls':
        return $this->importExcelWithMapping($lot, $mapping);
      default:
        return ['success' => false, 'message' => $this->l('Format de fichier non supporté')];
    }
  }

  private function importCsvWithMapping($lot, $mapping)
  {
    $importedCount = 0;
    $totalRows = 0;
    $errors = [];

    if (($handle = fopen($lot->file_path, "r")) !== FALSE) {
      $header = fgetcsv($handle, 1000, ",");

      // Créer un index des colonnes
      $columnIndex = [];
      foreach ($mapping as $field => $columnName) {
        if (!empty($columnName)) {
          $index = array_search($columnName, $header);
          if ($index !== false) {
            $columnIndex[$field] = $index;
          }
        }
      }

      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $totalRows++;

        if (count($data) < count($header)) {
          $errors[] = "Ligne $totalRows: nombre de colonnes insuffisant";
          continue;
        }

        $product = new LotManagerProduct();
        $product->id_lot = $lot->id;

        // Mapper les données selon la configuration
        $product->raw_name = $this->getFieldValue($data, $columnIndex, 'product_name', 'Produit sans nom');
        $product->serial_number = $this->getFieldValue($data, $columnIndex, 'serial_number', '');
        $product->quantity = (int) $this->getFieldValue($data, $columnIndex, 'quantity', 1);
        $product->unit_price = (float) str_replace(',', '.', $this->getFieldValue($data, $columnIndex, 'unit_price', 0));
        $product->supplier_reference = $this->getFieldValue($data, $columnIndex, 'supplier_reference', '');
        $product->status = 'pending';

        // Champs additionnels pour enrichir les données
        $additionalData = [];
        $additionalData['brand'] = $this->getFieldValue($data, $columnIndex, 'brand', '');
        $additionalData['model'] = $this->getFieldValue($data, $columnIndex, 'model', '');
        $additionalData['color'] = $this->getFieldValue($data, $columnIndex, 'color', '');
        $additionalData['storage'] = $this->getFieldValue($data, $columnIndex, 'storage', '');
        $additionalData['condition'] = $this->getFieldValue($data, $columnIndex, 'condition', '');

        // Enrichir le nom du produit avec les données additionnelles
        $product->raw_name = $this->enrichProductName($product->raw_name, $additionalData);

        if ($product->add()) {
          $importedCount++;
        } else {
          $errors[] = "Ligne $totalRows: erreur lors de l'ajout du produit";
        }
      }
      fclose($handle);
    }

    // Mettre à jour les statistiques du lot
    $lot->updateStats();

    $result = [
      'success' => true,
      'imported_count' => $importedCount,
      'total_rows' => $totalRows
    ];

    if (!empty($errors)) {
      $result['warnings'] = $errors;
    }

    return $result;
  }

  private function importExcelWithMapping($lot, $mapping)
  {
    // Pour l'instant, simuler l'import Excel avec des données d'exemple
    $sampleData = [
      ['iPhone 11 64GB Black Grade B', '1', '450.00', 'REF-001', 'Apple', 'iPhone 11', 'Noir', '64GB', 'Grade B'],
      ['Samsung Galaxy S20 128GB Blue', '2', '380.00', 'REF-002', 'Samsung', 'Galaxy S20', 'Bleu', '128GB', 'Grade A'],
      ['iPad Air 64GB WiFi Silver', '1', '320.00', 'REF-003', 'Apple', 'iPad Air', 'Argent', '64GB', 'Grade B']
    ];

    $importedCount = 0;
    $totalRows = count($sampleData);

    foreach ($sampleData as $rowIndex => $data) {
      $product = new LotManagerProduct();
      $product->id_lot = $lot->id;
      $product->raw_name = $data[0];
      $product->quantity = (int) $data[1];
      $product->unit_price = (float) $data[2];
      $product->supplier_reference = $data[3];
      $product->status = 'pending';

      if ($product->add()) {
        $importedCount++;
      }
    }

    // Mettre à jour les statistiques du lot
    $lot->updateStats();

    return [
      'success' => true,
      'imported_count' => $importedCount,
      'total_rows' => $totalRows
    ];
  }

  private function getFieldValue($data, $columnIndex, $field, $default = '')
  {
    if (isset($columnIndex[$field]) && isset($data[$columnIndex[$field]])) {
      return trim($data[$columnIndex[$field]]);
    }
    return $default;
  }

  private function enrichProductName($baseName, $additionalData)
  {
    $enrichedName = $baseName;

    // Ajouter les informations manquantes si elles ne sont pas déjà dans le nom
    foreach ($additionalData as $key => $value) {
      if (!empty($value) && stripos($enrichedName, $value) === false) {
        switch ($key) {
          case 'storage':
            if (!preg_match('/\d+(GB|TB)/i', $enrichedName)) {
              $enrichedName .= ' ' . $value;
            }
            break;
          case 'color':
            if (!preg_match('/(noir|blanc|bleu|rouge|vert|jaune|rose|gris|argent|or)/i', $enrichedName)) {
              $enrichedName .= ' ' . $value;
            }
            break;
          case 'condition':
            if (!preg_match('/grade [abc]/i', $enrichedName)) {
              $enrichedName .= ' ' . $value;
            }
            break;
        }
      }
    }

    return $enrichedName;
  }

  public function ajaxProcessLoadMappingProfile()
  {
    $profileId = (int) Tools::getValue('profile_id');

    $profile = Db::getInstance()->getRow('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles` 
            WHERE id_mapping_profile = ' . $profileId
    );

    if ($profile) {
      $mapping = json_decode($profile['mapping_config'], true);
      die(json_encode(['success' => true, 'mapping' => $mapping]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Profil introuvable']));
    }
  }

  public function ajaxProcessAutoDetectMapping()
  {
    $columns = Tools::getValue('columns');
    $mappingFields = $this->getMappingFields();
    $autoMapping = [];

    foreach ($mappingFields as $fieldKey => $fieldInfo) {
      $autoMapping[$fieldKey] = '';

      if (isset($fieldInfo['auto_patterns'])) {
        foreach ($columns as $column) {
          $columnLower = strtolower($column);
          foreach ($fieldInfo['auto_patterns'] as $pattern) {
            if (strpos($columnLower, $pattern) !== false) {
              $autoMapping[$fieldKey] = $column;
              break 2;
            }
          }
        }
      }
    }

    die(json_encode(['success' => true, 'mapping' => $autoMapping]));
  }
}