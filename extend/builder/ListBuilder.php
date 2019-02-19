<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 * Date: 2018/12/16
 * Time: 20:42
 */
namespace builder;
use think\Db;

/**
 * 数据列表自动生成器
 */
class ListBuilder
{
    protected $_meta_title;                  // 页面标题
    protected $_top_button_list = [];   // 顶部工具栏按钮组
    protected $_search_column_list  = [];           // 搜索参数配置
    protected $_tab_nav = [];           // 页面Tab导航
    protected $_table_column_list = []; // 表格标题字段
    protected $_table_data_list   = []; // 表格数据列表
    protected $_table_data_page;             // 表格数据分页
    protected $_right_button_list = []; // 表格右侧操作按钮组
    protected $_alter_data_list = [];   // 表格数据列表重新修改的项目
    protected $_extra_html;                  // 额外功能代码
    protected $_extra_search;                  //搜索html
    protected $_template;                    // 模版
    protected $_can_checkbox = true;                    // 是否可多选
    protected $forbidTitle = array('0' => '启用', '1' => '禁用');
    protected $_default_page_size = 10;

    private $module_name = '';
    private $controller_name = '';
    private $action_name = '';
    private $table_name = '';
    private $table_pk = 'id';

    //CSS容器
    private $_css_file_list = [
        '<link rel="shortcut icon" href="favicon.ico">',
        '<link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">',
        '<link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">',
        '<link href="/static/css/animate.css" rel="stylesheet">',
        '<link href="/static/css/style.css?v=4.1.0" rel="stylesheet">',
        '<link href="/static/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">',
        '<link href="/static/css/plugins/toastr/toastr.min.css" rel="stylesheet">'
    ];
    //JS容器
    private $_js_file_list = [
        '<script src="/static/js/jquery.2.2.4.min.js"></script>',
        '<script>jQuery.browser={};(function(){jQuery.browser.msie=false; jQuery.browser.version=0;if(navigator.userAgent.match(/MSIE ([0-9]+)./)){ jQuery.browser.msie=true;jQuery.browser.version=RegExp.$1;}})();</script>',
        '<script src="/static/js/bootstrap.min.js?v=3.3.6"></script>',
        '<script src="/static/js/jquery.ba-hashchange.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/extensions/export/bootstrap-table-export.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/extensions/export/tableExport.min.js"></script>',
        '<script src="/static/js/plugins/bootstrap-table/extensions/export/FileSaver.min.js"></script>',
        '<script src="/static/js/plugins/layer/layer.min.js"></script>',
        '<script src="/static/js/plugins/toastr/toastr.min.js"></script>',
        '<script src="/static/js/content.js"></script>',
        '<script src="/static/js/demo/bootstrap-table-demo.js"></script>'
    ];

    public function __construct()
    {
        $this->module_name = request()->module();
        $this->controller_name = request()->controller();
        $this->action_name = request()->action();
    }

    /**
     * 设置默认行数
     */
    public function setPageSize($limit)
    {
        $this->_default_page_size = $limit;
        return $this;
    }

    /**
     * 设置查询表的名称
     * @param $table_name 表名
     * @param $pk 主键
     * @return $this
     */
    public function setTableInfo($table_name, $pk = 'id')
    {
        $this->table_name = $table_name;
        $this->table_pk = $pk;
        return $this;
    }

    /**
     * 设置是否可多选
     * @param $can bool
     * @return $this
     */
    public function setCanCheckbox($can){
        $this->_can_checkbox = $can;
        return $this;
    }

    /**
     * 设置页面标题
     * @param $title 标题文本
     * @return $this
     *
     */
    public function setMetaTitle($meta_title) {
        $this->_meta_title = $meta_title;
        return $this;
    }

    public function setForbidTitle($title) {
        $this->forbidTitle = $title;
        return $this;
    }

    /**
     * 加入一个列表顶部工具栏按钮
     * @param string $type 按钮类型，主要有add/resume/forbid/recycle/restore/delete/self七几种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addTopButton($type, $attribute = null) {
        switch ($type) {
            case 'create':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['icon'] = '<i class="glyphicon glyphicon-plus" aria-hidden="true"></i>';
                $my_attribute['title'] = '新增';
                $my_attribute['class'] = 'btn open-window btn-success';
                $my_attribute['href']  = url($this->module_name.'/'.$this->controller_name.'/create');
                //如果定义了属性数组则与默认的进行替换合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'resume':  // 添加启用按钮(禁用的反操作)
                //预定义按钮属性以简化使用
                $my_attribute['icon'] = '<i class="glyphicon glyphicon-ok-circle" aria-hidden="true"></i>';
                $my_attribute['title'] = '启用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-primary ajax-post confirm';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;  // 要操作的数据模型
                $my_attribute['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    [
                        'status' => 'resume',
                        'model' => $my_attribute['model']
                    ]
                );

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'forbid':  // 添加禁用按钮(启用的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['icon'] = '<i class="glyphicon glyphicon-remove-circle" aria-hidden="true"></i>';
                $my_attribute['title'] = '禁用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-warning ajax-post confirm';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;
                $my_attribute['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    array(
                        'status' => 'forbid',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                //这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'recycle':  // 添加回收按钮(还原的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['icon'] = '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>';
                $my_attribute['title'] = '回收';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;
                $my_attribute['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    array(
                        'status' => 'recycle',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'restore':  // 添加还原按钮(回收的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '还原';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-success ajax-post confirm';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;
                $my_attribute['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    array(
                        'status' => 'restore',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'delete': // 添加删除按钮(我没有反操作，删除了就没有了，就真的找不回来了)
                // 预定义按钮属性以简化使用
                $my_attribute['icon'] = '<i class="glyphicon glyphicon-trash" aria-hidden="true"></i>';
                $my_attribute['title'] = '删除';
                $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
                $my_attribute['href']  = url($this->module_name.'/'.$this->controller_name.'/delete');
                //如果定义了属性数组则与默认的进行替换合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                // 这个按钮定义好了把它丢进按钮池里
                $this->_top_button_list[] = $my_attribute;
                break;
        }
        return $this;
    }

    /**
     * 加入一个搜索项
     * @param $name 表单名
     * @param $type 表单类型
     * @param $title 表单标题
     * @param $tip 表单提示说明
     * @param $must 是否必填
     * @param $options 表单options
     * @param $find_type 条件查询类型
     * @return $this
     */
    public function addSearchColumn($name, $type, $title, $tip = '', $options = [], $find_type = '=')
    {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['options'] = $options;
        $item['find_type'] = $find_type;
        $this->_search_column_list[] = $item;
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list Tab列表  array(
     *                               'title' => '标题',
     *                               'href' => 'http://www.corethink.cn'
     *                           )
     * @param $current_tab 当前tab
     * @return $this
     *
     */
    public function setTabNav($tab_list, $current_tab)
    {
        $this->_tab_nav = array(
            'tab_list' => $tab_list,
            'current_tab' => $current_tab
        );
        return $this;
    }

    /**
     * 加一个表格标题字段
     *
     */
    public function addTableColumn($name, $title, $type = null, $param = null, $align = 'left', $sortable = false)
    {
        $column = array(
            'name'  => $name,
            'title' => $title,
            'type'  => $type,
            'param' => $param,
            'align' => $align,
            'sortable' => $sortable,
        );
        $this->_table_column_list[] = $column;
        return $this;
    }


    /**
     * 加入一个数据列表右侧按钮
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addRightButton($type, $attribute = null)
    {
        switch ($type) {
            case 'edit':  // 编辑按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '编辑';
                $my_attribute['class'] = 'btn btn-success btn-xs open-window';
                $my_attribute['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/edit',
                    array($this->table_pk => '__data_id__')
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'forbid':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示启用/禁用
                //预定义按钮属
                $my_attribute['type'] = 'forbid';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;
                $my_attribute['0']['title'] = $this->forbidTitle[0];
                $my_attribute['0']['class'] = 'btn btn-primary btn-xs ajax-post confirm';
                $my_attribute['0']['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    array(
                        'status' => 'resume',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );
                $my_attribute['1']['title'] = $this->forbidTitle[1];
                $my_attribute['1']['class'] = 'btn btn-warning btn-xs ajax-post confirm';
                $my_attribute['1']['href']  = url(
                    $this->module_name.'/'.$this->controller_name.'/setStatus',
                    array(
                        'status' => 'forbid',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'delete':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['class'] = 'btn btn-danger btn-xs ajax-post confirm';
                $my_attribute['model'] = $attribute['model'] ? : $this->controller_name;
                $my_attribute['href'] = url(
                    $this->module_name.'/'.$this->controller_name.'/delete',
                    array(
                        'status' => 'delete',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'self':
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = 'label label-default';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
        }
        return $this;
    }

    /**
     * 修改列表数据
     * 有时候列表数据需要在最终输出前做一次小的修改
     * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
     * @param $page
     * @return $this
     */
    public function alterTableData($condition, $alter_data)
    {
        $this->_alter_data_list[] = array(
            'condition' => $condition,
            'alter_data' => $alter_data
        );
        return $this;
    }

    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     *
     */
    public function setExtraHtml($extra_html)
    {
        $this->_extra_html = $extra_html;
        return $this;
    }

    /*
     * 添加CSS文件
     */
    private function pushCssFile($file)
    {
        if(!in_array($file, $this->_css_file_list))array_push($this->_css_file_list, $file);
    }

    /*
     * 添加JS文件
     */
    private function pushJSFile($file)
    {
        if(!in_array($file, $this->_js_file_list))array_push($this->_js_file_list, $file);
    }

    /*
     * 显示顶部按钮
     */
    private function getTopButtonHtml()
    {
        $button_html = '<div class="btn-group hidden-xs" id="exampleTableEventsToolbar" role="group">';

        if(count($this->_top_button_list) > 0) {
            foreach ($this->_top_button_list as $button) {
                $button_html .= '<button type="button" class="' . $button['class'] . '" href="' . $button['href'] . '">';
                if (!empty($button['icon'])) $button_html .= $button['icon'] . '&nbsp;';
                $button_html .= $button['title'];
                $button_html .= '</button>';
            }
        }

        $button_html .= '</div>';
        return $button_html;
    }

    /*
     * 获取行内右侧按钮
     */
    private function getRightButtonHtml($item)
    {
        $button_html = '';
        if(count($this->_right_button_list) > 0) {
            foreach ($this->_right_button_list as $button) {

                if(empty($button['type'])){
                    $button_html .= '<button type="button" class="' . $button['class'] . '" href="' . $button['href'] . '">';
                    if (!empty($button['icon'])) $button_html .= $button['icon'] . '&nbsp;';
                    $button_html .= $button['title'];
                    $button_html .= '</button>&nbsp;';
                } else {
                    switch ($button['type']) {
                        case 'forbid':
                            $button_html .= '<button type="button" class="' . $button[$item['status']]['class'] . '" href="' . $button[$item['status']]['href'] . '">';
                            if (!empty($button[$item['status']]['icon'])) $button_html .= $button[$item['status']]['icon'] . '&nbsp;';
                            $button_html .= $button[$item['status']]['title'];
                            $button_html .= '</button>&nbsp;';
                            break;
                    }
                }
            }
        }

        return preg_replace(
            '/__data_id__/i',
            $item[$this->table_pk],
            $button_html
        );
    }

    /*
     * 显示内容列表
     */
    private function getTableHtml()
    {
        $html_js_content = '<script>';
        //默认页数
        $html_js_content .= 'pageSize = '.$this->_default_page_size.';';
        //获取列表内容的URL
        $html_js_content .= 'var getListUrl = "'.url($this->module_name.'/'.$this->controller_name.'/'.$this->action_name).'";';
        //拼接展示的字段内容
        if( count($this->_table_column_list) > 0 ){
            $field_html = '';
            if($this->_can_checkbox){
                $field_html .= '{checkbox:true},';
            } else {
                $field_html .= '{checkbox:false},';
            }

            foreach($this->_table_column_list as $item){
                $field = $item;
                $field['field'] = $item['name'];
                unset($field['name']);
                unset($field['type']);
                unset($field['param']);
                $field_html .= json_encode($field).',';
            }
            $html_js_content .= 'var fieldList = ['.substr($field_html, 0, -1).'];';
        }
        $html_js_content .= 'var tablePk = "'.$this->table_pk.'";';
        $html_js_content  .= '</script>';
        //JS内容添加到页面内
        $this->pushJSFile($html_js_content);
        return '<table id="tb_departments"></table>';
    }

    /*
     * 获取头部内容
     */
    private function getTopHtmlContent()
    {
        return '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>'.$this->_meta_title.'</title>
                '.implode('', $this->_css_file_list).'
                <style>
                .pull-left{margin-top:10px;}
                .fixed-table-container tbody td .th-inner, .fixed-table-container thead th .th-inner{text-align:center;}
                .glyphicon-remove{color:red;}
                .glyphicon-ban-circle{color:red;}
                .glyphicon-ok{color:green;}
                .search-box .control-label{text-align: right;text-align: right;height: 39px;line-height: 35px;}
                .search-box .form-group{margin-bottom: 15px;}
                .search-box .ibox{margin-bottom: 0px;}
                .listbuilder-tr-picture{width:40px; height:40px;}
                #ibox-search-form .m-b{margin:0px;}
                </style>
            </head>
            
            <body class="gray-bg">
                <div class="wrapper wrapper-content animated fadeInRight">
                    <div class="ibox float-e-margins">
        ';
    }

    /*
     * 获取搜索部分内容
     */
    private function searchHtmlContent()
    {
        $cout_search_list = count($this->_search_column_list);
        if($cout_search_list == 0){
            $this->pushJSFile('<script>searchStatus = false;</script>');
            return '';
        }
        if($cout_search_list == 1){
            $this->pushJSFile('<script>searchField = "'.$this->_search_column_list[0]['name'].'";searchStatus = true;</script>');
            return '';
        } else {
            $this->pushJSFile('<script>searchStatus = false;$(".fa-chevron-up").click();</script>');
            $search_html = '<div class="row search-box">
                <div class="col-sm-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>搜索条件</h5>
                            <div class="ibox-tools">
                                <a style="color:#000;" title="点击进行搜索" class="dropdown-toggle" href="javascript:;">
                                    搜索
                                </a>
                                <a style="color:#000;" title="点击重置搜索条件" class="clean-search" href="javascript:;">
                                    重置
                                </a>
                                　
                                <a class="collapse-link">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <form id="ibox-search-form" method="get" role="form" class="form-inline">';

            foreach($this->_search_column_list as $search_item){
                $search_html .= '<div class="form-group col-sm-4">
                                    <label class="col-sm-4 control-label">'.$search_item['title'].'：</label>
                                    <div class="col-sm-8">';
                    switch ($search_item['type']){
                        case 'select':
                            if(!is_array($search_item['options'])
                                || count($search_item['options']) == 0)continue;
                            $search_html .= '<select class="formbuilder-select form-control m-b" name="'.$search_item['name'].'">
                                                <option value="">请选择'.$search_item['title'].'</option>';
                            foreach($search_item['options'] as $key => $option){
                                $search_html .= '<option value="'.$key.'">'.$option.'</options>';
                            }
                            $search_html .= '</select>';
                            break;
                        case 'datetime':
                            $format = empty($search_item['options']['format']) ? 'YYYY-MM-DD' : $search_item['options']['format'];
                            $search_html .= '<input readonly onclick="laydate({istime: true, format: \''.$format.'\'})" id="layer-date-'.$search_item['name'].'" name="'.$search_item['name'].'" type="text"  class="formbuilder-input form-control layer-date" >';
                            $this->pushJSFile('<script src="/static/js/plugins/layer/laydate/laydate.js"></script>');
                            break;

                        default:
                            $search_html .= '<input type="text" name="'.$search_item['name'].'" placeholder="'.$search_item['title'].'" class="form-control">';
                    }
                                        $search_html .= '<span class="help-block m-b-none">'.$search_item['tip'].'</span>';
                $search_html .= '   </div>
                                </div>';
            }

            $search_html .= '   <div style="clear:both;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        }
        return $search_html;
    }

    /*
     * 获取底部内容
     */
    private function getBottomHtmlContent()
    {
        return '        
                    </div>
                </div>
                '.implode('', $this->_js_file_list).'
                <script>
                    $(function(){
                        var layerTipsObj;
                        $(".fixed-table-container").on("mouseover", ".listbuilder-tr-picture", function(){
                            var thisImgObj = $(this);
                            layerTipsObj = layer.tips(\'<img width="100%" src="\'+thisImgObj.attr("src")+\'" />\',thisImgObj, {
                                time:0,
                                tips:2,
                                area: \'40%\',
                                skin: \'layui-layer-nobg\', //没有背景色
                            });
                        });
                        $(".fixed-table-container").on("click", ".listbuilder-tr-picture", function(){
                            var thisImgObj = $(this);
                            layer.open({
                              type: 1,
                              title: false,
                              closeBtn: 1,
                              area: \'60%\',
                              skin: \'layui-layer-nobg\', //没有背景色
                              shadeClose: true,
                              content: \'<img width="100%" src="\'+thisImgObj.attr("src")+\'" />\'
                            });
                        });
                        $(".fixed-table-container").on("mouseout", ".listbuilder-tr-picture", function(){
                            layer.close(layerTipsObj);
                        });
                    });
                </script>
            </body>
        </html>
        ';
    }

    /*
     * 取出要显示的字段
     */
    public function getShowField()
    {
        $fields = [$this->table_pk];
        if( count($this->_table_column_list) > 0){
            foreach($this->_table_column_list as $item){
                if($item['name'] != 'right_button')array_push($fields, $item['name']);
            }
        }
        return array_unique($fields);
    }

    /*
     * 处理要返回的列表内容
     */
    public function getListReturn($list)
    {
        if(count($list) == 0 || count($this->_table_column_list) == 0)return $list;

        $data_list = [];
        foreach($list['rows'] as $item) {
            $data = [];
            foreach ($this->_table_column_list as $fields) {
                switch($fields['type']){
                    case 'right_button':
                        if( count($this->_right_button_list) == 0){
                            $data[$fields['name']] = '';
                        } else {
                            $button_html = $this->getRightButtonHtml($item);
                        }
                        $data[$fields['name']] = $button_html;
                        break;
                    case 'status' :
                        switch($item[$fields['name']]){
                            case '0':
                                $data[$fields['name']] = '<i class="glyphicon glyphicon-ban-circle"></i>';
                                break;
                            case '1':
                                $data[$fields['name']] = '<i class="glyphicon glyphicon-ok"></i>';
                                break;
                        }
                        break;
                    case 'defined_status':
                        $data[$fields['name']] =  $fields['param'][$item[$fields['name']]];
                        break;
                    case 'picture':
                        $data[$fields['name']] = '<img title="点击查看大图" class="listbuilder-tr-picture" src="'.getCover($item[$fields['name']]).'" />';
                        break;
                    case 'icon':
                        $data[$fields['name']] = '<i class="'.$item[$fields['name']].'"></i>';
                        break;
                    case 'date':
                        $data[$fields['name']] = intval($item[$fields['name']]) > 0 ? date('Y-m-d',$item[$fields['name']]) : '-';
                        break;
                    case 'datetime':
                        $data[$fields['name']] = intval($item[$fields['name']]) > 0 ? date('Y-m-d H:i:s',$item[$fields['name']]) : '-';
                        break;
                    case 'time':
                        $data[$fields['name']] = intval($item[$fields['name']]) > 0 ? date('H:i:s',$item[$fields['name']]) : '-';;
                        break;
                    case 'callback': // 调用函数
                        if (is_array($fields['param'])) {
                            $data[$fields['name']] = call_user_func_array($fields['param'], array($item[$fields['name']]));
                        } else {
                            $data[$fields['name']] = call_user_func($fields['param'], $item[$fields['name']]);
                        }
                    default:
                        $data[$fields['name']] = $item[$fields['name']];
                }
            }

            /**
             * 修改列表数据
             * 有时候列表数据需要在最终输出前做一次小的修改
             * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
             */
            if ($this->_alter_data_list) {
                foreach ($this->_alter_data_list as $alter) {
                    if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                        $data = array_merge($data, $alter['alter_data']);
                    }
                }
            }

            array_push($data_list, $data);
        }


        $list['rows'] = $data_list;
        return $list;
    }

    /**
     * 显示自定义页面
     */
    public function definedDisplay()
    {
        $html_content = $this->getTopHtmlContent();

        $html_content .= $this->searchHtmlContent();

        $html_content .= '<div class="row row-lg">
                            <div class="col-sm-12">
                                <div class="example-wrap">
                                    <!--<h4 class="example-title">'.$this->_meta_title.'</h4>-->
                                        <div class="example">';
        $html_content .= $this->getTopButtonHtml();
        $html_content .= $this->_extra_html;
        $html_content .= '</div></div></div></div>';
        $html_content .= $this->getBottomHtmlContent();
        return $html_content;
    }

    /**
     * 显示页面内容
     * @return string
     */
    public function display()
    {
        $html_content = $this->getTopHtmlContent();

        $html_content .= $this->searchHtmlContent();

        $html_content .= '<div class="row row-lg">
                            <div class="col-sm-12">
                                <div class="example-wrap">
                                    <!--<h4 class="example-title">'.$this->_meta_title.'</h4>-->
                                        <div class="example">';
        $html_content .= $this->getTopButtonHtml();
        $html_content .= $this->getTableHtml();

        $html_content .= '</div></div></div></div>';
        $html_content .= $this->_extra_html;
        $html_content .= $this->getBottomHtmlContent();
        return $html_content;
    }

    /**
     * 判断显示列表或返回列表内容
     */
    public function show()
    {
        $limit = request()->get('limit');
        $offset = request()->get('offset', 0);

        if(request()->isAjax()){
            $order_where = $this->getOrderWhere();
            $field = implode(',', $this->getShowField());
            $list['rows'] = Db::name($this->table_name)->where($order_where['where'])->field($field)->limit($offset, $limit)->order($order_where['order'])->select();
            $list['total'] = Db::name($this->table_name)->where($order_where['where'])->count();
            //处理list数据
            $return = $this->getListReturn($list);
            return $return;
        } else {
            return $this->display();
        }
    }

    /**
     * 获取排序与条件
     */
    public function getOrderWhere(){
        $order = request()->get('order');
        $ordername = request()->get('ordername');
        if(!empty($ordername)){
            $order = $ordername.' '.$order;
        } else {
            $order = $this->table_pk.' '.$order;
        }

        $where = [];
        foreach($this->_search_column_list as $search_item){
            $val = request()->get($search_item['name']);
            if($val !== null){
                if($search_item['find_type'] == 'like'){
                    $where[] = [$search_item['name'], $search_item['find_type'], "%{$val}%"];
                } else {
                    $where[] = [$search_item['name'], $search_item['find_type'], $val];
                }
            }
        }
        return ['order' => $order, 'where' => $where];
    }

    /**
     * 显示页面
     *
     */
    public function display_old() {
        // 编译data_list中的值
        foreach ($this->_table_data_list as &$data) {
            // 编译表格右侧按钮
            if ($this->_right_button_list) {
                foreach ($this->_right_button_list as $right_button) {
                    // 禁用按钮与隐藏比较特殊，它需要根据数据当前状态判断是显示禁用还是启用
                    if ($right_button['type'] === 'forbid' || $right_button['type'] === 'hide'){
                        $right_button = $right_button[$data['status']];
                    }

                    // 将约定的标记__data_id__替换成真实的数据ID
                    $right_button['href'] = preg_replace(
                        '/__data_id__/i',
                        $data[$this->table_pk],
                        $right_button['href']
                    );

                    // 编译按钮属性
                    $right_button['attribute'] = $this->compileHtmlAttr($right_button);
                    $data['right_button'] .= '<a '.$right_button['attribute']
                                          .'>'.$right_button['title'].'</a> ';
                }
            }

            // 根据表格标题字段指定类型编译列表数据
            foreach ($this->_table_column_list as &$column) {
                switch ($column['type']) {
                    case 'status':
                        switch($data[$column['name']]){
                            case '0':
                                $data[$column['name']] = '<i class="glyphicon glyphicon-remove"></i>';
                                break;
                            case '1':
                                $data[$column['name']] = '<i class="glyphicon glyphicon-ok"></i>';
                                break;
                        }
                        break;
                    case 'zdy_status':
                            $data[$column['name']] =  $column['param'][$data[$column['name']]];
                            break;
                    case 'byte':
                        $data[$column['name']] = format_bytes($data[$column['name']]);
                        break;
                    case 'icon':
                        $data[$column['name']] = '<i class="'.$data[$column['name']].'"></i>';
                        break;
                    case 'date':
                        $data[$column['name']] = time_format($data[$column['name']], 'Y-m-d');
                        break;
                    case 'datetime':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'time':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'avatar':
                        $data[$column['name']] = '<img class="listBuilderAvatar" style="width:40px;height:40px;" src="'.get_cover($data[$column['name']]).'">';
                        break;
                    case 'picture':
                        $data[$column['name']] = '<img class="picture" src="'.get_cover($data[$column['name']]).'">';
                        break;
                    case 'pictures':
                        $temp = explode(',', $data[$column['name']]);
                        $data[$column['name']] = '<img class="picture" src="'.get_cover($temp[0]).'">';
                        break;
                    case 'type':
                        $form_item_type = C('FORM_ITEM_TYPE');
                        $data[$column['name']] = $form_item_type[$data[$column['name']]][0];
                        break;
                    case 'callback': // 调用函数
                        if (is_array($column['param'])) {
                            $data[$column['name']] = call_user_func_array($column['param'], array($data[$column['name']]));
                        } else {
                            $data[$column['name']] = call_user_func($column['param'], $data[$column['name']]);
                        }
                        break;
                }
            }



            /**
             * 修改列表数据
             * 有时候列表数据需要在最终输出前做一次小的修改
             * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
             */
            if ($this->_alter_data_list) {
                foreach ($this->_alter_data_list as $alter) {
                    if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                        $data = array_merge($data, $alter['alter_data']);
                    }
                }
            }
        }

        //编译top_button_list中的HTML属性
        if ($this->_top_button_list) {
            foreach ($this->_top_button_list as &$button) {
                $button['attribute'] = $this->compileHtmlAttr($button);
            }
        }
/*
        $this->assign('meta_title',          $this->_meta_title);          // 页面标题
        $this->assign('top_button_list',     $this->_top_button_list);     // 顶部工具栏按钮
        $this->assign('search',              $this->_search);              // 搜索配置
        $this->assign('tab_nav',             $this->_tab_nav);             // 页面Tab导航
        $this->assign('table_column_list',   $this->_table_column_list);   // 表格的列
        $this->assign('table_data_list',     $this->_table_data_list);     // 表格数据
        $this->assign('table_data_list_key', $this->table_pk); // 表格数据主键字段名称
        $this->assign('table_data_page',     $this->_table_data_page);     // 表示个数据分页
        $this->assign('right_button_list',   $this->_right_button_list);   // 表格右侧操作按钮
        $this->assign('alter_data_list',     $this->_alter_data_list);     // 表格数据列表重新修改的项目
        $this->assign('extra_html',          $this->_extra_html);          // 额外HTML代码
        $this->assign('extra_search',        $this->_extra_search);          // 额外HTML代码
*/
        return '123';
    }
}
