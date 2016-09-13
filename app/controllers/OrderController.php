<?php

class OrderController extends ControllerBase {

  public function initialize () {
    $this->tag->setTitle('Orders');
    parent::initialize();
  }

  public function indexAction () {
    $orders = Orders::find();
    $currentPage = $this->request->get('page', 1);

    $paginator   = new \Phalcon\Paginator\Adapter\Model(
      array(
        "data"  => $orders,
        "limit" => 10,
        "page"  => $currentPage
      )
    );

    $this->view->data = $paginator->getPaginate();
  }

  public function addAction () {
    $form = new OrderForm();

    if ($this->request->isPost() && $this->request->hasFiles() && count($this->request->getUploadedFiles()) == 1) {
      if ($form->isValid($this->request->getPost())) {
        foreach ($this->request->getUploadedFiles() as $file) {
          $fileName = date('Y-m-d') . '_' .$file->getName();
          $filePath = $this->config->application->docsLink . $fileName;

          $file->moveTo($filePath);

          $row = 0;
          $order = null;
          if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
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
        echo '<pre>'; var_dump($form->getMessages()); die();
      }
    }

    $this->view->form = $form;
  }

  public function detailsAction ($id) {
    $this->view->items = Items::find(['parent_order' => $id]);
  }

  public function manageAction ($id) {
    $items = Items::find(['parent_order' => $id]);

    $managedData = [];
    foreach ($items as $item) {
      $calculationData = [];
      $typePackingParams = $this->fixture['types'][$item->type];

      $package = new Packages();
      $package->item_id = $item->id;
      $package->item_id = $item->id;

      if (count($typePackingParams) == 1) {
        $package->primary_no = $calculationData['primaryNo'] = intval($item->quantity/$typePackingParams[0]);
        $package->primary_quantity = $calculationData['primaryQuantity'] = $calculationData['primaryNo'] *
          $typePackingParams[0];
        $package->primary_leftover_quantity = $calculationData['primaryLeftoverQuantity'] = $item->quantity -
          $calculationData['primaryQuantity'];
        $package->total_quantity = $calculationData['totalQuantity'] = $calculationData['primaryQuantity'];
        $package->total_leftover_quantity = $calculationData['totalLeftoverQuantity'] = $item->quantity -
          $calculationData['totalQuantity'];

        $package->packing_info = $calculationData['packingInfo'] = sprintf('%d * %d + %d',
          $calculationData['primaryNo'], $typePackingParams[0], $calculationData['primaryLeftoverQuantity']);
      } else {
        $packingParamsSum = $typePackingParams[0] + $typePackingParams[1];
        $package->primary_no = $calculationData['primaryNo'] = intval($item->quantity/$packingParamsSum);
        $package->primary_quantity = $calculationData['primaryQuantity'] = $calculationData['primaryNo'] *
          $typePackingParams[0];
        $package->primary_leftover_quantity = $calculationData['primaryLeftoverQuantity'] = $item->quantity -
          $calculationData['primaryQuantity'];

        $package->secondary_no = $calculationData['secondaryNo'] =
          intval($calculationData['primaryLeftoverQuantity']/$typePackingParams[1]);
        $package->secondary_quantity = $calculationData['secondaryQuantity'] = $calculationData['secondaryNo'] *
          $typePackingParams[1];
        $package->total_quantity = $calculationData['totalQuantity'] = $calculationData['primaryQuantity'] +
          $calculationData['secondaryQuantity'];
        $package->total_leftover_quantity = $calculationData['totalLeftoverQuantity'] = $item->quantity -
          $calculationData['totalQuantity'];

        $package->packing_info = $calculationData['packingInfo'] = sprintf('%d * %d + %d * %d',
          $calculationData['primaryNo'], $typePackingParams[0],  $calculationData['secondaryNo'], $typePackingParams[1]);
      }

      // todo reimolement for unique insert
//      $package->save();

      $managedData[$item->id] = [
        'customer_order' => $item->customer_order,
        'type' => $item->type,
        'quantity' => $item->quantity,
        'barcode' => $item->barcode,
        'calculationData' => $calculationData,
      ];
    }

    $this->view->managedData = $managedData;
  }
}
