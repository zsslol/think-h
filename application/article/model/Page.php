<?php

namespace app\article\model;

use think\Model;

class Page extends Model
{
    //
    protected $table = 'cms_page';

    protected $auto = ['create_time', 'admin_id'];

    protected function setCreateTimeAttr($value)
    {
        return strtotime($value);
    }

    protected function setAdminIdAttr($value)
    {
        return session('user_info.id');
    }

    //获取单页内容
    public function getPage($name)
    {
        $where['name'] = $name;
        $where['status'] = 1;
        $content = $this->where($where)->field('id,status,admin_id', true)->find();
        if($content == false)return false;
        return $content->toArray();
    }
}
