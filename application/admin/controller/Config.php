<?php

namespace app\Admin\controller;

class Config extends Common
{
    /**
     * 设置页
     */
    public function config(){
        $model = new \app\Admin\model\Config();
        if($this->request->isAjax()){
            $result = $model->saveConfig(input('param.'));
            if($result)$this->return_success('保存成功');
            $this->return_error($model->getError());
        } else {
            $item_list = $model->getItemList();
            if ($item_list == false) $this->return_error('暂无可配置项');
            $form_builder = new \builder\FormBuilder();
            return $form_builder->setPostUrl(url('config'))
                                ->setMetaTitle('系统设置')
                                ->display_group_form($item_list);
        }
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $config_group = \app\Admin\model\Config::getConfigGroup();
        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('配置项列表')  // 设置页面标题
            ->addSearchColumn('name', 'text', '名称', '参数名称-模糊匹配', '', 'like')
            ->addSearchColumn('group', 'select', '所属分组', '配置项所属分组', $config_group)
            ->setTableInfo('admin_config_item')
            ->addTopButton('create')
            ->addTopButton('resume')
            ->addTopButton('forbid')
            ->addTopButton('delete')
            ->addTableColumn('id', 'ID', '', '', 'center', true)
            ->addTableColumn('name', '名称', '', '', 'left')
            ->addTableColumn('title', '标题', '', '', 'left')
            ->addTableColumn('group', '分组', 'defined_status', $config_group, 'center', true)
            ->addTableColumn('sort', '排序', '', '', 'right', true)
            ->addTableColumn('status', '状态', 'status', '', 'center', true)
            ->addTableColumn('update_time', '最后更新时间', 'datetime', '', 'center', true)
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete')
            ->show();
    }
    /**
     * 创建表单
     */
    private function getFormData($data = [])
    {
        if(count($data) == 0){
            $data = ['status' => 1, 'sort' => 0, 'type' => 'text'];
        }

        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('group', 'select', '分组', '所属分组', true, \app\Admin\model\Config::getConfigGroup())
            ->addFormItem('title', 'text', '标题', '配置项标题', true)
            ->addFormItem('tip', 'text', '提示', '配置项标题', false)
            ->addFormItem('name', 'text', '名称', '配置项名称', true)
            ->addFormItem('type', 'text', '类型', 'formBuilder内类型', true)
            ->addFormItem('options', 'textarea', '选项', '如果是单选、多选、下拉等类型 需要配置该项。下标:值 每行一个', false)
            ->addFormItem('value', 'text', '配置值', '配置值', false)
            ->addFormItem('sort', 'text', '排序', '配置项排序', true, '', '', 'data-type="number"')
            ->addFormItem('status', 'radio', '状态', '', true,[0 => '禁用', 1 => '启用'])
            ->setFormData($data);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        if($this->request->isAjax()){
            $data = input('post.');
            $validate = new \app\Admin\validate\Config();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new \app\Admin\model\Config($data);
            $result = $model->save();
            if($result){
                $this->return_success('新增成功');
            } else {
                $this->return_error('新增失败');
            }
        } else {
            $form_builder = self::getFormData();
            return $form_builder->setPostUrl(url('create'))
                ->setMetaTitle('新增')
                ->display();
        }
    }

    /**
     * 修改
     * @param $id
     * @return string
     */
    public function edit($id)
    {
        if($this->request->isAjax()){
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');

            $data = input('post.');
            $validate = new \app\Admin\validate\Config();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new \app\Admin\model\Config();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);
            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new \app\Admin\model\Config();
            $data = $model->find($id);
            if(!$data)$this->return_error('参数错误，请重试');

            $form_builder = self::getFormData($data->toArray());
            return $form_builder->setPostUrl(url('edit'))
                ->setMetaTitle('编辑')
                ->display();
        }
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        $ids = $this->request->param('ids','', 'intval');
        if(empty($ids))$this->return_error('参数错误，请刷新重试');

        $model = new \app\Admin\model\Config();
        $result = $model->destroy($ids);
        if($result){
            $this->return_success('删除成功');
        } else {
            $this->return_error('删除失败');
        }
    }

    /**
     * 禁启用
     */
    public function setstatus()
    {
        $ids = $this->request->param('ids','', 'intval');
        $status = $this->request->param('status','forbid');
        if(empty($ids))$this->return_error('参数错误，请刷新重试');
        $save['status'] = $status == 'forbid' ? 0 : 1;

        $model = new \app\Admin\model\Config();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }
}
