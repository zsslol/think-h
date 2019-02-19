<?php

namespace app\article\controller;

use app\admin\controller\Common;

class Categorys extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $Category = new \app\article\model\Category();
        $listBuilder = new \builder\ListBuilder();
        $listBuilder->setMetaTitle('分类列表')  // 设置页面标题
        ->setCanCheckbox(false)
            ->setPageSize('"All"')
            ->addTopButton('create')   // 添加新增按钮
            ->addTableColumn('id', 'ID', '', '', 'center')
            ->addTableColumn('title_show', '分类名称', '', '', 'left')
            ->addTableColumn('diyname', '分类别称', '', '', 'left')
            ->addTableColumn('type', '分类类型', 'defined_status', $Category->getType(), 'center')
            ->addTableColumn('status', '状态', 'status', '', 'center')
            ->addTableColumn('update_time', '最后更新时间', '', '', 'center')
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete');// 添加删除按钮

        if($this->request->isAjax()){
            $auth_rule = new \app\article\model\Category();
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
        $Category = new \app\article\model\Category();
        if(count($data) == 0){
            $data = ['status' => 1, 'pid' => 0, 'weigh' => 0, 'type' => 'channel'];
        }
        $type_tip = '
            <strong>栏目</strong>: 栏目类型下不可以发布文章,但可以添加子栏目、列表、链接<br>
            <strong>列表</strong>: 列表类型下可以发布文章,但不能添加子栏目<br>
        ';
        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('type', 'radio', '类型', $type_tip, true, $Category->getType())
            ->addFormItem('pid', 'select', '上级分类', '上级分类', true, $Category->getListData(true, 'channel'))
            ->addFormItem('name', 'text', '名称', '分类名称', true)
            ->addFormItem('diyname', 'text', '别称', '分类别称', true)
            ->addFormItem('image', 'picture', '图片', '图片', false)
            ->addFormItem('keywords', 'text', '关键字', '关键字', false)
            ->addFormItem('description', 'textarea', '描述', '描述', false)
            ->addFormItem('weigh', 'num', '权重', '分类权重，升序', true)
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
            $validate = new \app\Article\validate\Category();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new \app\Article\model\Category($data);
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
            if($data['id'] == $data['pid'])$this->return_error('上级分类不能选择自己');
            $validate = new \app\Article\validate\Category();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new \app\article\model\Category();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);
            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new \app\article\model\Category();
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

        $model = new \app\article\model\Category();
        $child_count = $model->where('pid', $ids)->count();
        if($child_count > 0)$this->return_error('请先删除掉该目录下子目录');

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

        $model = new \app\article\model\Category();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }
}
