<?php

namespace app\Admin\model;

use think\facade\Cache;
use think\facade\Session;
use think\Model;

class User extends Model
{

    protected $salt = 'abcdefg';

    protected $table = 'admin_user';

//    protected $insert = ['lastlogin_time', 'password', 'salt'];

    protected $user_token_prefix = 'AdminUserToken_';

    //默认写入最后登录时间
    protected function setLastloginTimeAttr($value){
        return time();
    }
    //生成密码
    public function setPasswordAttr($value)
    {
        $this->salt = uniqid();
        return md5($value.$this->salt);
    }
    //生成密码盐
    public function setSaltAttr($value)
    {
        return $this->salt;
    }

    //关联查询
    public function AuthGroup()
    {
        return $this->hasOne('AuthGroup', 'id','group')->bind('name');
    }

    /**
     * 修改密码
     */
    public function editUserPassword($uid, $old_password, $new_password, $update_token = false)
    {
        $user_info = $this->find($uid);
        if($user_info['password'] == md5($old_password.$user_info['salt'])){
            $this->error('新旧密码不能相同');
            return false;
        }
        $save['password']     = $this->setPasswordAttr($new_password);
        $save['salt']    = $this->setSaltAttr($new_password);

        $save_result = $this->where('id', $uid)->save($user_info);
        if($save_result && $update_token) {
            $token = md5($uid . time() . $this->setSaltAttr(''));
            self::saveUserToken($user_info['id'], $user_info['token']);
        }

        return $save_result;
    }

    /**
     * 登录
     */
    public function login($username, $password)
    {
        $where['username'] = $username;
        $user_info = $this->where($where)->withJoin('AuthGroup')->find();

        if($user_info == false){
            $this->error = '账号或密码错误';
            return false;
        }

        $user_info = $user_info->toArray();
        //验证密码
        if(md5($password.$user_info['salt']) != $user_info['password']){
            $this->error = '账号或密码错误';
            return false;
        }
        //验证状态
        if($user_info['status'] != 1){
            $this->error = '该用户已被禁用';
            return false;
        }
        if($user_info['auth_group']['status'] != 1){
            $this->error = '该用户角色已被禁用';
            return false;
        }

        //获取角色权限并存储
        $auth_model = new \app\Admin\model\AuthGroup();
        $user_auth = $auth_model->getAuthGroupRule($user_info['group']);
        if($user_auth == false){
            $this->error = '该用户角色无权访问';
            return false;
        }

        //生成本次登录凭证并存储
        $save['lastlogin_time'] = time();
        $user_info['token'] = md5($user_info['id'].$save['lastlogin_time'].$user_info['salt']);
        Session::set('user_info', $user_info);
        Session::set('is_login', 1);
        self::saveUserToken($user_info['id'], $user_info['token']);

        $this->save($save, ['id' => $user_info['id']]);

        return true;
    }

    /*
     * 检查角色权限
     */
    public function checkUserAuth($url = null)
    {
        if(empty($url)) {
            $module_name = request()->module();
            $controller_name = request()->controller();
            $action_name = request()->action();
            $url = strtolower($module_name . '/' . $controller_name . '/' . $action_name);
        }
        //不需登录名单
        $no_login_list = [
            'admin/common/login',
            'admin/common/logout'
        ];
        //白名单
        $white_list = [
            'admin/index/index'
        ];
        if(in_array($url, $no_login_list))return true;

        $user_info = Session::get('user_info');
        if($user_info['auth_group']['rules'] == '*'){
            $this->insertAdminLog($url);
            return true;
        }

        //登录状态
        if(!Session::has('is_login')){
            $this->error = '身份验证已失效，请重新登录';
            return 'no_login';
        }
        //token状态
        $user_token = self::getUserToken($user_info['id']);
        if($user_token != $user_info['token']){
            $this->error = '身份验证已失效，请重新登录';
            return 'no_login';
        } else {
            self::saveUserToken($user_info['id'], $user_info['token']);
        }

        if(in_array($url, $white_list))return true;

        //检查权限
        $rule_list_key = array_keys(session('user_auth.rule_list'));

        if(!in_array($url, $rule_list_key)){
            $this->error = '无权限访问';
            return in_array($url, $white_list) ? 'no_login' : 'no_auth';
        }

        $this->insertAdminLog($url);
        return true;
    }

    //写入日志
    private function insertAdminLog($url)
    {
        $auth_rule_model = new \app\admin\model\AuthRule();
        $user_info = Session::get('user_info');
        $rule_list = Session::get('user_auth.rule_list');

        $admin_log = new AdminLog();
        if (isset($rule_list[$url]['title'])) {
            $admin_log->title = $auth_rule_model->getAuthRuleTitle($rule_list[$url]['id']);
        } else {
            $admin_log->title = '操作';
        }

        $admin_log->admin_id = $user_info['id'];
        $admin_log->username = $user_info['username'];
        $admin_log->url = request()->url();
        $admin_log->content = json_encode(request()->param());
        $admin_log->ip = request()->ip();
        $admin_log->useragent = request()->header('user-agent');;
        $admin_log->save();
    }

    //获取用户TOKEN
    private function getUserToken($uid)
    {
        return Cache::get($this->user_token_prefix.$uid);
    }
    //存储用户TOKEN
    private function saveUserToken($uid, $token)
    {
        Cache::set($this->user_token_prefix.$uid, $token ,3600);
    }
}
