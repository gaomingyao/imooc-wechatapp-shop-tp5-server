<?php

namespace app\api\model;

use think\Model;

class User extends BaseModel
{
    public static function getByOpenID($openid){
      $user = self::where("openid","=",$openid)
        ->find();
      return $user;
    }
}
