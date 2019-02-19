<?php

namespace app\Admin\validate;

use think\Validate;

class AuthGroup extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'pid'  => 'require',
        'name'  => 'require|max:10',
        'rules'  => 'require',
        'status' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'pid.require' => '请选择上级角色',
        'name.require' => '请填写角色名称',
        'name.max' => '角色名称最长为十位',
        'rules.require' => '请选择角色权限',
        'status.require'        => '请选择状态',
    ];
}
