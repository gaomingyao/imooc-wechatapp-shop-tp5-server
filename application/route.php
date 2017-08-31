<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
// banner
Route::get("api/:version/banner/:id","api/:version.Banner/getBanner");
// theme
Route::get("api/:version/theme","api/:version.Theme/getSimpleList");
Route::get("api/:version/theme/:id","api/:version.Theme/getComplexOne");

Route::group("api/:version/product",function(){
  Route::get("/recend","api/:version.Product/getRecend");
  Route::get("/by_category","api/:version.Product/getAllInCategory");
  Route::get("/:id","api/:version.Product/getOne",[],['id'=>'\d+']);
});

Route::get("api/:version/category/all","api/:version.Category/getAllCategories");

// Token
Route::post("api/:version/token/user","api/:version.Token/getToken");
Route::post("api/:version/token/app","api/:version.Token/getAppToken");
Route::post("api/:version/token/verify","api/:version.Token/verifyToken");
// address
Route::post("api/:version/address","api/:version.Address/createOrUpdateAddress");
Route::get("api/:version/address","api/:version.Address/getUserAddress");
// order
Route::post("api/:version/order","api/:version.Order/placeOrder");
Route::get("api/:version/order/:id","api/:version.Order/getDetail",[],['id'=>'\d+']);
Route::get("api/:version/order/by_user","api/:version.Order/getSummaryByUser");
Route::get('api/:version/order/paginate', 'api/:version.Order/getSummary');
Route::put('api/:version/order/delivery', 'api/:version.Order/delivery');
// pay
Route::post("api/:version/pay/pre_order","api/:version.Pay/getPreOrder");
Route::post("api/:version/pay/notify","api/:version.Pay/receiveNotify");
