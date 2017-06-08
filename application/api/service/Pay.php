<?php

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\service\Token;

use think\Exception;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;

use app\lib\enum\OrderStatusEnum;

class Pay
{
  private $orderID;
  private $orderNO;

  function __construct($orderID){
    if (!$orderID) {
      throw new Exception("订单号不允许为null");
    }
    $this->orderID = $orderID;
  }

  private function makeWxPreOrder(){
    
  }

  public function pay(){
    //订单号不存在
    //订单号和用户不匹配
    //订单有可能已经支付
    $this->checkOrderValid();
    //进行库存量检测
    $orderService = new OrderService();
    $status = $orderService->checkOrderStock($this->orderID);
    if (!$status['pass']) {
      return $status;
    }else{

    }
  }

  private function checkOrderValid(){
    $order = OrderModel::where("id","=",$this->orderID)
      ->find();
    if (!$order) {
      throw new OrderException();
    }
    if (!Token::isValidOperate($order->user_id)) {
      throw new TokenException([
        "msg" => "订单与用户不匹配",
        "errorCode" => "10003"
      ]);
    }
    if ($order->status != OrderStatusEnum::UNPAID) {
      throw new OrderException([
        "msg" => "订单已经是被支付过了的",
        "errorCode" => 80003,
        "code" => 400
      ]);
    }
    $this->orderID = $order->order_no;
    return true;
  }
}
