<?php

namespace app\article\validate;

use think\Validate;

class Article extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'category_id'  => 'require',
        'title'  => 'require|max:50',
        'content'  => 'require',
        'create_time'  => 'require',
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
        'category_id.require' => '请选择文章分类',
        'title.require' => '请填写文章标题',
        'title.max' => '名称最长为50字',
        'content.require' => '请填写文章内容',
        'create_time.require' => '请选择文章发布时间',
        'weigh.require' => '请填写权重',
        'status.require'        => '请选择状态'
    ];
}
