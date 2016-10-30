<?php

use Phalcon\Mvc\Model;

/**
 * Packages
 */
class Packages extends Model {

  /**
   * @var integer
   */
  public $id;

  /**
   * @var int
   */
  public $item_id;

  /**
   * @var int
   */
  public $primary_no;

  /**
   * @var int
   */
  public $primary_type;

  /**
   * @var int
   */
  public $primary_quantity;

  /**
   * @var int
   */
  public $primary_leftover_quantity;

  /**
   * @var int
   */
  public $secondary_no;

  /**
   * @var int
   */
  public $secondary_type;

  /**
   * @var int
   */
  public $secondary_quantity;

  /**
   * @var int
   */
  public $total_quantity;

  /**
   * @var int
   */
  public $total_leftover_quantity;

  /**
   * Orders initializer
   */
  public function initialize () {
    $this->setSource("packages");

    $this->belongsTo('item_id', 'Items', 'id', [
      'alias' => 'Item',
      'reusable' => true,
    ]);
  }

}
