<?php

defined('YAR_SERVER_ON') or define('YAR_SERVER_ON', true);

require __DIR__ . '/../vendor/autoload.php';

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

date_default_timezone_set('Asia/Shanghai');

class BaseRPC
{
	protected $withMiddleware = true;

    public function runApp($requestMethod, $requestUri, $requestData = null , $PHPSESSID = null)
    {
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        $request = Request::createFromEnvironment($environment);

        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        if ( isset($PHPSESSID) ) {
            $request = $request->withCookieParams(['PHPSESSID' => $PHPSESSID]);
        }

        $response = new Response();

        $settings = require __DIR__ . '/../src/settings.php';

        $app = new App($settings);

        require __DIR__ . '/../src/dependencies.php';

        if ($this->withMiddleware) {
            require __DIR__ . '/../src/middleware.php';
        }

        require __DIR__ . '/../src/routes.php';

        require __DIR__ . '/../bow/helpers.php';

        $response = $app->process($request, $response);

        return $response;
    }
}

class UserAPI extends BaseRPC
{
	public function exec($uri , $data = null , $PHPSESSID = null)
	{
		return (string)$this->runApp('POST' , $uri , $data , $PHPSESSID)->getBody();
	}
}

$service = new Yar_Server(new UserAPI());

$service->handle();
