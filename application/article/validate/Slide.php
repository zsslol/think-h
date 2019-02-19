<?php

namespace app\article\validate;

use think\Validate;

class Slide extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'title'  => 'require|max:50',
        'cover'  => 'require',
        'sort' => 'require',
        'status' => 'require'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'title.require' => '请填写标题',
        'title.max' => '标题最长为50字',
        'cover.require' => '请上传图片',
        'sort.require' => '请填写权重',
        'status.require'        => '请选择状态'
    ];
}
