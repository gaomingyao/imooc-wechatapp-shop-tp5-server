<?php

namespace app\api\controller\v1;

use think\Controller;

use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;


class Address extends Controller
{
    public function createOrUpdateAddress(){
      (new AddressNew())->goCheck();
      //根据token获取uid
      $uid = TokenService::getCurrentUid();
      return $uid;
      //根据uid查询用户数据，判断是否存在，如果不存在抛出异常
      //获取用户从客户端提交过来的地址信息
      //根据用户地址信息是否存在，从而判断，是更新地址还是新增地址
    }
}
