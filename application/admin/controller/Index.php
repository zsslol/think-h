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

    public function formBuilder()
    {
        $form_data = [
            'id' => '1',
            'name' => '张三',
            'password' => '123456',
            'static' => '静态显示',
            'checkbox' => ['3', '4'],
           // 'radio' => '3',
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
                    ->addFormItem('id', 'hidden', '隐藏的表单元素', '', true)
                    ->addFormItem('name', 'text', '文本元素', '123', false)
                    ->addFormItem('name1', 'prefixText', '前缀文本', 'option为前缀', true, 'TEXT')
                    ->addFormItem('name2', 'suffixText', '后缀文本', 'option为后缀', true, 'TEXT')
                    ->addFormItem('textarea', 'textarea', '文本域', 'option为行数,默认3', true, '3')
                    ->addFormItem('editor', 'editor', '编辑器', '富文本编辑器', true)
                    ->addFormItem('password', 'password', '密码文本', '', true)
                    ->addFormItem('static', 'static_item', '静态元素', '')
                    ->addFormItem('checkbox', 'checkbox', '多选', '', true, $checkbox)
                    ->addFormItem('radio', 'radio', '单选', '', true, $checkbox)
                    ->addFormItem('select', 'select', '下拉列表', '', true, $checkbox)
                    ->setFormData($form_data)
                    ->display();
    }
}
