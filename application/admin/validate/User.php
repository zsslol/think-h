<?php

namespace app\Admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'username' => 'require|min:6|max:20|unique:admin_user',
	    'nickname' => 'require|min:2|max:20|unique:admin_user',
	    'password' => 'require|min:6|max:32',
	    'mobile' => 'require|mobile|unique:admin_user',
	    'email' => 'email|unique:admin_user',
	    'avatar' => 'number',
	    'status' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'username.require' => '请填写用户名',
        'username.min' => '用户名格式：6-20位，唯一',
        'username.max' => '用户名格式：6-20位，唯一',
        'username.unique' => '用户名已存在',
        'nickname.require' => '请填写用户昵称',
        'nickname.min' => '用户昵称格式：2-20位，唯一',
        'nickname.max' => '用户昵称格式：2-20位，唯一',
        'nickname.unique' => '用户昵称已存在',
        'password.min' => '密码长度应在6-32位',
        'password.max' => '密码长度应在6-32位',
        'mobile.require' => '请填写用户手机号',
        'mobile.unique' => '手机号已存在',
        'email.email' => '邮箱地址不正确',
        'email.unique' => '邮箱已存在',
        'avatar.number' => '头像信息不正确，请重试',
        'status.require' => '请选择状态'
    ];

    // edit 验证场景定义
    public function sceneEdit()
    {
        return $this->only(['password'])
            ->remove('password', 'require');
    }
}
