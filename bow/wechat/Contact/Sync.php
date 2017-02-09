<?php
namespace Bow\Wechat\Contact;

use Bow\Model\School\ParentStudent;
use Bow\Model\User;
use Bow\Model\Department;
use Bow\Model\UserDepartment;
use Bow\Model\UserRole;
use Bow\Wechat\Service\Login;
use Bow\Model\ContactSyncFlag;
use Bow\Wechat\Core\AbstractAPI;
use Illuminate\Database\QueryException;
use Interop\Container\ContainerInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Sync extends AbstractAPI
{

	// The application container
	protected $container;

	// Fetched from container
	protected $logger;

	function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->logger = $this->container->get('logger');
	}

	public function updateFromWechat($suite_id , $corpid , $seq = 0)
	{

		$this->logger->info('updateFromWechat:' . $suite_id . '-' . $corpid . '-' . $seq);
		try {
			$this->runUpdate($suite_id , $corpid , $seq);
		} catch (\Exception $e) {
			$this->logger->info($e->getMessage());
		}
	}

	public function runUpdate($suite_id , $corpid , $seq)
	{
		$row = [
			'suite_id' => $suite_id,
			'auth_corp_id' => $corpid,
		];

		// First find row from database or init row
		$flag = ContactSyncFlag::firstOrCreate($row , ['seq' => 0 , 'offset' => 0]);

		$this->logger->info('ContactSyncFlag:' . $flag->id);

		$login = new Login($this->container);
		$access_token = $login->get_corp_token($corpid , $suite_id);

		// Run under code if local contact not eq wechat server contact
		$uri = 'https://qyapi.weixin.qq.com/cgi-bin/sync/getpage?access_token=' . $access_token;

		$options = [
			'seq' => $flag->seq,
			'offset' => $flag->offset,
		];

		$this->logger->info(json_encode($options));

		$contacts = $this->parseJSON('post' , [$uri , json_encode($options)]);

		$this->logger->info($contacts);

		foreach ($contacts['data'] as $key => $value) {
			switch ($value['type']) {
				case 1:
					// User able to manage
					$user = $value['user'];
					$department_ids = $user['department'];
					$this->fixUserData($user , $corpid);
					// try {
					// 	$user_id = User::create($user)['id'];
					// 	$this->updateUserDepartmentRelation($user_id , $corpid , $department_ids);
					// } catch (QueryException $e) {
					// 	$this->logger->info($e->getMessage());
					// 	User::where('userid' , $user['userid'])->where('corpid' , $corpid)->update(['able_manage' => 1]);
					// 	$this->updateUserDepartmentRelation($this->getUserId($user['userid'] , $corpid) , $corpid , $department_ids);
					// }
					User::updateOrCreate(['userid' => $user['userid'] , 'corpid' => $corpid] , $user);
                    $user_id=$this->getUserId($user['userid'] , $corpid);
					$this->updateUserDepartmentRelation($user_id , $corpid , $department_ids);

                    if($user['status']!=2)
                        $this->updateUserRole($user_id  , $department_ids);

                    clear_user_cache($user_id);
					break;

				case 2:
					$userid = $value['userid'];
					User::where('userid' , $userid)->where('corpid' , $corpid)->update(['able_manage' => 0]);
                    $user_id=$this->getUserId($user['userid'] , $corpid);
                    clear_user_cache($user_id);
					break;

				case 3:
					$userid = $value['userid'];
					$user_id=$this->getUserId($userid , $corpid);
					User::where('userid' , $userid)->where('corpid' , $corpid)->delete();

					UserDepartment::where('user_id' , $user_id)->where('corpid' , $corpid)->delete();

                    UserRole::where('user_id' , $user_id)->delete();

                    clear_user_cache($user_id);
					break;

				case 4:
					$user = $value['user'];
					$department_ids = $user['department'];
					$this->fixUserData($user , $corpid);
					// User::where('userid' , $user['userid'])->where('corpid' , $corpid)->update($user);

                    User::updateOrCreate(['userid' => $user['userid'] , 'corpid' => $corpid] , $user);
                    $user_id=$this->getUserId($user['userid'] , $corpid);
                    clear_user_cache($user_id);
                    $this->updateUserDepartmentRelation($user_id , $corpid , $department_ids);

                    if($user['status']!=2)
                        $this->updateUserRole($user_id  , $department_ids);
					break;

				case 5:
					$department = $value['department'];
					$this->fixDepartmentData($department , $corpid);
					// try {
					// 	Department::create($department);
					// } catch (QueryException $e) {
					// 	Department::where('id' , $department['id'])->where('corpid' , $corpid)->update(['able_manage' => 1]);
					// }
					// Department::updateOrCreate(['id' => $department['id'] , 'corpid' => $corpid] , $department);
                    self::createOrupdate($corpid,$department);
					break;

				case 6:
					$department_id = $value['department_id'];
					Department::where('id' , $department_id)->where('corpid' , $corpid)->update(['able_manage' => 0]);
					break;

				case 7:
					$department_id = $value['department_id'];
					Department::where('id' , $department_id)->where('corpid' , $corpid)->delete();
					break;

				case 8:
					$department = $value['department'];
                    //Department::where('id' ,$department['id'])->where('corpid' , $corpid)->update($department);

                    self::createOrupdate($corpid,$department);
					break;

				case 9:
					$department = $value['department'];
					$this->fixDepartmentData($department , $corpid);
					// try {
					// 	Department::create($department);
					// } catch (QueryException $e) {
					// 	$this->logger->info($e->getMessage());
					// 	Department::where('id' , $department['id'])->where('corpid' , $corpid)->update($department);
					// }
					//Department::updateOrCreate(['id' => $department['id'] , 'corpid' => $corpid] , $department);
                    self::createOrupdate($corpid,$department);
					break;

				default:
					// Do nothing
					break;
			}
		}

		$flag->update([
			'seq' => $contacts['next_seq'],
			'offset' => $contacts['next_offset'],
		]);

		if ( ! $contacts['is_last'] ) {
			$this->runUpdate($suite_id , $corpid , $flag->seq);
		}
	}

	public function createOrupdate($corpid,$department)
    {
        $item=Department::where('id' ,$department['id'])->where('corpid' , $corpid)->first();
        if(!empty($item))
            Department::where('id' ,$department['id'])->where('corpid' , $corpid)->update($department);
        else
            Department::create($department);

    }


	public function fixUserData(&$user , $corpid)
	{
		if ( isset($user['extattr']) && !empty($user['extattr']) ) {
			$user['extattr'] = json_encode($user['extattr']);
		}
		$user['department'] = json_encode($user['department']);
		$user['corpid'] = $corpid;
		$user['able_manage'] = 1;
	}

	public function fixDepartmentData(&$department , $corpid)
	{
		$department['corpid'] = $corpid;
		$department['able_manage'] = 1;
	}

	/**
	 * @param $user_id Ours system autoincrement id
	 */
	public function delUserDepartmentRelation($user_id , $corpid)
	{
		UserDepartment::where('user_id' , $user_id)->where('corpid' , $corpid)->delete();
	}

	/**
	 *	@param $department_ids array [1,2,3] use wechat system department id
	 */
	public function updateUserDepartmentRelation($user_id , $corpid , array $department_ids)
	{
		if ($user_id == 0) {
			return ;
		}
		$ret=UserDepartment::where('user_id' , $user_id)->where('corpid' , $corpid)->get()->toArray();

		// $ret=object2array($ret);

		foreach ($ret as $key=>$val)
		{
		     if(!in_array($val['department_id'], $department_ids))
		         UserDepartment::where('user_id' , $user_id)->where('department_id' , $val['department_id'])->where('corpid' , $corpid)->delete();
		}

		foreach ($department_ids as $department_id) {


		    $ret=UserDepartment::where('user_id' , $user_id)->where('department_id' , $department_id)->where('corpid' , $corpid)->first();

			if(empty($ret))
			{
    			UserDepartment::create([
    				'user_id' => $user_id,
    				'department_id' => $department_id,
    				'corpid' => $corpid,
    			]);
			}
		}
	}
    public function updateUserRole($user_id  ,array $department_ids)
    {
    	try {
    		$user= User::find($user_id);
	        $role_array=array();
	        foreach ($department_ids as $department_id) {
	            $role_id = PARENT_ROLEID;
	            if ($department_id > 1)
	                $role_id = SCHOOLUSER_ROLE;

	            if (empty($role_array[$role_id])) {

	                $ur=false;
	                if($role_id==PARENT_ROLEID)
	                {
	                    $ur=ParentStudent::where('parent_id', $user_id)->first();
	                    if(empty($ur))
	                    {
	                        UserRole::where('user_id', $user_id)->where('role_id', $role_id)->delete();
	                        $ur=true;
	                    }
	                    else
	                        $ur = UserRole::where('user_id', $user_id)->where('role_id', $role_id)->first();

	                }
	                else if($role_id==PARENT_ROLEID)
	                    $ur = UserRole::where('user_id', $user_id)->where('role_id', $role_id)->first();

	                if (empty($ur)) {
	                    UserRole::create(['user_id' => $user_id,'role_id' => $role_id]);
	                }
	                $role_array[$role_id] = time();
	            }
	        }
    	} catch (\Exception $e) {
    		//
    	}
    }

	public function getUserId($userid , $corpid)
	{
		return User::where('userid' , $userid)->where('corpid' , $corpid)->value('id') ;
	}
}
