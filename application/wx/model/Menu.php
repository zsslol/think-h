<?php

namespace app\wx\model;

use think\Model;

class Menu extends Model
{
    protected $table = 'wx_wxmenu';

    protected $type = [
        'view' => 'URL跳转',
        'click' => '事件推送'
    ];

    public function getType()
    {
        return $this->type;
    }

    /**
     * 获取适用于列表输出的数据
     * @param $form_type 是否转成用于SELECT输出的数据
     * @return array
     */
    public function getListData($form_type = false)
    {
        $data_list = $this->order('pid asc, id asc')->select()->toArray();
        if(count($data_list) == 0)return [0 => '顶级目录'];
        $tree = new \common\Tree();
        $data_list = $tree->toFormatTree($data_list, 'name', 'id', 'pid',0, false);
        if($form_type){
            $datas = [0 => '顶级分类'];
            foreach($data_list as $data){
                $datas[$data['id']] = $data['title_show'];
            }
            return $datas;
        }
        return $data_list;
    }
}
