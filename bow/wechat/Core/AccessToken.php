<?php 
namespace Bow\Wechat\Core;

use EasyWeChat\Core\AccessToken as EasyWeChatAccessToken;
use EasyWeChat\Core\Exceptions\HttpException;

class AccessToken extends EasyWeChatAccessToken
{

	// API
    const API_TOKEN_GET = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
	
	function __construct($appId , $secret , $cache = null)
	{
		parent::__construct($appId , $secret , $cache);
	}

	/**
     * Get the access token from WeChat server.
     *
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @return string
     */
    public function getTokenFromServer()
    {
        $params = [
            'corpid' => $this->appId,
            'corpsecret' => $this->secret,
        ];

        $http = $this->getHttp();

        $token = $http->parseJSON($http->get(self::API_TOKEN_GET, $params));

        if (empty($token['access_token'])) {
            throw new HttpException('Request AccessToken fail. response: '.json_encode($token, JSON_UNESCAPED_UNICODE));
        }

        return $token;
    }
}
