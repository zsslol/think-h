<?php
/**
 * Created by PhpStorm.
 * User: Zhouss
 * Date: 2018/12/26
 * Time: 13:23
 */
namespace app\admin\controller;

use think\Controller;

class Common extends Controller{
    /**
     * 初始化
     */
    protected function initialize()
    {

    }

    /**
     * 返回处理结果
     */
    public function ajaxReturnData($msg, $data = [],$type = 'success')
    {
        $return['msg'] = $msg;
        $return['data'] = $data;
        $return['code'] = $type == 'success' ? 200 : 0;
        return $return;
    }

    /**
     * 新增
     */
    public function create()
    {

    }

    /**
     * 设置状态
     */
    public function setStatus(){
        return self::ajaxReturnData('操作成功');
    }

}