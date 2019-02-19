<?php

namespace app\Admin\model;

use think\Model;

class Upload extends Model
{
    protected $table = 'admin_uploads';

    protected static $save_path = DIRECTORY_SEPARATOR.'uploads';

    protected $upload_validate = [
        'image' => ['max_size' => '6', 'ext' => 'jpg,png,fig,jpeg'],
        'file' => ['max_size' => '30', 'ext' => 'rar,zip,7z,doc,xls,xlsx,ppt']
    ];

    /**
     * 获取文件地址
     */
    public static function getFileUrl($id)
    {
        $info = Upload::where('id',$id)->find();
        if($info == false)return '';
        return request()->server('HTTP_ORIGIN').Upload::$save_path.DIRECTORY_SEPARATOR.$info['save_path'].DIRECTORY_SEPARATOR.$info['save_name'];
    }

    /**
     * 获取文件指定字段信息
     */
    public static function getFileInfo($id, $file = 'name')
    {
        $info = Upload::where('id',$id)->find();
        if($info == false)return '';
        return $info[$file];
    }

    /**
     * 获取设置
     */
    public function getConfig()
    {
        return $this->upload_validate;
    }

    /**
     * 根据MD5值查找文件
     */
    public function checkFieldMd5($md5){
        $where['js_md5'] = $md5;
        $file_info = $this->field('id,name,save_path,save_name')->where($where)->find();
        if($file_info){
            $file_info = $file_info->toArray();
            $file_info['img_url'] = self::getFileUrl($file_info['id']);
            return $file_info;
        } else {
            return false;
        }
    }

    /**
     * 上传文件
     *
     */
    public function upload()
    {
        $file_type = input('post.file_type');

        if(empty($file_type)){
            $this->error = '参数不完整，无法上传';
            return false;
        }
        if(!in_array($file_type, array_keys($this->upload_validate))){
            $this->error = '参数不正确，无法上传';
            return false;
        }

        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->validate($this->upload_validate[$file_type])->move( '.'.self::$save_path);
        if($info){
            $file_info = $info->getInfo();
            $add_data = [
                'create_uid' => session('user_info.id'),
                'name' => substr($file_info['name'], -50),
                'save_name' => $info->getFilename(),
                'save_path' => date('Ymd'),
                'save_type' => 'local',
                'ext' => $info->getExtension(),
                'type' => $file_info['type'],
                'size' => $file_info['size'],
                'md5' => $info->md5(),
                'js_md5' => input('post.file_md5'),
                'status' => 1,
            ];
            $add_result = $this->save($add_data);
            if($add_result == false){
                $this->error = '网络错误，请重试';
                return false;
            }
            $add_data['id'] = $this->id;
            $add_data['img_url'] = self::getFileUrl($add_data['id']);
            return $add_data;
        }else{
            // 上传失败获取错误信息
            $this->error = $file->getError();
            return false;
        }
    }
}

