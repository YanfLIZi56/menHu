<?php
// 应用公共文件

function db($s){
    return Db::name($s);
}


/**
 * @return 获取当前分类ID和所有子类ID
 * @param $parentSortid 当前分类ID
 * @param $bm 对应表名
 */

/**
 * @return 允许请求的入口
 */
function safeWebLook(){

    //允许请求的入口 网址   start
    $allowed_domains = ['hsz.bcwhkj.cn', '10.141.128.246', '183.239.104.190', '127.0.0.1', 'www.stredcross.org.cn','stredcross.org.cn'];
    $parsed_url = parse_url($_SERVER['HTTP_HOST']);
    if (!in_array($parsed_url['path'], $allowed_domains)) {
        // 不在白名单中，拒绝请求
        // echo '不在白名单中，拒绝请求';
        // exit;
    }

    //允许请求的入口 网址   end
}
function getChildSortId($parentSortid,$bm){
    if($parentSortid){
        $SortWhere['cxsort']=['like',openrs_('cxproductsort','cxsort','cxid='.$parentSortid).'%'];

        $allSortid=db($bm)
            ->field('cxid')
            ->where($SortWhere)
            ->select();
        $alls=[];
        foreach($allSortid as $k=>$v){
            $alls[$k]=$v['cxid'];
        }

        $alls=implode(',',$alls);
        return $alls;
        //$SortWhere['cxsortid']=['in',$alls];
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




/*
数组转成json字符串
$code 返回状态码
$arr 数组类型
*/
function jsonencode($code,$mess,$arr=[],$other=[]){
    if(!is_numeric($code)){
        return false;
    }
    $data=[
        'codes'=>$code,
        'mess'=>$mess,
        'data'=>$arr,
    ];
//    if(is_array($arr) && $arr){
//        $data['data']=$arr;
//    }
    if($other){
        $data['other']=$other;
    }
    echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;
}


/**
 * @return 根据SQL返回第一个字段值
 * @param $tab 表名
 * @param $zd 字段名
 * @param array $where  SQL语句，如'cxid='.$param['cxsortid']).'%'
 * @param string $defval 默认值
 * @return mixed|string
 */
function openrs_($tab,$zd,$where=[],$defval=''){
    $data=\think\facade\Db::table($tab)->where($where)->find();
    if(!$data){
        return $defval;
    }else{
        return $data[$zd];
    }
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
 * @return 返回当前日期时分秒
 */
function Now(){
    return date('Y-m-d H:i:s',time());
}

/**
 * @param $len 长度
 * @param null $chars
 * @return 随机生成字符串$len（位数）
 */
function getRandomString($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}

/**
 * @return 有值
 * @param $e 要判断的值
 */
function isVal($e){
    if(!empty($e)) {
        if (isset($e)) {
            if (!empty($e)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }else{
        return  false;
    }

}