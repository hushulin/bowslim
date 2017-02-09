<?php
namespace Bow\Wechat\CorpAuth;

use Bow\Model\AuthCorpInfo;
use Bow\Model\AuthInfoAgent;
use Bow\Wechat\Service\Login;
use Illuminate\Database\QueryException;
use Interop\Container\ContainerInterface;

class Update
{

	protected $container;

	function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function updateFromChangeEvent($suite_id , $auth_corp_id)
	{
		$logger = $this->container->get('logger');
		$login = new Login($this->container);
		$auth_info = $login->get_auth_info($auth_corp_id , false);

		try {
			$id = AuthCorpInfo::create($auth_info['auth_corp_info'])['id'];
		} catch (QueryException $e) {
			AuthCorpInfo::where('corpid' , $auth_info['auth_corp_info']['corpid'])->update($auth_info['auth_corp_info']);
			$id = AuthCorpInfo::where('corpid' , $auth_info['auth_corp_info']['corpid'])->value('id');
		}

		$logger->info(sprintf('suite:%s , auth:%s , system:%s' , $suite_id , $auth_corp_id , $id));

		AuthInfoAgent::where('auth_corp_info_id' , $id)->delete();
		foreach ($auth_info['auth_info']['agent'] as $key => $value) {

			$this->fixAgentData($value , $id);

			try {
				AuthInfoAgent::create($value);
			} catch (\Exception $e) {
				$logger->info($e->getMessage());
			}

		}
	}

	protected function fixAgentData(&$value , $id)
	{
		$value['privilege'] = json_encode($value['privilege']);
		$value['auth_corp_info_id'] = (int)$id;
		// Let's fuck wechat
		unset($value['api_group']);
	}
}
