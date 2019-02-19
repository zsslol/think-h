<?php

namespace app\article\model;

use think\Model;

class Category extends Model
{
    //表名
    protected $table = 'cms_category';

    //分类类型
    public $type = [
        'channel' => '栏目',
        'list' => '列表'
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
    public function getListData($form_type = false, $select_data = false)
    {
        $data_list = $this->order('weigh asc,pid asc,id asc')->select()->toArray();
        if(count($data_list) == 0)return [0 => '顶级分类'];
        $tree = new \common\Tree();
        $data_list = $tree->toFormatTree($data_list, 'name', 'id', 'pid',0, false);
        if($form_type){
            if($select_data){
                $datas = [
                    0 => [
                        'title' => '顶级分类',
                        'disabled' => ''
                        ]
                ];
                foreach ($data_list as $data) {
                    $option['disabled'] = $data['type'] == $select_data ? '' : 'disabled';
                    if($data['status'] != 1)$option['disabled'] = 'disabled';
                    $option['title'] = $data['title_show'];
                    $datas[$data['id']] = $option;
                }
            } else {
                $datas = [0 => '顶级分类'];
                foreach ($data_list as $data) {
                    $datas[$data['id']] = $data['title_show'];
                }
            }
            return $datas;
        }
        return $data_list;
    }
}
