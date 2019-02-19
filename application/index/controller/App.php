<?php
namespace app\index\controller;

use app\index\controller\Home;

class App extends Home
{
    public function index()
    {
        if (is_wap()) {
            return $this->fetch('mobile');
        } else {
            return $this->fetch();
        }
    }

    /**
     * 文件的下载
     * @author fanqi<fq_mail@qq.com>
     * @access public
     */
    public function download()
    {
        //判断type是否有值
        $fileType = empty($_GET['type'])?$this->error('你的操作有误'):I('get.type');
        switch ($fileType){
            case 'android':
                $fileType = 'app.ANDROID_FILE';
                break;
            case 'ios':
                $fileType = 'app.IOS_FILE';
        }
        //获取文件的路径即判断文件是否存在
        $root = $_SERVER['DOCUMENT_ROOT'];
        $filePath = $root . getCover(getAdminConfig($fileType));
        if (!file_exists($filePath)) {
            echo '文件不存在';
            return;
        }
        //获取后缀创建文件名并下载
        $suffix = end(explode('.',$filePath));
        $fileName = 'justby.'.$suffix;
        $fileSize = filesize($filePath);
        $file = fopen($filePath,'r');
        header('Content-Type:application/octet-stream');
        header('Accept-Ranges:bytes');
        header('Accept-Length:'.filesize($filePath));
        header('Content-Disposition: attachment; filename='.$fileName);
        $buffer = 1024;
        $file_count = 0;
        //向浏览器返回数据
        while(!feof($file) && $file_count < $fileSize){
            $file_con = fread($file,$buffer);
            $file_count += $buffer;
            echo $file_con;
        }
    }

    /**
     * APP 关于我们的页面
     */
    public function details(){
        $id=$this->request->param('id');
        if(!$id) $this->error('id不能为空');
        $model = new \app\article\model\Article();
//        $info=D('ArticleView')->where('ArticleArticle.id='.$id)->find();
        $info = $model->getDetail($id);
        $this->assign("info",$info);
        return $this->fetch();
    }

    /**
     * 常见问题列表list
     */
    public function newslist(){
        $cid=$this->request->param('cateid');
        if(!$cid) $this->error('没有此分类');
        $model = new \app\article\model\Article();
        $where['category_id'] = $cid;
        $where['status'] = 1;
        $list = $model->where($where)->field('id,title')->order('id,title')->select()->toArray();
//        $list=D('ArticleView')->field('id,title')->where('cid='.$cid .' and ArticleBase.status=1')->select();
        $this->assign("list",$list);
        return $this->fetch();
    }
}