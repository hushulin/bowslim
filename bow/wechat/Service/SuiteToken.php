<?php
namespace Bow\Wechat\Service;

use Bow\Wechat\Core\AbstractAPI;

class SuiteToken extends AbstractAPI
{

	protected $container;

	const GET_SUITE_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';

	function __construct($container)
	{
		$this->container = $container;
	}

	public function get_suite_token()
	{

		$settings = $this->container->get('settings');
		$cache = $this->container->get('cache');

		$key = $settings['wechat']['suiteId'];

		$options = [
			'suite_id' => $settings['wechat']['suiteId'],
			'suite_secret' => $settings['wechat']['secret'],
			'suite_ticket' => $cache->fetch($key),
		];

		// Add cache mechanism [ten minutes error time]
		if ( !$cache->fetch( md5(json_encode($options)) ) ) {
			$suite_access_token = $this->parseJSON('post' , [self::GET_SUITE_TOKEN , json_encode($options)])['suite_access_token'];
			$cache->save( md5(json_encode($options)) , $suite_access_token , 6600);
		}

		return $cache->fetch( md5(json_encode($options)) );

		// Fixed bug options must be a json string
		// return $this->parseJSON('post' , [self::GET_SUITE_TOKEN , json_encode($options)]);
	}
}
