<?php
namespace app\demo\controller;

use think\Db;
use think\Controller;
use app\common\controller\Common;

class Index extends Common
{

	public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {



    	return $this->fetch();
    }


}