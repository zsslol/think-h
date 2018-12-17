$(document).ready(function () {
	$(".i-checks").iCheck({
		checkboxClass: "icheckbox_square-green",
		radioClass: "iradio_square-green",
	});

	//submit
	$(".btn-primary").click(function(){
		event.preventDefault();

        var submitSuccess = true;
		var data = {};
		var iboxContent = $(".ibox-content");
		
		iboxContent.find(".formbuilder-input").each(function(){
		    var thisObj = $(this);
            if(validateField(thisObj.attr("name"), thisObj.val())){
                data[thisObj.attr("name")] = thisObj.val();
            } else {
                submitSuccess = false;
            }
		});
		
		iboxContent.find(".formbuilder-select").each(function(){
		    var thisObj = $(this);
            if(validateField(thisObj.attr("name"), thisObj.val())){
                data[thisObj.attr("name")] = thisObj.val();
            } else {
                submitSuccess = false;
            }
		});
		
		iboxContent.find(".formbuilder-radio").each(function(){
		    var thisObj = $(this);
            var value = $("input[type='"+thisObj.attr("name")+"']:checked").val();
            if(validateField(thisObj.attr("name"), value)){
                data[thisObj.attr("name")] = value;
            } else {
                submitSuccess = false;
            }
		});

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

		iboxContent.find(".formbuilder-textarea").each(function(){
		    var thisObj = $(this);
            if(validateField(thisObj.attr("name"), thisObj.val())){
                data[thisObj.attr("name")] = thisObj.val();
            } else {
                submitSuccess = false;
            }
		});
		
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
		console.log(data);
	});

    function validateField(name, val){
        var formGroupObj = $('.form-group-'+name);
        var tipsObj = formGroupObj.find('.help-block');
        if(formGroupObj.find('.must').length > 0){
            if( val == '' || typeof (val) == "undefined"){
                formGroupObj.addClass('has-warning');
                tipsObj.html('此项为必须');
                return false;
            } else {
                formGroupObj.removeClass('has-warning');
                formGroupObj.removeClass('has-error');
                //formGroupObj.addClass('has-success');
                tipsObj.html(tipsObj.attr('_title'));
                return true;
            }
        }
        return true;
    }
});

	