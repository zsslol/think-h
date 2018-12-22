$(document).ready(function () {
    var layer_index = parent.layer.getFrameIndex(window.name);
    parent.layer.iframeAuto(layer_index);
	$(".i-checks").iCheck({
		checkboxClass: "icheckbox_square-green",
		radioClass: "iradio_square-green",
	});

	//提交按钮点击事件
	$(".btn-form-submit").click(function(){
        validateForm('.ibox-content');
	});

	//验证完整表单
	function validateForm(iboxClass) {
        var submitSuccess = true;
        var data = {};
        var iboxContent = $(iboxClass);

        //验证输入框元素
        iboxContent.find(".formbuilder-input").each(function(){
            var thisObj = $(this);
            var value = $.trim(thisObj.val());
            if(validateField(thisObj.attr("name"), value, thisObj )){
                data[thisObj.attr("name")] = value;
                thisObj.val(value);
            } else {
                submitSuccess = false;
            }
        });

        //验证下拉列表元素
        iboxContent.find(".formbuilder-select").each(function(){
            var thisObj = $(this);
            if(validateField(thisObj.attr("name"), thisObj.val())){
                data[thisObj.attr("name")] = thisObj.val();
            } else {
                submitSuccess = false;
            }
        });

        //验证单选元素
        iboxContent.find(".formbuilder-radio").each(function(){
            var thisObj = $(this);
            var value = $("input[type='"+thisObj.attr("name")+"']:checked").val();
            if(validateField(thisObj.attr("name"), value)){
                data[thisObj.attr("name")] = value;
            } else {
                submitSuccess = false;
            }
        });

        //验证多选元素
        iboxContent.find(".formbuilder-checkbox").each(function(){
            var thisObj = $(this);
            data[thisObj.attr("name")] = [];
            $('input[name="'+thisObj.attr("name")+'"]:checked').each(function(){
                data[thisObj.attr("name")].push($(this).val());
            });

            var name = thisObj.attr("name");
            name = name.substr(0, name.length - 2);
            if(validateField(name, data[thisObj.attr("name")]) == false){
                submitSuccess = false;
            }
        });

        //验证文本域元素
        iboxContent.find(".formbuilder-textarea").each(function(){
            var thisObj = $(this);
            if(validateField(thisObj.attr("name"), thisObj.val())){
                data[thisObj.attr("name")] = thisObj.val();
            } else {
                submitSuccess = false;
            }
        });

        //验证富文本编辑器元素
        iboxContent.find(".formbuilder-editor").each(function(){
            var thisObj = $(this);
            var value = $('.formbuilder-editor-'+thisObj.attr("_name")).code();
            if(validateField(thisObj.attr("_name"), value)){
                data[thisObj.attr("_name")] = value;
            } else {
                submitSuccess = false;
            }
        });

        if(submitSuccess == false)return false;

        var formObj = $('.form-horizontal');

        var ajaxSubmit = formObj.attr('ajax-submit');
        var ajaxUrl = formObj.attr('action');
        //控制提交按钮状态，防止重复提交
        var submitObj = iboxContent.find('.btn-form-submit');
        var waveObj = iboxContent.find('.sk-spinner-wave');

        submitObj.css('display', 'none');
        waveObj.css('display', 'block');

        if(ajaxSubmit != '1'){
            formObj.submit();
            return false;
        }

        parent.toastr.info('提交中，请等待...');
        $.post(ajaxUrl, data, function(result) {
            submitObj.css('display', 'block');
            waveObj.css('display', 'none');

            parent.tableDataRefresh();
            parent.toastr.clear();
            parent.toastr.success('提交成功');
            parent.layer.close(layer_index);
        }, 'JSON').error(function(){
            submitObj.css('display', 'block');
            waveObj.css('display', 'none');
            parent.toastr.error('提交失败，请重试');
        });
    }

    //验证单一字段
    function validateField(name, val, InputObj){
        var formGroupObj = $('.form-group-'+name);
        var tipsObj = formGroupObj.find('.help-block');
        if(formGroupObj.find('.must').length > 0 && ( val == '' || typeof (val) == "undefined") ){
            // formGroupObj.addClass('has-warning');
            formGroupObj.addClass('has-error');
            tipsObj.html('此项为必须');
            return false;
        } else {
            if(typeof(InputObj) != 'undefined'){
                var validateResult = validateInputDataType(InputObj);
                if(validateResult !== true){
                    formGroupObj.addClass('has-error');
                    tipsObj.html(validateResult);
                    return false;
                }
            }
            formGroupObj.removeClass('has-warning');
            formGroupObj.removeClass('has-error');
            //formGroupObj.addClass('has-success');
            tipsObj.html(tipsObj.attr('_title'));
            return true;
        }
        return true;
    }

    //验证文本框数据类型匹配
    function validateInputDataType(InputObj){
        var dataType = InputObj.attr('data-type');
        var minLength = InputObj.attr('min-length');
        var maxLength = InputObj.attr('max-length');
        var val = $.trim(InputObj.val());
        if(typeof(dataType) == 'undefined' || val == '')return true;

        switch (dataType.toLowerCase()){
            case 'idcard':
                var regular = /^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
                var msg = '请输入正确的身份证号';
            break;
            case 'email':
                var regular = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var msg = '请输入正确的电子邮箱';
            break;
            case 'mobile':
                var regular = /^[1][0-9]{10}$/;
                var msg = '请输入正确的手机号';
            break;
            case 'url':
                var regular = /^((https?|ftp|file):\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                var msg = '请输入正确的URL';
            break;
            case 'chinese':
                var regular = /[\u4E00-\u9FA5]/;
                var msg = '请输入汉字';
            break;
            case 'username':
                var regular = /^[a-zA-Z0-9_-]{4,16}$/;
                var msg = '请输入正确格式的用户名(字母数组组合)，4-16位长度';
            break;
            case 'password':
                var regular = /^.*(?=.{6,32})(?=.*\d)(?=.*[A-Z])(?=.*[a-z]).*$/;
                var msg = '请输入正确格式的密码(需包含大小写、数字的组合)，6-32位长度';
            break;
            case 'number':
                var regular = /^[0-9]*$/;
                var msg = '请输入数字';
            break;
            case 'money':
                var regular = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                var msg = '请输入正确的金额';
            break;
            case 'car_card':
                var regular = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
                var msg = '请输入正确的车牌';
            break;
            default:
                return true;
        }
        return validateData(regular, val, minLength, maxLength, msg);
    }

    //正则匹配与长度验证
    function validateData(regular, val, minLength, maxLength, msg){
        if(regular.test(val)){
            if(typeof(minLength) != 'undefined' && val.length < minLength)return '此项最少输入'+minLength+'位';
            if(typeof(maxLength) != 'undefined' && val.length > maxLength)return '此项最多输入'+maxLength+'位';
            return true;
        } else {
            return msg;
        }
    }
});

	