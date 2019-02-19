<?php

namespace app\article\model;

use think\Model;

class Slide extends Model
{
    //
    protected $table = 'cms_slide';

    protected $auto = ['create_time', 'admin_id'];

    protected function setCreateTimeAttr($value)
    {
        return strtotime($value);
    }

    protected function setAdminIdAttr($value)
    {
        return session('user_info.id');
    }

    public function getSlideList($limit = 3)
    {
        $where['status'] = 1;
        $list = $this->where($where)->field('title,cover,url')->order('sort asc, id desc')->select();
        if($list == false)return [];

        foreach ($list as &$vo){
            $vo['url'] = empty($vo['url']) ? 'javascript:;' : $vo['url'];
            $vo['cover'] = getCover($vo['cover']);
        }
        return $list;
    }
}
