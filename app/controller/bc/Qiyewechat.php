<?php
namespace app\api\controller\bc;


class Qiyewechat extends Base
{

        //http://pufu.bcwhkj.cn/api/bc.Qiyewechat/getUserList

    /**
     * @return test
     */
    public function test(){
        //http://pufu.bcwhkj.cn/api/bc.Qiyewechat/dd



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
     * @return 获取TOKEN
     */
    function getNewToken(){
        //http://pufu.bcwhkj.cn/api/bc.Qiyewechat/dd




        $returns['PostUrl']='https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.public_Qywechat_corpid.'&corpsecret='.public_Qywechat_corpsecret;




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
        $this->redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".public_Qywechat_corpid."&redirect_uri=".$rukou."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");

    }

    /**
     * @return 获取当前登陆的用户详细信息
     */
    function weChatBackData(){
        //获取CODE
        $param=request()->param();

       //dump($param);

        //根据CODE获取用户详细 信息
        $res2=vget('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$this->getLocalToken().'&code='.$param['code']);
        $res2=json_decode($res2);

        $userData=db('cxuser')->where('userid',$res2->UserId)->find();
        if($userData){
            db('cxuser')->where('userid',$res2->UserId)->update(['cxlastlogintime'=>now()]);
            $userData['cxlastlogintime']=now();
        }else{
            $res3=vget('https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getLocalToken().'&userid='.$res2->UserId);
            $res3=json_decode($res3);
            $insertD['cxfullname']=$res3->name;
            $insertD['userid']=$res3->userid;
            $insertD['cxusername']=$res3->userid;
            $rand='123456';
            $insertD['cxrand']=$rand;
            $insertD['cxpassword']=md5(md5('123456').$rand);
            $insertD['cxtoken']=createNoncestr();

            $insertD['cximg0']=$res3->thumb_avatar;
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
        $data='{"touser": "'.$param['userid'].'","msgtype": "text","agentid": "1000002","text": {"content": "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://pufu.bcwhkj.cn/h5/user.html">邮件中心视频实况</a>，聪明避开排队。"}}';
        //$data='{"touser": "BingChengWenHuaKeJi","msgtype": "text","agentid": "1000002","text": {"content": "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://work.weixin.qq.com\">邮件中心视频实况</a>，聪明避开排队。"}}';
        $urls='https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$this->getLocalToken();

        //$res=curl_post_ssl($urls,$data);
		$json=$this->http($urls,'POST',$data);
        $arr=json_decode($json,true);
        return json($arr);

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