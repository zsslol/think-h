<?php

namespace app\article\controller;

use app\admin\controller\Common;
use app\article\model\Slide;

class Slides extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('幻灯切换')  // 设置页面标题
                ->setTableInfo('cms_slide')
                ->addSearchColumn('name', 'text', '名称', '标题')
                ->addSearchColumn('status', 'select', '状态', '', ['1' => '启用', 0=>'禁用'])
                ->addTopButton('create')   // 添加启用按钮
                ->addTopButton('resume')   // 添加启用按钮
                ->addTopButton('forbid')   // 添加禁用按钮
                ->addTopButton('delete')   // 添加删除按钮
                ->addTableColumn('id', 'ID', '', '', 'right', true)
                ->addTableColumn('title', '标题', '', '', 'left')
                ->addTableColumn('cover', '图片', 'picture', '', 'center')
                ->addTableColumn('status', '状态', 'status', '', 'center', true)
                ->addTableColumn('create_time', '创建时间', 'datetime', '', 'center', true)
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
            $data = ['status' => 1,  'sort' => 0];
        }

        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('title', 'text', '标题', '', true)
            ->addFormItem('url', 'text', '链接', '', false)
            ->addFormItem('cover', 'picture', '图片', '', true)
            ->addFormItem('sort', 'num', '权重', '升序排列', true)
            ->addFormItem('status', 'radio', '状态', '状态', true,[0 => '禁用', 1 => '启用'])
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
            $validate = new \app\Article\validate\Slide();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new Slide($data);
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
            $validate = new \app\Article\validate\Slide();
            if(!$validate->scene('edit', ['username', 'nickname', 'mobile','email'])->check($data))$this->return_error($validate->getError());

            $model = new Slide();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);

            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new Slide();
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

        $result = Slide::destroy($ids);
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

        $model = new Slide();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }
}
