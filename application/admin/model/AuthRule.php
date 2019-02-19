<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 */
namespace app\admin\model;
use think\Model;

class AuthRule extends Model
{

    protected $table = 'admin_auth_rule';

    /**
     * 返回完整权限名称
     */
    public function getAuthRuleTitle($id)
    {
        $auth_rule_info = $this->field('pid,title')->where('id', $id)->find();
        $title = $auth_rule_info['title'];
        while ($auth_rule_info['pid'] > 0){
            $auth_rule_info = $this->field('pid,title')->where('id', $auth_rule_info['pid'])->find();
            $title = $auth_rule_info['title'].'-'.$title;
        }
        return $title;
    }

    /**
     * 获取适用于列表输出的数据
     * @param $form_type 是否转成用于SELECT输出的数据
     * @return array
     */
    public function getListData($form_type = false)
    {
        $data_list = $this->order('weigh asc, id asc')->select()->toArray();
        if(count($data_list) == 0)return [0 => '顶级目录'];
        $tree = new \common\Tree();
        $data_list = $tree->toFormatTree($data_list, 'title', 'id', 'pid',0, false);
        if($form_type === true){
            $datas = [0 => '顶级目录'];
            foreach($data_list as $data){
                $datas[$data['id']] = $data['title_show'];
            }
            return $datas;
        }
        if($form_type == 'tree')return $tree->list_to_tree($data_list, 'id', 'pid', 'children', 0, false);
        return $data_list;
    }
}