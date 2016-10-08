<?php

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component {

  private $_headerMenu = [
    'navbar-right' => [
      'index' => [
        'caption' => 'Home',
        'action' => 'index',
      ],
      'order' => [
        'caption' => 'Orders',
        'action' => 'index',
      ],
      'export' => [
        'caption' => 'Manual export',
        'action' => 'index',
      ],
    ],
  ];

  /**
   * Builds header menu with left and right items
   *
   * @return string
   */
  public function getMenu () {
    $controllerName = $this->view->getControllerName();
    $actionName = $this->view->getActionName();

    foreach ($this->_headerMenu as $position => $menu) {
      echo '<div class="nav-collapse">';
      echo '<ul class="nav navbar-nav ', $position, '">';
      foreach ($menu as $controller => $option) {
        if ($controllerName == $controller && $option['action'] == $actionName) {
          echo '<li class="active">';
        } else {
          echo '<li>';
        }
        echo $this->tag->linkTo($controller . '/' . $option['action'], $option['caption']);
        echo '</li>';
      }
      echo '</ul>';
      echo '</div>';
    }

  }

}
