<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    'id'             => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // SESSION 前缀
    'prefix'         => 'think_session',
    // 是否自动开启 SESSION
    'auto_start'     => true,
    //过期时间
    'expire'     => 3600,
    'type' => ''
    /*
    //类型
    'type'       => 'redis',
    // redis主机
    'host'       => '127.0.0.1',
    // redis端口
    'port'       => 8069,
    // 密码
    'password'   => 'xinhai_robot',
    */
];
