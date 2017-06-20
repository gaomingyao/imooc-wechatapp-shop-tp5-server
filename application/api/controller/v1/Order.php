<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;

use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;

use app\api\validate\OrderPlace;

class Order extends BaseController
{
    protected $beforeActionList = [
      "checkExclusiveScope" => ["only" => "placeOrder"]
    ];

    //用户选择商品后，向api提交所选择的商品信息
    //api接收到信息后，需要检查订单相关商品的库存量
    //有库存，把订单数据存入数据库 = 下单成功，返回客户端消息，通知用户可以支付了
    //调用支付接口，进行支付
    //还需要再次进行库存量检测
    //服务器调用微信的支付接口进行支付
    //小程序根据服务器返回的结果拉起微信支付
    //微信会返回一个支付的结果（异步）
    //成功：也需要进行库存量检测
    //成功：扣除库存量
    public function placeOrder(){
      (new OrderPlace())->goCheck();
      $products = input("post.products/a");
      $uid = TokenService::getCurrentUid();
      $order = new OrderService();
      $status = $order->place($uid,$products);
      return $status;
    }
}
