<?php
namespace Bow\safe;

use Slim\App;

class IpGuard
{
	// The application
	protected $app;
	
	function __construct(App $app)
	{
		$this->app = $app;
	}

	public function __invoke($request, $response, $next)
	{
		$container = $this->app->getContainer();

		$client_ip = $this->get_client_ip();

		if ( !in_array($client_ip, $container['settings']['enableips']) ) {
			return $response->withJson(['msg' => 'Forbidden access'] , 403);
		}

		$response = $next($request , $response);
		return $response;
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	protected function get_client_ip($type = 0) {
		$type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	        $pos    =   array_search('unknown',$arr);
	        if(false !== $pos) unset($arr[$pos]);
	        $ip     =   trim($arr[0]);
	    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}
}
