<?php

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\service\Token;

use think\Exception;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;

use app\lib\enum\OrderStatusEnum;
use think\Loader;
//extend/WxPay/WxPay.Api.php
Loader::import("WxPay.WxPay",EXTEND_PATH,".Api.php");

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

  private function makeWxPreOrder($totalPrice){
    $openid = Token::getCurrentTokenVar("openid");
    if (!$openid) {
      throw new TokenException();
    }
    $wxOrderData = new \WxPayUnifiedOrder();
    $wxOrderData->SetOut_trade_no($this->orderNO);
    $wxOrderData->SetTrade_type("JSAPI");
    $wxOrderData->SetTotal_fee($totalPrice*100);
    $wxOrderData->SetBody("零食商贩");
    $wxOrderData->SetOpenid($openid);
    $wxOrderData->SetNotify_url("");
  }

  private function getPaySignature($wxOrderData){
    $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
    if ($wxOrder['return_code'] != "SUCCESS" || $wxOrder['result_code'] != "SUCCESS") {
      Log::record($wxOrder,"error");
      Log::record("获取预支付订单失败","error");
    }
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
