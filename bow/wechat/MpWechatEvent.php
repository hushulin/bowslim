<?php
namespace Bow\Wechat;

use Bow\Model\User;
use Interop\Container\ContainerInterface;
use Exception;
use Illuminate\Support\Str;

class MpWechatEvent
{

	protected $container;

	function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	// 用户订阅事件处理
	public function handleUserSubscribe($openid)
	{
		$settings = $this->container->get('settings');
		$logger = $this->container->get('logger');
		$logger->info(sprintf('handle user subscribe event:%s' , $openid));

		if ( !$openid ) {
			return;
		}

		if ( ! $this->container->has('EasyWeChat.Application') ) {
			$logger->info(sprintf('no [EasyWeChat.Application] in the container'));
			return;
		}

		$app = $this->container->get('EasyWeChat.Application');
		$us = $app->user;

		$user = $us->get($openid);
		$values = [
			'openid' => $openid,
			'subscribe' => $user->subscribe,
			'name' => $user->nickname,
			'gender' => $user->sex,
			'avatar' => $user->headimgurl,
			'userid' => Str::random(64),
			'corpid' => $settings['mpwechat']['app_id'],
		];
		$model = User::updateOrCreate(['openid' => $openid] , $values);
		return $model;
	}

	// 用户取消订阅事件处理
	public function handleUserUnSubscribe($openid)
	{
		$logger = $this->container->get('logger');
		$logger->info(sprintf('handle user unsubscribe event:%s' , $openid));

		if ( !$openid ) {
			return;
		}

		if ( ! $this->container->has('EasyWeChat.Application') ) {
			$logger->info(sprintf('no [EasyWeChat.Application] in the container'));
			return;
		}

		$app = $this->container->get('EasyWeChat.Application');
		$us = $app->user;

		$user = $us->get($openid);
		$values = [
			'openid' => $openid,
			'subscribe' => $user->subscribe,
		];
		$model = User::updateOrCreate(['openid' => $openid] , $values);
		return $model;
	}

	public function init()
	{
		$settings = $this->container->get('settings');
		$logger = $this->container->get('logger');
		$logger->info(sprintf('handle dev init data'));

		if ( ! $this->container->has('EasyWeChat.Application') ) {
			$logger->info(sprintf('no [EasyWeChat.Application] in the container'));
			return;
		}

		$app = $this->container->get('EasyWeChat.Application');

		$next_openid = null;
		$tmpUsers = [];
		$userService = $app->user;


		try {
			$this->fetchUsers($next_openid , $tmpUsers , $userService);
		} catch (Exception $e) {
			$logger->info(sprintf('%s - %s - %s' , $e->getMessage() , $e->getLine() , $e->getFile()));
		}


		foreach ($tmpUsers as $key => $value) {

			$logger->info($value['openid']);


			$values = [
				'openid' => $value['openid'],
				'subscribe' => $value['subscribe'],
				'name' => $value['nickname'],
				'gender' => $value['sex'],
				'avatar' => $value['headimgurl'],
				'userid' => Str::random(64),
				'corpid' => $settings['mpwechat']['app_id'],
			];

			try {
				User::updateOrCreate(['openid' => $value['openid']] , $values);
			} catch (Exception $e) {
				$logger->info(sprintf('%s - %s - %s' , $e->getMessage() , $e->getLine() , $e->getFile()));
				continue;
			}

		}

		return;
	}

	public function fetchUsers(&$next_openid , &$tmpUsers , &$userService)
	{
		$users = $userService->lists($next_openid);

		$next_openid = $users->next_openid;

		foreach ($users->data as $key => $value) {

			$fetch = $userService->batchGet($value);

			$tmpUsers = array_merge($tmpUsers , $fetch->user_info_list);

			break;
		}

		// 递归中断
		if ( empty($next_openid) ) {
			return;
		}else {
			$this->fetchUsers($next_openid , $tmpUsers , $userService);
		}
	}
}
