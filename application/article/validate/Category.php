<?php

namespace app\article\validate;

use think\Validate;

class Category extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'type'  => 'require',
        'pid'  => 'require',
        'name'  => 'require|max:10|unique:cms_category',
        'diyname'  => 'require|max:10|unique:cms_category',
        'weigh'  => 'require',
        'status' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'type.require' => '请选择类型',
        'pid.require' => '请选择上级角色',
        'name.require' => '请填写名称',
        'name.max' => '名称最长为十位',
        'name.unique' => '名称应唯一',
        'diyname.require' => '请填写名称',
        'diyname.max' => '别称最长为十位',
        'diyname.unique' => '别称应唯一',
        'weigh.require' => '请填写权重',
        'status.require'        => '请选择状态'
    ];
}
