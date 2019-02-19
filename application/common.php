<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


function getCover($id){
    return \app\Admin\model\Upload::getFileUrl($id);
}

/**
 * 获取后台的配置参数
 * @param $name 配置名称, 可用（wx.name）取值。
 * @param string $default 无设置时的默认值
 * @return string
 */
function getAdminConfig($name = '', $default = ''){
    $is_get = \think\facade\Cache::tag('AdminConfig')->has('AdminConfig');
    if($is_get == false)\app\Admin\model\Config::setAdminConfig();
    $config = \think\facade\Cache::tag('AdminConfig')->get('AdminConfig');
    if(empty($name))return $config;
    $key = strpos($name, '.');
    if($key){
        $key_1 = substr($name, 0, $key);
        $key_2 = substr($name, $key+1);
        if(empty($key_2))return isset($config[$key_1]) ? $config[$key_1] : $default;
        return isset($config[$key_1][$key_2]) ? $config[$key_1][$key_2] : $default;
    } else {
        return isset($config[$name]) ? $config[$name] : $default;
    }
    return $config;
}

/**
 * 最多输出多少字
 * @param string $string
 * @param string $length
 * @param bool $strip_tags
 */
function strcut($string = '', $length = '', $strip_tags = false) {
    if($strip_tags)$string = strip_tags($string);
    $str = mb_substr($string, 0, $length, 'utf-8');
    if (strlen($str) < strlen($string)) {
        echo $str.'...';
    }else{
        echo $str;
    }
}

/**
 * 是否手机访问
 * @return bool
 *
 */
function is_wap() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;

}