<?php
/**
 * Created by PhpStorm.
 * User: Zhouss
 * Date: 2018/12/26
 * Time: 13:23
 */
namespace app\admin\controller;

use app\Admin\model\User;
use think\Controller;
use think\facade\Session;
use think\Validate;

class Common extends Controller{
    /**
     * 初始化
     */
    protected function initialize()
    {
        //检查权限
        $auth_group_model = new User();
        $check_result = $auth_group_model->checkUserAuth();
        if($check_result !== true){
            $this->return_error($auth_group_model->getError(), ['check_status' => $check_result]);
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        if($this->request->isAjax()){
            $data = input('post.');

            $rule = [
                'username' => 'require|min:6|max:20',
                'password' => 'require|min:6|max:32'
            ];
            $msg = [
                'username.require' => '请填写登录账号',
                'username.min' => '登录账号应在6-20位',
                'username.max' => '登录账号应在6-20位',
                'password.require' => '请填写密码',
                'password.min' => '密码长度应在6-32位',
                'password.max' => '密码长度应在6-32位'
            ];
            $validate = Validate::make($rule,$msg);
            $result = $validate->check($data);

            if($result == false)$this->return_error($validate->getError());

            $user_model = new \app\Admin\model\User();
            $login_result = $user_model->login($data['username'], $data['password']);

            if($login_result == false)$this->return_error($user_model->getError());
            $this->return_success('登录成功', ['url' => url('Admin/Index/Index')]);
        } else {
            return $this->fetch('login');
        }
    }

    /**
     * 登出
     */
    public function logout()
    {
        Session::delete('user_info');
        Session::delete('user_auth');
        Session::delete('is_login');
        $this->return_success('登出成功', ['url' => url('Admin/Common/login')]);
    }

    /**
     * 正确结果的快捷操作方法
     */
    public function return_success($msg, $data = [])
    {
        if($this->request->isAjax()){
            $this->ajaxReturnData($msg, $data);
        } else {
            $this->showReturnHtml($msg, $data);
        }
    }

    /**
     * 错误结果的快捷操作方法
     */
    public function return_error($msg, $data = [])
    {
        if($this->request->isAjax()){
            $this->ajaxReturnData($msg, $data, 'error');
        } else {
            $this->showReturnHtml($msg, $data, 'error');
        }
    }

    /**
     * 页面显示处理结果
     */
    public function showReturnHtml($msg, $data=[],$type = 'success')
    {
        $info['title'] = '无权限访问';
        $info['msg'] = $msg;

        $js = '<script>';
        $js .= " if (window.frames.length != parent.frames.length) {";
        if($type == 'success'){
            $js .= "parent.toastr.success('{$msg}');";
        } else {
            $js .= "parent.toastr.error('{$msg}');";
        }
        $js .= 'var layer_index = parent.layer.getFrameIndex(window.name);parent.layer.close(layer_index);';
        $js .= '} else {alert("'.$msg.'");';
        if($data['check_status'] == 'no_login') {
            $js .= 'location.href="' . url('Admin/Common/login') . '"';
        } else {
            $js .= 'location.href="' . url('Admin/Index/index') . '"';
        }
        $js .= '}</script>';


        $this->assign('info', $info);
        $this->assign('js', $js);
        exit($this->fetch('Admin@Public/success_error'));
    }

    /**
     * 返回处理结果
     */
    public function ajaxReturnData($msg, $data=[],$type = 'success')
    {
        $return['msg'] = $msg;
        $return['data'] = $data;
        $return['code'] = $type == 'success' ? 200 : 0;
        exit(json_encode($return));
    }

    /**
     * 设置状态
     */
    public function setStatus(){
        return self::ajaxReturnData('操作成功');
    }

}