<?php
namespace app\common\model;

use think\Model;

class AdvPos extends Model
{
    protected $tableName = 'adv_pos';

    public function getInfo($name, $path)
    {
        $adv_pos = cache('adv_pos_by_pos_' .$path. $name);
        if ($adv_pos === false) {
            $adv_pos = $this->where(array('name' => $name, 'path' => $path, 'status' => 1))->find();
            cache('adv_pos_by_pos_'  .$path. $name,$adv_pos);
        }
        return $adv_pos;
    }

    /*——————————————————分隔线————————————————*/

    public function switchType($type)
    {
        switch ($type) {
            case 1:
                $return = '单图';
                break;
            case 2:
                $return = '轮播';
                break;
            case 3:
                $return = '文字链接';
                break;
            case 4:
                $return = '代码';
                break;
            default:
                $return = '其他';

        }
        return $return;
    }


}