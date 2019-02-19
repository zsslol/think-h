<?php

namespace app\admin\controller;

use app\admin\model\AuthRule;

class AuthRules extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $listBuilder = new \builder\ListBuilder();
        $listBuilder->setMetaTitle('菜单列表')  // 设置页面标题
            ->setCanCheckbox(false)
            ->setPageSize('"All"')
            ->addTopButton('create')   // 添加新增按钮
            ->addTableColumn('id', 'ID', '', '', 'center')
            ->addTableColumn('title_show', '菜单名称', '', '', 'left')
            ->addTableColumn('weigh', '排序', '', '', 'right')
            ->addTableColumn('status', '状态', 'status', '', 'center')
            ->addTableColumn('update_time', '最后更新时间', '', '', 'center')
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete');// 添加删除按钮

        if($this->request->isAjax()){
            $auth_rule = new AuthRule();
            $data_list = $auth_rule->getListData();
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
    private function getFormData($data = [])
    {
        $AuthRule = new AuthRule();
        if(count($data) == 0)$data = ['status' => 1, 'weigh' => 0, 'pid' => 0];

        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('pid', 'select', '上级目录', '上级目录', true, $AuthRule->getListData(true))
            ->addFormItem('title', 'text', '名称', '菜单名称', true)
            ->addFormItem('name', 'text', 'URL', '格式：模块/控制器/操作', true)
            ->addFormItem('weigh', 'text', '排序', '', true,'','','data-type="number"')
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
            $validate = new \app\Admin\validate\AuthRule();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new AuthRule($data);
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
            $validate = new \app\Admin\validate\AuthRule();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new AuthRule();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);
            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new AuthRule();
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

        $child_count = AuthRule::where('pid', $ids)->count();
        if($child_count > 0)$this->return_error('请先删除掉该目录下子目录');

        $result = AuthRule::destroy($ids);
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

        $model = new AuthRule();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }

}
