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
  }

}
