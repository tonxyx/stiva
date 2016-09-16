<?php

class OrderController extends ControllerBase {

  public function initialize () {
    $this->tag->setTitle('Orders');
    parent::initialize();
  }

  public function indexAction () {
    $orders = Orders::find(['order' => 'upload_date desc']);
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

  /**
   * Export single item printing blocks
   *
   * @param $id
   *
   * @return array
   */
  public function exportSingleAction ($id) {
    $item =  Items::findFirst("id=$id");
    $itemPackage = Packages::findFirst("item_id=$id");
    $types = $this->fixture['types'];
    $totalPacks = 0;

    $printData = [
      'type' => $item->type,
      'width' => $item->width,
      'length' => $item->length,
      'customerOrder' => $item->customer_order,
    ];

    if (count($types[$item->type]) == 1) {
      $barcodeSecondPart = str_pad($types[$item->type][0], 3, '0', STR_PAD_LEFT);

      for ($i = 1; $i <= $itemPackage->primary_no; $i++, $totalPacks++) {
        $barcode = substr_replace($item->barcode, str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $barcodeSecondPart, -7);
        $printData['items']['barcode'][] = $barcode;
        $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
        $printData['items']['totalOfNo'][] = $i;
      }

      if ($itemPackage->primary_leftover_quantity) {
        $barcodeLeftover = substr_replace($item->barcode, str_pad(++$itemPackage->primary_no, 3, '0', STR_PAD_LEFT) .
          '-' . str_pad($itemPackage->primary_leftover_quantity, 3, '0', STR_PAD_LEFT), -7);
        $printData['items']['barcode'][] = $barcodeLeftover;
        $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcodeLeftover);
        $printData['items']['totalOfNo'][] = $itemPackage->primary_no;
        $totalPacks++;
      }

    } else if (count($types[$item->type]) == 2) {
      $barcodeSecondPartPrimary = str_pad($types[$item->type][0], 3, '0', STR_PAD_LEFT);
      $barcodeSecondPartSecondary = str_pad($types[$item->type][1], 3, '0', STR_PAD_LEFT);

      for ($i = 1; $i <= $itemPackage->primary_no; $i++, $totalPacks++) {
        $barcode = substr_replace($item->barcode, str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $barcodeSecondPartPrimary, -7);
        $printData['items']['barcode'][] = $barcode;
        $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
        $printData['items']['totalOfNo'][] = $i;
      }

      for ($j = 1; $j <= $itemPackage->secondary_no; $j++, $totalPacks++) {
        $barcode = substr_replace($item->barcode, str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $barcodeSecondPartSecondary, -7);
        $printData['items']['barcode'][] = $barcode;
        $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
        $printData['items']['totalOfNo'][] = $i;
      }

      echo '<pre>'; var_dump($printData); die();
    }

    $printData['totalPacks'] = $totalPacks;

    $this->view->printData = $printData;
  }

  private function generateBarcodePNG ($barcode) {
    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    return 'data:image/png;base64,' . base64_encode(
      $barcodeGenerator->getBarcode($barcode, $barcodeGenerator::TYPE_CODE_39));
  }
}
