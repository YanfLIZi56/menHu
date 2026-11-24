<?php
namespace app\controller\bc;


use app\BaseController;
use think\facade\Db;
use think\facade\Request;

class Wanneng extends Config
{
    public function __construct(){
        parent::__construct();

        $this->checkQuanXian();


    }


    /**
     * @return 万能列表
     */
    public function getList()
    {



        $param = request()->param();

        if ($param) {
            $search=[];
            $wheres='';
            $searchSql='1=1';
            if(!isset($param['page'])){$param['page']=1;}
            if(!isset($param['limit'])){$param['limit']=20;}
            if(r_('table')){
                $param['table']=$param['table'];
            }else{
                $param['table']='cxnews';
            }
            if(strlen($param['table'])>20){
                jsonencode(300,'biaoError','');
                exit;
            }
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            if($param['table']=='cxadmin'){
                if($this->instr($admin['cxMenu'],'adminUser.html')==-1){
                    jsonencode(300,'无权限','');
                    exit;
                }
            }

            if(r_('order')){$order=$param['order'];}else{$order='cxupdatetime desc';}

            if(r_('keywordName') && r_('keywordValue')){

                $search[$param['keywordName']]=['like','%'.$param['keywordValue'].'%'];
            }
            if(r_('keyword')){$search[]=['cxtitlecn|cxcontentcn','like','%'.$param['keyword'].'%'];}

            $searchSql0='';
//            if($param['table']=='cxnews'){
//                if (r_('cxsortid')) {
//                    $s=$param['cxsortid'];
//                    $sqlIn=Db::table('cxnews_sort')
//                        ->field('cxid')
//                        ->where('cxid|cxpid', 'in', $s)
//                        ->buildSql();
//                    $searchSql0=$sqlIn;
//
////                    $search['cxsortid']=['in',function($query) use ($s){
////                        $query->table('cxnews_sort')->where('cxid|cxpid',$s)->field('cxid');
////                    }];
//                }
//            }else{
//                if(r_('cxsortid')){$search['cxsortid']=['=',$param['cxsortid']];}
//
//            }


            if(r_('cxsortid')){
                if($param['table']=='cxnews'){
                    $rsSort=Db::name('cxnews_sort')->find($param['cxsortid']);
                    if($rsSort){
                        $rsSort=Db::name('cxnews_sort')->where('cxpids','like',$rsSort['cxpids']."%")->select()->toArray();
                        $d='9999';
                        foreach($rsSort as $k=>$v){
                            $d.=','.$v['cxid'];
                        }
                        $search[]=['cxsortid','in',$d];
                    }
                }else{

                    $search[]=['cxsortid','in',$param['cxsortid']];
                }
            }

            $admin=$this->CheckToKenAdmin($param['adminToken']);
            if($admin['cxadmin']!='adminzy'){
                if($param['table']=='cxnews_sort'){
                    $search[]=['cxid','in',$admin['cxpids']];
                }elseif($param['table']=='cxnews'){
                    $search[]=['cxsortid','in',$admin['cxpids']];
                }
            }
            unset($param['adminToken']);




            if(r_('cxpassed')){$search[]=['cxpassed','in',$param['cxpassed']];}
            if(r_('cxuserid')){$search[]=['cxuserid','=',$param['cxuserid']];}
            if(r_('cxlevel')){$search[]=['cxlevel','=',$param['cxlevel']];}
            if(r_('cxisRead')){$search['cxisRead']=['=',$param['cxisRead']];}
            if(r_('cxpid')){$search['cxpid']=['=',$param['cxpid']];}
            if(r_('cxpid2')){$search['cxpid2']=['=',$param['cxpid2']];}
            if(r_('cxpid3')){$search['cxpid3']=['=',$param['cxpid3']];}


            if (r_('cxphone')) { $search['cxphone'] = ['like', '%'.$param['cxphone'].'%'];}
            if (r_('cxid')) { $search[] = ['cxid','=', $param['cxid']];}
            if (r_('cxcontentcn')) { $search['cxcontentcn'] = ['like', '%'.$param['cxcontentcn'].'%'];}

//            if(r_('adminId')){
//                $adminUser=Db::table('cxadmin')->field('cxpids')->find($param['adminId']);
//                if($adminUser['cxpids']!='0' && $adminUser['cxpids']!=''){$searchSql.=" and cxsortid in (".$adminUser['cxpids'].")";}
//            }

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
                ->select();
            $data['sql']=Db::name($param['table'])->getlastsql();
//
            $data['rows']=$data['rows']->toArray();
            if(($param['table']=='cxnews_sort')  && $data['rows']){
                foreach ($data['rows'] as $k=>$v){
                    $SortSearch=[];
                    $rss=Db::name('cxnews_sort')->where('cxpids','like',$v['cxpids']."%")->select()->toArray();
                    $d='9999';
                    foreach($rss as $kk=>$vv){
                        $d.=','.$vv['cxid'];
                    }
                    $SortSearch[]=['cxsortid','in',$d];
                    $data['rows'][$k]['sl'] = Db::name('cxnews')->where($SortSearch)->count();

                }
            }


            if($data['rows']){
                foreach ($data['rows'] as $k=>$v){
                    if(isset($v['cxtoken'])){unset( $data['rows'][$k]['cxtoken']);}
                    if(isset($v['cxpassword'])){unset( $data['rows'][$k]['cxpassword']);}


                        if(isset($v['cximg0'])){
                            //$data['rows'][$k]['cximg0']=str_replace('https','http',$v['cximg0']);
                            $data['rows'][$k]['cximg0']=str_replace('https://www.stredcross.org.cn','',$v['cximg0']);

                        }

                }
            }

            $data['count'] = Db::table($param['table'])->where($searchSql)
                ->where($searchSql0)
                ->where($search)->count();
            $data['limit'] = $param['limit'];
            $data['pageCur'] = $param['page'];
            $data['pageCount'] = ceil($data['count']/$param['limit']);


            jsonencode(200,'获取列表数据完成',$data);

        }
    }


    public function info()
    {
        $param = request()->param();
        if ($param) {
            $this->qs('查询信息详情');
            unset($param['adminToken']);

            $rs = Db::table($param['table'])->find($param['cxid']);

            if(isset($rs['cximg0'])){

                if($rs['cximg0']){
                    //$data['rows'][$k]['cximg0']=str_replace('https','http',$v['cximg0']);
                    $rs['cximg0']=str_replace('https://www.stredcross.org.cn','',$rs['cximg0']);

                }
            }
            if($rs){
                jsonencode(200,'拉取单条数据成功',$rs);
            }else{
                jsonencode(300,'查询不到单个产品信息','');
            }

        }
    }


    public function add()
    {
        $param = request()->param(false);
        if ($param) {
            $data=[];
            $table=$param['table'];
            unset($param['table']);
            $this->qs('录入新资料');
            unset($param['adminToken']);
            if(r_('cxtitlecn')){$param['cxtitlecn']=$this->filterXss($param['cxtitlecn']);}
            if(r_('cxcontentcn')){$param['cxcontentcn']=$this->filterXss($param['cxcontentcn']);}
            if(r_('cxcontenten')){$param['cxcontenten']=$this->filterXss($param['cxcontenten']);}
            if(r_('cxcontentWapcn')){$param['cxcontentWapcn']=$this->filterXss($param['cxcontentWapcn']);}
            if(r_('cxcontentWapen')){$param['cxcontentWapen']=$this->filterXss($param['cxcontentWapen']);}

            $rs = Db::table($table)->insertGetId($param);


            if($rs) {
                $rs=Db::table($table)->find($rs);


                //$this->addLogs('录入文章',);
                jsonencode(200,'添加成功',$rs);

            }else {
                jsonencode(500,'添加失败',Db::table($table)->getlastsql());
            }


        }
    }

    /**
     * @return 添加日志|void
     */
    public function addLogs(){
        $param=request()->param();
        $this->addLog($param['title'],$param['userName']);
        jsonencode(200,'添加日志成功',$param);
    }

    public function edit()
    {
        $param = request()->param(false);
        if ($param) {
            $table=$param['table'];
            unset($param['table']);

            $this->checkQuanXian();
            $this->qs('修改操作');
            unset($param['adminToken']);

            if(r_('cxtitlecn')){$param['cxtitlecn']=$this->filterXss($param['cxtitlecn']);}
            if(r_('cxcontentcn')){$param['cxcontentcn']=$this->filterXss($param['cxcontentcn']);}
            if(r_('cxcontenten')){$param['cxcontenten']=$this->filterXss($param['cxcontenten']);}
            if(r_('cxcontentWapcn')){$param['cxcontentWapcn']=$this->filterXss($param['cxcontentWapcn']);}
            if(r_('cxcontentWapen')){$param['cxcontentWapen']=$this->filterXss($param['cxcontentWapen']);}
            $rs = Db::table($table)->where('cxid',$param['cxid'])->update($param);

            if($rs) {

                jsonencode(200,'修改成功',Db::table($table)->getlastsql());
            }else {
                jsonencode(500,'没有修改任何数据',Db::table($table)->getlastsql());
            }


        }
    }

    public function copy()
    {
        $param = request()->param();
        if ($param) {
            $table=$param['table'];
            unset($param['table']);
            $rs = Db::table($table)->find($param['cxid']);
            unset($rs['cxid']);
            Db::table($table)->insertGetId($rs);

            jsonencode(200,'复制数据成功',Db::table($table)->getlastsql());

        }
    }



    //分类cxpids初始化
    public function sortInit(){
        $biaoMing='cxnews_sort';
        $allSortTop=Db::name($biaoMing)->where('cxpid',0)->select()->toArray();
        $allSort=Db::name($biaoMing)->field('cxid,cxsortcn,cxpid,cxpids,cxpid1,cxpid2,cxpid3,cxpid4')->select()->toArray();
        if($allSort){
            foreach($allSort as $k=>$v){
                $data=[];
                if($v['cxpid']==0){
                    $data['cxpid1']=$v['cxid'];
                    $data['cxpids']=$v['cxid'].',';
                    Db::name($biaoMing)->where('cxid',$v['cxid'])->update($data);
                }else{
                    $newPids=$v['cxpid'].','.$v['cxid'].','; //pid1
                    $parentClassId=false;

                    if(!in_array($v['cxpid'],$allSortTop)){ //如果上级ID不是一级大类，也大于0的话  pid2
                        $parentClassId=Db::name($biaoMing)->where('cxid',$v['cxpid'])->find(); //找出上级类的上级类
                        if($parentClassId['cxpid']>0){
                            $parentClassId ?  $newPids=$parentClassId['cxpid'].','.$newPids : '';  //找到就加在前面
                        }
                    }
                    if($parentClassId){
                        if(!in_array($parentClassId['cxpid'],$allSortTop)){
                            $parentClassId2=Db::name($biaoMing)->where('cxid',$parentClassId['cxpid'])->find();
                            if($parentClassId2){
                                if($parentClassId2['cxpid']>0){
                                    $parentClassId2 ? $newPids=$parentClassId2['cxpid'].','.$newPids : '';
                                }
                            }
                        }
                    }

                    $d=explode(',',$newPids);

                    $allSort[$k]['cxid']=$v['cxid'];
                    $allSort[$k]['cxpids']= $newPids;
                    $allSort[$k]['cxpid1']= isset($d[0]) ? $d[0] : 0;
                    $allSort[$k]['cxpid2']= isset($d[1]) ? $d[1] : 0;
                    $allSort[$k]['cxpid3']= isset($d[2]) ? $d[2] : 0;
                    $allSort[$k]['cxpid4']= isset($d[3]) ? $d[3] : 0;

                    $data['cxpids']=$newPids;
                    $data['cxpid1']= isset($d[0]) ? $d[0] : 0;
                    $data['cxpid2']= isset($d[1]) ? $d[1] : 0;
                    $data['cxpid3']= isset($d[2]) ? $d[2] : 0;
                    $data['cxpid4']= isset($d[3]) ? $d[3] : 0;
                    Db::name($biaoMing)->where('cxid',$v['cxid'])->update($data);

                }
            }
        }
        //Db::name($biaoMing)->save($allSort);
        //dump($allSort);
        //exit;
        jsonencode(200,'更新分类PIDS完成','');
    }


    public function getMenu(){
        $table='cxzy';
        $param=request()->param();

        if(r_('adminToken')){
            $search=[];
            $admin=$this->CheckToKenAdmin($param['adminToken']);
            if($admin['cxadmin']=='adminzy'){
                $search[]=['cxid','>',0];
            }elseif($admin['cxadmin']){
                $glUrl=json_decode($admin['cxMenu']);
                $search[]=['cxurl','in',$glUrl];
            }else{
                jsonencode(500,'暂无权限访问,请重新登陆',$param);
                exit;
            }
            $order='cxupdatetime desc';
            $data['rows'] = Db::table($table)
                //->where('cxsortid','in',$alls)
                ->where($search)
                ->whereOr([['cxpid','in',0]])
                ->order($order)
                ->select();
            $data['sql']=Db::name($table)->getlastsql();
            $data['rows']=$data['rows']->toArray();
            jsonencode(200,'获取导航列表成功',$data);
        }else{

            jsonencode(500,'暂无权限访问,请重新登陆',$param);
        }


    }



}

?>