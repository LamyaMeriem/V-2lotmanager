<?php
/**
 * Lot Manager - Products Controller
 */


require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerSupplier.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerProduct.php';
class AdminLotManagerProductsController extends ModuleAdminController
{
  public function __construct()
  {
    $this->table = 'lot_manager_products';
    $this->className = 'LotManagerProduct';
    $this->identifier = 'id_lot_product';
    $this->bootstrap = true;
    $this->context = Context::getContext();

    parent::__construct();

    $this->meta_title = $this->l('Gestion des Produits');

    $this->fields_list = [
      'id_lot_product' => [
        'title' => $this->l('ID'),
        'align' => 'center',
        'class' => 'fixed-width-xs'
      ],
      'lot_name' => [
        'title' => $this->l('Lot'),
        'width' => 140
      ],
      'raw_name' => [
        'title' => $this->l('Nom du Produit')
      ],
      'serial_number' => [
        'title' => $this->l('N° Série'),
        'width' => 120
      ],
      'status' => [
        'title' => $this->l('Statut'),
        'width' => 100,
        'type' => 'select',
        'list' => [
          'pending' => $this->l('En attente'),
          'functional' => $this->l('Fonctionnel'),
          'defective' => $this->l('Défectueux'),
          'cancelled' => $this->l('Annulé')
        ],
        'filter_key' => 'a!status'
      ],
      'unit_price' => [
        'title' => $this->l('Prix d\'achat'),
        'align' => 'right',
        'type' => 'price',
        'currency' => true
      ],
      'sale_price' => [
        'title' => $this->l('Prix de vente'),
        'align' => 'right',
        'type' => 'price',
        'currency' => true
      ],
      'sku' => [
        'title' => $this->l('SKU'),
        'width' => 120
      ]
    ];

    $this->addRowAction('view');
    $this->addRowAction('edit');

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
    $this->_select = 'l.name as lot_name';
    $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'lot_manager_lots` l ON a.id_lot = l.id_lot';

    // Filtrage par lot si spécifié
    $id_lot = (int) Tools::getValue('id_lot');
    if ($id_lot > 0) {
      $this->_where = 'AND a.id_lot = ' . $id_lot;

      // Afficher les informations du lot
      $lot = new LotManagerLot($id_lot);
      $this->context->smarty->assign([
        'current_lot' => $lot,
        'qualification_mode' => true
      ]);
    }

    return parent::renderList();
  }

  public function initContent()
  {
    // Si on est en mode qualification
    $id_lot = (int) Tools::getValue('id_lot');
    if ($id_lot > 0 && Tools::getValue('qualification')) {
      $this->displayQualificationInterface($id_lot);
      return;
    }

    parent::initContent();
  }

  private function displayQualificationInterface($id_lot)
  {
    $lot = new LotManagerLot($id_lot);
    $products = $lot->getProducts();
    $defects = LotManagerDefect::getActiveDefects();

    $this->context->smarty->assign([
      'lot' => $lot,
      'products' => $products,
      'defects' => $defects,
      'qualification_mode' => true
    ]);

    $this->setTemplate('qualification.tpl');
  }

  public function ajaxProcessUpdateProductStatus()
  {
    $id_product = (int) Tools::getValue('id_product');
    $status = Tools::getValue('status');

    if (!in_array($status, ['pending', 'functional', 'defective', 'cancelled'])) {
      die(json_encode(['success' => false, 'message' => 'Statut invalide']));
    }

    $product = new LotManagerProduct($id_product);
    if (!Validate::isLoadedObject($product)) {
      die(json_encode(['success' => false, 'message' => 'Produit introuvable']));
    }

    $product->status = $status;

    if ($product->update()) {
      die(json_encode(['success' => true]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']));
    }
  }

  public function ajaxProcessSearchProducts()
  {
    $query = Tools::getValue('query');

    if (strlen($query) < 3) {
      die(json_encode(['success' => false, 'message' => 'Requête trop courte']));
    }

    $products = LotManagerProduct::searchProducts($query);

    die(json_encode(['success' => true, 'products' => $products]));
  }

  public function ajaxProcessAssignProduct()
  {
    $id_lot_product = (int) Tools::getValue('id_lot_product');
    $id_product = (int) Tools::getValue('id_product');
    $id_product_attribute = (int) Tools::getValue('id_product_attribute');
    $sale_price = (float) Tools::getValue('sale_price');

    $lotProduct = new LotManagerProduct($id_lot_product);
    if (!Validate::isLoadedObject($lotProduct)) {
      die(json_encode(['success' => false, 'message' => 'Produit introuvable']));
    }

    if ($lotProduct->assignToProduct($id_product, $id_product_attribute, $sale_price)) {
      die(json_encode(['success' => true]));
    } else {
      die(json_encode(['success' => false, 'message' => 'Erreur lors de l\'assignation']));
    }
  }
}