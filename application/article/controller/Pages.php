<?php

namespace app\article\controller;

use app\admin\controller\Common;
use app\article\model\Page;

class Pages extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('单页列表')  // 设置页面标题
                ->setTableInfo('cms_page')
                ->addSearchColumn('name', 'text', '名称', '单页名称')
                ->addSearchColumn('mobile', 'text', '标题', '文章标题-模糊匹配', '', 'like')
                ->addSearchColumn('status', 'select', '状态', '', ['1' => '启用', 0=>'禁用'])
                ->addTopButton('create')   // 添加启用按钮
                ->addTopButton('resume')   // 添加启用按钮
                ->addTopButton('forbid')   // 添加禁用按钮
                ->addTopButton('delete')   // 添加删除按钮
                ->addTableColumn('id', 'ID', '', '', 'right', true)
                ->addTableColumn('title', '标题', '', '', 'left')
                ->addTableColumn('name', '单页名称', '', '', 'left')
                ->addTableColumn('views', '点击量', '', '', 'right', true)
                ->addTableColumn('status', '状态', 'status', '', 'center', true)
                ->addTableColumn('create_time', '发布时间', 'datetime', '', 'center', true)
                ->addTableColumn('update_time', '最后更新时间', 'datetime', '', 'center', true)
                ->addTableColumn('right_button', '操作','right_button', '','center')
                ->addRightButton('edit')// 添加编辑按钮
                ->addRightButton('forbid') // 添加禁用/启用按钮
                ->addRightButton('delete')// 添加删除按钮
                ->show();
    }

    /**
     * 创建表单
     */
    private function getFormData($data = [])
    {

        if(count($data) == 0){
            $data = ['status' => 1,  'weigh' => 0, 'create_time' => date('Y-m-d H:i:s')];
        }

        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('name', 'text', '名称', '单页名称,建议英文字母组成（唯一）,20字内', true, '', '', 'min-length="1" max-length="20"')
            ->addFormItem('title', 'text', '标题', '文章标题，50字以内', true, '', '', 'min-length="1" max-length="50"')
            ->addFormItem('author', 'text', '作者', '作者，10字以内，用于网站展示', false, '', '')
            ->addFormItem('content', 'editor', '内容', '文章内容', true, '', '')
            ->addFormItem('image', 'picture', '封面', '文章封面', false)
            ->addFormItem('attachfile', 'file', '附件', '文章附件', false,'','')
            ->addFormItem('keywords', 'text', '关键字', '文章关键字', false)
            ->addFormItem('description', 'textarea', '描述', '文章描述', false)
            ->addFormItem('create_time', 'datetime', '发布时间', '文章发布时间', true, '', '','YYYY-MM-DD hh:mm:ss')
            ->addFormItem('status', 'radio', '状态', '文章状态', true,[0 => '禁用', 1 => '启用'])
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
            $validate = new \app\Article\validate\Page();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new Page($data);
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
            $validate = new \app\Article\validate\Page();
            if(!$validate->scene('edit', ['username', 'nickname', 'mobile','email'])->check($data))$this->return_error($validate->getError());

            $model = new Page();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);

            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new Page();
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

        $result = Page::destroy($ids);
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

        $model = new Page();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }
}
