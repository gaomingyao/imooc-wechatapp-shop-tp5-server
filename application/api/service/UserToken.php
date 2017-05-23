<?php

namespace app\api\service;

class UserToken
{
  public function get($code){
    return "token".$code;
  }
}
