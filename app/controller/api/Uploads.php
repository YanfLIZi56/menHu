<?php
namespace app\api\controller\v2;

use app\data\service\Dess;

use think\Validate;

class Uploads extends Base{
	public function index()
	{
	    exit;
	    $file = request()->file('file');
	    $param = request()->param();

	    $filePath = 'avatar';
	    $width = 1000;
	    $height = 5000;
	    if($file){
	        $filePaths = ROOT_PATH . 'public' . DS . 'uploads' . DS .$filePath;
	        if(!file_exists($filePaths)){
	            mkdir($filePaths,0777,true);
	        }
	        //52428800字节=50MB
	        $info = $file->validate(['size'=>52428800,'ext'=>'jpg,png,gif,mp4,bmp,jpeg,mov'])->move($filePaths);
	        if($info){
	            $imgpath = $filePaths . '/' . $info->getSaveName();
                    //return json(['code'=>200,'mess'=>$imgpath,'']);
	            //生成缩略图
	            $image = \think\Image::open($imgpath);
	            $image->thumb($width, $height)->save($imgpath);
	            $imgpath = '/public/uploads/'.$filePath.'/'.$info->getSaveName();
	            $data = [
	                'domain'    => $_SERVER['HTTP_HOST'],
	                'filepath'  => $imgpath,
	            ];


	            //删除旧文件
//                if(isset_($param['filePath'])){
//
//                    if($param['filePath']!=''){
//                        if(file_exists($_SERVER['DOCUMENT_ROOT'].$param['filePath'])){
//                        unlink($_SERVER['DOCUMENT_ROOT'].$param['filePath']);
//                        }
//                    }
//                }

                    return json(['code'=>200,'mess'=>'上传成功','data'=>$data]);
	        }else{
                    return jsonencode(300,'上传失败'.$info->getSaveName(),$file->getError());
	        }
	    }
	}
}