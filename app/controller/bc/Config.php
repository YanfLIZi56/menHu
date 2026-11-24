<?php
namespace app\controller\bc;

use app\BaseController;
use think\facade\Db;

class Config extends BaseController
{

    public function __construct(){
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST,PUT,OPTIONS,DELETE");
        header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept");
        //return json(['msg'=>'1','code'=>22]);

        safeWebLook();
    }
    //检查token跟id是否 匹配
    public function CheckToKen($token){
        $data=Db::name('cxuser')
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


    /**
     * @return 管理权限记录并检测
     */
    public function qs($nams='未知操作')
    {
        $param = request()->param();
        if (!r_('adminToken')) {
            jsonencode(500, '权限不足', '');
            exit;
        }
        $admin = $this->CheckToKenAdmin($param['adminToken']);
        $this->addLog($nams, $admin['cxadmin']);
        unset($param['adminToken']);

    }


    //检查token跟id是否 匹配
    public function CheckToKenAdmin($token){
        $data=Db::name('cxadmin')
            ->where('cxtoken',$token)
            //->where('cxtoken',kimi($token,0))
            ->find();
        if(!$data){
            return jsonencode(900,'TOKEN信息不匹配，请检查',$token);
            exit;
        }else{
            return $data;
        }

    }


    /**
     * @param string $titles 标题
     * @param string $users 操作人名称
     * @return 添加日志
     */
    public  function addLog($titles='',$users=''){
        $param=request()->param(false);
        $data['cxtitlecn']=$titles; //操作标题
        $data['cxuser']=$users; //操作者名称
        $data['cxurl']= request()->url(true);
        $data['cxip']=request()->server()['REMOTE_ADDR'];
        //if(isset($param['cxcontentcn'])){unset($param['cxcontentcn']);}
        if(isset($param['cximg2'])){unset($param['cximg2']);}
        if(isset($param['cximg3'])){unset($param['cximg3']);}
        if(isset($param['cxtitleen'])){unset($param['cxtitleen']);}
        if(isset($param['cxkeywordscn'])){unset($param['cxkeywordscn']);}
        if(isset($param['cxkeywordsen'])){unset($param['cxkeywordsen']);}
        if(isset($param['cxcontentWapcn'])){unset($param['cxcontentWapcn']);}
        if(isset($param['cxcontentWapen'])){unset($param['cxcontentWapen']);}
        if(isset($param['cxjson'])){unset($param['cxjson']);}
        if(isset($param['adminToken'])){unset($param['adminToken']);}
        if(isset($param['cxpassword'])){unset($param['cxpassword']);}

        $data['cxparam']=json_encode($param,JSON_UNESCAPED_UNICODE);
        $data['cxpassed']=1;
        $data['cxaddtime']=Now();
        $data['cxupdatetime']=Now();

        Db::name('cxlog')->insert($data);
    }

    function r_($e){

        $param = request()->param();
        if (isset($param[$e])) {
            if($param[$e]==''){
                return false;
            }else{
                return true;
            }

        } else {
            return false;
        }

    }


    /**
     *设置登录token  唯一性
     *@retrun  String
     */
    function getToken(){
        $str = md5(uniqid(md5(microtime(true)),true));
        $token = sha1($str.rand(10001,99999));
        return $token;
    }

    function checkQuanXian(){
        $param=request()->param();
        if(!isset($param['adminToken'])){
            jsonencode(500,'权限不足','');
            exit;
        }
        $admin=$this->CheckToKenAdmin($param['adminToken']);
        if($admin['cxadmin']!='adminzy'){
            if(!isset($param['table'])){
                if(isset($param['bm'])){
                    $param['table']=$param['bm'];
                }
            }
            if($param['table']=='cxnews_sort'){
                $search[]=['cxid','in',$admin['cxpids']];
            }elseif($param['table']=='cxnews'){
                $search[]=['cxsortid','in',$admin['cxpids']];
            }elseif($param['table']=='cxadmin'){
                if(stripos($admin['cxMenu'],'adminUser.html')==false ||
                    stripos($admin['cxMenu'],'adminUser.html')==false){
                    jsonencode(500,'权限不足','');
                    exit;
                }
            }elseif($param['table']=='cxguestbook'){
                if(stripos($admin['cxMenu'],'guestBook.html')==false){
                    jsonencode(500,'权限不足','');
                    exit;
                }
            }elseif($param['table']=='cxlog'){
                if(stripos($admin['cxMenu'],'log.html')==false){
                    jsonencode(500,'权限不足','');
                    exit;
                }
            }elseif($param['table']=='cxuser'){
                if(stripos($admin['cxMenu'],'user.html')==false){
                    jsonencode(500,'权限不足','');
                    exit;
                }
            }elseif($param['table']=='cxzy'){
                //这个不限制
            }else{
                jsonencode(500,'权限不足','');
                exit;
            }
        }
        unset($param['adminToken']);
    }

    /**
     * @param $string
     * @return 过滤XSS字符
     */
    function filterXss($string) {

        $string = trim($string);

        //$string = strip_tags($string);

        //$string = htmlspecialchars($string);


        //$string = str_ireplace('', '', $string);

        //$string = str_replace(array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string);
        $string =  str_ireplace(Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','script','alert','onload','onerror','javascript','vbscript','expression','applet','meta','xml','blink','link','embed','object','layer','title','bgsound','base','onload','onunload','onchange','onsubmit','onreset','onselect','onblur','onfocus','onabort','onkeydown','onkeypress','onkeyup','onclick','ondblclick','onmousedown','onmousemove','onmouseout','onmouseover','onmouseup','onunload','onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload','javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link',  'script', 'embed', 'object',  'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'), '', $string);

        $no = '/%0[0-8bcef]/';

        // $string = preg_replace ($no,'',$string);

        $no = '/%1[0-9a-f]/';

        //$string = preg_replace ($no,'',$string);

        $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';

        //$string = preg_replace ($no,'',$string);

        return $string;

    }
    /**
     * @return kimi加密
     * @param $strs 要加密的字符串
     * @param $isjiaMi 加密还是解密,默认加密
     */
    function kimi($strs,$isjiaMi=1)
    {
        if ($strs) {
            if ($isjiaMi == 1) {
                //加密

            } else {
                //解密
                return $this->privateDecrypt($strs);
            }
        } else {
            return '';
        }
    }

    /**
     * 解密
     * @param string $encryptString
     * @return string
     */
    function privateDecrypt($encryptString = '')
    {
        $privateKey       = "-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALbAEc6wXHCu8Jar
Ch9zjGlcicgzp40EpAxMc+F3gnQWvcfIDKLPvQ4p2AQYriyzMLTrH6LNSpi6dAHI
oMIJJsaMLUozZqhxvINx9srd7q3/9hCF7hrKBeP7pqzYctuoqbOeJHluoZpUAz1E
eCy5ZyEgGA0WriR7/SF2yJbUG07dAgMBAAECgYB7G1/jkEJ1ViOUG5wyG7CWuVPH
naRdBqdL8aSOWedzNG6bylNYygBdLirqd4W9buh8t3cUQJ7jy5aiLPi+PVP0fuaw
hezldl/NVTw3sjVnSekvaXsz/WpWA4N2Ijv9EHzO5t2jDMqXp/xddduiZYMRLp6S
ZmVUU7ET+Tmlki5QAQJBAPJJlwq6AyMN4BR+17T5fGTYPJBWjLkZM30yvQrOmbsq
AyTOOM2vu8J5OkyFvhnWnIDgFtVj2DUaYMO+lQZ6zwECQQDBF946Vy1BeiCEiurv
W+Qpqx9Zkbl7pzOOeMxOOIWtPRPjbShgSqf59ij+tqhq+T/uwTL5IhAC2Pds727R
wZvdAkEApzHjOjK8wW7syBfz8N4NWmXh/VTMjVr3rhjaXV/OtFq8iqSIjGANuJkB
gxbz5U609zYsa5sX8yU9yzZ0DmGnAQJAKi03elDObs3pSmo5lNzigoOBToQ5GpxT
85vJLJDGCigcLwIJ3raP6FZvb+uf7/XPlCwZITfifWy85suJzkFW9QJBAPD4wks1
yMXN3sKxUEOSonf8x4EeKHRt7owZhpeDotDf9rqbQEXzdE979eyFP/6TkDe6GxSA
1Su8EkO6imzhE3M=
-----END PRIVATE KEY-----";

        $privateKey=openssl_pkey_get_private($privateKey, "phrase");
        openssl_private_decrypt(base64_decode($encryptString), $decrypted,$privateKey );
        return $decrypted;
    }



    /**
     * 加密
     * @param string $data
     * @return string
     */
    function publicEncrypt($data = '')
    {
//   $encrypt_data = '';
//   //        $publicKey = self::JS_ENCRYPT;
//   //        openssl_public_encrypt($data, $encrypt_data, $publicKey);
//   $key       = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2wBHOsFxwrvCWqwofc4xpXInI
//M6eNBKQMTHPhd4J0Fr3HyAyiz70OKdgEGK4sszC06x+izUqYunQByKDCCSbGjC1K
//M2aocbyDcfbK3e6t//YQhe4aygXj+6as2HLbqKmzniR5bqGaVAM9RHgsuWchIBgN
//Fq4ke/0hdsiW1BtO3QIDAQAB";
//   $key_eol   = (string) implode("\n", str_split((string) $key, 64));
//   $publicKey = (string) "-----BEGIN PUBLIC KEY-----\n" . $key_eol . "\n-----END PUBLIC KEY-----";
//   openssl_public_encrypt($data, $encrypt_data, $publicKey);
//   $encrypt_data = base64_encode($encrypt_data);
//   return $encrypt_data;
    }

    /**
     * @param $variable
     * @param $text
     * @return 查询字符在指定串中出现的位置，没有返-1
     */
    function instr($all, $text) {
        if (strpos($all, $text) !== false) {
            return stripos($all, $text);
        } else {
            return -1;
        }
    }

}

