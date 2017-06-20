<?php

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\model\Product as ProductModel;
use app\api\service\Order as OrderService;


use app\lib\enum\OrderStatusEnum;

use think\Db;
use think\Log;
use think\Loader;
Loader::import("WxPay.WxPay",EXTEND_PATH,".Api.php");

class WxNotify extends \WxPayNotify
{
  public function NotifyProcess($data,&$msg){
    if ($data['result_code'] == "SUCCESS") {
      $orderNo = $data['out_trade_no'];
      Db::startTrans();
      try {
        $order = OrderModel::where("order_no","=",$orderNo)
          ->lock(true)
          ->find();
        if ($order->status == 1) {
          $service = new OrderService();
          $stockStatus = $service->checkOrderStock($order->id);
          if ($stockStatus['pass']) {
            $this->updateOrderStatus($order->id, true);
            $this->reduceStock();
          }else{
            $this->updateOrderStatus($order->id, false);
          }
        }
        Db::commit();
        return true;
      } catch (Exception $e) {
        Db::rollback();
        Log::error($e);
        return false;
      }
    }else{
      return true;
    }
  }

  private function reduceStock($stockStatus){
    foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
      ProductModel::where("id","=",$singlePStatus["id"])
        ->setDec("stock",$singlePStatus['count']);
    }
  }

  private function updateOrderStatus($orderID,$success){
    $status = $success?OrderStatusEnum::PAID :
      OrderStatusEnum::PAID_BUT_OUT_OF;
    OrderModel::where("id","=",$orderID)
      ->update(['status' => $status]);
  }
}
