$(function () {
    //提示窗配置
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    //1.初始化Table
    var oTable = new TableInit();
    oTable.Init();

    //2.初始化Button的点击事件
    var oButtonInit = new ButtonInit();
    oButtonInit.Init();

    $('.search-box .dropdown-toggle').click(function(){
        $('#tb_departments').bootstrapTable('selectPage', 1);
    });
    $('.search-box .clean-search').click(function(){
        $('#ibox-search-form')[0].reset();
        $('#tb_departments').bootstrapTable('selectPage', 1);
    });

    $('body').on('click', '#exampleTableEventsToolbar .btn,#tb_departments .btn', function(event){
        var thisBtn = $(this);
        var url = thisBtn.attr('href');
        if(typeof(url) == 'undefined')return false;
        if(thisBtn.hasClass('open-window')){
            layer.open({
                type: 2,
                shade: 0.01,
                title: false,
                area: ['70%', '450px'],
                fixed: false, //不固定
                // maxBtn: true,
                content: url
            });
        }

        if(thisBtn.hasClass('ajax-post')){
            if(thisBtn.hasClass('btn-xs')){
                var data = {};
            } else {
                var checkLimits = $('#tb_departments').bootstrapTable('getSelections');//获取选择行数据
                var ids = [];
                for (var i = 0; i < checkLimits.length; i++) {//循环读取选中行数据
                    ids.push(checkLimits[i][tablePk]);//获取选择行的值
                }
                if (ids.length == 0) {
                    toastr.error('请选择要操作的数据');
                    return false;
                }
                var data = {ids : ids};
            }

            if(thisBtn.hasClass('confirm')){
                var layerConfirm = layer.confirm('确认执行['+thisBtn.html()+']操作吗？该操作无法撤销', {
                    title: '请确认',
                    btn: ['确定','取消'] //按钮
                }, function(){
                    layer.close(layerConfirm);
                    ajaxGetRequestData(url, data);
                }, function(){
                });
            } else {
                ajaxGetRequestData(url, data);
            }

        }
        return false;
    });
});

function ajaxGetRequestData(url, data){
    var layerLoad = layer.load();
    $.get(url, data, function(result){
        layer.close(layerLoad);
        if(result.code == 200){
            tableDataRefresh();
            toastr.success(result.msg);
        } else {
            toastr.error(result.msg);
        }
    }, 'JSON').error(function(){
        layer.close(layerLoad);
        toastr.error('网络连接失败，请重试');
    });
}

function tableDataRefresh(){
    $('#tb_departments').bootstrapTable('refresh');
}

var TableInit = function () {
    var oTableInit = new Object();

    var pageList = pageSize == "All" ? ['All'] : [10, 30, 50, 100, 'All'];

    //初始化Table
    oTableInit.Init = function () {
        $('#tb_departments').bootstrapTable({
            url: getListUrl,         //请求后台的URL（*）
            method : 'get',
            toolbar: '#exampleTableEventsToolbar',                //工具按钮用哪个容器
            striped: true,                      //是否显示行间隔色
            cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
            pagination: true,                   //是否显示分页（*）
            sortable: true,                     //是否启用排序
            sortOrder: "desc",                   //排序方式
            queryParams: oTableInit.queryParams,//传递参数（*）
            sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
            pageNumber:1,                       //初始化加载第一页，默认第一页
            pageSize: pageSize,                       //每页的记录行数（*）
            pageList: pageList,        //可供选择的每页的行数（*）
            search: searchStatus,                       //是否显示表格搜索，此搜索是客户端搜索，不会进服务端，所以，个人感觉意义不大
            strictSearch: true,
            showColumns: true,                  //是否显示所有的列
            showRefresh: true,                  //是否显示刷新按钮
            minimumCountColumns: 2,             //最少允许的列数
            clickToSelect: false,                //是否启用点击选中行
            // height: 602,                        //行高，如果没有设置height属性，表格自动根据记录条数觉得表格高度
            uniqueId: tablePk,                     //每一行的唯一标识，一般为主键列
            showToggle:true,                    //是否显示详细视图和列表视图的切换按钮
            cardView: false,                    //是否显示详细视图
            detailView: false,                   //是否显示父子表
            columns: fieldList,
            showExport: true,              //是否显示导出按钮(此方法是自己写的目的是判断终端是电脑还是手机,电脑则返回true,手机返回falsee,手机不显示按钮)
            exportDataType: "basic",              //basic', 'all', 'selected'.
            exportTypes:['excel'],	    //导出类型
            //exportButton: $('#btn_export'),     //为按钮btn_export  绑定导出事件  自定义导出按钮(可以不用)
            exportOptions:{
                //ignoreColumn: [0,0],            //忽略某一列的索引
                fileName: $('.example-title').html(),              //文件名称设置
                worksheetName: 'Sheet1',          //表格工作区名称
                tableName: $('.example-title').html(),
                excelstyles: ['background-color', 'color', 'font-size', 'font-weight'],
                //onMsoNumberFormat: DoOnMsoNumberFormat
            },
            onLoadSuccess: function () {
            },
            onLoadError: function () {
                //showTips("数据加载失败！");
            },
        });
    };

    //得到查询的参数
    oTableInit.queryParams = function (params) {
        var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
            limit: params.limit,   //页面大小
            offset: params.offset,  //页码
            order: params.order,
            ordername: params.sort,

        };
        if(searchStatus == false) {
            var formData = $('#ibox-search-form').serializeArray();
            $.each(formData, function () {
                if (this.value != '') temp[this.name] = this.value;
            });
        } else {
            var keyword = $(".search").find("input").val();
            if(keyword != '')temp[searchField] = keyword;
        }
        return temp;
    };
    return oTableInit;
};


var ButtonInit = function () {
    var oInit = new Object();
    var postdata = {};

    oInit.Init = function () {
        //初始化页面上面的按钮事件
    };

    return oInit;
};