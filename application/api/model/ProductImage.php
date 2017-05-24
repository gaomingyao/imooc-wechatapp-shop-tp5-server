<?php

namespace app\api\model;

use think\Model;

class ProductImage extends BaseModel
{
    protected $hidden = ['delete_time','img_id','product_id','id'];

    public function imgUrl(){
      return $this->belongsTo("Image","img_id","id");
    }
}
