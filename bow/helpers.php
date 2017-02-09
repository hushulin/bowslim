<?php

use Bow\Dbservice\Authcorp;
use Bow\Model\User;
use Bow\Model\School\Student;
use Bow\Model\School\Kinsfolk;
use Bow\Model\Subjects;
use Bow\Model\School\Grade;
use Bow\Model\School\SchoolType;
use Bow\Model\AuthCorpInfo;
use Bow\Model\Department;
use Bow\Model\School\ClassRoom;

$settings = $app->getContainer()->get('settings');
$container = $app->getContainer();

if ( ! function_exists('cdn') ) {
    function cdn($path)
    {
        global $settings;
        return  rtrim($settings['host'], '/\\') . '/' . $path;
    }
}

if ( ! function_exists('session') ) {
    function session($key , $value = null , $expires = 0)
    {
        global $container;
        $php_session_id = $container->get('request')->getCookieParam('PHPSESSID');
        $cache = $container->get('cache');
        $num = func_num_args();
        switch ($num) {
            case 1:
                return $cache->fetch($key . $php_session_id);
                break;

            case 2:
                return $cache->save($key . $php_session_id , $value);
                break;

            case 3:
                return $cache->save($key . $php_session_id , $value , $expires);
                break;

            default:
                throw new Exception("Error in session function");
                break;
        }
    }
}



/**
 * 判断是否是AJAX请求
 * @author  wuyi
 * @return  true or false
 */
if ( ! function_exists('IS_AJAX') ) {
    function IS_AJAX()
    {
        if(isset($_SERVER['HTTP_REQUEST_TYPE'])&&$_SERVER['HTTP_REQUEST_TYPE']=='ajax')
            return true;

        return false;
    }
}


/**
 * 构造接口返回数据格式
 * @author  wuyi
 * @param $data 返回的数据
 * @param $info 返回信息
 * @param $status 返回状态 1=成功，0=失败
 * @return  true or false
 */
if ( ! function_exists('Ajax_return') ) {
    function Ajax_return($data,$info,$status)
    {
        return ['data'=>$data,'info'=>$info,'status'=>$status];
    }
}

/**
 * 数组合并
 * @author  wuyi
 * @param $config 数组
 * @param $default 数组
 * @return  数组
 */
if (!function_exists('extend')) {

    function extend($config, $default) {
        foreach ($default as $key => $val) {
            if (!isset($config [$key])) {
                $config [$key] = $val;
            } else if (is_array($config [$key])) {
                $config [$key] = extend($config [$key], $val);
            }
        }

        return $config;
    }

}

/**
 * 判断数字为整型
 * @author  wuyi
 * @param $value
 * @return
 */
if ( ! function_exists('isInteger') ) {
    function isInteger( $value )
    {
        return is_numeric($value) && is_int($value+0);
    }
}


/**
 * 判断是否json 格式
 * @author  wuyi
 * @param $value
 * @return
 */
if ( ! function_exists('is_json') ) {
    function is_json($value) {
        return is_null(json_decode($value));
    }
}

if ( ! function_exists('getcorpidbyid') ) {
    function getcorpidbyid($id,$container)
    {
        $school=AuthCorpInfo::find($id);
        return $school['corpid'];
        //$ac= new Authcorp($container);
        //return $ac->get_schoool_corpid($id);

    }
}

if ( ! function_exists('getcorpinfoidbycorpid') ) {
    function getcorpinfoidbycorpid($corpid)
    {
        $school=AuthCorpInfo::where('corpid',$corpid)->get();
        return $school[0]->id;

    }
}
/**
 * object to array 格式
 * @author  wuyi
 * @param $value
 * @return
 */
if ( ! function_exists('object2array') ) {
    function object2array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }
}

if ( ! function_exists('create_uuid') ) {
    function create_uuid(){
        list($a,$b)= explode(" ", microtime()) ;
        $id= $b.substr($a, 2);
        return $id;
    }
}


/**
 * 根据部门ID获取部门的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getSysDepartmentInfo') ) {
    function getSysDepartmentInfo($department_id)
    {
        try {
            $result = Department::findOrFail($department_id);
        } catch (Exception $e) {
            return null;
        }
        return  $result ;

    }
}

/**
 * 根据学校ID获取学校的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getSysSchoolInfo') ) {
    function getSysSchoolInfo($school_id)
    {
        try {
            $result = AuthCorpInfo::findOrFail($school_id);
        } catch (Exception $e) {
            return null;
        }
        return  $result ;

    }
}


/**
 * 根据课程ID获取课程的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getSubjectInfo') ) {
    function getSubjectInfo($subject_id)
    {
        return Subjects::find($subject_id);

    }
}

/**
 * 根据年级ID获取年级的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getSysUserInfo') ) {
    function getSysUserInfo($user_id)
    {
        try {
            global $container;
            $cache = $container->get('cache');

            $key=REDIS_USER.$user_id;
            $result=$cache->fetch($key);
            if(empty($result))
            {
                $result= User::find($user_id);
                $cache->save($key,$result);
            }
        } catch (Exception $e) {
            return null;
        }
        return  $result ;
    }
}

if ( ! function_exists('clear_user_cache') ) {
    function clear_user_cache($user_id)
    {
        global $container;
        $cache = $container->get('cache');

        $key=REDIS_USER.$user_id;
        $cache->save($key,null);
    }
}

if ( ! function_exists('clear_student_cache') ) {
    function clear_student_cache($student_id)
    {
        global $container;
        $cache = $container->get('cache');

        $key=REDIS_STUDENT.$student_id;
        $cache->save($key,null);
    }
}

/**
 * 根据年级ID获取年级的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getClassRoomInfo') ) {
    function getClassRoomInfo($class_id)
    {
        try {
            $result= ClassRoom::find($class_id);
        } catch (Exception $e) {
            return null;
        }
        return  $result ;
    }
}

/**
 * 根据用户ID获取用户的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */
if ( ! function_exists('getSysGradeInfo') ) {
    function getSysGradeInfo($grade_id)
    {
        try {
            $result = Grade::findOrFail($grade_id);
        } catch (Exception $e) {
            return null;
        }
        return  $result ;
    }
}





/**
 * 根据学生ID获取学生的信息
 * @author  wuyi
 * @param $user_id 用户的ID
 * @return
 */

if ( ! function_exists('getStudentInfo') ) {
    function getStudentInfo($student_id)
    {
        try {
            global $container;
            $cache = $container->get('cache');

            $key=REDIS_STUDENT.$student_id;
            $result=$cache->fetch($key);
            if(empty($result))
            {
                $result = Student::findOrFail($student_id);
                $cache->save($key,$result);
            }
        } catch (Exception $e) {
            return null;
        }
        return  $result ;

    }
}

/**
 * 根据亲属关系ID获取亲属的信息
 * @author  wuyi
 * @param $kinsfolks_id 亲属的ID
 * @return
 */
if ( ! function_exists('getKinsfolksInfo') ) {
    function getKinsfolksInfo($kinsfolks_id)
    {
        try {
            $result = Kinsfolk::findOrFail($kinsfolks_id);
        } catch (Exception $e) {
            return null;
        }
        return  $result ;

    }
}



