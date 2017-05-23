<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;

class ThemeException extends BaseException
{
  public $code = 404;
  public $msg = "请求的主题不存在";
  public $errorCode = 30000;
}
