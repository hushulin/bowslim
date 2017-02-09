<?php
namespace Bow\Wechat\Service;

use Bow\Model\Authorize;
use Bow\Wechat\Core\AbstractAPI;

class Login extends AbstractAPI
{
	protected $container;

	function __construct($container)
	{
		$this->container = $container;
	}

	public function get_pre_auth_code()
	{

		$settings = $this->container->get('settings');
		$cache = $this->container->get('cache');

		$suite_id = $settings['wechat']['suiteId'];

		$suiteToken = new SuiteToken($this->container);
		$suite_access_token = $suiteToken->get_suite_token();

		$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token='.$suite_access_token;

		$options = ['suite_id' => $suite_id,];

		// Add cache mechanism [ten minutes error time]
		if ( !$cache->fetch( md5( $uri . $suite_id ) ) ) {
			$arr = $this->parseJSON('post' , [$uri , json_encode($options)]);
			$cache->save(md5( $uri . $suite_id ) , $arr['pre_auth_code'] , (int)$arr['expires_in'] - 600);
		}

		return $cache->fetch( md5( $uri . $suite_id ) );
	}

	/**
	 * Return a uri that guide user to authorize
	 * @param $redirect_uri
	 */
	public function get_authorize_uri($redirect_uri)
	{
		$pre_auth_code = $this->get_pre_auth_code();

		$settings = $this->container->get('settings');
		$suite_id = $settings['wechat']['suiteId'];

		$state = time();

		return sprintf('https://qy.weixin.qq.com/cgi-bin/loginpage?suite_id=%s&pre_auth_code=%s&redirect_uri=%s&state=%s' ,
						$suite_id , $pre_auth_code , urlencode($redirect_uri) , $state);
	}

	public function get_authorize_uri_mobile()
	{
		$settings = $this->container->get('settings');
		$suite_id = $settings['wechat']['suiteId'];

		return sprintf('https://qy.weixin.qq.com/cgi-bin/3rd_loginpage?action=jumptoauthpage&suiteid=%s&t=wap' , $suite_id);
	}

	/**
	 * Suite settings in session
	 * @param $appid array [1,2,3]
	 * @param $auth_type 0:official 1:test
	 */
	public function set_session_info(array $appid , $auth_type)
	{
		$suiteToken = new SuiteToken($this->container);
		$suite_access_token = $suiteToken->get_suite_token();

		$pre_auth_code = $this->get_pre_auth_code();

		$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=' . $suite_access_token;

		$options = [
			'pre_auth_code' => $pre_auth_code,
			'session_info' => [
				'appid' => $appid,
				'auth_type' => $auth_type,
			],
		];

		return $this->parseJSON('post' , [$uri , json_encode($options)]);
	}

	// Return array
	public function get_permanent_code($auth_code = '')
	{
		$suiteToken = new SuiteToken($this->container);
		$suite_access_token = $suiteToken->get_suite_token();

		$settings = $this->container->get('settings');
		$suite_id = $settings['wechat']['suiteId'];

		$cache = $this->container->get('cache');

		$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=' . $suite_access_token;

		$options = [
			'suite_id' => $suite_id,
			'auth_code' => $auth_code,
		];

		// Add cache mechanism [ten minutes error time]
		if ( !$cache->fetch( md5( $uri . $suite_id . $auth_code ) ) ) {
			$arr = $this->parseJSON('post' , [$uri , json_encode($options)]);
			$cache->save(md5( $uri . $suite_id  . $auth_code ) , $arr , (int)$arr['expires_in'] - 600);
		}

		return $cache->fetch( md5( $uri . $suite_id  . $auth_code ) );
	}

	// Return auth info
	public function get_auth_info($corpid , $needcache = true)
	{

		$settings = $this->container->get('settings');
		$suite_id = $settings['wechat']['suiteId'];

		$cache = $this->container->get('cache');

		if (!$needcache) {
			$suiteToken = new SuiteToken($this->container);
			$suite_access_token = $suiteToken->get_suite_token();

			$permanent_code = $this->get_permanent_from_db($suite_id , $corpid);

			if ( empty( $permanent_code ) ) {
				throw new \Exception("Please authorize corp first!", 10001);
			}

			$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=' . $suite_access_token;

			$options = [
				'suite_id' => $suite_id,
				'auth_corpid' => $corpid,
				'permanent_code' => $permanent_code,
			];


			$auth_info = $this->parseJSON('post' , [$uri , json_encode($options)]);

			return $auth_info;
		}

		if ( !$cache->fetch( $suite_id . $corpid . '.auth_info' ) ) {

			$suiteToken = new SuiteToken($this->container);
			$suite_access_token = $suiteToken->get_suite_token();

			$permanent_code = $this->get_permanent_from_db($suite_id , $corpid);

			if ( empty( $permanent_code ) ) {
				throw new \Exception("Please authorize corp first!", 10001);
			}

			$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=' . $suite_access_token;

			$options = [
				'suite_id' => $suite_id,
				'auth_corpid' => $corpid,
				'permanent_code' => $permanent_code,
			];


			$auth_info = $this->parseJSON('post' , [$uri , json_encode($options)]);

			// Cache time or forever now 2 hours
			$cache->save( $suite_id . $corpid . '.auth_info' , $auth_info , 7200 );
		}

		return $cache->fetch( $suite_id . $corpid . '.auth_info' );
	}

	public function get_corp_token($corpid , $suite_id = null)
	{

		$settings = $this->container->get('settings');
		if ( $suite_id == null ) {
			$suite_id = $settings['wechat']['suiteId'];
		}
		$cache = $this->container->get('cache');

		// $logger = $this->container->get('logger');
		// $logger->info(sprintf('获取微信云商token：%s - %s' , $suite_id , $corpid));

		if ( !$cache->fetch( md5($suite_id . $corpid . '.access_token') ) ) {

			$suiteToken = new SuiteToken($this->container);
			$suite_access_token = $suiteToken->get_suite_token();

			$permanent_code = $this->get_permanent_from_db($suite_id , $corpid);

			if ( empty( $permanent_code ) ) {
				return null;
			}

			$uri = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=' . $suite_access_token;

			$options = [
				'suite_id' => $suite_id,
				'auth_corpid' => $corpid,
				'permanent_code' => $permanent_code,
			];


			$access_token = $this->parseJSON('post' , [$uri , json_encode($options)]);

			$cache->save( md5($suite_id . $corpid . '.access_token') , $access_token['access_token'] , (int)$access_token['expires_in'] - 600 );
		}

		return $cache->fetch( md5($suite_id . $corpid . '.access_token') );
	}

	/**
	 * Get permanent code from database
	 * @param $suite_id string
	 * @param $auth_corpid string
	 * @param $permanent_code
	 */
	public function get_permanent_from_db($suite_id , $auth_corpid)
	{
		return Authorize::where('suite_id' , $suite_id)
						->where('auth_corpid' , $auth_corpid)
						->value('permanent_code');
	}
}
