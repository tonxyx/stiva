<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\File;

class OrderForm extends Form {

  /**
   * Initialize the products form
   */
  public function initialize ($entity = null, $options = []) {

    $order = new File("order");
    $order->setLabel("Order");
//    $order->addValidator(new \Phalcon\Validation\Validator\File());
    $this->add($order);
  }
}
