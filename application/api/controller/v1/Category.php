<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;

class Category extends Controller
{
    public function getAllCategories(){
      $categories = CategoryModel::with("img")->select();
      if ($result->isEmpty()) {
        throw new CategoryException();
      }
      return $categories;
    }
}
