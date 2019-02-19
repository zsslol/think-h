<?php
namespace app\index\controller;

use app\index\controller\Home;
/**
 * 前台默认控制器
 *
 */
class Index extends Home {
    /**
     * 默认方法
     *
     */
    public function index() {
        $article_model = new \app\article\model\Article();
        $slide_model = new \app\article\model\Slide();

        //幻灯片
        $this->assign('slide_list',$slide_model->getSlideList());

        //行业新闻
        $hyxw_list = $article_model->getArticleList(14, 3, [],'content,description');
        $this->assign('hyxw_list',$hyxw_list['list']);

        return $this->fetch();
    }

    //小胖介绍页
    public function robot(){
    	return $this->fetch();
    }

    //列表
    public function articleList($diyname){
        $category = \app\article\model\Category::where('diyname',$diyname)->field('id,name,diyname,keywords,description')->find();
        if($category == false)$this->error('参数错误，请重试', url('index'));

        $article_model = new \app\article\model\Article();
        $this->assign('data', $article_model->getArticleList($category['id'], 10));
        $this->assign('category', $category->toArray());
        return $this->fetch();
    }

    //单页
    public function page($name){
        $model = new \app\article\model\Page();
        $content = $model->getPage($name);
        if($content == false)$this->error('参数错误，请重试', url('index'));

        $this->assign('page', $content);
        return $this->fetch();
    }

    //详情
    public function detail($id){
        $model = new \app\article\model\Article();
        $content = $model->getDetail($id);
        if($content == false)$this->error('参数错误，请重试', url('index'));
        $this->assign('info', $content);
        return $this->fetch();
    }
    
    //显示地图
    public function map(){
        $count=D('Gps/DeviceUpdateDemo')->count();
        $this->assign("count",$count);

        $this->assign('bdmap_ak', C('gps_config.bdmap_ak'));
        $this->display();
    }

    //获取所有设备的位置
    public function getLocations(){
        $list=D('Gps/DeviceUpdateDemo')->field('longitude,latitude')->select();
        if($list){
            $this->success($list,null,true);
        }else{
            $this->error('暂无数据',null,true);
        }
    }
}
