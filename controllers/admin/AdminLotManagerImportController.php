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

    if (Tools::isSubmit('submitMapping')) {
      $this->processMapping($lot);
    }

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

    if (($handle = fopen($filePath, "r")) !== false) {
      // Tenter de détecter le délimiteur
      $firstLine = fgets($handle);
      rewind($handle);
      $delimiter = ',';
      if (strpos($firstLine, ';') !== false) {
        $delimiter = ';';
      }

      // Lire l'en-tête
      $header = fgetcsv($handle, 0, $delimiter);
      if ($header) {
        // Nettoyer les caractères invisibles des en-têtes (comme le BOM UTF-8)
        $header = array_map(function ($h) {
          return preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', trim($h));
        }, $header);

        $analysis['columns'] = $header;

        // Lire quelques lignes d'exemple
        $sampleCount = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false && $sampleCount < 5) {
          if (count($data) === count($header)) {
            $analysis['sample_data'][] = array_combine($header, $data);
            $sampleCount++;
          }
        }

        // Compter le reste des lignes
        while (fgetcsv($handle, 0, $delimiter) !== false) {
          $analysis['total_rows']++;
        }
        $analysis['total_rows'] += $sampleCount;
      }
      fclose($handle);
    }

    return $analysis;
  }

  private function analyzeExcelFile($filePath)
  {
    try {
      $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
      $worksheet = $spreadsheet->getActiveSheet();
      $header = [];
      $sample_data = [];
      $total_rows = $worksheet->getHighestRow();

      // Lire l'en-tête (première ligne)
      foreach ($worksheet->getRowIterator(1, 1) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
          $header[] = trim($cell->getValue());
        }
      }

      // Lire jusqu'à 5 lignes d'exemple (lignes 2 à 6)
      for ($rowIndex = 2; $rowIndex <= min(6, $total_rows); $rowIndex++) {
        $rowData = [];
        $colIndex = 0;
        $rowIterator = $worksheet->getRowIterator($rowIndex, 1);
        if ($rowIterator->valid()) {
          $row = $rowIterator->current();
          $cellIterator = $row->getCellIterator();
          $cellIterator->setIterateOnlyExistingCells(false);
          foreach ($cellIterator as $cell) {
            if (isset($header[$colIndex])) {
              $rowData[$header[$colIndex]] = trim($cell->getValue());
            }
            $colIndex++;
          }
        }
        if (!empty(array_filter($rowData))) { // N'ajoute pas de lignes vides
          $sample_data[] = $rowData;
        }
      }

      return [
        'success' => true,
        'file_type' => 'Excel',
        'columns' => $header,
        'sample_data' => $sample_data,
        'total_rows' => $total_rows > 0 ? $total_rows - 1 : 0
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'message' => 'Erreur lors de l\'analyse du fichier Excel: ' . $e->getMessage(),
        'columns' => [],
        'sample_data' => []
      ];
    }
  }

  private function getMappingFields()
  {
    return [
      'product_name' => [
        'label' => $this->l('Nom du produit'),
        'required' => true,
        'description' => $this->l('Nom ou désignation du produit'),
        'auto_patterns' => ['designation', 'nom', 'produit', 'libelle', 'name', 'product', 'description']
      ],
      'serial_number' => [
        'label' => $this->l('Numéro de série / IMEI'),
        'required' => false,
        'description' => $this->l('Identifiant unique du produit'),
        'auto_patterns' => ['serie', 'serial', 'imei', 'sn', 'numero', 'id']
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
        'auto_patterns' => ['prix', 'price', 'cout', 'cost', 'unitaire', 'bid price per unit']
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
            LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_suppliers` s ON mp.id_supplier = s.id_supplier
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

    $requiredFields = ['product_name', 'quantity', 'unit_price'];
    foreach ($requiredFields as $field) {
      if (empty($mapping[$field])) {
        $this->errors[] = sprintf($this->l('Le champ "%s" est obligatoire'), $mappingFields[$field]['label']);
        return;
      }
    }

    if (Tools::getValue('save_profile') && Tools::getValue('profile_name')) {
      $this->saveMappingProfile($mapping, Tools::getValue('profile_name'), $lot->id_supplier);
    }

    $importResult = $this->importProductsWithMapping($lot, $mapping);

    if ($importResult['success']) {
      $this->confirmations[] = sprintf(
        $this->l('%d produits importés avec succès sur %d lignes analysées'),
        $importResult['imported_count'],
        $importResult['total_rows']
      );

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
      'mapping' => pSQL(json_encode($mapping)),
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

    if (($handle = fopen($lot->file_path, "r")) !== false) {
      $delimiter = ',';
      $firstLine = fgets($handle);
      rewind($handle);
      if (strpos($firstLine, ';') !== false) {
        $delimiter = ';';
      }

      $header = fgetcsv($handle, 0, $delimiter);
      $header = array_map(function ($h) {
        return preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', trim($h)); }, $header);

      $columnIndex = [];
      foreach ($mapping as $field => $columnName) {
        if (!empty($columnName)) {
          $index = array_search($columnName, $header);
          if ($index !== false) {
            $columnIndex[$field] = $index;
          }
        }
      }

      while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (count($data) !== count($header) || empty(array_filter($data))) {
          continue;
        }
        $totalRows++;

        $quantity = (int) $this->getImportFieldValue($data, $columnIndex, 'quantity', 1);
        if ($quantity < 1)
          $quantity = 1;

        $baseSerialNumber = $this->getImportFieldValue($data, $columnIndex, 'serial_number');

        for ($i = 0; $i < $quantity; $i++) {
          $product = new LotManagerProduct();
          $product->id_lot = $lot->id;

          if ($quantity > 1 && !empty($baseSerialNumber)) {
            $product->serial_number = $baseSerialNumber . '-' . ($i + 1);
          } else {
            $product->serial_number = $baseSerialNumber;
          }

          $product->quantity = 1; // Chaque produit est unique
          $product->raw_name = $this->getImportFieldValue($data, $columnIndex, 'product_name', 'Produit sans nom');
          $product->unit_price = (float) str_replace(',', '.', $this->getImportFieldValue($data, $columnIndex, 'unit_price', 0));
          $product->supplier_reference = $this->getImportFieldValue($data, $columnIndex, 'supplier_reference');
          $product->status = 'pending';

          $additionalData = [];
          $additionalData['brand'] = $this->getImportFieldValue($data, $columnIndex, 'brand');
          $additionalData['model'] = $this->getImportFieldValue($data, $columnIndex, 'model');
          $additionalData['color'] = $this->getImportFieldValue($data, $columnIndex, 'color');
          $additionalData['storage'] = $this->getImportFieldValue($data, $columnIndex, 'storage');
          $additionalData['condition'] = $this->getImportFieldValue($data, $columnIndex, 'condition');
          $product->raw_name = $this->enrichProductName($product->raw_name, $additionalData);

          if ($product->add()) {
            $importedCount++;
          } else {
            $errors[] = "Ligne $totalRows, Item " . ($i + 1) . ": erreur d'ajout.";
          }
        }
      }
      fclose($handle);
    }

    $lot->updateStats();

    return [
      'success' => true,
      'imported_count' => $importedCount,
      'total_rows' => $totalRows,
      'warnings' => $errors,
    ];
  }

  private function importExcelWithMapping($lot, $mapping)
  {
    try {
      $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($lot->file_path);
      $worksheet = $spreadsheet->getActiveSheet();
      $totalRows = $worksheet->getHighestRow();
      $header = [];
      $importedCount = 0;
      $errors = [];

      foreach ($worksheet->getRowIterator(1, 1) as $row) {
        foreach ($row->getCellIterator() as $cell) {
          $header[] = trim($cell->getValue());
        }
      }

      $columnIndex = [];
      foreach ($mapping as $field => $columnName) {
        if (!empty($columnName)) {
          $index = array_search($columnName, $header);
          if ($index !== false) {
            $columnIndex[$field] = $index;
          }
        }
      }

      for ($rowIndex = 2; $rowIndex <= $totalRows; $rowIndex++) {
        $rowIterator = $worksheet->getRowIterator($rowIndex, 1);
        if (!$rowIterator->valid())
          continue;

        $data = [];
        $cellIterator = $rowIterator->current()->getCellIterator();
        foreach ($cellIterator as $cell) {
          $data[] = $cell->getValue();
        }

        if (empty(array_filter($data)))
          continue;

        $quantity = (int) $this->getImportFieldValue($data, $columnIndex, 'quantity', 1);
        if ($quantity < 1)
          $quantity = 1;

        $baseSerialNumber = $this->getImportFieldValue($data, $columnIndex, 'serial_number');

        for ($i = 0; $i < $quantity; $i++) {
          $product = new LotManagerProduct();
          $product->id_lot = $lot->id;

          if ($quantity > 1 && !empty($baseSerialNumber)) {
            $product->serial_number = $baseSerialNumber . '-' . ($i + 1);
          } else {
            $product->serial_number = $baseSerialNumber;
          }

          $product->quantity = 1; // Chaque produit est unique
          $product->raw_name = $this->getImportFieldValue($data, $columnIndex, 'product_name', 'Produit sans nom');
          $product->unit_price = (float) str_replace(',', '.', $this->getImportFieldValue($data, $columnIndex, 'unit_price', 0));
          $product->supplier_reference = $this->getImportFieldValue($data, $columnIndex, 'supplier_reference');
          $product->status = 'pending';

          $additionalData = [];
          $additionalData['brand'] = $this->getImportFieldValue($data, $columnIndex, 'brand');
          $additionalData['model'] = $this->getImportFieldValue($data, $columnIndex, 'model');
          $additionalData['color'] = $this->getImportFieldValue($data, $columnIndex, 'color');
          $additionalData['storage'] = $this->getImportFieldValue($data, $columnIndex, 'storage');
          $additionalData['condition'] = $this->getImportFieldValue($data, $columnIndex, 'condition');
          $product->raw_name = $this->enrichProductName($product->raw_name, $additionalData);

          if ($product->add()) {
            $importedCount++;
          } else {
            $errors[] = "Ligne $rowIndex, Item " . ($i + 1) . ": erreur d'ajout.";
          }
        }
      }

      $lot->updateStats();

      return [
        'success' => true,
        'imported_count' => $importedCount,
        'total_rows' => $totalRows > 0 ? $totalRows - 1 : 0,
        'warnings' => $errors,
      ];

    } catch (\Exception $e) {
      return ['success' => false, 'message' => 'Erreur lors de l\'importation: ' . $e->getMessage()];
    }
  }

  private function getImportFieldValue($data, $columnIndex, $field, $default = '')
  {
    if (isset($columnIndex[$field]) && isset($data[$columnIndex[$field]])) {
      return trim($data[$columnIndex[$field]]);
    }
    return $default;
  }

  private function enrichProductName($baseName, $additionalData)
  {
    $enrichedName = $baseName;
    foreach ($additionalData as $key => $value) {
      if (!empty($value) && stripos($enrichedName, (string) $value) === false) {
        $enrichedName .= ' ' . $value;
      }
    }
    return $enrichedName;
  }

  public function ajaxProcessLoadMappingProfile()
  {
    $profileId = (int) Tools::getValue('profile_id');
    $profile = Db::getInstance()->getRow('
            SELECT * FROM `' . _DB_PREFIX_ . 'lot_manager_mapping_profiles` 
            WHERE id_mapping_profile = ' . $profileId
    );
    if ($profile) {
      $mapping = json_decode($profile['mapping'], true);
      die(json_encode(['success' => true, 'mapping' => $mapping]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Profil introuvable']));
    }
  }

  public function ajaxProcessAutoDetectMapping()
  {
    $columns = Tools::getValue('columns');
    if (!is_array($columns)) {
      die(json_encode(['success' => false, 'mapping' => []]));
    }
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