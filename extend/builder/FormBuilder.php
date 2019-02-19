<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 * Date: 2018/12/16
 * Time: 20:42
 */
namespace builder;
use app\Admin\model\Upload;

/**
 * 表单生成器
 */
class FormBuilder
{
    private $_method = 'post';   //提交方式
    private $_meta_title;            // 页面标题
    private $_tab_nav = [];     // 页面Tab导航
    private $_post_url;              // 表单提交地址
    private $_form_items = [];  // 表单项目
    private $_extra_items = []; // 额外已经构造好的表单项目
    private $_form_data = [];   // 表单数据
    private $_extra_html;            // 额外功能代码
    private $_ajax_submit = true;    // 是否ajax提交

    //CSS容器
    private $_css_file_list = [
        '<link rel="shortcut icon" href="favicon.ico">',
        '<link href="/static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">',
        '<link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">',
        '<link href="/static/css/plugins/iCheck/custom.css" rel="stylesheet">',
        '<link href="/static/css/animate.css" rel="stylesheet">',
        '<link href="/static/css/style.css?v=4.1.0" rel="stylesheet">'
    ];
    //JS容器
    private $_js_file_list = [
        '<script src="/static/js/jquery.min.js?v=2.1.4"></script>',
        '<script src="/static/js/bootstrap.min.js?v=3.3.6"></script>',
        '<script src="/static/js/plugins/iCheck/icheck.min.js"></script>',
        '<script src="/static/js/formbuilder-validate.js" charset="UTF-8"></script>'
    ];

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
     * @param $type 表单类型
     * @param $title 表单标题
     * @param $tip 表单提示说明
     * @param $must 是否必填
     * @param $options 表单options
     * @param $extra_class 表单项是否隐藏
     * @param $extra_attr 表单项额外属性
     * @return $this
     */
    public function addFormItem($name, $type, $title, $tip = '', $must = false, $options = [], $extra_class = '', $extra_attr = '')
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

    /*
     * 添加CSS文件
     */
    public function pushCssFile($file)
    {
        if(!in_array($file, $this->_css_file_list))array_push($this->_css_file_list, $file);
        return $this;
    }

    /*
     * 添加JS文件
     */
    public function pushJSFile($file)
    {
        if(!in_array($file, $this->_js_file_list))array_push($this->_js_file_list, $file);
        return $this;
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
        if(isset($this->_form_data[$name]))return $this->_form_data[$name];
    }

    /*
     * 单选，多选选中控制
     */
    private function getCheckBoxChecked($name, $val, $return = 'checked=""'){
        if(isset($this->_form_data[$name])){
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
     * 上传文件
     */
    private function file($item){
        $upload_model = new \app\admin\model\Upload();
        $upload_config = $upload_model->getConfig();
        $html = '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input class="formbuilder-input form-control " value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="hidden" />
                        <div id="uploader-'.$item['name'].'-box" class="wu-example">
                            <div class="btns">
                                <div id="'.$item['name'].'_uploader_file">选择文件</div>
                            </div>
                            <div class="' . $item['name'] . '_uploader-list">';

        if(!empty($this->getTextItemValue($item['name']))) {
            $html .= '<div class="upload-file-item upload-file-'.$this->getTextItemValue($item['name']).'">
                                    <table class="table table-striped">
                                      <tbody>
                                        <tr>
                                          <th>'.Upload::getFileInfo($this->getTextItemValue($item['name'])).'</th>
                                          <td width="150" align="center" class="upload-message">已上传</td>
                                          <td width="50" align="center"><a  _parentClass="upload-file-'.$this->getTextItemValue($item['name']).'" class="remove-file" href="javascript:;">删除</a></td>
                                        </tr>
                                      </tbody>
                                    </table>
                                </div>
                            ';
        }
        $html .= '       </div></div>
                        <div style="width:100%; clear: both;"></div>
                        '.$this->getTipHtml($item['tip'].'&nbsp;&nbsp;&nbsp;&nbsp;支持格式:'.$upload_config['file']['ext']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';

        $js = '
            <script type="text/javascript">
                $(function(){
                    var boxObj = $(".form-group-'.$item['name'].'");
                    var webUploadConfig = {
                        auto: false,
                        duplicate: true,           
                        // swf文件路径
                        swf: \'/static/js/plugins/webuploader/Uploader.swf\',
                        // 文件接收服务端。
                        server: "'.url('Admin/Uploads/uploadFile').'",
                        // 选择文件的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: "#'.$item['name'].'_uploader_file",
                        // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                        resize: false,
                        fileNumLimit: 1,     
                        fileSingleSizeLimit:'.$upload_config['file']['max_size'].'*1024*1024, 
                        formData: { 
                          file_type: "file",
                          guid : WebUploader.Base.guid()
                        }, 
                        // 只允许选择图片文件。
                        accept: {
                            title: "Files",
                            extensions: "'.$upload_config['file']['ext'].'",
                            mimeTypes: "file/*"
                        }
                    };
                    var uploader_'.$item['name'].'_obj;
                    '.$item['name'].'CreateWebUpload(webUploadConfig);
                    function '.$item['name'].'CreateWebUpload(webUploadConfig){
                        uploader_'.$item['name'].'_obj = WebUploader.create(webUploadConfig);
                        // 文件上传过程中创建进度条实时显示。
                        uploader_'.$item['name'].'_obj.on( \'uploadProgress\', function( file, percentage ) {
                            var jindu = percentage * 100;
                            boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'上传中：\'+jindu.toFixed(2)+ \'%\');
                        });
    
                        // 当有文件添加进来的时候
                        uploader_'.$item['name'].'_obj.on(\'fileQueued\', function (file) {
                            boxObj.find(".'.$item['name'].'_uploader-list").html(addFileItem(file));
                            boxObj.find(".remove-file").css("display", "none");
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-file").css("display", "none");
                            uploader_'.$item['name'].'_obj.md5File( file )
                               // 及时显示进度
                               .progress(function(percentage) {
                                //   console.log(\'Percentage:\', percentage);
                                    var at_percentage = Math.floor(percentage * 100);
                                    boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'扫描中，进度:\'+ at_percentage + \'%\');
                                })
                               // 完成
                               .then(function(val) {
                                    uploader_'.$item['name'].'_obj.options.formData.file_md5 = val;  
                                      //检查是否需要上传
                                      boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'文件校验中...\');
                                      $.post("'.url('Admin/Uploads/uploadFile').'", {check_file : 1, md5_val : val}, function(result){
                                            if(result.code == 200){
                                                if(result.data.id == 0){
                                                    boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'上传中...\');
                                                    uploader_'.$item['name'].'_obj.upload();	
                                                } else {
                                                    uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                    boxObj.find(".remove-file").css("display", "");
                                                    boxObj.children(".col-sm-10").children("input[name='.$item['name'].']").val(result.data.id);
                                                    boxObj.find(".form-group-'.$item['name'].'-file").attr(\'src\', result.data.img_url);
                                                    boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'秒传成功！\');
                                                }
                                            } else {
                                                uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                boxObj.find(".remove-file").css("display", "");
                                                boxObj.find(".upload-file-"+file.id).find(".upload-message").html(result.msg);
                                                parent.layer.alert(result.msg); 
                                                return false;
                                            }
                                      }, \'JSON\').error(function(){
                                        uploader_'.$item['name'].'_obj.removeFile( file, true );
                                        boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'校验失败！请检查网络\');
                                        boxObj.find(".remove-file").css("display", "");
                                        parent.layer.alert(file.name+"校验失败，请检查网络！");
                                    });
                               });
                        });
    
                        // 完成上传完了，成功或者失败，先删除进度条。
                        uploader_'.$item['name'].'_obj.on( \'uploadComplete\', function( file ) {
                            boxObj.find(".progress").css("display", "none");
                            boxObj.find(".remove-file").css("display", "");
    //                        boxObj.find(".upload-file-"+file.id).find(".upload-message").remove();
                        });
    
                        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
                        uploader_'.$item['name'].'_obj.on( \'uploadSuccess\', function( file , response) {
                            boxObj.find(".remove-file").css("display", "");
                            boxObj.find(".progress").css("display", "none");
                            if(response.code == 200){
                                boxObj.find(".upload-file-"+file.id).find(".upload-message").html(\'上传成功!\');
                                boxObj.children(".col-sm-10").children("input[name='.$item['name'].']").val(response.data.id);
                                uploader_'.$item['name'].'_obj.destroy();
                                '.$item['name'].'CreateWebUpload(webUploadConfig);
                            } else {
                                parent.layer.alert(file.name+"-"+response.msg);
                            }
                        });
    
                        // 文件上传失败，显示上传出错。
                        uploader_'.$item['name'].'_obj.on( \'uploadError\', function( file , response) {
                            boxObj.find(".remove-file").css("display", "");
                            parent.layer.alert(\'上传失败\');
                        });
    
                        // 删除文件
                        $(document).on("click", ".form-group-'.$item['name'].' .remove-file", function() {
                            var thisItem = $(this);
                            var layerConfirm = parent.layer.confirm(\'你要删除该文件吗？\', {
                              btn: [\'确定\',\'取消\'] //按钮
                            }, function(){
                                boxObj.find("input[name='.$item['name'].']").val("");
                                boxObj.find("."+thisItem.attr("_parentClass")).remove();
                                parent.layer.close(layerConfirm);
                            }, function(){
                              
                            });
                        });
                    }
                    
                    $(".formbuilder-group-form-nav li").click(function(){
                       uploader_'.$item['name'].'_obj.destroy();
                        '.$item['name'].'CreateWebUpload(webUploadConfig);
                    });
                    
                    function addFileItem(file){
                        var itemHtml = \'<div class="upload-file-\'+file.id+\' upload-file-item">\';
                        itemHtml += \'	<table class="table table-striped">\';
                        itemHtml += \'	  <tbody>\';
                        itemHtml += \'		<tr>\';
                        itemHtml += \'		  <th>\'+file.name+\'</th>\';
                        itemHtml += \'		  <td width="150" align="center" class="upload-message">已上传</td>\';
                        itemHtml += \'		  <td width="50" align="center"><a class="remove-file" _parentClass="upload-file-\'+file.id+\'" href="javascript:;">删除</a></td>\';
                        itemHtml += \'		</tr>\';
                        itemHtml += \'	  </tbody>\';
                        itemHtml += \'	</table>\';
                        itemHtml += \'</div>\';
                        return itemHtml;
                    }
                });
            </script>
        ';
        $this->pushCssFile('<link rel="stylesheet" type="text/css" href="/static/js/plugins/webuploader/webuploader.css">');
        $this->pushCssFile('<link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/animate.css" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/style.css?v=4.1.0" rel="stylesheet">');
        $this->pushJSFile('<script src="/static/js/plugins/webuploader/webuploader.nolog.min.js"></script>');
        $this->pushJSFile($js);
        return $html;
    }
    /*
     * 上传图片
     */
    private function picture($item){
        $upload_model = new \app\admin\model\Upload();
        $upload_config = $upload_model->getConfig();
        $html = '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input class="formbuilder-input form-control " value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="hidden" />
                        <div id="uploader-'.$item['name'].'-box" class="wu-example">
                            <div class="btns">
                                <div id="'.$item['name'].'_uploader_picture">选择图片</div>
                            </div>
                            <div class="' . $item['name'] . '_uploader-list">';

        if(!empty($this->getTextItemValue($item['name']))) {
            $html .= '      
                                <div class="upload-picture-item">
                                    <img class="form-group-' . $item['name'] . '-picture" src="' . Upload::getFileUrl($this->getTextItemValue($item['name'])) . '" />
                                    <a class="remove-picture" href="javascript:;"><i class="glyphicon glyphicon-remove-sign"></i></a>
                                    <div class="infos">
                                        <div class="progress progress-striped active">
                                            <div style="width: 0%" class="progress-bar progress-bar-success">
                                                <span class="sr-only"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ';
        }
        $html .= '       </div></div>
                        <div style="width:100%; clear: both;"></div>
                        '.$this->getTipHtml($item['tip'].'&nbsp;&nbsp;&nbsp;&nbsp;支持格式:'.$upload_config['image']['ext']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';

        $js = '
            <script type="text/javascript">
                $(function(){
                    var boxObj = $(".form-group-'.$item['name'].'");
                    var webUploadConfig = {
                        auto: false,
                        duplicate: true,           
                        // swf文件路径
                        swf: \'/static/js/plugins/webuploader/Uploader.swf\',
                        // 文件接收服务端。
                        server: "'.url('Admin/Uploads/uploadFile').'",
                        // 选择文件的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: "#'.$item['name'].'_uploader_picture",
                        // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                        resize: false,
                        fileNumLimit: 1,     
                        fileSingleSizeLimit:'.$upload_config['image']['max_size'].'*1024*1024, 
                        formData: { 
                          file_type: "image",
                          guid : WebUploader.Base.guid()
                        }, 
                        // 只允许选择图片文件。
                        accept: {
                            title: "Images",
                            extensions: "'.$upload_config['image']['ext'].'",
                            mimeTypes: "image/*"
                        }
                    };
                    var uploader_'.$item['name'].'_obj;
                    '.$item['name'].'CreateWebUpload(webUploadConfig);
                    function '.$item['name'].'CreateWebUpload(webUploadConfig){
                        uploader_'.$item['name'].'_obj = WebUploader.create(webUploadConfig);
                        // 文件上传过程中创建进度条实时显示。
                        uploader_'.$item['name'].'_obj.on( \'uploadProgress\', function( file, percentage ) {
                            boxObj.find(".progress").css("display", "block");
                            boxObj.find(".progress").find(".progress-bar-success").css( \'width\', percentage * 100 + \'%\' );
                        });
    
                        // 当有文件添加进来的时候
                        uploader_'.$item['name'].'_obj.on(\'fileQueued\', function (file) {
                            // 创建缩略图
                            uploader_'.$item['name'].'_obj.makeThumb(file, function (error, src) {
                                if (error) {
                                    parent.layer.alert("请上传图片文件!");
                                    return;
                                } else {
                                    boxObj.find(".'.$item['name'].'_uploader-list").html(addPictureItem(file));
                                    boxObj.find(".form-group-'.$item['name'].'-picture").attr(\'src\', src);
                                    boxObj.find(".remove-picture").css("display", "none");
                                }
                                
                            }, 200, 200);
                            
                            uploader_'.$item['name'].'_obj.md5File( file )
                               // 及时显示进度
                               .progress(function(percentage) {
                                //   console.log(\'Percentage:\', percentage);
                                    var at_percentage = Math.floor(percentage * 100);
                                    boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'文件扫描中，扫描进度:\'+ at_percentage + \'%\');
                                })
                               // 完成
                               .then(function(val) {
                                    uploader_'.$item['name'].'_obj.options.formData.file_md5 = val;  
                                      //检查是否需要上传
                                      boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'文件校验中...\');
                                      $.post("'.url('Admin/Uploads/uploadFile').'", {check_file : 1, md5_val : val}, function(result){
                                            if(result.code == 200){
                                                if(result.data.id == 0){
                                                    boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'上传中...\');
                                                    uploader_'.$item['name'].'_obj.upload();	
                                                } else {
                                                    uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                    boxObj.find(".remove-picture").css("display", "");
                                                    $("input[name='.$item['name'].']").val(result.data.id);
                                                    addPictureItem(result.data);
                                                    boxObj.find(".form-group-'.$item['name'].'-picture").attr(\'src\', result.data.img_url);
                                                    boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'秒传成功！\');
                                                    
                                                    uploader_'.$item['name'].'_obj.destroy();
                                                    '.$item['name'].'CreateWebUpload(webUploadConfig);
                                                }
                                            } else {
                                                uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                boxObj.find(".remove-picture").css("display", "");
                                                boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(result.msg);
                                                parent.layer.alert(result.msg); 
                                                return false;
                                            }
                                      }, \'JSON\').error(function(){
                                        uploader_'.$item['name'].'_obj.removeFile( file, true );
                                        boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'校验失败！请检查网络\');
                                        boxObj.find(".remove-picture").css("display", "");
                                        parent.layer.alert(file.name+"校验失败，请检查网络！");
                                    });
                               });
                        });
    
                        // 完成上传完了，成功或者失败，先删除进度条。
                        uploader_'.$item['name'].'_obj.on( \'uploadComplete\', function( file ) {
                            boxObj.find(".progress").css("display", "none");
                            boxObj.find(".remove-picture").css("display", "");
    //                        boxObj.find(".upload-picture-"+file.id).find(".upload-message").remove();
                        });
    
                        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
                        uploader_'.$item['name'].'_obj.on( \'uploadSuccess\', function( file , response) {
                            boxObj.find(".remove-picture").css("display", "");
                            boxObj.find(".progress").css("display", "none");
                            if(response.code == 200){
                                boxObj.find(".upload-picture-"+file.id).find(".upload-message").html(\'上传成功!\');
                                $("input[name='.$item['name'].']").val(response.data.id);
                                boxObj.find(".form-group-'.$item['name'].'-picture").attr(\'src\', response.data.img_url);
                            } else {
                                parent.layer.alert(file.name+"-"+response.msg);
                            }
                            uploader_'.$item['name'].'_obj.destroy();
                            '.$item['name'].'CreateWebUpload(webUploadConfig);
                        });
    
                        // 文件上传失败，显示上传出错。
                        uploader_'.$item['name'].'_obj.on( \'uploadError\', function( file , response) {
                            boxObj.find(".remove-picture").css("display", "");
                            parent.layer.alert(\'上传失败\');
                            uploader_'.$item['name'].'_obj.destroy();
                            '.$item['name'].'CreateWebUpload(webUploadConfig);
                        });
    
                        // 删除图片
                        $(document).on("click", ".form-group-'.$item['name'].' .remove-picture", function() {
                            var thisItem = $(this);
                            var layerConfirm = parent.layer.confirm(\'你要删除该图片吗？\', {
                              btn: [\'确定\',\'取消\'] //按钮
                            }, function(){
                                boxObj.find("input[name='.$item['name'].']").val("");
                                thisItem.parent().remove();
                                parent.layer.close(layerConfirm);
                            }, function(){
                              
                            });
                        });
                    }
                    $(".formbuilder-group-form-nav li").click(function(){
                       uploader_'.$item['name'].'_obj.destroy();
                        '.$item['name'].'CreateWebUpload(webUploadConfig);
                    });
                     
                    function addPictureItem(file){
                        var itemHtml = \'<div class="upload-picture-\'+file.id+\' upload-picture-item">\';
                        if(typeof(file.img_url) != "undefined"){
                            itemHtml += \'<img class="form-group-' . $item['name'] . '-picture" src="\'+file.img_url+\'" />\';
                        } else {
                            itemHtml += \'<img class="form-group-' . $item['name'] . '-picture" src="" />\';    
                        }
                        itemHtml += \'            <a class="remove-picture" href="javascript:;"><i class="glyphicon glyphicon-remove-sign"></i></a>\';
                        itemHtml += \'            <div class="infos">\';
                        itemHtml += \'                  <div class="upload-message"></div>\';
                        itemHtml += \'                <div class="progress progress-striped active">\';
                        itemHtml += \'                    <div style="width: 0%" class="progress-bar progress-bar-success">\';
                        itemHtml += \'                        <span class="sr-only"></span>\';
                        itemHtml += \'                    </div>\';
                        itemHtml += \'                </div>\';
                        itemHtml += \'            </div>\';
                        itemHtml += \'        </div>\';
                        return itemHtml;
                    }
                });
            </script>
        ';
        $this->pushCssFile('<link rel="stylesheet" type="text/css" href="/static/js/plugins/webuploader/webuploader.css">');
        $this->pushCssFile('<link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/animate.css" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/style.css?v=4.1.0" rel="stylesheet">');
        $this->pushJSFile('<script src="/static/js/plugins/webuploader/webuploader.nolog.min.js"></script>');
        $this->pushJSFile($js);
        return $html;
    }
    /*
     * 上传多图
     */
    private function pictures($item){
        $upload_model = new \app\admin\model\Upload();
        $upload_config = $upload_model->getConfig();
        $html = '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input class="formbuilder-input form-control " value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="hidden" />
                        <div id="uploader-'.$item['name'].'-box" class="wu-example">
                            <div class="btns">
                                <div id="'.$item['name'].'_uploader_pictures">选择图片</div>
                            </div>
                            <div class="' . $item['name'] . '_uploader-list">';

        if(!empty($this->getTextItemValue($item['name']))) {
            $picture_ids = explode(',', $this->getTextItemValue($item['name']));
            if(is_array($picture_ids) && count($picture_ids) > 0){
                foreach($picture_ids as $picture_id) {
                    $html .= '      
                                <div class="upload-pictures-item">
                                    <img class="form-group-' . $item['name'] . '-picture" src="'.Upload::getFileUrl($picture_id).'" />
                                    <a class="remove-picture" picture_id="'.$picture_id.'" href="javascript:;"><i class="glyphicon glyphicon-remove-sign"></i></a>
                                    <div class="infos">
                                        <div class="progress progress-striped active">
                                            <div style="width: 0%" class="progress-bar progress-bar-success">
                                                <span class="sr-only"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ';
                }
            }
        }
        $html .= '       </div></div>
                        <div style="width:100%; clear: both;"></div>
                        '.$this->getTipHtml($item['tip'].'&nbsp;&nbsp;&nbsp;&nbsp;支持格式:'.$upload_config['image']['ext']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';

        $js = '
            <script type="text/javascript">
                $(function(){
                    var boxObj = $(".form-group-'.$item['name'].'");
                    var webUploadConfig = {
                        auto: false,
                        duplicate: true,           
                        // swf文件路径
                        swf: \'/static/js/plugins/webuploader/Uploader.swf\',
                        // 文件接收服务端。
                        server: "'.url('Admin/Uploads/uploadFile').'",
                        // 选择文件的按钮。可选。
                        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                        pick: "#'.$item['name'].'_uploader_pictures",
                        // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                        resize: false,
                        fileNumLimit: 1,     
                        fileSingleSizeLimit:'.$upload_config['image']['max_size'].'*1024*1024, 
                        formData: { 
                          file_type: "image",
                          guid : WebUploader.Base.guid()
                        }, 
                        // 只允许选择图片文件。
                        accept: {
                            title: "Images",
                            extensions: "'.$upload_config['image']['ext'].'",
                            mimeTypes: "image/*"
                        }
                    };
                    var uploader_'.$item['name'].'_obj;
                    '.$item['name'].'CreateWebUpload(webUploadConfig);
                    function '.$item['name'].'CreateWebUpload(webUploadConfig){
                        uploader_'.$item['name'].'_obj = WebUploader.create(webUploadConfig);
                        // 文件上传过程中创建进度条实时显示。
                        uploader_'.$item['name'].'_obj.on( \'uploadProgress\', function( file, percentage ) {
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".progress").css("display", "block");
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".progress").find(".progress-bar-success").css( \'width\', percentage * 100 + \'%\' );
                        });
    
                        // 当有文件添加进来的时候
                        uploader_'.$item['name'].'_obj.on(\'fileQueued\', function (file) {
                            // 创建缩略图
                            uploader_'.$item['name'].'_obj.makeThumb(file, function (error, src) {
                                if (error) {
                                    parent.layer.alert("请上传图片文件!");
                                    return;
                                } else {
                                    boxObj.find(".'.$item['name'].'_uploader-list").append(addPictureItem(file));
                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".form-group-'.$item['name'].'-picture").attr(\'src\', src);
                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "none");
                                }
                                
                            }, 200, 200);
                            
                            uploader_'.$item['name'].'_obj.md5File( file )
                               // 及时显示进度
                               .progress(function(percentage) {
                                //   console.log(\'Percentage:\', percentage);
                                    var at_percentage = Math.floor(percentage * 100);
                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-pictures-"+file.id).find(".upload-message").html(\'文件扫描中，扫描进度:\'+ at_percentage + \'%\');
                                })
                               // 完成
                               .then(function(val) {
                                    uploader_'.$item['name'].'_obj.options.formData.file_md5 = val;  
                                      //检查是否需要上传
                                      boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-message").html(\'文件校验中...\');
                                      $.post("'.url('Admin/Uploads/uploadFile').'", {check_file : 1, md5_val : val}, function(result){
                                            if(result.code == 200){
                                                if(result.data.id == 0){
                                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-message").html(\'上传中...\');
                                                    uploader_'.$item['name'].'_obj.upload();	
                                                } else {
                                                    uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                    boxObj.find(".remove-picture").css("display", "");
                                                    pictureIds(result.data.id);
                                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".form-group-'.$item['name'].'-picture").attr(\'src\', result.data.img_url);
                                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").attr(\'picture_id\', result.data.id);
                                                    boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-message").html(\'秒传成功！\');
                                                }
                                            } else {
                                                uploader_'.$item['name'].'_obj.removeFile( file, true );
                                                boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "");
                                                boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-pictures-"+file.id).find(".upload-message").html(result.msg);
                                                parent.layer.alert(result.msg); 
                                                return false;
                                            }
                                      }, \'JSON\').error(function(){
                                        uploader_'.$item['name'].'_obj.removeFile( file, true );
                                        boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-pictures-"+file.id).find(".upload-message").html(\'校验失败！请检查网络\');
                                        boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "");
                                        parent.layer.alert(file.name+"校验失败，请检查网络！");
                                    });
                               });
                        });
    
                        // 完成上传完了，成功或者失败，先删除进度条。
                        uploader_'.$item['name'].'_obj.on( \'uploadComplete\', function( file ) {
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".progress").css("display", "none");
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "");
    //                        boxObj.find(".upload-pictures-"+file.id).find(".upload-message").remove();
                        });
    
                        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
                        uploader_'.$item['name'].'_obj.on( \'uploadSuccess\', function( file , response) {
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "");
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".progress").css("display", "none");
                            if(response.code == 200){
                                boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".upload-message").html(\'上传成功!\');
                                boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".form-group-'.$item['name'].'-picture").attr(\'src\', response.data.img_url);
                                boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").attr(\'picture_id\', response.data.id);
                                pictureIds(response.data.id);
                            } else {
                                parent.layer.alert(file.name+"-"+response.msg);
                            }
                            uploader_'.$item['name'].'_obj.destroy();
                            '.$item['name'].'CreateWebUpload(webUploadConfig);
                        });
    
                        // 文件上传失败，显示上传出错。
                        uploader_'.$item['name'].'_obj.on( \'uploadError\', function( file , response) {
                            boxObj.find(".upload-pictures-'.$item['name'].'-"+file.id).find(".remove-picture").css("display", "");
                            parent.layer.alert(\'上传失败\');
                            uploader_'.$item['name'].'_obj.destroy();
                            '.$item['name'].'CreateWebUpload(webUploadConfig);
                        });
    
                        // 删除图片
                        $(document).on("click", ".form-group-'.$item['name'].' .remove-picture", function() {
                            var thisItem = $(this);
                            var layerConfirm = parent.layer.confirm(\'你要删除该图片吗？\', {
                              btn: [\'确定\',\'取消\'] //按钮
                            }, function(){
                                pictureIds(thisItem.attr("picture_id"));
                                thisItem.parent().remove();
                                parent.layer.close(layerConfirm);
                            }, function(){
                              
                            });
                        });
                    }
                    
                    $(".formbuilder-group-form-nav li").click(function(){
                       uploader_'.$item['name'].'_obj.destroy();
                        '.$item['name'].'CreateWebUpload(webUploadConfig);
                    });
                    
                    function pictureIds(val){
                        var pictureIds = $("input[name='.$item['name'].']").val();
                        if(pictureIds == ""){
                            var ids = new Array();
                        } else {
                            var ids = pictureIds.split(",");
                        }
                        var index = ids.indexOf(val);
                        if (index > -1) {
                            ids.splice(index, 1);
                        } else {
                            ids.push(val);
                        }
                        $("input[name='.$item['name'].']").val(ids.join(\',\'));
                    }
                    
                    function addPictureItem(file){
                        var itemHtml = \'<div class="upload-pictures-'.$item['name'].'-\'+file.id+\' upload-pictures-item">\';
                        if(typeof(file.img_url) != "undefined"){
                            itemHtml += \'<img class="form-group-' . $item['name'] . '-picture" src="\'+file.img_url+\'" />\';
                        } else {
                            itemHtml += \'<img class="form-group-' . $item['name'] . '-picture" src="" />\';    
                        }
                        itemHtml += \'            <a class="remove-picture" href="javascript:;"><i class="glyphicon glyphicon-remove-sign"></i></a>\';
                        itemHtml += \'            <div class="infos">\';
                        itemHtml += \'                  <div class="upload-message"></div>\';
                        itemHtml += \'                <div class="progress progress-striped active">\';
                        itemHtml += \'                    <div style="width: 0%" class="progress-bar progress-bar-success">\';
                        itemHtml += \'                        <span class="sr-only"></span>\';
                        itemHtml += \'                    </div>\';
                        itemHtml += \'                </div>\';
                        itemHtml += \'            </div>\';
                        itemHtml += \'        </div>\';
                        return itemHtml;
                    }
                });
            </script>
        ';
        $this->pushCssFile('<link rel="stylesheet" type="text/css" href="/static/js/plugins/webuploader/webuploader.css">');
        $this->pushCssFile('<link href="/static/css/font-awesome.css?v=4.4.0" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/animate.css" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/style.css?v=4.1.0" rel="stylesheet">');
        $this->pushJSFile('<script src="/static/js/plugins/webuploader/webuploader.nolog.min.js"></script>');
        $this->pushJSFile($js);
        return $html;
    }

    /*
     * 数字
     */
    private function num($item)
    {
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input value="'.$this->getTextItemValue($item['name']).'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control '.$item['extra_class'].'" data-type="number" '.$item['extra_attr'].'>
                        '.$this->getTipHtml($item['tip']).'
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
     * 隐藏文本元素
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
                    </div>
                    <div class="hr-line-dashed"></div>';
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
                if(is_array($option)){
                    $html_content .= '<option '.$option['disabled'].' ' . $this->getCheckBoxChecked($item['name'], $key, 'selected=""') . ' value="' . $key . '">' . $option['title'] . '</option>';
                } else {
                    $html_content .= '<option ' . $this->getCheckBoxChecked($item['name'], $key, 'selected=""') . ' value="' . $key . '">' . $option . '</option>';
                }
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
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <textarea name="'.$item['name'].'" class="formbuilder-textarea form-control '.$item['extra_class'].'" '.$item['extra_attr'].'  rows="3">'.$this->getTextItemValue($item['name']).'</textarea>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 编辑器
     */
    private function editor($item){
        $this->pushCssFile('<link href="/static/css/plugins/summernote/summernote.css" rel="stylesheet">');
        $this->pushCssFile('<link href="/static/css/plugins/summernote/summernote-bs3.css" rel="stylesheet">');
        $this->pushJSFile('<script src="/static/js/plugins/summernote/summernote.min.js"></script>');
        $this->pushJSFile('<script src="/static/js/plugins/summernote/summernote-zh-CN.js"></script>');
        $js = '<script>
                    $(document).ready(function () {
                        $(".formbuilder-editor-'.$item['name'].'").summernote({
                            lang: "zh-CN"
                        });
                    });
                    </script>';
        $this->pushJSFile($js);
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                            <div _name="'.$item['name'].'" class="formbuilder-editor-'.$item['name'].' '.$item['extra_class'].' form-editor formbuilder-editor" '.$item['extra_attr'].'>
                                '.$this->getTextItemValue($item['name']).'
                            </div>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                 <div class="hr-line-dashed"></div>';
    }

    /*
     * 时间选择器
     */
    private function datetime($item)
    {
        $this->pushJSFile('<script src="/static/js/plugins/layer/laydate/laydate.js"></script>');
        $format = empty($item['extra_attr']) ? 'YYYY-MM-DD' : $item['extra_attr'];
        return '<div class="form-group form-group-'.$item['name'].'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input readonly onclick="laydate({istime: true, format: \''.$format.'\'})" value="'.$this->getTextItemValue($item['name']).'" id="layer-date-'.$item['name'].'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control layer-date '.$item['extra_class'].'" >
                        <label onclick="laydate({elem:\'#layer-date-'.$item['name'].'\', istime: true, format: \''.$format.'\'});" class="laydate-icon"></label>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
    }

    /*
     * 时间段选择器
     */
    private function datetimes($item)
    {
        $end_time_name = empty($item['options']) ? $item['name'].'_endtime' : $item['options'];
        $format = empty($item['extra_attr']) ? 'YYYY-MM-DD' : $item['extra_attr'];

        $this->pushJSFile('<script src="/static/js/plugins/layer/laydate/laydate.js"></script>');
        $js = "
        <script>
        //日期范围限制
        var start = {
            elem: '#layer-date-{$item['name']}',
            format: '{$format}',
            //min: laydate.now(), //设定最小日期为当前日期
            max: '2099-06-16 23:59:59', //最大日期
            istime: true,
            istoday: false,
            choose: function (datas) {
                end.min = datas; //开始日选好后，重置结束日的最小日期
                end.start = datas //将结束日的初始值设定为开始日
            }
        };
        var end = {
            elem: '#layer-date-{$end_time_name}',
            format: '{$format}',
            //min: laydate.now(),
            max: '2099-06-16 23:59:59',
            istime: true,
            istoday: false,
            choose: function (datas) {
                start.max = datas; //结束日选好后，重置开始日的最大日期
            }
        };
        laydate(start);
        laydate(end);
        </script>";
        $this->pushJSFile($js);

        return '<div class="form-group form-group-'.$item['name'].'  form-group-'.$end_time_name.'">
                    <label class="col-sm-2 control-label">'.$this->getMustHtml($item['must']).$item['title'].'</label>
                    <div class="col-sm-10">
                        <input placeholder="开始日期" readonly value="'.$this->getTextItemValue($item['name']).'" id="layer-date-'.$item['name'].'" name="'.$item['name'].'" type="text"  class="formbuilder-input form-control layer-date '.$item['extra_class'].'" >
                        <label class="laydate-icon inline demoicon"></label>
                        <input placeholder="结束日期" readonly value="'.$this->getTextItemValue($end_time_name).'" id="layer-date-'.$end_time_name.'" name="'.$end_time_name.'" type="text"  class="formbuilder-input form-control layer-date '.$item['extra_class'].'" >
                        <label class="laydate-icon inline demoicon"></label>
                        '.$this->getTipHtml($item['tip']).'
                    </div>
                </div>
                <div class="hr-line-dashed"></div>';
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
                .wrapper {padding:0;}
                .wrapper-content {padding:0;}
                .m-t {margin-top: 0;}
                .ibox {margin-bottom: 0;}
                .checkbox label, .radio label{padding-left: 0px; padding-right: 20px;}
            </style>
        </head>
        <body class="gray-bg">
        <input type="hidden" name="submit_pre" />
        <div id="submit_pre_box" style="display: none;"></div>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-sm-12">
                    <div class="ibox float-e-margins">
                    <form ajax-submit="'.$this->_ajax_submit.'" action="'.$this->_post_url.'" method="'.$this->_method.'" class="form-horizontal m-t" id="signupForm">
                        <div class="ibox-title">
                            <h5>'.$this->_meta_title.'</h5>
                        </div>
        ';
    }

    /*
     * 获取底部数据
     */
    private function getBottomHtmlContent()
    {
        return '</form></div>
                </div>
            </div>
        </div>
            '.implode('', $this->_js_file_list).'
        </body>
        </html>
        ';
    }

    /**
     * 生成页面数据
     * @return string
     */
    public function display()
    {
        $form_html = '';
        if(count($this->_form_items) > 0) {
            $form_html .= '<div class="ibox-content">';

            foreach ($this->_form_items as $items) {
                $type = $items['type'];
                $form_html .= $this->$type($items);
            }
            $form_html .= $this->_extra_html;
            $form_html .= '<div class="form-group">
                                <div class="col-sm-8 col-sm-offset-3">
                                    <button class="btn btn-primary btn-form-submit" type="button">提　交</button>
                                    <div style="margin:0; display: none;" class="sk-spinner sk-spinner-wave">
                                        <div class="sk-rect1"></div>
                                        <div class="sk-rect2"></div>
                                        <div class="sk-rect3"></div>
                                        <div class="sk-rect4"></div>
                                        <div class="sk-rect5"></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
        }

        return $this->getTopHtmlContent().$form_html.$this->getBottomHtmlContent();
    }

    /**
     * 生成多表单页
     * @return string
     */
    public function display_group_form($form_list)
    {
        $form_html = '';

        if(count($form_list)> 0){
            $form_html .= '<div class="tabs-container">';
            $key = 0;
            $form_html .= '<ul class="nav nav-tabs formbuilder-group-form-nav">';
            foreach($form_list as $form_key => $form){
                if ($key == 0) {
                    $form_html .= '<li class="active"><a data-toggle="tab" href="#tab-' . $form_key . '" aria-expanded="true">' . $form['title'] . '</a></li>';
                } else {
                    $form_html .= '<li class=""><a data-toggle="tab" href="#tab-' . $form_key . '" aria-expanded="false">' . $form['title'] . '</a></li>';
                }
                $key++;
            }
            $form_html .= '</ul>';

            $key = 0;
            $form_html .= '<div class="tab-content">';
            foreach($form_list as $form_key => $form) {
                /*
                if($key == 0) {
                    $form_html .= ' <div id="tab-' . $form_key . '" class="tab-pane active">';
                } else {
                    $form_html .= ' <div id="tab-' . $form_key . '" class="tab-pane active">';
                }
                */
                $form_html .= ' <div id="tab-' . $form_key . '" class="tab-pane active">';
                $form_html .= '<div class="ibox-content">';
                foreach ($form['item_list'] as $item) {
                    $this->_form_data[$item['name']] = $item['value'];
                    $type = $item['type'];
                    $form_html .= $this->$type($item);
                }
                $form_html .= '</div>';
                $form_html .= '</div>';
                $key++;
            }
            $form_html .= '</div>';

            $form_html .= '</div>';
        }
        $form_html .= $this->_extra_html;
        $form_html .= '<div class="ibox-content">';
        $form_html .= '<div class="form-group">
                                <div class="col-sm-8 col-sm-offset-3">
                                    <button class="btn btn-primary btn-form-submit" type="button">提　交</button>
                                    <div style="margin:0; display: none;" class="sk-spinner sk-spinner-wave">
                                        <div class="sk-rect1"></div>
                                        <div class="sk-rect2"></div>
                                        <div class="sk-rect3"></div>
                                        <div class="sk-rect4"></div>
                                        <div class="sk-rect5"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>';
        $form_js = '
            <script>
                $(function(){
                    $(".tab-content").children(".tab-pane").removeClass("active");
                    $(".tab-content").children(".tab-pane:first-child").addClass("active");
                });
            </script>
        ';
        $this->pushJSFile($form_js);
        return $this->getTopHtmlContent().$form_html.$this->getBottomHtmlContent();
    }
}