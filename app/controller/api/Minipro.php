<?php
namespace app\api\controller\v2;


class Minipro extends Base
{

    /**
     * @return 根据CODE返回OPENID和session_key
     */
    function returnOpenid(){
        //传递过来的CODE通过小程序获取，只能用一次
        $param=request()->param();


        $returns['PostUrl']='https://api.weixin.qq.com/sns/jscode2session?appid='.public_wx_AppID.'&secret='.public_wx_AppSecret.'&js_code='.$param['code'].'&grant_type=authorization_code';




        $res=vget($returns['PostUrl']);
        $res=json_decode($res,true);

        //return json(($res));
        if(isset($res['errcode'])){

            if ($res['errcode']=='40163'){
                $res['msg']='code已经使用过了';
            }elseif ($res['errcode']=='40029'){
                $res['msg']='code错误';
            }else{
            }
        }else{
            if(!isset($param['cxbinfxid'])){$param['cxbinfxid']=0;}
            if(!isset($param['nickname'])){$param['nickname']='';}
            if(!isset($param['headimgurl'])){$param['headimgurl']='';}
            $this->regUser($res['openid'],$param['nickname'],$param['headimgurl'],$param['cxbinfxid']);
            $res['msg']='正确返回数据';

        }
        return json($res);
        exit;
        //return $res;
//        $returns['token']=$res->access_token;
//        $returns['returnAllData']=$res;
//        jsonencode(0,'OK',$returns);
    }



        //http://pufu.bcwhkj.cn/api/bc.Qiyewechat/getUserList

    /**
     * @return test
     */
    public function test(){
        //http://yewu.bcwhkj.cn/api/v2.Wechat/dd



        //企业ID
        $returns['corpid']='wwad441a9a6532b286';

        //应用的凭证密钥
        $returns['corpsecret']='iyfyj2rOwmNO0Hc_IkaB5a3C-RYvTJQdoJJTV7d4c9A';


        $returns['PostUrl']='https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$returns['corpid'].'&corpsecret='.$returns['corpsecret'];




        $res=vget($returns['PostUrl']);
        $res=json_decode($res,true);
        return $res['access_token'];
//        $returns['token']=$res->access_token;
//        $returns['returnAllData']=$res;
//        jsonencode(0,'OK',$returns);
    }

    /**
     * @return 写入FormId
     */
    public function getFormId(){
        $param=request()->param();
        return json($param);

    }


    /**
     * @return 获取TOKEN
     */
    function getNewToken(){
        https://yewu.bcwhkj.cn/api/v2.Wechat/getNewToken




        $returns['PostUrl']='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.public_wx_AppID.'&secret='.public_wx_AppSecret;




        $res=vget($returns['PostUrl']);
        $res=json_decode($res,true);
        return $res['access_token'];
//        $returns['token']=$res->access_token;
//        $returns['returnAllData']=$res;
//        jsonencode(0,'OK',$returns);
    }

    /**
     * @return 读取本地TOKEN
     */
    public function getLocalToken(){
		$file_access_token=$_SERVER['DOCUMENT_ROOT']."/wxcache/access_token.json";
        if(file_exists($file_access_token)){
			$file = file_get_contents($file_access_token,true);
	    	$result = json_decode($file,true);
			if($result['access_token']){
				if (time() > $result['expires']){  //如果本地日期过期就重新拉取网上新的TOKEN
			        $data = array();
			        $data['access_token'] = $this->getNewToken();
			        $data['expires']=time()+240;
			        $jsonStr =  json_encode($data);
			        $fp = fopen($file_access_token, "w");
			        fwrite($fp, $jsonStr);
			        fclose($fp);
			        return $data['access_token'];
			    }else{
			        return $result['access_token'];
			    }
			}else{
				$data = array();
		        $data['access_token'] = $this->getNewToken();
		        $data['expires']=time()+240;
		        $jsonStr =  json_encode($data);
		        $fp = fopen($file_access_token, "w");
		        fwrite($fp, $jsonStr);
		        fclose($fp);
		        return $data['access_token'];
			}

		}else{
			$data = array();
	        $data['access_token'] = $this->getNewToken();
	        $data['expires']=time()+240;
	        $jsonStr =  json_encode($data);
	        $fp = fopen($file_access_token, "w");
	        fwrite($fp, $jsonStr);
	        fclose($fp);
	        return $data['access_token'];
		}
    }

    /**
     * @return 跳转微信身份获取入口
     */
    function goWeChat(){

        //http://pufu.bcwhkj.cn/h5/user.html

        //通过此功能 自动跳转微信用户接口页，然后微信会通过REDIRECT_URI返回对应网址后，传递CODE给我们，
        //通过weChatBackData函数拉取CODE后再调用API识别用户身份
        //http://pufu.bcwhkj.cn/api/bc.Qiyewechat/goWeChat
        //$rukou=request()->domain().'/api/bc.Qiyewechat/weChatBackData?url='.$urls;
        $param=request()->param();
        $rukou=$param['urls'];
        $rukou=urlencode($rukou);

        //应用授权作用域，
        //snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
        //snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）

        $this->redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".public_wx_AppID."&redirect_uri=".$rukou."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect");
    }


    /**
     * return 公共接口-注册会员
     */
    public function  regUser($openid='123',$nickname='',$headimgurl='',$cxbinfxid=0){
        $param=request()->param();
        if(isset($param['openid'])){$openid=$param['openid'];}
        if(!$nickname){$nickname=$openid;}

        $userData=db('cxuser')->where('cxopenid',$openid)->find();
        if($userData){
            unset($userData['cxpassword']);
            db('cxuser')->where('cxopenid',$openid)->update(['cxlastlogintime'=>now()]);
            return jsonencode(0,'老用户登陆成功',$userData);
        }else{
            $insertD['cxopenid']=$openid;
            $insertD['cxbinfxid']=$cxbinfxid;
            $insertD['cxfullname']=preg_replace('/[\xf0-\xf7].{3}/', '', $nickname);
            if($nickname==''){
                $insertD['cxname']='未命名';
            }else{
                $insertD['cxname']=preg_replace('/[\xf0-\xf7].{3}/', '', $nickname);
            }
            $insertD['cxusername']=createNoncestr();
            $insertD['cximg0']=$headimgurl;
            $rand='123456';
            $insertD['cxrand']=$rand;
            $insertD['cxpassword']=md5(md5('123456').$rand);
            $insertD['cxtoken']=createNoncestr();

            $insertD['cxlock']=1;
            $insertD['cxuserlevel']=6;
            $insertD['cxlastlogintime']=now();

            $newUserId=db('cxuser')->insertGetId($insertD);
            $userData=db('cxuser')->find($newUserId);


            $filename1=$newUserId.'_shoukuan.jpg';
            qrcodes(request()->domain().'/h5/payment.php?cxid='.$newUserId,$filename1,3,8);

            $filename1=$_SERVER['DOCUMENT_ROOT'].'/'.$filename1;

            //删除生成的多余文件
            //unlink($filename1);
            return jsonencode(0,'新注册成功',$userData);

        }
    }

    /**
     * @return 获取当前登陆的用户详细信息
     */
    function weChatBackData(){
        //获取CODE
        $param=request()->param();

       //dump($param);

        //根据CODE获取网页授权access_token

        $res2=vget('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.public_wx_AppID.'&secret='.public_wx_AppSecret.'&code='.$param['code'].'&grant_type=authorization_code');
        //$res2=vget('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$this->getLocalToken().'&code='.$param['code']);

        $res2=json_decode($res2);


        $res3=vget('https://api.weixin.qq.com/sns/userinfo?access_token='.$res2->access_token.'&openid='.$res2->openid.'&lang=zh_CN');
        //$res2=vget('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$this->getLocalToken().'&code='.$param['code']);

        $res3=json_decode($res3);


        $userData=db('cxuser')->where('cxopenid',$res2->openid)->find();
        if($userData){
            db('cxuser')->where('cxopenid',$res3->openid)->update(['cxlastlogintime'=>now()]);
        }else{
            $insertD['cxopenid']=$res3->openid;
            $insertD['cxfullname']=preg_replace('/[\xf0-\xf7].{3}/', '', $res3->nickname);
            $insertD['cxusername']=createNoncestr();
            $insertD['cximg0']=$res3->headimgurl;
            $rand='123456';
            $insertD['cxrand']=$rand;
            $insertD['cxpassword']=md5(md5('123456').$rand);
            $insertD['cxtoken']=createNoncestr();

            $insertD['cxlock']=1;
            $insertD['cxuserlevel']=6;
            $insertD['cxlastlogintime']=now();

            $newUserId=db('cxuser')->insertGetId($insertD);
            $userData=db('cxuser')->find($newUserId);
        }
        return jsonencode(0,'用户微信身份识别成功',$userData);

    }

    /**
     * @return 获取部门人员列表
     */
    public function getUserList(){

        //token
        $param=request()->param();


        //department_id	是	获取的部门id
        //fetch_child	否	1/0：是否递归获取子部门下面的成员


        $res2=vget('https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token='.$this->getLocalToken().'&department_id=64&fetch_child=1');


        $res2=json_decode($res2);

        return json($res2);


    }


    /**
     * @return 根据CODE获取用户详情
     */
    public function getUserInfo(){

        $res2=vget('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$this->getLocalToken().'&code=07hEaAw9Cd_j4UyfCuKKmgtYnbAS2j6VU7gagANK22M');
        $res2=json_decode($res2);

        return json($res2);


    }

    /**
     * @return 发送消息
     */
    public function sendWx(){
        //userid
        $param=request()->param();
        $data='{
          "touser": "'.$param['openid'].'",
          "template_id": "Pnd0JDGKPaHNeY_0XkbX4kpJD4rSLrvFOysEkHKHpco",
          "page": "index",
          "data": {
              "thing7": {
                  "value": "339208499"
              },
              "time9": {
                  "value": "2019年10月1日 15:01"
              },
          }
        }';
        //$data='{"touser": "BingChengWenHuaKeJi","msgtype": "text","agentid": "1000002","text": {"content": "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://work.weixin.qq.com\">邮件中心视频实况</a>，聪明避开排队。"}}';
        $urls='https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$this->getLocalToken();

        //$res=curl_post_ssl($urls,$data);
        //$json=$this->http($urls,'POST',$data);

        $json=$this->https_requests($urls,$data,'json');
        $arr=json_decode($json,true);
        return json($arr);

    }

    function https_requests($url,$data,$type){
        if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
            $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * @return 小程序码
     */
    public function creatMa(){
        //echo 'sdf';
        //userid
        $param=request()->param();
        $data='{
         "page": "pages/index/index",
         "scene": "a=1",
         "check_path": true,
         "env_version": "release"
        }';
        //$data='{"touser": "BingChengWenHuaKeJi","msgtype": "text","agentid": "1000002","text": {"content": "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://work.weixin.qq.com\">邮件中心视频实况</a>，聪明避开排队。"}}';
        $urls='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$this->getLocalToken();

        //$res=curl_post_ssl($urls,$data);
        $json=$this->http($urls,'POST',$data);
        return $json;
        //$arr=json_decode($json,true);
        //return json($arr);

    }



	//接口发送
	function https_request($url){
	    $info=curl_init();
	    curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($info,CURLOPT_HEADER,0);
	    curl_setopt($info,CURLOPT_NOBODY,0);
	    curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($info,CURLOPT_URL,$url);
	    $output= curl_exec($info);
	    curl_close($info);
	    return $output;
	}

	function http($url, $method, $postfields = null, $headers = array()){
	    $ci = curl_init();
	//  curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	//  curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
	//  curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($ci,CURLOPT_HEADER,0);
	    curl_setopt($ci,CURLOPT_NOBODY,0);
	    curl_setopt($ci,CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ci,CURLOPT_SSL_VERIFYHOST, false);
	    switch (strtolower($method)) {
	        case 'post':
	                curl_setopt($ci, CURLOPT_POST, true);
	                if (!empty($postfields)) {
	                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
	                }
	                break;
	        }
	        curl_setopt($ci, CURLOPT_URL, $url);
	        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
	//      curl_setopt($ci, CURLINFO_HEADER_OUT, true);

	        $response = curl_exec($ci);
	        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

	        curl_close($ci);
	//      return array($http_code, $response);
			return $response;
	}
}









?>