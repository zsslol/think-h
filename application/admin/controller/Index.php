<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
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
                    ->setFormData($form_data)
                    ->display();
    }
}
