<?php
namespace app\controller\bc;

use app\BaseController;
use think\facade\Db;
use think\Validate;
use think\facade\Request;

class Uploads extends Config{

	public function index()
	{
        //safeWebLook();
	    $file = request()->file('file');
	    $param = request()->param();

        if(!r_('adminToken')){echo '权限不足';exit;}
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            $this->addLog('上传文件',$admin['cxadmin']);
            unset($param['adminToken']);

	    $filePath = 'avatar';
	    if($file){
	        //$filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
	        $filePaths = ROOT_PATH() . 'public/uploads' ;
	        if(!file_exists($filePaths)){
	            mkdir($filePaths,0777,true);
	        }

            try {
	            //验证参数	说明
                //fileSize	上传文件的最大字节
                //fileExt	文件后缀，多个用逗号分割或者数组
                //fileMime	文件MIME类型，多个用逗号分割或者数组
                //image	验证图像文件的尺寸和类型
               // validate(['image'=>'fileSize:10240|fileExt:jpg|image:200,200,jpg'])
                validate(['file'=>['fileSize:52428800','fileExt'=>'jpg,png,gif,mp4,bmp,jpeg,mov,pdf,cls,doc,ppt,docx,xlc']])->check(['file'=>$file]);
                $info = \think\facade\Filesystem::disk('public')->putFile( '', $file,'md5');
//                try {
//                    //组装路径（文件名）
//                    $thumb = 'storage/img/';
//                    $date = date('Ymd');
//                    file_exists($thumb.$date)?'':mkdir($thumb.$date,0777,true);
//                    $time = md5((string)time());
//                    $thumbPath = $thumb.$date.'/'.$time;
//
//                    //找到原图对原图进行压缩处理
//                    $image = \think\Image::open('storage/'.$info);
//                    $image->thumb(100,100)->save($thumbPath);
//                }catch (\Exception $exception)
//                {
//                    return $exception->getMessage();
//                }

                $imgpath=Request::root(true).'/uploads/' .$info;


//                $image = \think\Image::open($imgpath);
//// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
//                $image->thumb(150, 150)->save('./thumb.png');

                $data['msg']='上传完成';
                $data['filepath']=$imgpath;
                $data['code']=0;
                $data['data']=$info;

                return json($data);

            } catch (\think\exception\ValidateException $e) {
                //jsonencode(500,'上传失败',$e->getMessage());
                $data['msg']='上传失败';
                $data['filepath']='';
                $data['code']=300;
                $data['data']=$e->getMessage();
                return json($data);
            }
	        exit;



	            //删除旧文件
//                if(isset_($param['filePath'])){
//
//                    if($param['filePath']!=''){
//                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$param['filePath'])){
//                        unlink($_SERVER['DOCUMENT_ROOT'].$param['filePath']);
//                        }
//                    }
//                }
	    }
	}
}