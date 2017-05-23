<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\validate\IDCollection;
use app\api\validate\IDMustBePostiveInt;
use app\api\model\Theme as ThemeModel;
use app\lib\exception\ThemeException;

class Theme extends Controller
{
  /**
   * @url /theme?ids=id1,id2,id3...
   * @return 一组theme模型
   */
    public function getSimpleList($ids=''){
      (new IDCollection())->goCheck();
      $ids = explode(",",$ids);
      $result = ThemeModel::with("topicimg","headimg")->select();
      if ($result->isEmpty()) {
        throw new ThemeException;
      }
      return $result;
    }
    /**
     * @url /theme/:id
     */
    public function getComplexOne($id){
      (new IDMustBePostiveInt())->goCheck();
      $result = ThemeModel::getThemeWithProducts($id);
      if (!$result) {
        throw new ThemeException;
      }
      return $result;
    }
}
