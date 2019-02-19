<?php
namespace app\admin\controller;

use app\Admin\model\User;

class Index extends Common
{
    public function index()
    {
        if(!\think\facade\Session::has('is_login')){
            $this->redirect(url('Admin/Common/login'));
        }
        $auth_group_model = new \app\admin\model\AuthGroup();
        $auth_group_model->getAuthGroupRule(session('user_info.group'));
        return $this->fetch();
    }

    /**
     * 个人首页
     */
    public function myindex()
    {
        return '个人首页-暂为空';
    }

    /**
     * 清空缓存
     */
    public function clearRuntime()
    {
        $cache = new \think\facade\Cache();
        $cache::clear();
        $this->return_success('操作成功');
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        if($this->request->isAjax()){
            $data = input('param.');
            if($data['new_password'] != $data['re_new_password'])$this->return_error('两次新密码不一致');

            $user_model = new User();
            $result = $user_model->editUserPassword(session('user_info.id'), $data['password'], $data['new_password']);
            if($result){
                $this->return_success('修改成功');
            } else {
                $this->return_error($user_model->getError());
            }

        } else {
            $form_builder = new \builder\FormBuilder();
            return $form_builder->setMetaTitle('修改密码')
                ->setPostUrl(url('editPassword'))
                ->addFormItem('password', 'password', '旧密码', '', true, '', '', 'data-type="password"')
                ->addFormItem('new_password', 'password', '新密码', '', true, '', '', 'data-type="password"')
                ->addFormItem('re_new_password', 'password', '重复新密码', '', true, '', '', 'data-type="password"')
                ->display();
        }
    }

    public function delete()
    {
        return $this->ajaxReturnData('操作失败', 'error');
    }

    public function setStatus()
    {
        $status = request()->param('status');

        return $this->ajaxReturnData('操作成功');
    }

    public function test_post()
    {
        sleep(2);
        return $_POST;
    }

    public function GetDepartment()
    {
        $datas = [];
        $offset = request()->get('offset');
        $limit = request()->get('limit', 15, 'intval');
        for ($i = $offset+1; $i <= $offset + $limit; $i++)
        {
            $data = [];
            $data['ID'] = $i;
            $data['Name'] = "销售部".$i ;
            $data['Level'] = $i;
            $data['Desc'] = "暂无描述信息";
            array_push($datas, $data);
        }
        $return['rows'] = $datas;
        $return['total'] = 800;
        return $return;
    }

    public function listBuilder()
    {
        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('设备列表')  // 设置页面标题
            ->addSearchColumn('id', 'text', 'ID')
            ->addSearchColumn('create_time', 'datetime', '添加时间', '点击选择时间', ['format' => 'YYYY-MM-DD hh:mm:ss'])
            ->addSearchColumn('status', 'select', '状态', '', ['1' => '启用', 0=>'禁用'])
            ->setTableInfo('permissions')
            ->addTopButton('create')   // 添加新增按钮
            ->addTopButton('resume')   // 添加启用按钮
            ->addTopButton('forbid')   // 添加禁用按钮
            ->addTopButton('delete')   // 添加删除按钮
            ->addTopButton('create',array("title"=>"Excel导入","href"=>url('importexcel')))   // 添加删除按钮
            ->addTableColumn('id', 'ID', '', '', 'right', true)
            ->addTableColumn('description', '菜单名称', '', '', 'left', true)
            ->addTableColumn('level', '级别', '', '', 'center', true)
            ->addTableColumn('status', '状态', 'status', '', 'center', true)
            ->addTableColumn('sort_order', '排序', '', '', 'center', true)
            ->addTableColumn('updated_at', '最后更新时间', '', '', 'center', true)
            ->addTableColumn('right_button', '操作','right_button', '','center', true)
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete')// 添加删除按钮
            ->show();
    }

    public function edit()
    {
        return $this->create();
    }

    public function create()
    {
        $form_data = [
            'id' => '1',
            'name' => '123456',
            'password' => 'Aa123456',
            'static' => '静态显示',
            'checkbox' => ['3', '4'],
            'radio' => '3',
            'select' => '3',
            'textarea' => 'Z-THINK',
            'editor' => '<h2>Z-THINK</h2>'
        ];

        $checkbox = [
            '1' => '元素一',
            '2' => '元素二',
            '3' => '元素三',
            '4' => '元素四',
        ];
        $form_builder = new \builder\FormBuilder();
        return $form_builder->setMetaTitle('新增')
                    ->setPostUrl(url('test_post'))
//                    ->setAjaxSubmit(false)
                    ->addFormItem('id', 'hidden', '隐藏的表单元素', '', true)
                    ->addFormItem('name', 'text', '文本元素', '123', false, '', '', 'data-type="username" min-length="5" max-length="10"')
                    ->addFormItem('name', 'prefixText', '前缀文本', 'option为前缀', true, 'TEXT', '', 'data-type="money"')
                    ->addFormItem('name', 'suffixText', '后缀文本', 'option为后缀', true, 'TEXT')
                    ->addFormItem('textarea', 'textarea', '文本域', '文本域', true, '','')
                    ->addFormItem('editor', 'editor', '编辑器', '富文本编辑器', true)
                    ->addFormItem('password', 'password', '密码文本', '', true, '', '', 'data-type="password"')
                    ->addFormItem('static', 'static_item', '静态元素', '')
                    ->addFormItem('checkbox', 'checkbox', '多选', '', true, $checkbox)
                    ->addFormItem('radio', 'radio', '单选', '', true, $checkbox)
                    ->addFormItem('select', 'select', '下拉列表', '', true, $checkbox)
                    ->addFormItem('datetime', 'datetime', '时间选择器', '可单击选择时间', false, '', '', 'YYYY/MM/DD hh:mm:ss')
                    ->addFormItem('start_time', 'datetimes', '时间范围选择器', 'options为结束时间字段名', false, 'end_time', '', 'YYYY/MM/DD hh:mm:ss')
                    ->addFormItem('picture', 'picture', '图片上传', '', true)
                    ->addFormItem('pictures', 'pictures', '多图上传', '', true)
                    ->addFormItem('file', 'file', '文件上传', '', true)
                    ->setFormData($form_data)
                    ->display();
    }

    public function create_group_form()
    {
        $fields = '[{"name":"id","type":"hidden","title":"\u9690\u85cf\u7684\u8868\u5355\u5143\u7d20","tip":"","must":true,"options":[],"extra_class":"","extra_attr":""},{"name":"name","type":"text","title":"\u6587\u672c\u5143\u7d20","tip":"123","must":false,"options":"","extra_class":"","extra_attr":"data-type=\"username\" min-length=\"5\" max-length=\"10\""},{"name":"name","type":"prefixText","title":"\u524d\u7f00\u6587\u672c","tip":"option\u4e3a\u524d\u7f00","must":true,"options":"TEXT","extra_class":"","extra_attr":"data-type=\"money\""},{"name":"name","type":"suffixText","title":"\u540e\u7f00\u6587\u672c","tip":"option\u4e3a\u540e\u7f00","must":true,"options":"TEXT","extra_class":"","extra_attr":""},{"name":"textarea","type":"textarea","title":"\u6587\u672c\u57df","tip":"\u6587\u672c\u57df","must":true,"options":"","extra_class":"","extra_attr":""},{"name":"editor","type":"editor","title":"\u7f16\u8f91\u5668","tip":"\u5bcc\u6587\u672c\u7f16\u8f91\u5668","must":true,"options":[],"extra_class":"","extra_attr":""},{"name":"password","type":"password","title":"\u5bc6\u7801\u6587\u672c","tip":"","must":true,"options":"","extra_class":"","extra_attr":"data-type=\"password\""},{"name":"static","type":"static_item","title":"\u9759\u6001\u5143\u7d20","tip":"","must":false,"options":[],"extra_class":"","extra_attr":""},{"name":"checkbox","type":"checkbox","title":"\u591a\u9009","tip":"","must":true,"options":{"1":"\u5143\u7d20\u4e00","2":"\u5143\u7d20\u4e8c","3":"\u5143\u7d20\u4e09","4":"\u5143\u7d20\u56db"},"extra_class":"","extra_attr":""},{"name":"radio","type":"radio","title":"\u5355\u9009","tip":"","must":true,"options":{"1":"\u5143\u7d20\u4e00","2":"\u5143\u7d20\u4e8c","3":"\u5143\u7d20\u4e09","4":"\u5143\u7d20\u56db"},"extra_class":"","extra_attr":""},{"name":"select","type":"select","title":"\u4e0b\u62c9\u5217\u8868","tip":"","must":true,"options":{"1":"\u5143\u7d20\u4e00","2":"\u5143\u7d20\u4e8c","3":"\u5143\u7d20\u4e09","4":"\u5143\u7d20\u56db"},"extra_class":"","extra_attr":""},{"name":"datetime","type":"datetime","title":"\u65f6\u95f4\u9009\u62e9\u5668","tip":"\u53ef\u5355\u51fb\u9009\u62e9\u65f6\u95f4","must":false,"options":"","extra_class":"","extra_attr":"YYYY\/MM\/DD hh:mm:ss"},{"name":"start_time","type":"datetimes","title":"\u65f6\u95f4\u8303\u56f4\u9009\u62e9\u5668","tip":"options\u4e3a\u7ed3\u675f\u65f6\u95f4\u5b57\u6bb5\u540d","must":false,"options":"end_time","extra_class":"","extra_attr":"YYYY\/MM\/DD hh:mm:ss"},{"name":"picture","type":"picture","title":"\u56fe\u7247\u4e0a\u4f20","tip":"","must":true,"options":[],"extra_class":"","extra_attr":""},{"name":"pictures","type":"pictures","title":"\u591a\u56fe\u4e0a\u4f20","tip":"","must":true,"options":[],"extra_class":"","extra_attr":""},{"name":"file","type":"file","title":"\u6587\u4ef6\u4e0a\u4f20","tip":"","must":true,"options":[],"extra_class":"","extra_attr":""}]';
        $array = [
            'group1' => ['title' => '分组1', 'item_list' => json_decode($fields,true)],
            'group2' => ['title' => '分组2', 'item_list' => json_decode($fields,true)],
            'group3' => ['title' => '分组3', 'item_list' => json_decode($fields,true)]
        ];
        $form_builder = new \builder\FormBuilder();
        return $form_builder->setMetaTitle('新增')->display_group_form($array);
    }
}
