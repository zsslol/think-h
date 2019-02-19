<?php

namespace app\Admin\validate;

use think\Validate;

class AuthRule extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'pid'  => 'require',
        'name'  => 'require',
        'title'   => 'require|max:10',
        'weigh' => 'number|require',
        'status' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'pid.require' => '请选择上级目录',
        'name.require' => '请填写URL',
        'title.require'     => '请填写名称',
        'weigh.number'   => '排序必须是数字',
        'weigh.require'  => '请填写排序',
        'status.require'        => '请选择状态',
    ];
}
