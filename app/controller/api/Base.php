<?php
namespace app\api\controller\v2;

use think\Controller;

use think\Db;
use think\Request;

use app\data\service\Aliyuncs as alycs;

class Base extends Controller
{

    protected $model_mb_user_token;
	public function __construct(){
		parent::__construct();
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST,PUT,OPTIONS,DELETE");
        header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept");
        //$this->model_mb_user_token=$this->datatoken();
	}

    //检查token跟id是否 匹配
    public function CheckToKen($token){
        $data=db('cxuser')
            ->where('cxtoken',$token)
            //->where('cxtoken',kimi($token,0))
            ->find();
        if(!$data){
            return jsonencode(900,'TOKEN信息不匹配，请检查',$data);
            exit;
        }else{
            return $data;
        }

    }
	public function _empty($name){
		$this->redirect(request()->root());
    }
	
	public function topseo(){
		return db('cxconfig')->where('cxid=1')->find();
	}

	protected function datatoken(){
        $lstoken=request()->param('lstoken','');
        if(!$lstoken){
            $token=request()->param('token');
            $model_mb_user_token=db('cxuser')->where('cxtoken=:token',['token'=>$token])->find();
            if(!$model_mb_user_token){
                jsonencode(301,'登录中...');
            }
            return $model_mb_user_token;
        }

    }

    /**
     * 获取当前设置的社区
     * @author NN
     * @param $token
     *
     */
    protected function getcurcity($token){
        $udb=db('cxuser')->where('cxtoken=:token',['token'=>$token])->find();
        if(!$udb){
            jsonencode(301,'参数有误');
        }
        //查询当前城市
        $cityswitch=db("cxcityswitch")->where('cxuserid=:uid',['uid'=>$udb['cxid']])->find();
        if(!$cityswitch){
            jsonencode(301,'请先设置社区');
        }
        //查询社区表
        $becomedb=db("cxbecome")->where('cxid=:id',['id'=>$cityswitch['cxbecomeid']])->find();
        return $becomedb;
    }

    /**
     * 图片上传
     *
     */

    protected function upimgs($file,$w=0,$h=0){
        //上传文件保存路径

        $upload_file = ROOT_PATH . 'public' . DS . 'uploads' . DS ;

//        echo $upload_file;

        $update_array = array();

        $getSaveName='';

        if ($file) {


            $info = $file->validate(['ext' => 'jpeg,jpg,png,gif'])->move($upload_file);




            if ($info) {

                $getSaveName = $info->getSaveName();
                if(Serversystem()){
                    $getSaveName = str_replace('\\', '/', $info->getSaveName());
                }


            }

            else {

                // 上传失败获取错误信息

                $this->error($file->getError());

            }

            $thumbfilename='';
            if($w!=0 && $h!=0){
                //生成缩略图

//                $thumbfilename=str_replace($info->getFilename(),"t_".$info->getFilename(),$getSaveName);
                $thumbfilename=$getSaveName;
                $image = \think\Image::open($upload_file . '/' . $getSaveName);

                $image->thumb($w, $h, \think\Image::THUMB_SCALING)->save($upload_file . '/' . $thumbfilename);
            }



        }


        if($thumbfilename){
            $thumbfilename='uploads/'.$thumbfilename;
        }else{
            $thumbfilename='';
        }

        return ['images'=>'uploads/'.$getSaveName,'thumbimages'=>$thumbfilename];



//        if (!empty($upload[$keyname])) {
//
//            $update_array[$keyname] = $upload[$keyname];
//
//        }
    }

    /**
     * 图片检测
     * @author NN
     * @param $imgurl 数据为的图片地址
     */
    protected function aliyuncspic($imgurl,$c=''){
        if(strpos($imgurl, httpurlRoot())===false){
            $imgurl=httpurlRootImg($imgurl);
        }
//        jsonencode(303,$imgurl);
        $pic=alycs::pic($imgurl);
//        return $pic;rate
        if($pic['label']!=='normal' && $pic['label']!=='sexy'){
            //删除旧头像
            $oldimg1=$_SERVER['DOCUMENT_ROOT'].'public/'.$imgurl;
//            if(file_exists($oldimg1)){
//                if(@unlink($oldimg1)){
//                    jsonencode(303,$c.alycs::picstart()[$pic['label']]);
//                }
//            }else{
//                jsonencode(303,$c.alycs::picstart()[$pic['label']]);
//            }
            if(@unlink($oldimg1)){
                if($pic['label']!=200){
                    jsonencode(303,$c.alycs::picstart()[$pic['label']]);
                }else{
                    jsonencode(303,$c.'图片内容识别失败');
                }

            }else{
                if($pic['label']!=200){
                    jsonencode(303,$c.alycs::picstart()[$pic['label']]);
                }else{
                    jsonencode(303,$c.'图片内容识别失败');
                }
            }


        }
    }

    /**
     * 文本检测
     * @author NN
     * @param $text 文本
     */
    protected function aliyuncstext($c,$n=''){
        $text=alycs::text($c);
        if($text['label']!=='normal'){
            jsonencode(303,$n.alycs::textstart()[$text['label']]);
        }
    }


    protected function contoken(){
        return $this->topseo()['cxtoken'];
    }
	//接口图片路径
	protected function apiurlimages($imgname){
		if($imgname){
			return request()->root(true).'/public/'.$imgname;
		}
	}
	
	protected  function loginvalidate(){ //登陆验证

	}

    //模拟前台请求服务器api接口
    public function getDataFromServer($request,$token,$method='get',$data=[]){
        //时间戳
        $timeStamp = time();
        //随机数
        $randomStr = $this -> createNonceStr();

        $data['timeStamp']=$timeStamp;
        $data['randomStr']=$randomStr;

        //生成签名
        $signature = $this -> arithmetic($data,$token);
        $data['signature']=$signature;

        //判断post请求和get请求
        if($method=='get'){
            echo "get";
            $params= http_build_query($data);
            //url地址
            $url = $request.'?'.$params;
            $result = $this -> httpGet($url);
        }else if($method=='post'){
            echo "post";
            $result = $this -> httpPost($request,$data);
        }

        return $result;

    }

    protected function httpPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    //curl模拟get请求。
    protected function httpGet($url){
        $curl = curl_init();

        //需要请求的是哪个地址
        curl_setopt($curl,CURLOPT_URL,$url);
        //表示把请求的数据已文件流的方式输出到变量中
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    //随机生成字符串
    protected function createNonceStr($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "z".$str;
    }

    /**
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
     */
    protected function arithmetic($data,$token){
//        $arr['timeStamp'] = $timeStamp;
//        $arr['randomStr'] = $randomStr;
//        $arr['token'] = self::TOKEN;
        $data['token']=$token;
        //按照首字母大小写顺序排序
        sort($data,SORT_STRING);
        //拼接成字符串
        $str = implode($data);
        //进行加密
        $signature = sha1($str);
        $signature = md5($signature);
        //转换成大写
        $signature = strtoupper($signature);
        return $signature;
    }

	/**
     * 数据库开启事务
     */
    public function startTrans()
    {
        Db::startTrans();
    }

    /**
     * 数据库事务提交
     */
    public function commit()
    {
        Db::commit();
    }

    /**
     * 数据库事务回滚
     */
    public function rollback()
    {
        Db::rollback();
    }
}	
?>