<?php

namespace app\api\validate;

class AddressNew extends BaseValidate
{
  protected $rule = [
    "code" => "require|isNotEmpty",
    "mobile" => "require|isNotEmpty",
    "province" => "require|isNotEmpty",
    "city" => "require|isNotEmpty",
    "country" => "require|isNotEmpty",
    "detatil" => "require|isNotEmpty"
  ];
  protected $message = [
    "code" => "没有code还想获取token?"
  ];
}
