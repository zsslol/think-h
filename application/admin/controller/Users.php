<?php

namespace app\Admin\controller;

use app\Admin\model\User;

class Users extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $auth_group_model = new \app\admin\model\AuthGroup();
        $group_list = $auth_group_model->getListData(true);
        unset($group_list[0]);

        $listBuilder = new \builder\ListBuilder();
        $listBuilder->setMetaTitle('用户列表')  // 设置页面标题
            ->addSearchColumn('username', 'text', '用户名', '用户名-模糊匹配', '', 'like')
            ->addSearchColumn('mobile', 'text', '手机号', '手机号-模糊匹配', '', 'like')
            ->addSearchColumn('email', 'text', '邮箱', '邮箱-模糊匹配', '', 'like')
            ->addSearchColumn('status', 'select', '状态', '', ['1' => '启用', 0=>'禁用'])
            ->addSearchColumn('group', 'select', '角色', '', $group_list)
            ->setCanCheckbox(false)
            ->addTopButton('create')   // 添加新增按钮
            ->addTableColumn('id', 'ID', '', '', 'right', true)
            ->addTableColumn('username', '用户名', '', '', 'left')
            ->addTableColumn('nickname', '昵称', '', '', 'left')
            ->addTableColumn('name', '角色', '', '', 'left')
            ->addTableColumn('status', '状态', 'status', '', 'center', true)
            ->addTableColumn('lastlogin_time', '最后登录时间', 'datetime', '', 'center', true)
            ->addTableColumn('update_time', '最后更新时间', '', '', 'center', true)
            ->addTableColumn('right_button', '操作','right_button', '','center')
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete')// 添加删除按钮
            ->alterTableData(['key' => 'id', 'value' => 1], ['right_button' => null]);

        if($this->request->isAjax()){
            $model = new User();

            $limit = request()->get('limit');
            $offset = request()->get('offset', 0);
            $field = implode(',', $listBuilder->getShowField());
            $order_where = $listBuilder->getOrderWhere();

            $list['rows'] = $model->withJoin(['AuthGroup' => ['name']])->where($order_where['where'])->limit($offset, $limit)->order($order_where['order'])->select()->toArray();
            $list['total'] = $model->withJoin('AuthGroup')->where($order_where['where'])->count();
            //处理list数据
            $return = $listBuilder->getListReturn($list);
            return $return;
        } else {
            return $listBuilder->display();
        }
    }

    /**
     * 创建表单
     */
    private function getFormData($data = [])
    {
        if(count($data) == 0){
            $password_must = true;
            $password_tips = '用户密码，(需包含大小写、数字的组合)，6-32位长度';
            $data = ['status' => 1];
        } else {
            $password_must = false;
            $password_tips = '用户密码，为空时不更新, (需包含大小写、数字的组合)，6-32位长度';
        }

        $auth_group_model = new \app\admin\model\AuthGroup();
        $group_list = $auth_group_model->getListData(true);
        unset($group_list[0]);
        $form_builder = new \builder\FormBuilder();
        return $form_builder
            ->addFormItem('id', 'hidden', '隐藏的表单元素', '')
            ->addFormItem('group', 'select', '所属角色', '用户所属权限角色', true, $group_list)
            ->addFormItem('username', 'text', '用户名', '用户登录账号,6-20位，唯一', true, '', '', 'data-type="username" min-length="6" max-length="20"')
            ->addFormItem('nickname', 'text', '用户昵称', '用户昵称,2-20位，唯一', true, '', '', 'data-type="text" min-length="2" max-length="20"')
            ->addFormItem('password', 'password', '用户密码', $password_tips, $password_must,'','','data-type="password"')
            ->addFormItem('mobile', 'text', '手机号码', '用户的手机号码，唯一', true)
            ->addFormItem('email', 'text', '电子邮箱', '用户的电子邮箱地址，唯一', false,'','','data-type="email"')
            ->addFormItem('avatar', 'picture', '头像', '点击上传用户的头像', true)
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
            $validate = new \app\Admin\validate\User();
            if(!$validate->check($data))$this->return_error($validate->getError());

            $model = new User($data);
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
            $validate = new \app\Admin\validate\User();
            if(!$validate->scene('edit', ['username', 'nickname', 'mobile','email'])->check($data))$this->return_error($validate->getError());

            $password = input('post.password');
            $model = new User();
            if(!empty($password)) {
                $data['password'] = $password;
                $data['salt'] = '';
                $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);
            } else {
                unset($data['password']);
                $result = $model->isUpdate(true)->allowField(array_keys($data))->save($data, ['id' => $data['id']]);
            }
            if($result){
                $this->return_success('更新成功');
            } else {
                $this->return_error('更新失败');
            }
        } else {
            $id = $this->request->param('id', '', 'intval');
            if(empty($id)) $this->return_error('参数错误，请重试');
            $model = new User();
            $data = $model->field('password,salt', true)->find($id);
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

        $result = User::destroy($ids);
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

        $model = new User();
        $result = $model->save($save, ['id' => $ids]);

        if($result){
            $this->return_success('操作成功');
        } else {
            $this->return_error('操作失败');
        }
    }
}
