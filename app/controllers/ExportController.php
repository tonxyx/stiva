<?php

class ExportController extends ControllerBase {

  public function initialize () {
    $this->tag->setTitle('Export');
    parent::initialize();
  }

  public function indexAction () {
    $form = new ExportForm();

    if ($this->request->isPost()) {
      $post = $this->request->getPost();
      if ($form->isValid($post)) {
        $this->view->printData = [
          'barcode' => $post['barcode'],
          'barcodeCode' => $this->generateBarcodePNG($post['barcode']),
          'type' => $post['type'],
          'width' => $post['width'],
          'length' => $post['length'],
          'orderNo' => $post['orderNo'],
          'customerOrder' => $post['customerOrder'],
        ];
      } else {
        foreach ($form->getMessages() as $message)
          $this->flash->error($message->getMessage());
      }
    }

    $this->view->form = $form;
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
    $this->view->printData = $this->prepareItemDataForPrint($item);
  }

  /**
   * Prepare item data for print
   */
  private function prepareItemDataForPrint ($item) {
    if ($item->quantity) {
      $itemPackage = Packages::findFirst("item_id=$item->id");
      $types = $this->fixture['types'];

      $printData = [
        'type' => $item->type,
        'width' => $item->width,
        'length' => $item->length,
        'customerOrder' => $item->customer_order,
      ];

      if (count($types[$item->type]) == 1) {
        $barcodeSecondPart = str_pad($types[$item->type][0], 3, '0', STR_PAD_LEFT);

        for ($i = 1; $i <= $itemPackage->primary_no; $i++) {
          $barcode = substr_replace($item->barcode, str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $barcodeSecondPart, -7);
          $printData['items']['barcode'][] = $barcode;
          $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
          $printData['items']['totalOfNo'][] = $i;
          $printData['items']['totalInPackage'][] = (int) $barcodeSecondPart;
        }

        if ($itemPackage->primary_leftover_quantity) {
          $barcodeLeftover = substr_replace($item->barcode, str_pad(++$itemPackage->primary_no, 3, '0', STR_PAD_LEFT) .
            '-' . str_pad($itemPackage->primary_leftover_quantity, 3, '0', STR_PAD_LEFT), -7);
          $printData['items']['barcode'][] = $barcodeLeftover;
          $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcodeLeftover);
          $printData['items']['totalOfNo'][] = $itemPackage->primary_no;
          $printData['items']['totalInPackage'][] = $itemPackage->primary_leftover_quantity;
        }

      } else if (count($types[$item->type]) == 2) {
        $barcodeSecondPartPrimary = str_pad($types[$item->type][0], 3, '0', STR_PAD_LEFT);
        $barcodeSecondPartSecondary = str_pad($types[$item->type][1], 3, '0', STR_PAD_LEFT);

        if ($itemPackage->primary_no && $itemPackage->secondary_no) {
          for ($i = 1; $i <= $itemPackage->primary_no; $i+=2) {
            $barcode = substr_replace($item->barcode, str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $barcodeSecondPartPrimary, -7);
            $printData['items']['barcode'][] = $barcode;
            $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
            $printData['items']['totalOfNo'][] = $i;
            $printData['items']['totalInPackage'][] = (int) $barcodeSecondPartPrimary;
          }

          if ($itemPackage->secondary_no == 1) {
            $barcode = substr_replace($item->barcode, str_pad(2, 3, '0', STR_PAD_LEFT) . '-' .
              $barcodeSecondPartSecondary, -7);
            $printData['items']['barcode'][] = $barcode;
            $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
            $printData['items']['totalOfNo'][] = 2;
            $printData['items']['totalInPackage'][] = (int) $barcodeSecondPartSecondary;
          } else {
            for ($j = 2; $j <= $itemPackage->secondary_no; $j+=2) {
              $barcode = substr_replace($item->barcode, str_pad($j, 3, '0', STR_PAD_LEFT) . '-' .
                $barcodeSecondPartSecondary, -7);
              $printData['items']['barcode'][] = $barcode;
              $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
              $printData['items']['totalOfNo'][] = $j;
              $printData['items']['totalInPackage'][] = (int) $barcodeSecondPartSecondary;
            }
          }
        } else if ($itemPackage->primary_no == 1) {
          $barcode = substr_replace($item->barcode, str_pad(1, 3, '0', STR_PAD_LEFT) . '-' .
            $barcodeSecondPartPrimary, -7);
          $printData['items']['barcode'][] = $barcode;
          $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
          $printData['items']['totalOfNo'][] = 1;
          $printData['items']['totalInPackage'][] = (int) $barcodeSecondPartPrimary;
        } else if ($itemPackage->secondary_no == 1) {
          $barcode = substr_replace($item->barcode, str_pad(1, 3, '0', STR_PAD_LEFT) . '-' .
            $barcodeSecondPartSecondary, -7);
          $printData['items']['barcode'][] = $barcode;
          $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcode);
          $printData['items']['totalOfNo'][] = 1;
          $printData['items']['totalInPackage'][] = (int) $barcodeSecondPartSecondary;
        }

        if ($itemPackage->total_leftover_quantity) {
          $max = $itemPackage->primary_no > $itemPackage->secondary_no ?
            $itemPackage->primary_no : ($itemPackage->secondary_no == 1 ? 1 : $itemPackage->secondary_no);

          $barcodeLeftover = substr_replace($item->barcode, str_pad(++$max, 3, '0', STR_PAD_LEFT) .
            '-' . str_pad($itemPackage->total_leftover_quantity, 3, '0', STR_PAD_LEFT), -7);

          $printData['items']['barcode'][] = $barcodeLeftover;
          $printData['items']['barcodeCode'][] = $this->generateBarcodePNG($barcodeLeftover);
          $printData['items']['totalOfNo'][] = $max;
          $printData['items']['totalInPackage'][] = $itemPackage->total_leftover_quantity;
        }
      }

      return $printData;
    }
  }

  /**
   * PNG barcode generator
   *
   * @param $barcode
   *
   * @return string
   */
  private function generateBarcodePNG ($barcode) {
    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    return 'data:image/png;base64,' . base64_encode(
      $barcodeGenerator->getBarcode('*' . $barcode . '*', $barcodeGenerator::TYPE_CODE_39));
  }

  /**
   * SVG barcode generator
   *
   * @param $barcode
   *
   * @return string
   */
  private function generateBarcodeSVG ($barcode) {
    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    return 'data:image/svg;base64,' . base64_encode(
      $barcodeGenerator->getBarcode('*' . $barcode . '*', $barcodeGenerator::TYPE_CODE_39));
  }

  /**
   * Export single item printing blocks
   *
   * @param $order
   */
  public function exportAction ($order) {
    $orderItems = Items::find([
      "parent_order=$order",
      // 'order' => 'customer_order ASC',
      // 'order' => 'width DESC',
    ]);

    $orderData = [];
    foreach ($orderItems as $orderItem) {
      $orderData[] = $this->prepareItemDataForPrint($orderItem);
    }

    // $this->view->orderData = $this->sortArrayByArray($orderData, $this->fixture['sortOrder']);
    $this->view->orderData = $orderData;
  }
}
