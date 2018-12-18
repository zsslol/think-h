<?php
namespace app\admin\controller;

use builder\FormBuilder;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function test_post()
    {
        sleep(2);
        return $_POST;
    }

    public function formBuilder()
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
        $form_builder = new FormBuilder();
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
                    ->addFormItem('datetime', 'datetime', '时间选择器', '可单击选择时间', true, '', '', 'YYYY/MM/DD hh:mm:ss')
                    ->addFormItem('start_time', 'datetimes', '时间范围选择器', 'options为结束时间字段名', true, 'end_time', '', 'YYYY/MM/DD hh:mm:ss')
                    ->setFormData($form_data)
                    ->display();
    }
}
