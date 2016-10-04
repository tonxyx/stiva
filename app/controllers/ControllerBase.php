<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller {

  protected function initialize () {
    $this->tag->prependTitle('STIVA | ');
    $this->view->setTemplateAfter('main');
    $this->assets->addCss('css/bootstrap.min.css')
      ->addCss('css/main.css')
      ->addCss('css/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css');
    $this->assets->addJs('js/jquery.min.js')
      ->addJs('js/bootstrap.min.js')
      ->addJs('js/utils.js')
      ->addJs('js/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js');
  }

  protected function forward ($uri) {
    $uriParts = explode('/', $uri);
    $params = array_slice($uriParts, 2);

    return $this->dispatcher->forward([
        'controller' => $uriParts[0],
        'action' => $uriParts[1],
        'params' => $params,
      ]
    );
  }

  /**
   * Sort array by param by example array order
   *
   * @param array $array
   * @param array $orderArray
   * @param string $type
   *
   * @return array
   */
  protected function sortArrayByArray (array $array, array $orderArray, $param = 'type') {
      $ordered = array();
      foreach ($orderArray as $sortKey) {
        foreach ($array as $key => $value) {
          if ($sortKey == $value[$param]) {
              $ordered[$key] = $value;
              unset($array[$key]);
          }
        }
      }
      return $ordered + $array;
  }
}
