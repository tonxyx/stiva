<?php

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component {

  private $_headerMenu = [
    'navbar-left' => [
      'index' => [
        'caption' => 'Home',
        'action' => 'index',
      ],
      'order' => [
        'caption' => 'Orders',
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

    $auth = $this->session->get('auth');
    if ($auth) {
      $this->_headerMenu['navbar-right']['session'] = [
        'caption' => 'Log Out',
        'action' => 'end',
      ];
    } else {
      unset($this->_headerMenu['navbar-left']['invoices']);
    }

    $controllerName = $this->view->getControllerName();
    foreach ($this->_headerMenu as $position => $menu) {
      echo '<div class="nav-collapse">';
      echo '<ul class="nav navbar-nav ', $position, '">';
      foreach ($menu as $controller => $option) {
        if ($controllerName == $controller) {
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
