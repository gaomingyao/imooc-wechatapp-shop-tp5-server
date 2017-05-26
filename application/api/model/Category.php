<?php

namespace app\api\model;

use think\Model;

class Category extends BaseModel
{
    protected $hidden = ['delete_time','update_time','topic_img_id'];
    public function img(){
      return $this->belongsTo("Image","topic_img_id","id");
    }
}
