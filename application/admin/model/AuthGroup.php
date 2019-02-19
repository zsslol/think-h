<?php

namespace app\Admin\model;

use think\facade\Session;
use think\Model;

class AuthGroup extends Model
{
    protected $table = 'admin_auth_group';

    /**
     * 获取角色权限
     */
    public function getAuthGroupRule($group_id){
        $auth_group_info = $this->where('id', $group_id)->find();
        if($auth_group_info == false)return false;
        if($auth_group_info['status'] != 1)return false;

        $where['status']=1;
        if($auth_group_info['rules'] == '*'){

        } else {
            $rule_ids = explode(',', $auth_group_info['rules']);
            if(count($rule_ids) < 1)return false;
            $where['id']=$rule_ids;
        }

        //菜单列表
        $auth_rule_model = new \app\admin\model\AuthRule();
        $rule_list = $auth_rule_model->field('id,pid,name,title')->where($where)->order('pid asc, id asc')->select()->toArray();
        if($rule_list == false)return false;
        $rules = [];
        foreach ($rule_list as $rule){
            $rule['name_lower'] = strtolower($rule['name']);
            $rules[$rule['name_lower']] = $rule;
        }

        //树菜单
        $tree = new \common\Tree();
        $rule_tree_list = $tree->list_to_tree($rule_list, 'id', 'pid', 'child',0, false);
        $user_auth = ['rule_list' => $rules, 'rule_tree_list' => $rule_tree_list];
        \think\facade\Session::set('user_auth', $user_auth);
        return true;
    }

    /**
     * 获取适用于列表输出的数据
     * @param $form_type 是否转成用于SELECT输出的数据
     * @return array
     */
    public function getListData($form_type = false)
    {
        $data_list = $this->order('pid asc, id asc')->select()->toArray();
        if(count($data_list) == 0)return [0 => '顶级分类'];
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
