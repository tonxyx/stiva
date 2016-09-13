<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller {

  protected function initialize () {
    $this->tag->prependTitle('STIVA | ');
    $this->view->setTemplateAfter('main');
    $this->assets->addCss('css/bootstrap.min.css');
    $this->assets->addJs('js/jquery.min.js')
      ->addJs('js/bootstrap.min.js')
      ->addJs('js/utils.js');
  }

  protected function forward ($uri) {
    $uriParts = explode('/', $uri);
    $params = array_slice($uriParts, 2);

    return $this->dispatcher->forward(
      [
        'controller' => $uriParts[0],
        'action' => $uriParts[1],
        'params' => $params,
      ]
    );
  }
}
