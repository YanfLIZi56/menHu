<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;


class Home extends Config
{

    public function __construct()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST,PUT,OPTIONS,DELETE");
        header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept");
        parent::__construct();
    }

    public function asd(){
        phpinfo(); 

    }

    public function ddos(){
        $param = request()->param();
        $param['table']='cxnews';
        $data['rows'] = Db::table($param['table'])
            //->where('cxsortid','in',$alls)
            ->page(1, 10)
            ->select();
        jsonencode(200,'你好',$data);
    }

    public function getlist()
    {

        $param = request()->param();
        if ($param) {
            $search=[];
            $wheres='';
            $searchSql='1=1';
            if(!isset($param['page'])){$param['page']=1;}
            if(!isset($param['limit'])){
                $param['limit']=20;
            }else{
                $param['limit']=$this->filterXss(intval($param['limit']));
            }
            if(r_('table')){
                $param['table']=$param['table'];
                if($param['table']=='cxnews' || $param['table']=='cxnews_sort'){ //只允许访问的表

                }else{
                    echo '无权限';
                    exit;
                }
            }else{
                jsonencode(500,'无效请求','');
                //$param['table']='cxnews';
            }
            if(r_('order')){$order=$param['order'];}else{$order='cxupdatetime desc';}

            if(isset($param['keywordName']) && isset($param['keywordValue'])){
                $search[$param['keywordName']]=['like','%'.$param['keywordValue'].'%'];
            }
            if(r_('keyword')){$search[]=['cxtitlecn|cxcontentcn','like','%'.$param['keyword'].'%'];}

            $searchSql0='';
//            if($param['table']=='cxnews'){
//                if (r_('cxsortid')) {
//                    $allpid='';
//                    $sqlIn=Db::table('cxnews_sort')
//                        ->field('cxid')
//                        ->where('cxpids', 'like', $param['cxsortid'].',%')
//                        ->select();
//                    foreach($sqlIn as $K=>$v){
//                        $v[]
//                    }
//                    echo $sqlIn;
//                    exit;
//                    $searchSql0=$sqlIn;
//
//                }
//            }else{
//                if(r_('cxsortid')){$search[]=['cxsortid','in',$param['cxsortid']];}
//
//            }

            if(r_('cxsortid')){
                $rsSort=Db::name('cxnews_sort')->find($param['cxsortid']);
                if($rsSort){
                    $rsSort=Db::name('cxnews_sort')->where('cxpids','like',$rsSort['cxpids']."%")->select()->toArray();
                    $d='9999';
                    foreach($rsSort as $k=>$v){
                        $d.=','.$v['cxid'];
                    }
                    $search[]=['cxsortid','in',$d];
                }
            }
            if(r_('cxpassed')){ $search[]=['cxpassed','=',$param['cxpassed']];}
            if(r_('cxuserid')){ $search[]=['cxuserid','=',$param['cxuserid']];}
            if(r_('cxisRead')){ $search[]=['cxisRead','=',$param['cxisRead']];}
            if(r_('cxpid')){    $search[]=['cxpid','in',$param['cxpid']];}
            if(r_('cxid')) {    $search[]=['cxid','=', $param['cxid']];}


            if(isset($param['selectSql'])){$searchSql=$param['selectSql'];}

//            if(!empty($param['date1']) && !empty($param['date2'])){
//                $searchSql.=" and cxGenJinTime>='".$param['date1']." 00:00:00' and cxGenJinTime<='".$param['date2']." 23：59:59'";
//            }
//            if(!empty($param['date1']) && empty($param['date2'])){
//                $searchSql.=" and cxGenJinTime >='".$param['date1']." 00:00:00'";
//            }
//            if(empty($param['date1']) && !empty($param['date2'])){
//                $searchSql.=" and cxGenJinTime <='".$param['date2']." 23：59:59'";
//            }

            if(!empty($param['addDate1']) && !empty($param['addDate2'])){
                $searchSql.=" and cxaddtime>='".$param['addDate1']." 00:00:00' and cxaddtime<='".$param['addDate2']." 23：59:59'";
            }
            if(!empty($param['addDate1']) && empty($param['addDate2'])){
                $searchSql.=" and cxaddtime >='".$param['addDate1']." 00:00:00'";
            }
            if(empty($param['addDate1']) && !empty($param['addDate2'])){
                $searchSql.=" and cxaddtime <='".$param['addDate2']." 23：59:59'";
            }


            if(!empty($param['updateDate1']) && !empty($param['updateDate2'])){
                $searchSql.=" and cxupdatetime>='".$param['updateDate1']." 00:00:00' and cxupdatetime<='".$param['updateDate2']." 23：59:59'";
            }
            if(!empty($param['updateDate1']) && empty($param['updateDate2'])){
                $searchSql.=" and cxupdatetime >='".$param['updateDate1']." 00:00:00'";
            }
            if(empty($param['updateDate1']) && !empty($param['updateDate2'])){
                $searchSql.=" and cxupdatetime <='".$param['updateDate2']." 23：59:59'";
            }


            if(!empty($param['GenJinDate1']) && !empty($param['GenJinDate2'])){
                $searchSql.=" and cxGenJinTime>='".$param['GenJinDate1']." 00:00:00' and cxGenJinTime<='".$param['GenJinDate2']." 23:59:59'";
            }
            if(!empty($param['GenJinDate1']) && empty($param['GenJinDate2'])){
                $searchSql.=" and cxGenJinTime >='".$param['GenJinDate1']." 00:00:00'";
            }
            if(empty($param['GenJinDate1']) && !empty($param['GenJinDate2'])){
                $searchSql.=" and cxGenJinTime <='".$param['GenJinDate2']." 23:59:59'";
            }



            $data['rows'] = Db::table($param['table'])
                //->where('cxsortid','in',$alls)
                ->where($searchSql)
                ->where($searchSql0)
                ->where($search)
                ->page($param['page'], $param['limit'])
                ->order($order)
                ->select()->toArray();
            //$data['sql']=Db::name($param['table'])->getlastsql();
//
//            if(($param['table']=='cxnews')  && $data['rows']){
//                foreach ($data['rows'] as $k=>$v){
//                    $data['rows'][$k]['cxsortcn']=openrs_('cxnews_sort','cxsortcn','cxid='.$v['cxsortid']);
//                    if($v['cxuserid']>0){
//                        $data['rows'][$k]['cxuserName']=openrs_('cxuser','cxname','cxid='.$v['cxuserid']);
//                    }else{
//                        $data['rows'][$k]['cxuserName']='';
//
//                    }
//
//                }
//            }
//
            foreach ($data['rows'] as $k=>$v){
                if($v['cximg0']!=''){
                    //$data['rows'][$k]['cximg0']=str_replace('https','http',$v['cximg0']);
                    $data['rows'][$k]['cximg0']=str_replace('https://www.stredcross.org.cn','',$v['cximg0']);
                }
            }


            $data['count'] = count(Db::table($param['table'])
                //->where('cxsortid','in',$alls)
                ->where($searchSql)
                ->where($searchSql0)
                ->where($search)->select());
            $data['limit'] = $param['limit'];
            $data['pageCur'] = $param['page'];
            $data['pageCount'] = ceil($data['count']/$param['limit']);


            jsonencode(200,'获取列表数据完成',$data);

        }
    }



    /**
     * @return 获取详情（cxid）
     */
    public function info()
    {
        $param = request()->param();
        if (empty($param['cxid'])) {jsonencode(400, '要查询的信息ID不能为空');}
        r_('table') ? $table=$param['table'] : $table='cxproduct';

        $data = Db::name($table)
            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->find($param['cxid']);
        if(isset($data['cxcontentcn'])){
            $data['cxcontentcn']=str_replace('https://www.stredcross.org.cn/','/',$data['cxcontentcn']);
        }




        if ($data) {
            jsonencode(200, '获取详情数据成功', $data);
        } else {
            jsonencode(300, '查无此信息', $data);
        }


    }

    /**
     * @return 分类ID下第一个文章内容（cxsortid）
     */
    public function getFirstProInfo()
    {
        $param = request()->param();
        if (empty($param['cxsortid'])) {
            jsonencode(400, '要查询的信息ID不能为空');
        }
        $data = [];

        $data = Db::name('cxproduct')
            ->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->where('cxsortid', $param['cxsortid'])
            ->limit(1)
            ->find();

        $data['cxsortcn'] = openrs_('cxproductsort', 'cxsortcn', ['cxid' => $data['cxsortid']], '');

        if ($data) {
            jsonencode(200, '获取详情数据成功', $data);
        } else {
            jsonencode(300, '查无此信息', $data);
        }


    }


    /**
     * @return 系统数据
     */
    public function getSys()
    {
        $param = request()->param();
        $data = [];

        $data = Db::name('cxconfig')
            //->field('cxwebtitlecn,cxjson')
            ->find(1);

        // $data['cxjson'] = json_decode($data['cxjson']);


        if ($data) {
            jsonencode(200, '获取系统数据成功', $data);
        } else {
            jsonencode(300, '获取系统数据失败', $data);
        }


    }


    /**
     * @return 获取对应分类信息（cxsortid）
     */
    public function getProSort()
    {
        $param = request()->param();
        $data = [];

        $data = Db::name('cxproductsort')
            ->field('cxid,cxsortcn,cxSorting,cximg0')
            ->order('cxSorting asc,cxsort asc,cxid desc')
            ->find($param['cxsortid']);


        if ($data) {
            jsonencode(200, '获取对应分类数据成功', $data);
        } else {
            jsonencode(300, '查无此信息', $data);
        }


    }


    /**
     * @return 获取全部产品
     */
    public function getAllPro()
    {
        $param = request()->param();
        $wheres = [];
        $table =  r_('bm') ? $param['bm'] : 'cxproduct';
        $tableSort = $table=='cxproduct' ? $table.'sort' : $table.'_sort';
        //if(!isset($param['keyword'])){$param['keyword']='';} //搜索关键词
        if (!isset($param['page'])) { $param['page'] = 1;}//定位页面
        if (!isset($param['limit'])) {$param['limit'] = 20;} //每页分页数
        if (!isset($param['bm'])) {$param['bm'] = 'cxproduct';}//要查询的表名
        if (!isset($param['order'])) {$param['order'] = 'cxupdatetime desc,cxid desc';} //排序条件

        if(r_('$field')){$field=$param['field'];}else{$field='';}
        if(r_('cxuserid')){$wheres['cxuserid']=['=',$param['cxuserid']];}
        if(r_('cxMenu')){$wheres['cxid']=['in',$param['cxMenu']];}
        if(r_('cxpassed')){$wheres['cxpassed']=['=',$param['cxpassed']];}


        $alls = [];

        if (r_('keyword')) {
            if($param['bm']=='cxadmin'){
                $wheres['cxadmin'] = ['like', '%' . $param['keyword'] . '%'];
            }else{
                $wheres['cxtitlecn|cxcontentcn'] = ['like', '%' . $param['keyword'] . '%'];
            }
        }

        if (r_('cxsortid')) {
            $SortWhere['cxsort'] = ['like', openrs_($tableSort, 'cxsort', 'cxid=' . $param['cxsortid']) . '%'];
            $allSortid = Db::name('cxproductsort')
                ->field('cxid')
                ->where($SortWhere)
                ->select();
            foreach ($allSortid as $k => $v) {
                $alls[$k] = $v['cxid'];
            }

            $alls = implode(',', $alls);
            $wheres['cxsortid']=['in',$alls];
        }

        $data['rows'] = Db::name($table)
            ->where($wheres)
            ->page($param['page'], $param['limit'])
            ->order($param['order'])
            ->select();
        $data['sql']=Db::name($table)->getlastsql();


        $data['count'] = Db::name($table)->field($field)->where($wheres)->count();;
        $data['limit'] = $param['limit'];
        $data['pageCur'] = $param['page'];
        $data['pageCount'] = ceil($data['count']/$param['limit']);





        jsonencode(200, '获取列表数据成功' ,  $data);
    }

    /**
     * @return 获取全部分类数据
     */
    public function getAllProSort()
    {
        $param = request()->param();
        $search='';
        if(r_('cxsortid')){
            $search['cxid']=['in',$param['cxsortid']];
        }
        $table=r_('table') ? $param['table'] : 'cxproductsort';
        $order=r_('order') ? $param['order'] : 'cxupdatetime desc,cxid desc';

        $data = Db::name($table)
            //->field('cxid,cxsortcn,cxsort,cxpid,cxpids')
            ->order($order)
            ->where($search)
            ->select();


        if ($data) {
            jsonencode(200, '获取分类列表数据成功', $data);
        } else {
            jsonencode(300, '查无此信息', $data);
        }
        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');


    }

    /**
     * @return 获取分类类型表数据
     */
    public function getSort(){
        $param = request()->param();
        $search='';
        $table=$param['table']?$param['table']:'cxproductsort';
        if(!isset($param['page'])){$param['page']=1;}//定位页面
        if(!isset($param['limit'])){$param['limit']=20;} //每页分页数
        if(!isset($param['order'])){$param['order']='cxid desc';} //排序条件
        if(r_('cxpassed')){$search['cxpassed']=['=',$param['cxpassed']];}
        $rs = Db::name($table)
            ->where($search)
            ->order('cxid desc')
            ->select();
        $data['sql'] = Db::name($table)->where($search)->getLastSql();
        $data['count'] = Db::name($table)->where($search)->count();;
        $data['limit'] = $param['limit'];
        $data['pageCur'] = $param['page'];
        $data['pageCount'] = ceil($data['count']/$param['limit']);
        $data['rows'] = $rs;

        jsonencode(200, '获取分类数据成功',$data);
    }

    /**
     * @return 获取对应ID分类的子类别（cxsortid）
     */
    public function getProSortSun()
    {
        $param = request()->param();
        if (empty($param['cxsortid'])) {
            jsonencode(400, '要查询的分类ID不能为空');
        }

        $table=r_('table') ? $param['table'] : 'cxnews_sort';
        $order=r_('order') ? $param['order'] : 'cxpids asc,cxid desc';
        $rs=Db::name($table)->find($param['cxsortid']);
        if($rs){
            $search=[];

            $search[]=['cxpids','like',$rs['cxpid1'].',%'];
            if(r_('cxpassed')){$search[]=['cxpassed','in',$param['cxpassed']];}

            $data['rows'] = Db::name($table)
                ->where($search)
                ->order($order)
                ->select();
            //$data['sql']=Db::name($table)->getlastsql();
            jsonencode(200, '获取单一分类及子分类列表数据成功', $data);

        }
        else {
            jsonencode(300, '查无此信息', '');
        }
        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');


    }


    /**
     * @return 留言
     */
    public function guestAdd()
    {
        $param = request()->param();
        $data = [];

        if (empty($param['cxtitlecn'])) {
            jsonencode(400, '姓名不能为空');
        }
        if (empty($param['cxphone'])) {
            jsonencode(400, '手机号不能为空');
        }
        if (empty($param['cxYuYueTime'])) {
            jsonencode(400, '预约时间不能为空');
        }
//        if (empty($param['cxcontentcn'])) {
//            jsonencode(400, '内容不能为空');
//        }
        $param['cxip']=Request::ip();

        $data = Db::name('cxguestbook')->insert($param);


        if ($data) {
            jsonencode(200, '留言提交成功', $data);
        } else {
            jsonencode(300, '提交失败', $data);
        }


    }


    /**
     * @return  添加
     */
    public function add()
    {
        $seach = [];
        $param = request()->param(false);
        $data = [];
        $table = '';
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        if(r_('cxtitlecn')){$param['cxtitlecn']=htmlspecialchars_decode($param['cxtitlecn']);}
        if(r_('cxcontentcn')){$param['cxcontentcn']=htmlspecialchars_decode($param['cxcontentcn']);}
        if(r_('cxcontenten')){$param['cxcontenten']=htmlspecialchars_decode($param['cxcontenten']);}

        $data = Db::name($table)->insertGetId($param);
        if ($data) {
            jsonencode(200, '添加成功', $data);
        } else {
            jsonencode(300, '添加失败', $data);

        }
    }

    /**
     * @return  批量添加
     */
    public function addAll()
    {
        $seach = [];
        $param = request()->param();
        $data = [];
        $table = '';
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data = Db::name($table)->insertAll($param);
        if ($data) {
            jsonencode(200, '修改成功', $data);
        } else {
            jsonencode(300, '修改失败', $data);

        }
    }


    /**
     * @return  复制
     */
    public function copy()
    {
        $param = request()->param();
        if ($param) {
            $table = $param['bm'];
            unset($param['bm']);
            $rs = Db::name($table)->find($param['cxid']);
            unset($rs['cxid']);
            $rs['cxupdatetime'] = Now();
            $rs['cxaddtime'] = Now();
            $getid = Db::name($table)->insertGetId($rs);

            $data['data'] = $getid;
            $data['code'] = 0;
            $data['msg'] = '复制数据成功';


            return json($data);

        }
    }


    /**
     * @return  修改
     */
    public function edit()
    {
        $seach = [];
        $param = request()->param(false);
        $data = [];
        $table = '';

        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $cid = $param['cxid'];
        unset($param['cxid']);
        unset($param['cxsortcn']);
//        if($table=='cxsjshenqing'){
//            if($param['cxpassed']==1){
//                $dbuserData=Db::name('cxuser')->find($param['cxuserid']);
//                if(DateDiff('d',$dbuserData['cxsjEndTime'],Now())>0){
//                    $Vipdate=Now();
//                }else{
//                    $Vipdate=$dbuserData['cxsjEndTime'];
//
//                }
//                $Vipdate=DateAdd('d',pubSjTime(),$Vipdate);
//                $s=Db::name('cxuser')->where('cxid',$param['cxuserid'])->update(['cxuserlevel'=>3,'cxsjEndTime'=>$Vipdate]);
//            }
//        }
        if(r_('cxcompany')){$param['cxcompany']=htmlspecialchars_decode($param['cxcompany']);}
        if(r_('cxtitlecn')){$param['cxtitlecn']=htmlspecialchars_decode($param['cxtitlecn']);}
        if(r_('cxcontentcn')){$param['cxcontentcn']=htmlspecialchars_decode($param['cxcontentcn']);}
        if(r_('cxcontenten')){$param['cxcontenten']=htmlspecialchars_decode($param['cxcontenten']);}
        //$param['cxupdatetime']=Now();

        $data = Db::name($table)->where('cxid', $cid)->update($param);
        $param['sql']=Db::name($table)->getlastsql();
        jsonencode(0, '修改成功', $param);


    }

    /**
     * @return  删除
     */
    public function del()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data['result'] = Db::name($table)->where('cxid', 'in', $param['cxid'])->delete(true);
        $data['sql']=Db::name($table)->getLastSql();
        if($table=='cxproduct'){
            $data['resultSun'] = Db::name('cxproduct_sun')->where('cxpid', 'in', $param['cxid'])->delete();
        }
        if($table=='cxproductorders'){
            $data['resultSun'] = Db::name('cxproductshopping')->where('cxorderid', 'in', $param['cxid'])->delete();
        }
        if ($data) {
            jsonencode(200, '删除成功', $data);
        } else {
            jsonencode(300, '删除失败', $data);

        }


    }

    /**
     * @return  更新排序
     */
    public function upTime()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxupdatetime' => Now()]);
        if ($data) {
            jsonencode(200, '更新成功', $data);
        } else {
            jsonencode(300, '更新失败', $data);

        }
    }

    /**
     * @return  批量更新状态
     */
    public function upZt()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $update='';
        $update['cxpassed']=$param['cxpassed'];
        if($table=='cxproductorders'){
            $passNameArr=explode(',',public_orderPassed);
            $update['cxpassedName']=$passNameArr[$param['cxpassed']];
        }
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update($update);
        if ($data) {
            jsonencode(200, '更新状态成功', $data);
        } else {
            jsonencode(300, '更新状态失败', $data);

        }
    }

    /**
     * @return  批量更新表的某个值
     */
    public function upAttr()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $update=[];
        $update[$param['fieldName']]=$param['value'];
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update($update);
        if ($data) {
            jsonencode(200, '更新成功', $data);
        } else {
            jsonencode(300, '更新失败', $data);

        }
    }

    /**
     * @return  批量变更分类
     */
    public function changeSort()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxsortid' => $param['cxsortid']]);
        if ($data) {
            jsonencode(200, '变更成功', $data);
        } else {
            jsonencode(300, '变更失败', $data);

        }


    }
    /**
     * @return  批量变更所属人员
     */
    public function changRenYuan()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxuserid' => $param['cxuserid']]);
        if ($data) {
            jsonencode(200, '变更成功', $data);
        } else {
            jsonencode(300, '变更失败', $data);

        }


    }
    /**
     * @return  获取是否有新消息
     */
    public function getNewMess()
    {
        $param = request()->param(false);
        $table = $param['table'];
        $search = r_('searchSql') ? $param['searchSql'] : 'cxisRead=0';
        $data = Db::name($table)->where($search)->count();
        jsonencode(200, '查询新消息成功 - '.Db::name($table)->getLastSql(), $data);
    }



    /**
     * @return  清空表
     */
    public function clearBiao()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名
        unset($param['bm']);
        $data = Db::name($table)->where('cxid', '>', 0)->delete();
        if ($data) {
            jsonencode(200, '清空成功', $data);
        } else {
            jsonencode(300, '清空失败', $data);

        }


    }


}
