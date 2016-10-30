<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;

class Services extends \Base\Services {

  /**
   * We register the events manager
   */
  protected function initDispatcher () {
    $eventsManager = new EventsManager;

    /**
     * Handle exceptions and not-found exceptions using NotFoundPlugin
     */
    $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

    $dispatcher = new Dispatcher;
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
  }

  /**
   * The URL component is used to generate all kind of urls in the application
   */
  protected function initUrl () {
    $url = new UrlProvider();
    $url->setBaseUri($this->get('config')->application->baseUri);

    return $url;
  }

  protected function initView () {
    $view = new View();
    $view->setViewsDir(APP_PATH . $this->get('config')->application->viewsDir);

    return $view;
  }

  protected function initFixture () {
    $fixture = [
      'types' => [
        // 10,60 isti
        '10' => [8, 7],
        '60' => [8, 7],
        // 2, 78, 62, 67, 90, 51 isti
        '2' => [13, 12],
        '78' => [13, 12],
        '62' => [13, 12],
        '67' => [13, 12],
        '90' => [13, 12],
        '51' => [13, 12],
        // 6, 40, 49, 52 isti
        '6' => [13, 12],
        '40' => [13, 12],
        '49' => [13, 12],
        '52' => [13, 12],
        // 30, 33 isti
        '30' => [40],
        '33' => [40],
        '45' => [40],
      ],
      'sortOrder' => [10, 60, 2, 78, 62, 67, 90, 51, 6, 40, 49, 52, 30, 33, 45],
      'coverTypes' => [30, 33, 45],
    ];

    return $fixture;
  }

  /**
   * Database connection is created based in the parameters defined in the configuration file
   */
  protected function initDb () {
    $config = $this->get('config')->get('database')->toArray();

    $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
    unset($config['adapter']);

    return new $dbClass($config);
  }

  /**
   * If the configuration specify the use of metadata adapter use it or use memory otherwise
   */
  protected function initModelsMetadata () {
    return new MetaData();
  }

  /**
   * Start the session the first time some component request the session service
   */
  protected function initSession () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
  }

  /**
   * Register the flash service with custom CSS classes
   */
  protected function initFlash () {
    return new FlashSession([
      'error' => 'alert alert-danger',
      'success' => 'alert alert-success',
      'notice' => 'alert alert-info',
      'warning' => 'alert alert-warning',
    ]);
  }

  /**
   * Register a user component
   */
  protected function initElements () {
    return new Elements();
  }

}
