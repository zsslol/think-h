<?php
namespace app\admin\controller;

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

    public function GetDepartment()
    {
        $datas = [];
        for ($i = 0; $i <= 50; $i++)
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

        $data_list = [
            ['id' => 99, 'robot_rid' => '1234567890', 'is_active' => '激活99'],
            ['id' => 99, 'robot_rid' => '1234567891', 'is_active' => '激活98'],
            ['id' => 99, 'robot_rid' => '1234567892', 'is_active' => '激活97'],
            ['id' => 99, 'robot_rid' => '1234567893', 'is_active' => '激活96'],
            ['id' => 99, 'robot_rid' => '1234567894', 'is_active' => '激活95'],
            ['id' => 99, 'robot_rid' => '1234567895', 'is_active' => '激活94'],
            ['id' => 99, 'robot_rid' => '1234567896', 'is_active' => '激活93'],
            ['id' => 99, 'robot_rid' => '1234567897', 'is_active' => '激活92']
        ];

        $listBuilder = new \builder\ListBuilder();
        return $listBuilder->setMetaTitle('设备列表')  // 设置页面标题
            ->addTopButton('create')   // 添加新增按钮
            ->addTopButton('resume')   // 添加启用按钮
            ->addTopButton('forbid')   // 添加禁用按钮
            ->addTopButton('delete')   // 添加删除按钮
            ->addTopButton('self',array("title"=>"Excel导入","class"=>"btn btn-primary","href"=>url('importexcel')))   // 添加删除按钮
            ->addTopButton('self',array("title"=>"批量激活","class"=>"btn btn-success ajax-post confirm","href"=>url('setActive')))   // 添加删除按钮
            ->addTableColumn('id', 'ID')
            ->addTableColumn('robot_rid', '设备ID')
            ->addTableColumn('robot_sid', '设备序列号')
            ->addTableColumn('is_active', '激活状态','zdy_status',array('0'=>"未激活","1"=>"已激活"))
            ->addTableColumn('is_online', '在线状态','status')
            ->addTableColumn('im_minute', '已用分钟(语音/视频/紧急)')
            ->addTableColumn('file_size', '已用空间(MB)')
            ->addTableColumn('right_button', '操作')
            ->setTableDataList($data_list)  // 数据列表
            ->addRightButton('edit')// 添加编辑按钮
            ->addRightButton('forbid') // 添加禁用/启用按钮
            ->addRightButton('delete')// 添加删除按钮
//            ->setTableDataPage($page->show())
            ->display();
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
                    ->addFormItem('datetime', 'datetime', '时间选择器', '可单击选择时间', true, '', '', 'YYYY/MM/DD hh:mm:ss')
                    ->addFormItem('start_time', 'datetimes', '时间范围选择器', 'options为结束时间字段名', true, 'end_time', '', 'YYYY/MM/DD hh:mm:ss')
                    ->setFormData($form_data)
                    ->display();
    }
}
