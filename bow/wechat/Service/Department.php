<?php
namespace Bow\Wechat\Service;

use Bow\Wechat\Core\AbstractAPI;

class Department extends AbstractAPI
{
	protected $container;

	function __construct($container)
	{
		$this->container = $container;
	}

	/**
	 * 创建部门,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数 {"name": "广州研发中心","parentid": 1,"order": 1,"id": 1}
	 * @return 
	 */
	public function Createdepartment_api($access_token,$options)
	{
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token={$access_token}";
	    
	    return $this->parseJSON('post' , [$uri , json_encode($options, JSON_UNESCAPED_UNICODE)]);
	}
	
	/**
	 * 更新部门,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数 {"name": "广州研发中心","parentid": 1,"order": 1,"id": 1}
	 * 请求包结构体为（如果非必须的字段未指定，则不更新该字段之前的设置值）
	 * @return
	 */
	public function Updatedepartment_api($access_token,$options)
	{
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token={$access_token}";
	     
	    return $this->parseJSON('post' , [$uri , json_encode($options, JSON_UNESCAPED_UNICODE)]);
	}
	
	/**
	 * 删除部门,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $id  部门ID 
	 * @return "errcode": 0,"errmsg": "deleted"
	 */
	public function Deletdepartment_api($access_token,$id) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/department/delete";
	    $params = ['access_token' => $access_token,'id' => $id];
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
	/**
	 * 获取部门列表,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $id  部门ID id=0获取全部部门信息 不为0 获取指定部门及其下的子部门
	 * @return 
	 */
	public function Getdepartmentlist_api($access_token,$id=0) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/department/list";
	    $params = ['access_token' => $access_token];
	    if($id)
	        $params['id']=$id;
	    return $this->parseJSON('get' , [$uri,$params]);
	}
}
