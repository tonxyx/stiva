<?php

class OrderController extends ControllerBase {

  public function initialize () {
    $this->tag->setTitle('Orders');
    parent::initialize();
  }

  public function indexAction () {
    $orders = Orders::find(['order' => 'upload_date desc']);
    $currentPage = $this->request->get('page', 1);

    $paginator = new \Phalcon\Paginator\Adapter\Model(
      [
        "data" => $orders,
        "limit" => 10,
        "page" => $currentPage
      ]
    );

    $this->view->data = $paginator->getPaginate();
  }

  public function addAction () {
    $form = new OrderForm();

    if ($this->request->isPost() && $this->request->hasFiles() && count($this->request->getUploadedFiles()) == 1) {
      if ($form->isValid($this->request->getPost())) {
        foreach ($this->request->getUploadedFiles() as $file) {
          $fileName = date('Y-m-d') . '_' . $file->getName();
          $filePath = $this->config->application->docsLink . $fileName;

          $file->moveTo($filePath);

          $row = 0;
          $order = null;
          if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, null, ";")) !== false) {
              if ($row == 1) {
                $order = Orders::initCSVData($data, $fileName);
              }

              if ($row != 0) {
                Items::saveCSVData($data, $order);
              }
              $row++;
            }
            fclose($handle);
          }

        }

        $this->flash->success("File uploaded and saved.");

        return $this->response->redirect('order/index');
      } else {
        foreach ($form->getMessages() as $message)
          $this->flash->error($message->getMessage());

        return $this->response->redirect('index/index');
      }
    }

    $this->view->form = $form;
  }

  public function detailsAction ($id) {
    $this->view->items = Items::find("parent_order=$id");
  }

  public function manageAction ($id) {
    $items = Items::find("parent_order=$id");

    $managedData = [];
    foreach ($items as $item) {
      $managedData[$item->id] = [
        'customer_order' => $item->customer_order,
        'type' => $item->type,
        'quantity' => $item->quantity,
        'barcode' => $item->barcode,
        'calculationData' => Packages::find("item_id=$item->id")->toArray()[0],
      ];
    }

    $this->view->managedData = $managedData;
  }
}
