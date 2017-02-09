<?php
namespace Bow\Wechat\Service;

use Bow\Wechat\Core\AbstractAPI;
use Bow\Model\User;

class Members extends AbstractAPI
{
    const API_CREATEMEMBER = "https://qyapi.weixin.qq.com/cgi-bin/user/create";
	protected $container;

	function __construct($container)
	{
		$this->container = $container;
	}

	/**
	 * 创建成员,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数
	 * @return 
	 */
	public function Createmember_api($access_token,$options)
	{
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token={$access_token}";
	    
	    return $this->parseJSON('post' , [$uri , json_encode($options, JSON_UNESCAPED_UNICODE)]);
	}
	
	/**
	 * 获取成员信息,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数
	 * @return
	 */
	public function Getmember_api($access_token,$userid) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/get";
	    $params = ['access_token' => $access_token,'userid' => $userid];
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
	/**
	 * 二次验证,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数
	 * @return
	 */
	public function Authsucc_api($access_token,$userid) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/authsucc";
	    $params = ['access_token' => $access_token,'userid' => $userid];
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
	/**
	 * 获取成员信息,本地数据库
	 * @author  wuyi
	 * @param $where 条件
	 * @return
	 */
	public function Getmember_local($where) {
	   return User::where( $where)->get()->toArray();
	}
	
	/**
	 * 更新成员,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $options  创建成员的参数
	 * @return
	 */
	public function Updatemember_api($access_token,$options,$user_id) {

        clear_user_cache($user_id);
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token={$access_token}";
	    
	    return $this->parseJSON('post' , [$uri , json_encode($options, JSON_UNESCAPED_UNICODE)]);
	}
	
	/**
	 * 删除成员,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $userid  成员userid
	 * @return "errcode": 0,"errmsg": "deleted"
	 */
	public function Deletemember_api($access_token,$userid) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/delete";
	    $params = ['access_token' => $access_token,'userid' => $userid];
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
	/**
	 * 批量删除成员,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $useridlist  数组成员userid  ["zhangsan", "lisi"]
	 * @return "errcode": 0,"errmsg": "deleted"
	 */
	public function Batchdeletemember_api($access_token,$useridlist) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token={$access_token}";
	    $params = ['useridlist' => $useridlist];
	    return $this->parseJSON('post' , [$uri,json_encode($params)]);
	}
	
	
	/**
	 * 获取部门成员,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $department_id  部门id
	 * @param $fetch_child  1/0：是否递归获取子部门下面的成员
	 * @param $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加，未填写则默认为4
	 * @return   "errcode": 0,"errmsg": "ok","userlist": [{"userid": "zhangsan","name": "李四","department": [1, 2]}]
	 */
	public function Getdepuserlist_api($access_token,$department_id,$fetch_child=1,$status=0) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist";
	    $params = [
	        'access_token' => $access_token,
	        'department_id' => $department_id,
	        'fetch_child' => $fetch_child,
	        'status' => $status,
	    ];
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
	/**
	 * 获取部门成员详情,通过API
	 * @author  wuyi
	 * @param $access_token 调用接口凭证
	 * @param $department_id  部门id
	 * @param $fetch_child  1/0：是否递归获取子部门下面的成员
	 * @param $status 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加，未填写则默认为4
	 * @return   "errcode": 0,"errmsg": "ok","userlist": [{"userid": "zhangsan","name": "李四","department": [1, 2],...}]
	 */
	public function Getdepuserinfolist_api($access_token,$department_id,$fetch_child=1,$status=0) {
	    $uri = "https://qyapi.weixin.qq.com/cgi-bin/user/list";
	    $params = [
	        'access_token' => $access_token,
	        'department_id' => $department_id,
	        'fetch_child' => $fetch_child,
	        'status' => $status,
	    ];
	    
	    return $this->parseJSON('get' , [$uri,$params]);
	}
	
}
