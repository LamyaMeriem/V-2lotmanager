<?php
/**
 * Lot Manager - Configuration Controller
 */

require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';


class AdminLotManagerConfigurationController extends ModuleAdminController
{
  public function __construct()
  {
    $this->bootstrap = true;
    $this->context = Context::getContext();

    parent::__construct();

    $this->meta_title = $this->l('Configuration - Gestionnaire de Lots');
  }

  public function initContent()
  {
    parent::initContent();

    // Traitement des formulaires
    if (Tools::isSubmit('submitSupplier')) {
      $this->processSupplierForm();
    }

    if (Tools::isSubmit('submitDefect')) {
      $this->processDefectForm();
    }

    if (Tools::isSubmit('submitDictionary')) {
      $this->processDictionaryForm();
    }

    // Récupération des données
    $suppliers = $this->getSuppliers();
    $defects = $this->getDefects();
    $dictionary = $this->getDictionary();

    $this->context->smarty->assign([
      'suppliers' => $suppliers,
      'defects' => $defects,
      'dictionary' => $dictionary,
      'current_tab' => Tools::getValue('tab', 'suppliers')
    ]);

    $this->setTemplate('configuration.tpl');
  }

  private function getSuppliers()
  {
    $sql = new DbQuery();
    $sql->select('s.*, COUNT(l.id_lot) as total_lots');
    $sql->from('lot_manager_suppliers', 's');
    $sql->leftJoin('lot_manager_lots', 'l', 's.id_supplier = l.id_supplier');
    $sql->groupBy('s.id_supplier');
    $sql->orderBy('s.name ASC');

    return Db::getInstance()->executeS($sql);
  }

  private function getDefects()
  {
    return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_defects` 
            ORDER BY frequency DESC, name ASC
        ');
  }

  private function getDictionary()
  {
    return Db::getInstance()->executeS('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'lot_manager_dictionary` 
            WHERE active = 1
            ORDER BY category, priority DESC
        ');
  }

  private function processSupplierForm()
  {
    $name = Tools::getValue('supplier_name');
    $contact = Tools::getValue('supplier_contact');
    $email = Tools::getValue('supplier_email');
    $phone = Tools::getValue('supplier_phone');
    $address = Tools::getValue('supplier_address');

    if (empty($name)) {
      $this->errors[] = $this->l('Le nom du fournisseur est obligatoire');
      return;
    }

    $supplier = new LotManagerSupplier();
    $supplier->name = $name;
    $supplier->contact_name = $contact;
    $supplier->email = $email;
    $supplier->phone = $phone;
    $supplier->address = $address;
    $supplier->active = 1;

    if ($supplier->add()) {
      $this->confirmations[] = $this->l('Fournisseur ajouté avec succès');
    } else {
      $this->errors[] = $this->l('Erreur lors de l\'ajout du fournisseur');
    }
  }

  private function processDefectForm()
  {
    $name = Tools::getValue('defect_name');
    $description = Tools::getValue('defect_description');

    if (empty($name)) {
      $this->errors[] = $this->l('Le nom de la panne est obligatoire');
      return;
    }

    $defect = new LotManagerDefect();
    $defect->name = $name;
    $defect->description = $description;
    $defect->frequency = 0;
    $defect->active = 1;

    if ($defect->add()) {
      $this->confirmations[] = $this->l('Panne ajoutée avec succès');
    } else {
      $this->errors[] = $this->l('Erreur lors de l\'ajout de la panne');
    }
  }

  private function processDictionaryForm()
  {
    $category = Tools::getValue('dict_category');
    $pattern = Tools::getValue('dict_pattern');
    $replacement = Tools::getValue('dict_replacement');
    $priority = (int) Tools::getValue('dict_priority', 0);

    if (empty($category) || empty($pattern) || empty($replacement)) {
      $this->errors[] = $this->l('Tous les champs sont obligatoires');
      return;
    }

    $result = Db::getInstance()->insert('lot_manager_dictionary', [
      'category' => pSQL($category),
      'pattern' => pSQL($pattern),
      'replacement' => pSQL($replacement),
      'priority' => (int) $priority,
      'active' => 1,
      'date_add' => date('Y-m-d H:i:s'),
      'date_upd' => date('Y-m-d H:i:s')
    ]);

    if ($result) {
      $this->confirmations[] = $this->l('Règle de dictionnaire ajoutée avec succès');
    } else {
      $this->errors[] = $this->l('Erreur lors de l\'ajout de la règle');
    }
  }

  public function ajaxProcessToggleDefect()
  {
    $id_defect = (int) Tools::getValue('id_defect');
    $active = (int) Tools::getValue('active');

    $result = Db::getInstance()->update(
      'lot_manager_defects',
      ['active' => $active],
      'id_defect = ' . $id_defect
    );

    if ($result) {
      die(json_encode(['success' => true]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']));
    }
  }

  public function ajaxProcessDeleteSupplier()
  {
    $id_supplier = (int) Tools::getValue('id_supplier');

    // Vérifier qu'aucun lot n'utilise ce fournisseur
    $count = Db::getInstance()->getValue('
            SELECT COUNT(*) 
            FROM `' . _DB_PREFIX_ . 'lot_manager_lots` 
            WHERE id_supplier = ' . $id_supplier
    );

    if ($count > 0) {
      die(json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer : ce fournisseur est utilisé dans ' . $count . ' lot(s)'
      ]));
    }

    $supplier = new LotManagerSupplier($id_supplier);
    if ($supplier->delete()) {
      die(json_encode(['success' => true]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']));
    }
  }

  public function ajaxProcessDeleteDefect()
  {
    $id_defect = (int) Tools::getValue('id_defect');

    // Vérifier qu'aucun produit n'utilise cette panne
    $count = Db::getInstance()->getValue('
            SELECT COUNT(*) 
            FROM `' . _DB_PREFIX_ . 'lot_manager_product_defects` 
            WHERE id_defect = ' . $id_defect
    );

    if ($count > 0) {
      die(json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer : cette panne est utilisée sur ' . $count . ' produit(s)'
      ]));
    }

    $defect = new LotManagerDefect($id_defect);
    if ($defect->delete()) {
      die(json_encode(['success' => true]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']));
    }
  }
}