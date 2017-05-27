<?php

namespace app\api\service;
use app\api\model\User as UserModel;
use app\lib\exception\WeChatException;
use app\lib\exception\TokenException;
use app\lib\enum\ScopeEnum;

class UserToken extends Token
{
  protected $code;
  protected $wxAppId;
  protected $wxAppSecret;
  protected $wxLoginUrl;

  public function __construct($code){
    $this->code = $code;
    $this->wxAppId = config("wx.app_id");
    $this->wxAppSecret = config("wx.app_secret");
    $this->wxLoginUrl = sprintf(config("wx.login_url"),$this->wxAppId,$this->wxAppSecret,$this->code);
  }

  public function get(){
    $result = curl_get($this->wxLoginUrl);
    $wxResult = json_decode($result,true);
    if (empty($wxResult)) {
      throw new Exception("获取open_id及session_key时异常，微信内部错误");
    }else {
      $loginFail = array_key_exists("errcode",$wxResult);
      if ($loginFail) {
        $this->processLoginError($wxResult);
      }else{
        return $this->grantToken($wxResult);
      }
    }
  }

  private function grantToken($wxResult){
    //拿到openid
    $openid = $wxResult['openid'];
    //从数据库查看，openid是否已存在
    $user = UserModel::getByOpenID($openid);
    //如果存在不做处理，如果不存在新增用户
    if ($user) {
      $uid = $user->id;
    }else{
      $uid = $this->newUser($openid);
    }
    //生成令牌，准备缓存数据，写入缓存
    $cachedValue = $this->prepareCachedValue($wxResult,$uid);
    //把令牌返回到客户端
    $token = $this->sevaToCache($cachedValue);
    return $token;
  }

  private function sevaToCache($cachedValue){
    $key = self::generateToken();
    $value = json_encode($cachedValue);
    $expire_in = config("setting.token_expire_in");

    $request = cache($key,$value,$expire_in);
    if (!$request) {
      throw new TokenException([
        "msg" => "服务器缓存异常",
        "errorCode" =>　10005
      ]);
    }
    return $key;
  }

  private function prepareCachedValue($wxResult,$uid){
    $cachedValue = $wxResult;
    $cachedValue['uid'] = $uid;
    $cachedValue['scope'] = ScopeEnum::User;
    return $cachedValue;
  }

  private function newUser($openid){
    $user = UserModel::create([
      "openid" => $openid
    ]);
    return $user->id;
  }

  private function processLoginError($wxResult){
    throw new WeChatException([
      "msg" => $wxResult['errmsg'],
      "errorCode" => $wxResult['errcode']
    ]);
  }
}
