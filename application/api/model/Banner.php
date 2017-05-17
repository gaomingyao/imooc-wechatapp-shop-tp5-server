<?php

namespace app\api\model;

use think\Model;
use think\Db;

class Banner extends Model
{
    public static function getBannerById($id)
    {
      //TOD0:根据Banner ID号 获取Banner信息
      Db::table("banner_item");
      Db::where("banner_id","=",$id);
      $result = Db::select();
      return $result;
    }
}
