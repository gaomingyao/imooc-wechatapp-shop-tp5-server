<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Order as OrderModel;

use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;

use app\api\validate\PagingParameter;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\OrderPlace;

use app\lib\exception\OrderException;

class Order extends BaseController
{
    protected $beforeActionList = [
      "checkExclusiveScope" => ["only" => "placeOrder"],
      "checkPrimaryScope" => ["only" => "getDetail,getSummaryByUser"],
      'checkSuperScope' => ['only' => 'delivery,getSummary']
    ];
    public function getSummaryByUser($page=1,$size=15){
      (new PagingParameter())->goCheck();
      $uid = TokenService::getCurrentUid();
      $pagingOrders = OrderModel::getSummaryByUser($uid,$page,$size);
      if ($pagingOrders->isEmpty()) {
        return [
          'data' => [],
          'current_page' => $pagingOrders->getCurrentPage()
        ];
      }
      $data = $pagingOrders->hidden(['snap_items','snap_address','prepay_id'])
        ->toArray();
      return [
        'data' => $data,
        'current_page' => $pagingOrders->getCurrentPage()
      ];
    }


    /**
     * 获取全部订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page=1, $size = 20){
        (new PagingParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByPage($page, $size);
        if ($pagingOrders->isEmpty())
        {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    public function getDetail($id){
      (new IDMustBePostiveInt())->goCheck();
      $orderDetail = OrderModel::get($id);
      if (!$orderDetail) {
        throw new OrderException();
      }
      return $orderDetail
        ->hidden(['prepay_id']);
    }

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


    public function delivery($id){
        (new IDMustBePositiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            return new SuccessMessage();
        }
    }
}
