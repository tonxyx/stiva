<?php

use Phalcon\Mvc\Model;

/**
 * Orders
 */
class Orders extends Model {

  /**
   * @var integer
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $file_name;

  public $upload_date;

  public $delivery_date;

  /**
   * Orders initializer
   */
  public function initialize () {
    $this->setSource("orders");

    $this->belongsTo('parent_order', 'Items', 'id', [
      'reusable' => true,
    ]);
  }

  /**
   * Init order for import data
   *
   * @param $data
   * @param $fileName
   *
   * @return Orders
   */
  static public function initCSVData ($data, $fileName) {
    $order = new Orders();
    $order->name = 'Order_' . date('Y-m-d');
    $order->file_name = $fileName;
    $order->upload_date = date('Y-m-d');
    $order->delivery_date = $data[12];
    $order->save();

    return $order;
  }

}
