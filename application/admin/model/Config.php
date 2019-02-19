<?php

namespace app\Admin\model;

use think\Db;
use think\Model;

class Config extends Model
{
    protected $table = 'admin_config_item';


    /**
     * 获取配置项分组
     */
    public static function getConfigGroup()
    {
        $config_group = explode("\n",getAdminConfig('system.CONFIG_GROUP'));

        $config_list = [];
        foreach($config_group as $config){
            $config_array = explode(":", $config);
            $config_list[$config_array[0]] = $config_array[1];
        }
        return $config_list;
    }

    /**
     * 存储设置
     */
    public function saveConfig($data)
    {
        $where['status'] = 1;
        $list = $this->where($where)->field('name, title, value, tip, options, group, type')->order('sort asc, id asc')->select();
        if($list == false){
            $this->error = '暂无可用配置项，无法更新';
            return false;
        }
        $config_list = [];
        $save_data = [];
        foreach($list as $item){
            $config_list[$item['group']][$item['name']] = !empty($data[$item['name']]) ? $data[$item['name']] : $item['value'];
        }

        foreach($config_list as $group_key => $group_config){
            $save_data[] = ['group' => $group_key, 'config' => json_encode($group_config)];
        }

        $delete_result = Db::name('admin_config')->delete(true);
        if($delete_result === false){
            $this->error = '网络错误';
            return false;
        }
        $save_result = Db::name('admin_config')->insertAll($save_data);
        if($save_result == false){
            $this->error = '网络错误';
            return false;
        }
        $set_result = $this->setAdminConfig($config_list);
        if($save_result == false){
            $this->error = '缓存文件保存失败，请联系管理员';
            return false;
        }

        return true;
    }

    /**
     * 获取库里的设置
     */
    public static function getAdminConfig()
    {
        $config_list = Db::name('admin_config')->select();
        if($config_list ==false)exit('系统设置项没有生成，请联系管理员');
        foreach($config_list as $config_item){
            $config[$config_item['group']] = json_decode($config_item['config'], true);
        }
        return $config;
    }

    /**
     * 存储设置
     */
    public static function setAdminConfig($config = null)
    {
        if($config == null)$config = self::getAdminConfig();
        return \think\facade\Cache::tag('AdminConfig')->set('AdminConfig',$config, 0);
    }

    /**
     * 获取用于生成表单的字段列表
     */
    public function getItemList()
    {
        $where['status'] = 1;
        $list = $this->where($where)->field('name, title, value, tip, options, group, type')->order('sort asc, id asc')->select();
        if($list == false) return false;

        $config = self::getAdminConfig();

        $group_item_list = [];
        foreach($list as $item){
            $item['value'] = isset($config[$item['group']][$item['name']]) ? $config[$item['group']][$item['name']] : $item['value'];
            $item['must'] = true;
            $item['extra_class'] = '';
            $item['extra_attr'] = '';

            if(empty($item['options'])){
                $item['options'] = [];
            } else {
                $options = explode("\n", $item['options']);
                $option_list = [];
                foreach($options as $option){
                    $key_val = explode(":", $option);
                    $option_list[$key_val[0]] = $key_val[1];
                }
                $item['options'] = $option_list;
            }

            $group_item_list[$item['group']]['item_list'][] = $item;
        }
        $config_group = self::getConfigGroup();
        foreach($group_item_list as $group_ley => $group_item){
            $group_item_list[$group_ley]['title'] = $config_group[$group_ley];
        }

        $return = [];
        foreach($config_group as $key => $vo){
            $return[$key] = $group_item_list[$key];
        }

        return $return;
    }
}
