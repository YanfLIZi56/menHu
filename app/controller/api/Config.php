<?php
namespace app\controller\api;

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
}
