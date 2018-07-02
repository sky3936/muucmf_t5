<?php
namespace app\api\controller;

use think\Controller;
use think\Db;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class File extends Controller
{
    /* 文件上传 */
    public function uploadPicture()
    {   
        /* 调用文件上传组件上传文件 */
        $file = request()->file('file');

        if (empty($file)) {
            $this->error('No file upload or server upload limit exceeded');
        }
        //判断是否已经存在附件
        $sha1 = $file->hash();
        //处理已存在图片
        if($sha1){
            $pic_info = Db::name('Picture')->where(['sha1'=>$sha1])->find();
            if($pic_info){
                $this->success('Upload successful','',$pic_info); 
            }
        }
        //初始化回传信息
        $return = [];
        //获取上传驱动
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $driver = check_driver_is_exist($driver);
        //构建返回数据
        $data['driver'] = $driver;

        if($driver == 'local'){
            $info = $file->validate(['size'=>15678000,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads'  . DS . 'picture');

            if($info){
                // 成功上传后 获取上传信息
                $data['path'] = DS . 'uploads'  . DS . 'picture'  . DS . $info->getSaveName();
                $data['path'] = str_replace("\\","/",$data['path']);
                $data['md5'] = $info->md5();
                $data['sha1'] = $info->sha1();
                $data['create_time'] = time();
                $data['status'] = 1;
            }else{
                $return['code'] = 0;
                $return['msg'] = $file->getError();
            }
        }else{
            //获取驱动配置
            $uploadConfig = get_upload_config($driver);
            //文件本地路径
            $filePath = $file->getRealPath();
        }
        //写入数据库
        $id = Db::name('Picture')->insertGetId($data);
        if($id){
            $data['id'] = $id;

            $return['code'] = 1;
            $return['msg'] = 'Upload successful';
            $return['data'] = $data;
            
        }else{
            $return['code'] = 0;
            $return['msg'] = '写入数据库错误';
        }
        return json($return);
    }
    /**
     * 用户头像上传
     * @return [type] [description]
     */
    public function uploadAvatar(){

        $aUid = is_login();

        /* 调用文件上传组件上传文件 */
        $file = request()->file('file');

        if (empty($file)) {
            $this->error('No file upload or server upload limit exceeded');
        }
        $return = [];
        //获取上传驱动
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $driver = check_driver_is_exist($driver);
        //构建返回数据
        $data['driver'] = $driver;
        $data['uid'] = $aUid;
        if($driver == 'local'){
            $info = $file
            ->validate(['size'=>15678000,'ext'=>'jpg,png,gif'])
            ->rule('uniqid')
            ->move(ROOT_PATH . 'public' . DS . 'uploads'  . DS . 'avatar' . DS . $aUid);

            if($info){
                // 成功上传后 获取上传信息
                $data['path'] = DS . 'uploads'  . DS . 'avatar' . DS . $aUid . DS . $info->getSaveName();
                $return['code'] = 1;
                $return['msg'] = 'Upload successful';
                $return['data'] = $data;
            }else{
                $return['code'] = 0;
                $return['msg'] = $file->getError();
            }
        }else{
            //获取驱动配置
            $uploadConfig = get_upload_config($driver);
            //文件本地路径
            $filePath = $file->getRealPath();
        }
        
        //返回
        return json($return);
    }

}