<?php
namespace app\api\controller\v2;

use app\data\service\Dess;

use think\Validate;
use app\api\controller\bc\Qiyewechat as QiYe;




class Qiyewechat extends Base
{

/**
     * @return 发送消息
     */
    public function sendWx(){
        //http://pufu.bcwhkj.cn/api/v2.Qiyewechat/sendWx?toUserId=1&contents=123
        $QiYe=new QiYe;
        $param=request()->param();
        $userid=db('cxuser')->where('cxid',$param['toUserId'])->find();
//        $data='{
//        "touser": "'.$userid['userid'].'",
//        "msgtype": "text",
//        "agentid": "1000004",
//        "text": {
//            "content": "'.$param['contents'].'\n['.now().']\n<a href=\"http://pufu.bcwhkj.cn/h5/list.html\">请点击查看</a>"
//            }
//        }';
        $data='{
           "touser" : "'.$userid['userid'].'",
           "toparty" : "64",
           "agentid" : "1000004",
           "msgtype" : "textcard",
           "textcard" : {
                       "title" : "'.$param['contents'].'",
                       "description" : "'.now().'",
                       "url" : "http://pufu.bcwhkj.cn/h5/list.html",
                        "btntxt":"点击查看"
           }
        }';

        //$data='{"touser": "BingChengWenHuaKeJi","msgtype": "text","agentid": "1000002","text": {"content": "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://work.weixin.qq.com\">邮件中心视频实况</a>，聪明避开排队。"}}';
        $urls='https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$QiYe->getLocalToken();

        //$res=curl_post_ssl($urls,$data);
		$json=$QiYe->http($urls,'POST',$data);
        $arr=json_decode($json,true);
        return json($arr);

    }

    public function goWeChat(){
        //http://pufu.bcwhkj.cn/api/v2.Qiyewechat/goWeChat
        $QiYe=new QiYe;
        $userid=$QiYe->goWeChat();
    }
    public function weChatBackData(){
        //http://pufu.bcwhkj.cn/api/v2.Qiyewechat/goWeChat
        $QiYe=new QiYe;
        $userid=$QiYe->weChatBackData();
    }
}

?>
