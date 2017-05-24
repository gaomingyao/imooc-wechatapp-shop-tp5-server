<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\TokenGet;
use app\api\service\UserToken;

class Token extends Controller
{
    public function getToken($code = ""){
      (new TokenGet())->goCheck();
      $ut = new UserToken($code);
      $token = $ut->get();
      return [
        "token" => $token
      ];
    }
}
