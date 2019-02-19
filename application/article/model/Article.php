<?php

namespace app\article\model;

use think\Model;

class Article extends Model
{
    //
    protected $table = 'cms_article';

    protected $auto = ['create_time', 'admin_id'];

    protected function setCreateTimeAttr($value)
    {
        return strtotime($value);
    }

    protected function setAdminIdAttr($value)
    {
        return session('user_info.id');
    }



    //关联查询
    public function Category()
    {
        return $this->hasOne('Category', 'id','category_id')->bind('name,diyname');
    }

    public function getDetail($id, $detail_url = 'Index/Index/detail')
    {
        $where[$this->table.'.id'] = $id;
        $where[$this->table.'.status'] = 1;
        $info = $this->where($where)->withJoin(['Category' => ['name', 'diyname']])->find();
        if($info == false)return false;

        $previous_where[] = [$this->table.'.id', '<', $id];
        $previous_where[] = [$this->table.'.status', '=',1];
        $previous = $this->where($previous_where)->field('id,title')->order('id desc')->find();
        if($previous){
            $info['previous'] = [
                'href' => url($detail_url, ['id' => $previous['id']]),
                'title' => $previous['title']
            ];
        } else {
            $info['previous'] = [
                'href' => 'javascript:;',
                'title' => '没有了'
            ];
        }
        $next_where[] = [$this->table.'.id', '>', $id];
        $next_where[] = [$this->table.'.status', '=',1];
        $next = $this->where($next_where)->field('id,title')->order('id asc')->find();
        if($next){
            $info['next'] = [
                'href' => url($detail_url, ['id' => $next['id']]),
                'title' => $next['title']
            ];
        } else {
            $info['next'] = [
                'href' => 'javascript:;',
                'title' => '没有了'
            ];
        }

        return $info->toArray();
    }

    /**
     * 获取文章分类列表
     * @param $cid
     * @param int $page
     * @param int $limit
     * @param array $where
     * @param bool $pic
     */
    public function getArticleList($cid, $limit = 10, $where = [], $ext_field = '',$fields = 'id,author,category_id,title,image,views,create_time')
    {
        $default_where[$this->table.'.category_id'] = $cid;
        $default_where[$this->table.'.status'] = 1;

        if(!empty($ext_field))$fields .= ','.$ext_field;
        $list = $this
            ->where( array_merge($default_where, $where) )
            ->field($fields)
            ->withJoin(['Category' => ['name', 'diyname']])
            ->order("$this->table.weigh asc, $this->table.id desc")
            ->paginate($limit);
        if($list == false)return ['list' => [], 'page' => ''];
        if($list[0]['image']){
            foreach($list as &$vo){
                if(intval($vo['image']) > 0) {
                    $vo['image'] = getCover($vo['image']);
                } else {
                    $vo['image'] = '';
                }
            }
        }

        $data = $list->toArray();
        $data = $list->toArray();
        return ['list' => $data['data'], 'page' => $list->render()];
    }
}
