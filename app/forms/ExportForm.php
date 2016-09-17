<?php

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;

class ExportForm extends Form {

  /**
   * Initialize the products form
   */
  public function initialize ($entity = null, $options = []) {

    $barcode = new Text('barcode');
    $barcode->setLabel('Barcode');
    $barcode->addValidator(new PresenceOf());
    $barcode->addValidator(new Regex([
      'pattern' => '/^[0-9]{1}[-][0-9]{7}[-][0-9]{2}[-][0-9]{1}[-][0-9]{3}[-][0-9]{3}$/',
    ]));
    $this->add($barcode);

    $type = new Text('type');
    $type->setLabel('Tip palete');
    $type->addValidator(new PresenceOf());
    $type->addValidator(new Regex([
      'pattern' => '/^[0-9]{1,2}$/',
    ]));
    $this->add($type);

    $width = new Text('width');
    $width->setLabel('Sirina');
    $width->addValidator(new PresenceOf());
    $width->addValidator(new Regex([
      'pattern' => '/^[0-9]*$/',
    ]));
    $this->add($width);

    $length = new Text('length');
    $length->setLabel('Duzina');
    $length->addValidator(new PresenceOf());
    $length->addValidator(new Regex([
      'pattern' => '/^[0-9]*$/',
    ]));
    $this->add($length);

    $orderNo = new Text('orderNo');
    $orderNo->setLabel('Niz/st.enot');
    $orderNo->addValidator(new PresenceOf());
    $orderNo->addValidator(new Regex([
      'pattern' => '/^[0-9]*[\/][0-9]*$/',
    ]));
    $this->add($orderNo);

    $customerOrder = new Text('customerOrder');
    $customerOrder->setLabel('Customer Order');
    $customerOrder->addValidator(new PresenceOf());
    $customerOrder->addValidator(new Regex([
      'pattern' => '/^[0-9]{7}[-][0-9]{2}[-][0-9]{2}[-][0-9]{2}$/',
    ]));
    $this->add($customerOrder);
  }
}
