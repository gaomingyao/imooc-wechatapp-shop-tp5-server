<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\model\Banner as BannerModel;

use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerMissException;


class Banner extends Controller
{
    /**
     * 获取指定id的banner信息
     * @url /banner/:id
     * @http GET
     * @id banner的id号
     */
    public function getBanner($id)
    {
      (new IDMustBePostiveInt())->goCheck();
      $banner = BannerModel::get($id);
      if (!$banner) {
        throw new BannerMissException();
      }
      return $banner;
    }
}
