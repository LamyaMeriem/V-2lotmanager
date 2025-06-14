<?php
class LotManagerAudit extends ObjectModel
{
  public $id;
  public $id_employee;
  public $id_lot;
  public $id_lot_product;
  public $action;
  public $details;
  public $imei;
  public $date_add;

  public static $definition = [
    'table' => 'lot_manager_audit',
    'primary' => 'id_audit',
    'fields' => [
      'id_employee' => ['type' => self::TYPE_INT, 'required' => true],
      'id_lot' => ['type' => self::TYPE_INT],
      'id_lot_product' => ['type' => self::TYPE_INT],
      'action' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 100],
      'details' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
      'imei' => ['type' => self::TYPE_STRING, 'size' => 100],
      'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
    ],
  ];
}
