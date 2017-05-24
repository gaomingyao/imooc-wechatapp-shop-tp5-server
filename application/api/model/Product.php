<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    protected $hidden = ['delete_time','pivot','update_time','create_time','category_id','from'];
    public function getMainImgUrlAttr($value,$data){
      return $this->prefixImgUrl($value,$data);
    }
    public function imgs(){
      return $this->hasMany("ProductImage","product_id","id");
    }
    public function properties(){
      return $this->hasMany("ProductProperty","product_id","id");
    }
    public static function getMostRecend($count){
      $products = self::limit($count)
        ->order("create_time desc")
        ->select();
      return $products;
    }
    public static function getProductByCategoryID($CategoryID){
      $products = self::where("category_id","=",$CategoryID)->select();
      return $products;
    }
    public static function getProductDetail($id){
      $product = self::with(["imgs","imgs.imgUrl","properties"])
        ->find($id);
      return $product;
    }
}
