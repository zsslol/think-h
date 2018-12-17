<?php
/**
 * Created by PhpStorm.
 * User: zhouss
 * Date: 2018/12/16
 * Time: 20:06
 */
namespace app\admin\model;

use think\Model;
use Jackchow\Rbac\Traits\RbacUser;

class Admins extends Model
{
    use RbacUser;

    protected $hidden=['password','created_at','updated_at'];

}