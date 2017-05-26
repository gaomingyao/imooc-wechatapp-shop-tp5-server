<?php

namespace app\lib\exception;

use app\lib\exception\BaseException;

class SuccessMessage extends BaseException
{
  public $code = 201;
  public $msg = "ok";
  public $errorCode = 0;
}
