<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\Count;
use app\api\validate\IDMustBePostiveInt;
use app\api\model\Product as ProductModel;
use app\lib\exception\ProductException;

class Product extends Controller
{
    public function getRecend($count = 15){
      (new Count())->goCheck();
      $result = ProductModel::getMostRecend($count);
      if ($result->isEmpty()) {
        throw new ProductException();
      }
      $result = $result->hidden(['summary']);
      return $result;
    }
    public function getAllInCategory($id){
      (new IDMustBePostiveInt())->goCheck();
      $result = ProductModel::getProductByCategoryID($id);
      if ($result->isEmpty()) {
        throw new ProductException();
      }
      $result = $result->hidden(['summary']);
      return $result;
    }
}
