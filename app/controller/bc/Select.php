<?php
namespace app\controller\bc;


use app\BaseController;
use think\facade\Db;

class Select extends Config
{
    public function index()
    {

        $param = request()->param();
        if ($param) {
            $search=[];
            $searchSql='1=1';
            if(!isset($param['page'])){$param['page']=1;}
            if(!isset($param['limit'])){$param['limit']=20;}
            if(r_('table')){
                $param['table']=$param['table'];
            }else{
                $param['table']='cxproduct';
            }
            if(r_('order')){$order=$param['order'];}else{$order='cxupdatetime desc';}

            if(isset($param['keywordName']) && isset($param['keywordValue'])){
                $search[$param['keywordName']]=['like','%'.$param['keywordValue'].'%'];
            }
            if($param['table']=='cxkehu'){
                if(r_('keyword')){$search['cxtitlecn|cxWeiXin|cxcompany|cxcontentcn']=['like','%'.$param['keyword'].'%'];}
            }else{
                if(r_('keyword')){$search['cxtitlecn|cxcontentcn']=['like','%'.$param['keyword'].'%'];}
            }
            if(r_('cxpassed')){$search['cxpassed']=['=',$param['cxpassed']];}
            if(r_('cxuserid')){$search['cxuserid']=['=',$param['cxuserid']];}
            if(r_('cxlevel')){$search['cxlevel']=['=',$param['cxlevel']];}
            if (r_('jieGuo')) { $search['cxGenJinJieGuo'] = ['=', $param['jieGuo']];}
            if (r_('cxphone')) { $search['cxphone'] = ['like', '%'.$param['cxphone'].'%'];}
            if (r_('cxNewOldKeHu')) { $search['cxNewOldKeHu'] = ['=', $param['cxNewOldKeHu']];}
            if (r_('cxid')) { $search['cxid'] = ['=', $param['cxid']];}
            if (r_('cxcontentcn')) { $search['cxcontentcn'] = ['like', '%'.$param['cxcontentcn'].'%'];}

            if(r_('adminId')){
                $adminUser=Db::name('cxadmin')->field('cxpids')->find($param['adminId']);
                if($adminUser['cxpids']!='0' && $adminUser['cxpids']!=''){$searchSql.=" and cxsortid in (".$adminUser['cxpids'].")";}
            }

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


            if(r_('cxBiaoQian')){
                if(count($param['cxBiaoQian'])>1){

                    $search['cxBiaoQian']=[];
                    foreach ($param['cxBiaoQian'] as $k=>$v){
                        array_push($search['cxBiaoQian'],['like','%'.$v.'%']);
                    }
                }else if(count($param['cxBiaoQian'])==1){
                    $search['cxBiaoQian']=['like','%'.$param['cxBiaoQian'][0].'%'];

                }
            }


            if(r_('cxsortid')){
                $alls=[];
                $SortWhere=[];
                $SortWhere['cxsort']=['like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%'];
                $allSortid=Db::name('cxproductsort')
                    ->field('cxid')
                    ->where($SortWhere)
                    ->select();
                foreach($allSortid as $k=>$v){
                    $alls[$k]=$v['cxid'];
                }

                $alls=implode(',',$alls);
                $search['cxsortid']=['in',$alls];
            }
            $rs = Db::name($param['table'])
                //->where('cxsortid','in',$alls)
                ->where($searchSql)
                ->where($search)
                ->page($param['page'], $param['limit'])
                ->order($order.',cxid desc')
                ->select();
            $sql=Db::name($param['table'])->getlastsql();
            $rs=$rs->toArray();

            if(($param['table']=='cxproduct' || $param['table']=='cxkehu' || $param['table']=='cxcp' || $param['table']=='cxkeyword')  && $rs){
                foreach ($rs as $k=>$v){
                    $rs[$k]['cxsortcn']=openrs_('cxproductsort','cxsortcn','cxid='.$v['cxsortid']);
                    if($v['cxuserid']>0){
                        $rs[$k]['cxuserName']=openrs_('cxuser','cxname','cxid='.$v['cxuserid']);
                    }else{
                        $rs[$k]['cxuserName']='';

                    }
                    if($param['table']=='cxkehu'){
                        if(($v['cxGenJinTime'])){
                            $data1=date_create(Now());
                            $data2=date_create($v['cxGenJinTime']);
                            $rs[$k]['cxTimes'] = $v['cxGenJinTime']?date_diff($data1,$data2)->format("%R%a"):'';
                        }
                    }

                }
            }

            $data['code'] = 0;
            $data['msg'] = '拉取列表数据成功';
            $data['count'] = Db::name($param['table'])
                //->where('cxsortid','in',$alls)
                ->where($searchSql)
                ->where($search)
                ->count();;
            $data['limit'] = $param['limit'];
            $data['pageCur'] = $param['page'];
            $data['pageCount'] = ceil($data['count']/$param['limit']);
            $data['data'] = $rs;
            $data['sql'] = $sql;


            return json($data);

        }
    }


    public function info()
    {
        $param = request()->param();
        if ($param) {


            $rs = Db::name($param['table'])->find($param['cxid']);

            $data['code'] = 0;
            $data['msg'] = '拉取单条数据成功';
            $data['data'] = $rs;


            return json($data);

        }
    }


    public function add()
    {
        $param = request()->param();
        if ($param) {
            $data=[];
            $table=$param['table'];
            unset($param['table']);
            $rs = Db::name($table)->insertGetId($param);


            if($rs) {
                $data['code'] = 0;
                $rs=Db::name($table)->find($rs);
            }else {
                $data['code'] = 500;
            }


            $data['msg'] = '添加数据成功';
            $data['data'] = $rs;


            return json($data);

        }
    }


    public function edit()
    {
        $param = request()->param();
        if ($param) {
            $table=$param['table'];
            unset($param['table']);
            $rs = Db::name($table)->where('cxid',$param['cxid'])->update($param);

            if($rs) {
                $data['code'] = 0;
            }else {
                $data['code'] = 500;
            }

            $data['data'] = $rs;
            $data['code'] = 0;
            $data['msg'] = '修改数据成功';


            return json($data);

        }
    }

    public function copy()
    {
        $param = request()->param();
        if ($param) {
            $table=$param['table'];
            unset($param['table']);
            $rs = Db::name($table)->find($param['cxid']);
            unset($rs['cxid']);
            Db::name($table)->insertGetId($rs);

            $data['data'] = $rs;
            $data['code'] = 0;
            $data['msg'] = '复制数据成功';


            return json($data);

        }
    }
}

?>