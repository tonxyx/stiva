<?php

use Phalcon\Mvc\Model\Resultset;

class OrderController extends ControllerBase {

  public function initialize () {
    $this->tag->setTitle('Orders');
    parent::initialize();
  }

  public function indexAction () {
    $orders = Orders::find(['order' => 'upload_date desc']);
    $currentPage = $this->request->get('page', 1);

    $paginator = new \Phalcon\Paginator\Adapter\Model([
      "data" => $orders,
      "limit" => 10,
      "page" => $currentPage,
    ]);

    $this->view->data = $paginator->getPaginate();
  }

  public function addAction () {
    $form = new OrderForm();

    if ($this->request->isPost() && $this->request->hasFiles() && count($this->request->getUploadedFiles()) == 1 &&
        $this->request->getUploadedFiles()[0]->getError() == 0 &&
        $this->request->getUploadedFiles()[0]->getSize() > 0) {
      if ($form->isValid($this->request->getPost())) {
        foreach ($this->request->getUploadedFiles() as $file) {
          $fileName = $file->getName();
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
    } else {
      return $this->response->redirect('index/index');
    }

    $this->view->form = $form;
  }

  public function detailsAction ($id, $ordered) {
    $items = Items::find([
        "parent_order = {$id}",
        'order' => 'order_custom ASC'
    ])->toArray();

    $this->view->items = $items;

    if (!$ordered) {
      $this->view->items = $this->sortArrayByArray($items, $this->fixture['sortOrder']);
    }
  }

  public function manageAction ($id, $ordered) {
    $items = Items::find([
        "parent_order = {$id}",
        'order' => 'order_custom ASC'
    ]);

    $managedData = [];
    foreach ($items as $item) {
      $managedData[$item->id] = [
        'customer_order' => $item->customer_order,
        'type' => $item->type,
        'quantity' => $item->quantity,
        'width' => $item->width,
        'length' => $item->length,
        'barcode' => $item->barcode,
        'calculationData' => Packages::find("item_id=$item->id")->toArray()[0],
      ];
    }

    $this->view->managedData = $managedData;

    if (!$ordered) {
      $this->view->managedData = $this->sortArrayByArray($managedData, $this->fixture['sortOrder']);
    }
  }

  public function updatePackageDataAction () {
    $this->view->disable();

    if ($this->request->isPost() && $this->request->isAjax()) {
      $post = $this->request->getPost();
      $data = explode('_', $post['pk']);

      $package = Packages::findFirst("id=$data[0]");

      switch ($data[1]) {
        case 'primary':
          $package->primary_no = $post['value'];
          $package->save();
          break;
        case 'primaryType':
          $package->primary_type = $post['value'];
          $package->save();
          break;
        case 'secondary':
          $package->secondary_no = $post['value'];
          $package->save();
          break;
        case 'secondaryType':
          $package->secondary_type = $post['value'];
          $package->save();
          break;
        case 'leftover':
          $package->total_leftover_quantity = $post['value'];
          $package->save();
          break;
        default:
          $this->response->setContent(json_encode([
            'error' => true,
          ]));
          break;

        return $response;
      }
    } else {
      $this->response->setContent(json_encode([
        'error' => true,
      ]));
    }
  }

  public function updatePackageOrderAction () {
    $this->view->disable();

    if ($this->request->isPost() && $this->request->isAjax()) {
      $orderData = json_decode($this->request->getPost('data'))[0];

      foreach ($orderData as $key => $value) {
        $newKey = ++$key;

        $item = Items::findFirst("id=$value->id");
        $item->order_custom = $newKey;
        $item->save();

        if ($key == 1) {
          $order = Orders::findFirst("id=$item->parent_order");
          $order->ordered = 1;
          $order->save();
        }

        $package = Packages::findFirst("id=$value->id");
        $package->order_custom = $newKey;
        $package->save();
      }

      $this->response->setContent(json_encode([
        'success' => true,
      ]));
    } else {
      $this->response->setContent(json_encode([
        'error' => true,
      ]));
    }
  }
}
