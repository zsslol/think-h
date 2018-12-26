<?php

namespace app\admin\controller;

use app\admin\model\Permissions;

class Roles extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $listBuilder = new \builder\ListBuilder();
        $listBuilder->setMetaTitle('设备列表')  // 设置页面标题
            ->setCanCheckbox(false)
            ->setPageSize('"All"')
            ->addTopButton('create')   // 添加新增按钮
            ->addTableColumn('id', 'ID', '', '', 'center')
            ->addTableColumn('title_show', '菜单名称', '', '', 'left')
            ->addTableColumn('level', '级别', '', '', 'center')
            ->addTableColumn('sort_order', '排序', '', '', 'right')
            ->addTableColumn('status', '状态', 'status', '', 'center')
            ->addTableColumn('updated_at', '最后更新时间', '', '', 'center')
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete');// 添加删除按钮

        if($this->request->isAjax()){
            $Permissions = new Permissions();
            $data_list = $Permissions->getListData();
            $return['rows'] = $data_list;
            $return['total'] = count($data_list);
            return $listBuilder->getListReturn($return);
        } else {
            return $listBuilder->display();
        }
    }

    /**
     * 创建表单
     */
    private function getFormData()
    {
        $Permissions = new Permissions();
        $default = ['status' => 1, 'sort_order' => 0, 'parent_id' => 0];
        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('parent_id', 'select', '上级目录', '上级目录', true, $Permissions->getListData(true))
            ->addFormItem('name', 'text', 'URL', '格式：模块/控制器/操作', true)
            ->addFormItem('sort_order', 'text', '排序', '', true,'','','data-type="number"')
            ->addFormItem('status', 'radio', '状态', '', true,[0 => '禁用', 1 => '启用'])
            ->setFormData($default);
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        if($this->request->isAjax()){
            parent::create();
        } else {
            $form_builder = self::getFormData();
            return $form_builder->setPostUrl(url('create'))
                                ->setMetaTitle('新增')
                                ->display();
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $owner = new Roles();
        $owner->name         = $this->request->param('name');
        $owner->description  = $this->request->param('description'); // 可选
        $owner->created_at   = date('Y-m-d H:i:s');
        $owner->updated_at   = date('Y-m-d H:i:s');
        $owner->save();
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
        $owner = new Roles();
        $owner->id         = $id;
        $owner->name         = $this->request->param('name');
        $owner->description  = $this->request->param('description'); // 可选
        $owner->created_at   = date('Y-m-d H:i:s');
        $owner->updated_at   = date('Y-m-d H:i:s');
        $owner->save();
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
