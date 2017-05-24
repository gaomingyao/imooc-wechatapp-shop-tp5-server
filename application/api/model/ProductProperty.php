<?php

namespace app\api\model;

use think\Model;

class ProductProperty extends BaseModel
{
    protected $hidden = ['delete_time','id','product_id','update_time'];
}
