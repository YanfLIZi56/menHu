<?php
namespace app\controller\bc;

use app\BaseController;
use think\facade\Db;


class Home extends Config
{

    public function __construct(){
        parent::__construct();
    }



    public function getlist()
    {
        $param = request()->param();


        $this->checkQuanXian();
        $this->qs('查询列表');
        unset($param['adminToken']);


        $wheres = [];
        //if(!isset($param['keyword'])){$param['keyword']='';} //搜索关键词
        if (!isset($param['page'])) { $param['page'] = 1;}//定位页面
        if (!isset($param['limit'])) {$param['limit'] = 20;} //每页分页数
        if (!isset($param['bm'])) {$param['bm'] = 'cxnews';}//要查询的表名
        if (!isset($param['order'])) {$param['order'] = 'cxupdatetime desc,cxid desc';} //排序条件
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
            $SortWhere['cxsort'] = ['like', openrs_('cxproductsort', 'cxsort', 'cxid=' . $param['cxsortid']) . '%'];
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

        $searchSql0='';
        if($param['bm']=='cxnews'){
            if (r_('cxsortid')) {
                $s=$param['cxsortid'];
                $sqlIn=Db::table('cxnews_sort')
                    ->field('cxid')
                    ->where('cxid|cxpid', 'in', $s)
                    ->buildSql();
                $searchSql0='cxsortid in '.$sqlIn;

//                    $search['cxsortid']=['in',function($query) use ($s){
//                        $query->table('cxnews_sort')->where('cxid|cxpid',$s)->field('cxid');
//                    }];
            }
        }else{
            if(r_('cxsortid')){$wheres['cxsortid']=['=',$param['cxsortid']];}

        }


        $rs = Db::name($param['bm'])
            ->where($wheres)
            ->where($searchSql0)
            ->page($param['page'], $param['limit'])
            ->order($param['order'])
            ->select()->toArray();
        foreach ($rs as $k=>$v){
            if($v['cximg0']){
                //$data['rows'][$k]['cximg0']=str_replace('https','http',$v['cximg0']);
                $rs[$k]['cximg0']=str_replace('https://www.stredcross.org.cn','',$v['cximg0']);

            }
        }
        $sql=Db::name($param['bm'])->getlastsql();

//        if($rs && ($param['bm']=='cxproduct' ||  $param['bm']=='cxkehu')){
//            foreach ($rs as $k=>$v){
//                if($v['cxsortid']>0){$data[$k]['cxsortcn']=openrs_('cxproductsort','cxsortcn','cxid='.$v['cxsortid']);}
//                if($v['cxuserid']>0){$data[$k]['cxuserName']=openrs_('cxuser','cxname','cxid='.$v['cxuserid']);}
//            }
//        }
        if($rs && ($param['bm']=='cxadmin')){
            foreach ($rs as $k=>$v){
                if($v['cxpids']){
                    $temps=Db::name('cxproductsort')->where('cxid','in',$v['cxpids'])->select();
                    $str = '';
                    foreach ($temps as $key => $value)
                    {
                        $key>0 ? $str.=',' : $str=$str;
                        $str.=$value['cxsortcn'];
                    }
                    $rs[$k]['cxpidsName']=$str;
                }
            }
        }


        $data['rows'] = $rs;
        $data['count'] = count($rs);;
        $data['limit'] = $param['limit'];
        $data['pageCur'] = $param['page'];
        $data['pageCount'] = ceil($data['count']/$param['limit']);
        //$data['sql'] = $sql;
        jsonencode(200, '获取列表数据成功', $data);
    }

// /public/uploads/20220701/c2475baff776b8e14d4ab70732576e60.xlsx



    /**
     * @return 获取详情（cxid）
     */
    public function getProInfo()
    {
        $param = request()->param();
        if (empty($param['cxid'])) {jsonencode(400, '要查询的信息ID不能为空');}
        r_('bm') ? $table=$param['bm'] : $table='cxproduct';

        $data = Db::name($table)
            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->find($param['cxid']);

        if ($table == 'cxproduct') {
            $data['cxsortcn'] = openrs_('cxproductsort', 'cxsortcn', ['cxid' => $data['cxsortid']], '');
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

        $data['cxjson'] = json_decode($data['cxjson']);


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
     * @return 获取全部分类数据
     */
    public function getAllProSort()
    {
        $param = request()->param();
        $search=[];
        if(r_('cxsortid')){
            $search[]=['cxid','in',$param['cxsortid']];
        }

        $data = Db::name('cxnews_sort')
            ->field('cxid,cxsortcn,cxpids,cxpid')
            ->order('cxpids asc,cxid desc')
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
     * @return 获取对应ID分类的子类别（cxsortid）
     */
    public function getProSortSun()
    {
        $param = request()->param();
        if (empty($param['cxsortid'])) {
            jsonencode(400, '要查询的分类ID不能为空');
        }
        $data = [];

        $SortWhere = [];

        $SortWhere['cxsort'] = ['like', openrs_('cxproductsort', 'cxsort', 'cxid=' . $param['cxsortid']) . '%'];

        if (isset($param['noSelf'])) {
            $SortWhere['cxid'] = ['<>', $param['cxsortid']];
        }

        $data = Db::name('cxproductsort')
            ->where($SortWhere)
            ->field('cxid,cxsort,cxsortcn,cxSorting,cximg0,cxpid')
            ->order('cxSorting asc,cxsort asc')
            ->select();


        if ($data) {
            jsonencode(200, '获取单一分类及子分类列表数据成功', $data);
        } else {
            jsonencode(300, '查无此信息', $data);
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

        if (empty($param['cxtitle'])) {
            jsonencode(400, '标题不能为空');
        }
        if (empty($param['cxphone'])) {
            jsonencode(400, '电话不能为空');
        }
        if (empty($param['cxcontent'])) {
            jsonencode(400, '内容不能为空');
        }

        $param['cxtype'] = 1;
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
        if(r_('cxcompany')){$param['cxcompany']=htmlspecialchars_decode($param['cxcompany']);}
        if(r_('cxtitlecn')){$param['cxtitlecn']=htmlspecialchars_decode($param['cxtitlecn']);}
        if(r_('cxcontentcn')){$param['cxcontentcn']=htmlspecialchars_decode($param['cxcontentcn']);}
        if(r_('cxcontenten')){$param['cxcontenten']=htmlspecialchars_decode($param['cxcontenten']);}
        if(r_('cxphone') && $table=='cxkehu'){
            if(r_('cxGuoNeiWai')){
                if($param['cxGuoNeiWai']=='国内'){
                    $checkPhoneChongFu=Db::name('cxkehu')->where('cxphone',$param['cxphone'])->select();
                    if($checkPhoneChongFu){
                        jsonencode(500, '手机号重复，无法录入，请检查', $data);
                        exit;
                    }
                }
            }
        }

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

            if(r_('adminToken')){
                $admin=$this->CheckToKenAdmin($param['adminToken']);
                $this->addLog('复制操作',$admin['cxadmin']);
                unset($param['adminToken']);
            }

            unset($param['bm']);
            $rs = Db::name($table)->find($param['cxid']);
            unset($rs['cxid']);
            $rs['cxtitlecn'] = $rs['cxtitlecn'] .' 副本';
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

        $admin=$this->CheckToKenAdmin($param['adminToken']);
        $this->addLog('删除操作',$admin['cxadmin']);
        unset($param['adminToken']);

        unset($param['bm']);
        $data = Db::table($table)->where('cxid', 'in', $param['cxid'])->delete();
        if ($data) {
            jsonencode(200, '删除成功', $data);
        } else {
            jsonencode(300, '删除失败'.Db::table($table)->getlastsql(), $data);

        }


    }



    /**
     * @return  更新排序
     */
    public function upTime()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名

        if(r_('adminToken')){
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            $this->addLog('更新排序操作',$admin['cxadmin']);
            unset($param['adminToken']);
        }

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

        if(r_('adminToken')){
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            $this->addLog('更新状态操作',$admin['cxadmin']);
            unset($param['adminToken']);
        }

        unset($param['bm']);
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxpassed' => $param['cxpassed']]);
        if ($data) {
            jsonencode(200, '更新状态成功', $data);
        } else {
            jsonencode(300, '更新状态失败', $data);

        }
    }

    /**
     * @return  批量变更分类
     */
    public function changeSort()
    {
        $param = request()->param();
        $table = $param['bm'];//要查询的表名

        if(r_('adminToken')){
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            $this->addLog('变更分类操作',$admin['cxadmin']);
            unset($param['adminToken']);
        }

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
?>