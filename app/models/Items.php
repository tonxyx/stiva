<?php

use Phalcon\Mvc\Model;

/**
 * Items
 */
class Items extends Model {

  /**
   * @var integer
   */
  public $id;

  /**
   * @var integer
   */
  public $parent_order;

  /**
   * @var integer
   */
  public $group;

  /**
   * @var integer
   */
  public $order_id;

  /**
   * @var integer
   */
  public $item_no;

  /**
   * @var integer
   */
  public $pal_cov;

  public $date;

  /**
   * @var integer
   */
  public $customer_order;

  /**
   * @var integer
   */
  public $type;

  /**
   * @var integer
   */
  public $set;

  /**
   * @var integer
   */
  public $quantity;

  /**
   * @var integer
   */
  public $width;

  /**
   * @var integer
   */
  public $length;

  /**
   * @var string
   */
  public $barcode;

  public $delivery_date;

  /**
   * Orders initializer
   */
  public function initialize () {
    $this->setSource("items");

    $this->belongsTo('parent_order', 'Orders', 'id', [
      'reusable' => true,
    ]);

    $this->belongsTo('item_id', 'Packages', 'id', [
      'reusable' => true,
    ]);
  }

  /**
   * Save order items for import data
   *
   * @param $data
   * @param $order
   */
  static public function saveCSVData ($data, $order) {
    $item = new Items();
    $item->group = $data[0];
    $item->parent_order = $order->id;
    $item->order_id = $data[1];
    $item->item_no = $data[2];
    $item->pal_cov = $data[3];
    $item->date = date('Y-m-d', strtotime($data[4]));
    $item->customer_order = $data[5];
    $item->type = $data[6];
    $item->set = $data[7];
    $item->quantity = $data[8];
    $item->width = $data[9];
    $item->length = $data[10];
    $item->barcode = $data[11];
    $item->delivery_date = $data[12];
    $item->save();

    // prepare data for packages too
    $calculationData = [];
    $typePackingParams = \Phalcon\Di::getDefault()->get('fixture')['types'][$item->type];

    $package = new Packages();
    $package->item_id = $item->id;

    if (count($typePackingParams) == 1) {
      $package->primary_no = $calculationData['primaryNo'] = intval($item->quantity/$typePackingParams[0]);
      $package->primary_quantity = $calculationData['primaryQuantity'] = $calculationData['primaryNo'] *
        $typePackingParams[0];
      $package->primary_leftover_quantity = $calculationData['primaryLeftoverQuantity'] = $item->quantity -
        $calculationData['primaryQuantity'];
      $package->total_quantity = $calculationData['totalQuantity'] = $calculationData['primaryQuantity'];
      $package->total_leftover_quantity = $calculationData['totalLeftoverQuantity'] = $item->quantity -
        $calculationData['totalQuantity'];

      $package->packing_info = $calculationData['packingInfo'] = sprintf('%d * %d + %d',
        $calculationData['primaryNo'], $typePackingParams[0], $calculationData['primaryLeftoverQuantity']);
    } else {
      $packingParamsSum = $typePackingParams[0] + $typePackingParams[1];
      $package->primary_no = $calculationData['primaryNo'] = intval($item->quantity/$packingParamsSum);
      $package->primary_quantity = $calculationData['primaryQuantity'] = $calculationData['primaryNo'] *
        $typePackingParams[0];
      $package->primary_leftover_quantity = $calculationData['primaryLeftoverQuantity'] = $item->quantity -
        $calculationData['primaryQuantity'];

      $package->secondary_no = $calculationData['secondaryNo'] =
        intval($calculationData['primaryLeftoverQuantity']/$typePackingParams[1]);
      $package->secondary_quantity = $calculationData['secondaryQuantity'] = $calculationData['secondaryNo'] *
        $typePackingParams[1];
      $package->total_quantity = $calculationData['totalQuantity'] = $calculationData['primaryQuantity'] +
        $calculationData['secondaryQuantity'];
      $package->total_leftover_quantity = $calculationData['totalLeftoverQuantity'] = $item->quantity -
        $calculationData['totalQuantity'];

      $package->packing_info = $calculationData['packingInfo'] = sprintf('%d * %d + %d * %d',
        $calculationData['primaryNo'], $typePackingParams[0],  $calculationData['secondaryNo'], $typePackingParams[1]);
    }

    if (!$package->save()) {
      echo '<pre>';
      var_dump($package->getMessages());
      die();
    }
  }

}
