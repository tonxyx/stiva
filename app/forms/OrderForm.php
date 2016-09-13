<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\File;
use Phalcon\Validation\Validator\PresenceOf;

class OrderForm extends Form {

  /**
   * Initialize the products form
   */
  public function initialize ($entity = null, $options = []) {

    $order = new File("order");
    $order->setLabel("Order");
    $this->add($order);
  }
}
