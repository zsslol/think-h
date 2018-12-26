<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 * Date: 2018/12/16
 * Time: 20:05
 */
namespace app\admin\model;

use Jackchow\Rbac\RbacPermission;

class Permissions extends RbacPermission
{
    public function buildTrees($data, $pId)
    {
        $treenodes = array();
        foreach($data as $k => $v)
        {
            if($v['parent_id'] == $pId)
            {
                $v['_child'] = $this->buildTrees($data, $v['id']);
                $treenodes[] = $v;
            }
        }
        return $treenodes;
    }

    /**
     * 获取适用于列表输出的数据
     * @param $form_type 是否转成用于SELECT输出的数据
     * @return array
     */
    public function getListData($form_type = false)
    {
        $data_list = $this->order('sort_order asc,level_id asc')->select()->toArray();
        if(count($data_list) == 0)return [];
        $tree = new \common\Tree();
        $data_list = $tree->toFormatTree($data_list, 'description', 'id', 'parent_id',0, false);
        if($form_type){
            $datas = [0 => '顶级目录'];
            foreach($data_list as $data){
                $datas[$data['id']] = $data['title_show'];
            }
            return $datas;
        }
        return $data_list;
    }
}