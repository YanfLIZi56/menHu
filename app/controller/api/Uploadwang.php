<?php
namespace app\controller\api;


use app\BaseController;
use think\Validate;

class Uploadwang extends Config{
    public function u(){
        // 获取表单上传文件 例如上传了001.jpg
        $files = request()->file('file');
        $returnData['errno']=300;
        $returnData['msg']='上传内容不能为空';
        $returnData['data']='';
        if(!$files){
            $returnData['errno']=0;
            $returnData['msg']='上传内容不能为空';
            $returnData['data']='';
            return json($returnData);
            exit;
        }
        // 上传到本地服务器
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'uploads', $files,'md5');

        $returnData['errno']=0;
        $returnData['msg']='上传成功';
        $returnData['data']=$savename;
        return $returnData;

    }
	public function index()
	{
	    exit;
        $files = request()->file('file');
	    $param = request()->param();
        if(!$files){
            $returnData['errno']=0;
            $returnData['msg']='上传内容不能为空';
            $returnData['data']='';
            return json($returnData);
            exit;
        }
        echo ROOT_PATH();
        $filePaths = ROOT_PATH() . 'public/uploads/' ;
        if(!file_exists($filePaths)){
            mkdir($filePaths,0777,true);
        }

        // 获取表单上传文件
        $files = request()->file();
        try {
            validate(['image'=>'filesize:10240|fileExt:jpg|image:200,200,jpg'])
                ->check($files);
            $savename = [];
            foreach($files as $file) {
                $savename[] = \think\facade\Filesystem::putFile( 'topic', $file);
            }
        } catch (\think\exception\ValidateException $e) {
            echo $e->getMessage();
        }


        if($files){

            // 获取表单上传文件
            foreach($files as $file){

                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $info = $file->validate(['size'=>52428800,'ext'=>'jpg,png,bmp,jpeg,pdf,gif,rar,zip,doc,cls,ppt,docx,clsx,pptx,mp4,bmp,jpeg,mov,avi,wmv,flv'])->rule('uniqid')->move(root_path() . 'public' . DS . 'uploads');
                    if($info){
                        // 成功上传后 获取上传信息

                        //echo $info->getExtension(); // 输出 jpg

                        //echo $info->getFilename(); // 输出 42a79759f284b767dfcb2a0197904287.jpg

                        echo $_SERVER['HTTP_HOST']. DS. 'public' . DS . 'uploads' . DS.$info->getSaveName();
                        // 输出 路径/42a79759f284b767dfcb2a0197904287.jpg
                    }else{
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
            }
        }

	}
}