<?php
namespace Bow\Wechat\Service;

use Bow\Wechat\Core\AbstractAPI;

class Oauth extends AbstractAPI
{
    const API_GETUSERINFO = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo";
	protected $container;

	function __construct($container)
	{
		$this->container = $container;
	}

	/**
	 * 企业请求获取code方法
     * @author  wuyi
     * @param $response $response对象
     * @param $corpid  企业号ID
     * @param $callback_uri 企业获取code 回调地址
     * @return  
     */
	public function OauthRedirect($response,$corpid,$callback_uri)
	{
		$uri="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$corpid}&redirect_uri={$callback_uri}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
		return $response->withRedirect($uri);
	}

	
	/**
	 * 根据code获取成员信息
	 * @author  wuyi
	 * @param $response $response对象
	 * @param $corpid  企业号ID
	 * @param $callback_uri 企业获取code 回调地址
	 * @return {"UserId":"USERID","DeviceId":"DEVICEID"} UserId 成员UserID  DeviceId 手机设备号
	 */
	public function Getuserinfo($access_token,$code)
	{
	    $params = ['access_token' => $access_token,'code' => $code];
	    return $this->parseJSON('get' , [self::API_GETUSERINFO,$params]);
	}
	
}
