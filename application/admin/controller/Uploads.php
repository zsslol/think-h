<?php

namespace app\Admin\controller;

use think\Request;

class Uploads extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('角色列表')  // 设置页面标题
            ->setTableInfo('admin_uploads')
            ->addTopButton('resume')   // 添加启用按钮
            ->addTopButton('forbid')   // 添加禁用按钮
            ->addTopButton('delete')   // 添加删除按钮
            ->addTableColumn('id', 'ID', '', '', 'center')
            ->addTableColumn('name', '文件名称', '', '', 'left')
            ->addTableColumn('type', '文件类型、', '', '', 'left', true)
            ->addTableColumn('save_path', '存储目录', '', '', 'left')
            ->addTableColumn('save_name', '存储名称', '', '', 'left')
            ->addTableColumn('save_type', '存储类型', '', '', 'center')
            ->addTableColumn('size', '大小(字节)', '', '', 'right', true)
            ->addTableColumn('create_time', '上传时间', 'datetime', '', 'center', true)
            ->addTableColumn('status', '状态', 'status', '', 'center', true)
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete')// 添加删除按钮
            ->show();
    }

    /**
     * 上传
     */
    public function uploadFile()
    {
        $model = new \app\admin\model\Upload();

        $check_file = input('post.check_file');
        if($check_file == 1){
            $md5 = input('post.md5_val');
            if(empty($md5)){
                $this->return_error('参数不完整，请刷新重试');
            }
            $file_info = $model->checkFieldMd5($md5);
            if($file_info == false){
                $this->return_success('无此文件记录', ['id' => 0]);
            }
            $this->return_success('文件已存在', $file_info);
        } else {
            $result = $model->upload();
            if($result == false)$this->ajaxReturnData($model->getError());
            $this->ajaxReturnData('上传成功', $result, 'success');
        }

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
