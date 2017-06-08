<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;

use app\api\validate\IDMustBePostiveInt;

class Pay extends BaseController
{
    protected $beforeActionList = [
      "checkExclusiveScope" => ["only" => "getPreOrder"]
    ];

    public function getPreOrder($id=''){
      (new IDMustBePostiveInt())->goCheck();
    }
}
