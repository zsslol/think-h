<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 * Date: 2018/12/16
 * Time: 20:42
 */
namespace builder;

class FormBuilder
{
    private $_method = 'post';   //提交方式
    private $_return_button = true;  //显示返回按钮
    private $_meta_title;            // 页面标题
    private $_tab_nav = array();     // 页面Tab导航
    private $_post_url;              // 表单提交地址
    private $_form_items = array();  // 表单项目
    private $_extra_items = array(); // 额外已经构造好的表单项目
    private $_form_data = array();   // 表单数据
    private $_extra_html;            // 额外功能代码
    private $_ajax_submit = true;    // 是否ajax提交

    /**
     * 设置页面标题
     * @param $title 标题文本
     * @return $this
     */
    public function setMetaTitle($meta_title)
    {
        $this->_meta_title = $meta_title;
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.neocean.top')
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab)
    {
        $this->_tab_nav = array('tab_list' => $tab_list, 'current_tab' => $current_tab);
        return $this;
    }

    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items)
    {
        $this->_extra_items = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url)
    {
        $this->_post_url = $post_url;
        return $this;
    }

    /**
     * 加入一个表单项
     * @param $name 表单名
     * @param $type 表单类型(取值参考系统配置FORM_ITEM_TYPE)
     * @param $title 表单标题
     * @param $tip 表单提示说明
     * @param $must 是否必填
     * @param $options 表单options
     * @param $extra_class 表单项是否隐藏
     * @param $extra_attr 表单项额外属性
     * @return $this
     */
    public function addFormItem($name, $type, $title, $tip, $must = false, $options = array(), $extra_class = '', $extra_attr = '')
    {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['must'] = $must;
        $item['options'] = $options;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $this->_form_items[] = $item;
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data)
    {
        $this->_form_data = $form_data;
        return $this;
    }

    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     */
    public function setExtraHtml($extra_html)
    {
        $this->_extra_html = $extra_html;
        return $this;
    }

    /**
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true)
    {
        $this->_ajax_submit = $ajax_submit;
        return $this;
    }

    /**
     * 生成页面数据
     * @return string
     */
    public function display()
    {
        $form_html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>'.$this->_meta_title.'</title>
            <link rel="shortcut icon" href="favicon.ico"> <link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
            <link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">
            <link href="/static/css/plugins/iCheck/custom.css" rel="stylesheet">
            <link href="/static/css/animate.css" rel="stylesheet">
            <link href="/static/css/style.css?v=4.1.0" rel="stylesheet">
            <!-- 全局js -->
            <script src="/static/js/jquery.min.js?v=2.1.4"></script>
            <script src="/static/js/bootstrap.min.js?v=3.3.6"></script>
            <!-- 自定义js -->
            <script src="/static/js/content.js?v=1.0.0"></script>
            <!-- iCheck -->
            <script src="/static/js/plugins/iCheck/icheck.min.js"></script>
        </head>
        <body class="gray-bg">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-sm-12">
                    <div class="ibox float-e-margins">
                    <form action="'.$this->_post_url.'" method="'.$this->_method.'" class="form-horizontal m-t" id="signupForm">
                        <div class="ibox-title">
                            <h5>'.$this->_meta_title.'</h5>
                        </div>
        ';

        if(count($this->_form_items) > 0) {
            $form_html .= '<div class="ibox-content">';
            $form_html .= '<form method="'.$this->_method.'" class="form-horizontal">';

            foreach ($this->_form_items as $items) {
                $type = $items['type'];
                $form_html .= $this->$type($items);
            }

            $form_html .= '
                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-3">
                                    <button class="btn btn-primary" type="button">提交</button>
                                </div>
                            </div>
                        </form>
                        </div>';
        }
        $form_html .= '
                    </div>
                </div>
            </div>
        </div>
            <script type="text/javascript" src="/static/js/formbuilder-validate.js" charset="UTF-8"></script>
            <!--
            <script type="text/javascript" src="" charset="UTF-8"></script>
            统计代码，可删除-->
        </body>
        </html>
        ';
        return $form_html;
    }

    /*
     * 是否必填
     */
    private function getMustHtml($must)
    {
        if($must)return '<span class="must">*</span>';
    }

    /*
     * 元素注释
     */
    private function getTipHtml($tip)
    {
        if(!empty($tip)){
            return '<span class="help-block m-b-none" _title="'.$tip.'">'.$tip.'</span>';
        } else {
            return '<span class="help-block m-b-none" _title="'.$tip.'"></span>';
        }
    }

    /*
     * 非空输出
     */
    private function getNotEmptyVal($val)
    {
        if(!empty($val))return $val;
    }

    /*
     * 文本元素取值
     */
    private function getTextItemValue($name)
    {
        if(!empty($this->_form_data[$name]))return $this->_form_data[$name];
    }

    /*
     * 单选，多选选中控制
     */
    private function getCheckBoxChecked($name, $val, $return ='checked=""'){
        if(!empty($this->_form_data[$name])){
            if(is_array($this->_form_data[$name])){
                if(in_array($val, $this->_form_data[$name]))return $return;
            } else {
                if($val == $this->_form_data[$name])return $return;
            }
        }
    }

    /*
     * 前缀输入
     */
    private function prefixText($item)
    {
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <div class="input-group m-b"><span class="input-group-addon">'.$item['options'].'</span>
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>
                        </div>'.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 后缀输入
     */
    private function suffixText($item)
    {
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <div class="input-group m-b">
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>
                        <span class="input-group-addon">'.$item['options'].'</span>
                        </div>'.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 文本元素
     */
    private function text($item)
    {
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 文本元素
     */
    private function hidden($item)
    {
        return '<div style="display:none;" class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>';
    }

    /*
     * 密码元素
     */
    private function password($item)
    {
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="password" class="formbuilder-input form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 静态显示
     */
    private function static_item($item)
    {
        return  '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <p class="form-control-static">'.$this->getTextItemValue($item['name']).'</p>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 复选框
     */
    private function checkbox($item)
    {
        $html_content = '<div class="form-group form-group-'.$item['name'].'">
                            <label class="col-sm-2 control-label">
                                '.$this->getMustHtml($item['must']).$item['title'].'
                            </label>
                            <div class="col-sm-10">
                                <div class="checkbox i-checks">';

        if(count($item['options']) > 0){
            foreach($item['options'] as $key => $option){
                $class_attr = $this->getCheckBoxChecked($item['name'], $key).' class="formbuilder-checkbox '.$this->getNotEmptyVal($item['extra_class']).'" '.$this->getNotEmptyVal($item['extra_attr']);
                $html_content .= '<label><input name="'.$item['name'].'[]" '.$class_attr.' type="checkbox" value="'.$key.'"> <i></i> '.$option.'</label>';
            }
        }

        $html_content .= '</div>
                            '.$this->getTipHtml($item['tip']).'
                        </div>
                    </div>';
        return $html_content;
    }

    /*
     * 单选框
     */
    private function radio($item)
    {
        $html_content = '<div class="form-group form-group-'.$item['name'].'">
                            <label class="col-sm-2 control-label">
                                '.$this->getMustHtml($item['must']).$item['title'].'
                            </label>
                            <div class="col-sm-10">
                                <div class="radio i-checks">';

        if(count($item['options']) > 0){
            foreach($item['options'] as $key => $option){
                $class_attr = $this->getCheckBoxChecked($item['name'], $key).' class="formbuilder-radio '.$this->getNotEmptyVal($item['extra_class']).'" '.$this->getNotEmptyVal($item['extra_attr']);
                $html_content .= '<label><input name="'.$item['name'].'" '.$class_attr.' type="radio" value="'.$key.'"> <i></i> '.$option.'</label>';
            }
        }

        $html_content .= '</div>
                        '.$this->getTipHtml($item['tip']).'
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>';
        return $html_content;
    }

    /*
     * 下拉列表
     */
    private function select($item)
    {
        $html_content = '<div class="form-group form-group-'.$item['name'].'">
                            <label class="col-sm-2 control-label">
                            '.$this->getMustHtml($item['must']).$item['title'].'
                            </label>
                            <div class="col-sm-10">
                                <select class="formbuilder-select form-control m-b" name="'.$item['name'].'">
                                    <option value="">请选择'.$item['title'].'</option>';

        if(count($item['options']) > 0){
            foreach($item['options'] as $key => $option){
                $html_content .=  '<option '.$this->getCheckBoxChecked($item['name'], $key, 'selected=""').' value="'.$key.'">'.$option.'</option>';
            }
        }
        $html_content .= '</select>
                            '.$this->getTipHtml($item['tip']).'
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>';

        return $html_content;
    }

    /*
     * 文本域
     */
    private function textarea($item)
    {
        $rows = intval($item['options']) > 0 ? $item['options'] : 3;
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <textarea rows="'.$rows.'" name="'.$item['name'].'" class="formbuilder-textarea form-control '.$item['extra_class'].'" '.$item['extra_attr'].'>'.$this->getTextItemValue($item['name']).'</textarea>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 编辑器
     */
    private function editor($item){
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                            <div _name="'.$item['name'].'" class="formbuilder-editor-'.$item['name'].' '.$item['extra_class'].' form-editor formbuilder-editor" '.$item['extra_attr'].'>
                                '.$this->getTextItemValue($item['name']).'
                            </div>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                    <link href="/static/css/plugins/summernote/summernote.css" rel="stylesheet">
                    <link href="/static/css/plugins/summernote/summernote-bs3.css" rel="stylesheet">
                    <script src="/static/js/plugins/summernote/summernote.min.js"></script>
                    <script src="/static/js/plugins/summernote/summernote-zh-CN.js"></script>
                    <script>
                    $(document).ready(function () {
                        $(".formbuilder-editor-'.$item['name'].'").summernote({
                            lang: "zh-CN"
                        });
                    });
                    </script>
                    ';
    }

}