<?php

namespace app\api\service;

use app\api\model\Product as ProductModel

use app\lib\exception\OrderException;

class Order
{
  //订单商品列表，也就是客户端传递过来的products参数
  protected $oProducts;

  //真实的商品信息（包括库存量）
  protected $products;

  protected $uid;

  public function place($uid,$oProducts){
    //$oProducts,$products做对比

    //$products从数据查出来
    $this->oProducts = $oProducts;
    $this->products = getProductsByOrder($oProducts);
    $this->uid = $uid;
  }
  private function getOrderStatus(){
    $status = [
      'pass' => true,
      'orderPrice' => 0,
      'pStatusArray' => []
    ];
    foreach ($this->oProducts as $oProduct) {
      $pStatus = getProductStatus(
        $oProduct['product_id'],
        $oProduct['count'],
        $this->products
      );
      if (!$pStatus['haveStock']) {
        $status['pass'] = false;
      }
      $status['orderPrice'] += $pStatus['totalPrice'];
      array_push($status['pStatusArray'],$pStatus);
    }
    return $status;
  }
  private function getProductStatus($oPID,$oCount,$products){
    $pIndex = -1;
    $pStatus = [
      'id' => null,
      'haveStock' => false,
      'count' => 0,
      'name' => '',
      'totalPrice' => 0
    ];
    for ($i=0; $i < count($products); $i++) {
      if ($oPID == $products[$i]['id']) {
        $pIndex = $i;
      }
    }
    if ($pIndex == -1) {
      throw new OrderException([
        'msg' => "id为".$oPID."商品不存在，创建订单失败"
      ]);
    }else{
      $product = $products[$pIndex];
      $pStatus['id'] = $product['id'];
      $pStatus['count'] = $oCount;
      $pStatus['name'] = $product['name'];
      $pStatus['totalPrice'] = $product['price']*$oCount;
      if ($product['stock'] - $oCount >= 0) {
        $pStatus['haveStock'] = true;
      }
    }
    return $pStatus;
  }
  //根据订单信息查找真实的商品信息
  private function getProductsByOrder($oProducts){
    $oPIDs = [];
    foreach ($oProducts as $item) {
      array_push($oPIDs,$item);
    }
    $products = ProductModel::all($oPIDs)
      ->visible(["id","price","stock","name","main_img_url"])
      ->toArray();
    return $products;
  }
}
