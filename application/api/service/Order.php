<?php

namespace app\api\service;

use app\api\model\Product as ProductModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct as OrderProductModel;
use app\api\model\UserAddress as UserAddressModel;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;

class Order
{
  protected $oProducts;
  protected $products;
  protected $uid;

  public function place($uid,$oProducts){
    $this->oProducts = $oProducts;
    $this->products = $this->getProductsByOrder($oProducts);
    $this->uid = $uid;
    $status = $this->getOrderStatus();
    if (!$status['pass']) {
      $status['order_id'] = -1;
      return $status;
    }
    //开始创建订单
    $orderSnap = $this->snapOrder($status);
    $order = $this->createOrder($orderSnap);
    $order['pass'] = true;
    return $order;
  }

  private function createOrder($snap)
  {
      Db::startTrans();
      try {
          $orderNo = $this->makeOrderNo();
          $order = new OrderModel();
          $order->user_id = $this->uid;
          $order->order_no = $orderNo;
          $order->total_price = $snap['orderPrice'];
          $order->total_count = $snap['totalCount'];
          $order->snap_img = $snap['snapImg'];
          $order->snap_name = $snap['snapName'];
          $order->snap_address = $snap['snapAddress'];
          $order->snap_items = json_encode($snap['pStatus']);
          $order->save();

          $orderID = $order->id;
          $create_time = $order->create_time;

          foreach ($this->oProducts as &$p) {
              $p['order_id'] = $orderID;
          }
          $orderProduct = new OrderProductModel();
          $orderProduct->saveAll($this->oProducts);
          Db::commit();
          return [
              'order_no' => $orderNo,
              'order_id' => $orderID,
              'create_time' => $create_time
          ];
      } catch (Exception $ex) {
          Db::rollback();
          throw $ex;
      }
  }

  //生成订单快照
  private function snapOrder($status){
    $snap = [
      "orderPrice" => 0,
      "totalCount" => 0,
      "pStatus" => [],
      "snapAddress" => null,
      "snapName" => "",
      "snapImg" => ""
    ];
    $snap['orderPrice'] = $status['orderPrice'];
    $snap['totalCount'] = $status['totalCount'];
    $snap['pStatus'] = $status['pStatusArray'];
    $snap['snapAddress'] = json_encode($this->getUserAddress());
    $snap['snapName'] = $this->products[0]['name'];
    $snap['snapImg'] = $this->products[0]['main_img_url'];
    if (count($this->products) > 1) {
      $snap['snapName'] = $snap['snapName']."等";
    }
    return $snap;
  }

  private function getUserAddress(){
    $userAddress = UserAddressModel::where("user_id","=",$this->uid)
      ->find();
    if (!$userAddress) {
      throw new UserException([
        "msg" => "用户收货地址不存在，下单失败",
        "errorCode" => "60001",
      ]);
    }
    return $userAddress->toArray();
  }

  public function checkOrderStock($orderID){
    $oProducts = OrderProductModel::where("order_id","=",$orderID)
      ->select();
    $this->oProducts = $oProducts;
    $this->products = $this->getProductsByOrder($oProducts);
    $status = $this->getOrderStatus();
    return $status;
  }

  private function getOrderStatus(){
    $status = [
      'pass' => true,
      'orderPrice' => 0,
      'totalCount' => 0,
      'pStatusArray' => []
    ];
    foreach ($this->oProducts as $oProduct) {
      $pStatus = $this->getProductStatus(
        $oProduct['product_id'],
        $oProduct['count'],
        $this->products
      );
      if (!$pStatus['haveStock']) {
        $status['pass'] = false;
      }
      $status['orderPrice'] += $pStatus['totalPrice'];
      $status['totalCount'] += $pStatus['counts'];
      array_push($status['pStatusArray'],$pStatus);
    }
    return $status;
  }
  private function getProductStatus($oPID,$oCount,$products){
    $pIndex = -1;
    $pStatus = [
      'id' => null,
      'haveStock' => false,
      'counts' => 0,
      'price' => 0,
      'name' => '',
      'totalPrice' => 0,
      'main_img_url' => null
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
      $pStatus['counts'] = $oCount;
      $pStatus['price'] = $product['price'];
      $pStatus['main_img_url'] = $product['main_img_url'];
      $pStatus['name'] = $product['name'];
      $pStatus['totalPrice'] = $product['price'] * $oCount;
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
      array_push($oPIDs,$item['product_id']);
    }
    $products = ProductModel::all($oPIDs)
      ->visible(["id","price","stock","name","main_img_url"])
      ->toArray();
    return $products;
  }

  public static function makeOrderNo()
  {
      $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
      $orderSn =
          $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
              'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
              '%02d', rand(0, 99));
      return $orderSn;
  }
}
