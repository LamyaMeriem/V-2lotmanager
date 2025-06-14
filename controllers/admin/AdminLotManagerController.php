<?php
/**
 * Lot Manager - Main Admin Controller
 */

require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerLot.php';
require_once _PS_MODULE_DIR_ . 'lotmanager/classes/LotManagerDefect.php';

class AdminLotManagerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->meta_title = $this->l('Gestionnaire de Lots');
    }

    public function initContent()
    {
        parent::initContent();

        // Redirection vers le tableau de bord par défaut
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminLotManagerDashboard'));
    }
}