<?php

namespace app\api\validate;

use think\Validate;
use think\Exception;
use app\lib\exception\ParameterException;
use think\Request;

class BaseValidate extends Validate
{
  public function goCheck(){
    // 获取http传入的参数
    // 对这些参数做检验
    $request = Request::instance();
    $params = $request->param();

    $result = $this->batch()->check($params);
    if(!$result){
      $e = new ParameterException([
        "msg" => $this->error
      ]);
      throw $e;
    }else{
      return true;
    }
  }
}
