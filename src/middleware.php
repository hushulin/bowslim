<?php
// Application middleware

// Permission for ip [Wechat can not forbidden]
// $app->add(new \Bow\safe\IpGuard($app));

// Boot Eloquent
$app->add( function ($request, $response, $next) use ($app) {
	$container = $app->getContainer();
	$container->get('db');
	return $next($request , $response);
});
