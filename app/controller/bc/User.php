<?php
namespace app\controller\bc;


use app\BaseController;
use think\facade\Db;
use think\facade\Session;


class User extends Config
{

    public function getList()
    {
        $param=request()->param();
        $wheres=[];
        //if(!isset($param['keyword'])){$param['keyword']='';} //搜索关键词
        if(!isset($param['page'])){$param['page']=1;}//定位页面
        if(!isset($param['limit'])){$param['limit']=20;} //每页分页数
        $param['table']='cxuser';
        if(!isset($param['order'])){$param['order']='cxregtime desc,cxid desc';} //排序条件
        //if(isset($param['cxsortid'])){$wheres['cxsortid']=['in',$param['cxsortid']];}

        $alls=[];
//    	$allSortid=Db::name('cxproductsort')
//            ->field('cxid')
//            ->where('cxsort','like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%')
//            ->select();

        if(r_('keyword')){
            $wheres['cxname|cxfullname']=['like','%'.$param['keyword'].'%'];
        }

        if(r_('cxsortid')){
            $wheres['cxsortid']=['=',$param['cxsortid']];
        }

        $rs=Db::name($param['table'])
            ->where($wheres)
            ->page($param['page'],$param['limit'])
            ->order($param['order'])
            ->select();
        $sql=Db::name($param['table'])->getlastsql();
        $rs=$rs->toArray();

        foreach ($rs as $k=>$v) {
            //$rs[$k]['cxuserlevelName'] = openrs_('cxuserlevel','cxuserlevel',['cxid'=>$v['cxuserlevel']],'');
//            $rs[$k]['cxsortName'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$v['cxsortid']],'');
//            if(r_('sl')){
//                $rs[$k]['cxYeWuCount'] = Db::name('cxproduct')->where('cxuserid',$v['cxid'])->count();
//                $rs[$k]['cxKeHuCount'] = Db::name('cxkehu')->where('cxuserid',$v['cxid'])->count();
//            }

        }



        $data['count'] = count($rs);
        $data['limit'] = $param['limit'];
        $data['pageCur'] = $param['page'];
        $data['pageCount'] = ceil($data['count']/$param['limit']);
        $data['rows'] = $rs;
        $data['sql'] = $sql;

        jsonencode(200,'拉取列表数据成功',$data);
    }


    /**
     * @return 获取对应分类信息（cxsortid）
     */
    public function getProSort()
    {
        $param=request()->param();
        $data=[];
        $search='';
        if(r_('cxuserlevel')){
            $search['cxuserlevel']=['=',$param['cxuserlevel']];
        }

        $data['rows']=Db::name('cxuserlevel')
            ->order('cxid desc')
            ->where($search)
            ->select();


        if($data){
            jsonencode(200,'获取全部分类数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }




    }

    /**
     * @return 获取详情（cxid）
     */
    public function getInfo()
    {
        $param=request()->param();

        $this->qs('查询用户信息');

        $admin=$this->CheckToKenAdmin($param['adminToken']);
        if($this->instr($admin['cxMenu'],'adminUser.html')==-1){
            jsonencode(300,'无权限','');
            exit;
        }
        unset($param['adminToken']);


        if(empty($param['cxid'])){jsonencode(400,'要查询的信息ID不能为空');}
        $data=[];

        $data=Db::name('cxuser')
            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->find($param['cxid']);

        //$data['cxuserlevelName'] = openrs_('cxuserlevel','cxuserlevel',['cxid'=>$data['cxuserlevel']],'');

        if($data){
            jsonencode(200,'获取详情数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }




    }


    /**
     * @return  添加
     */
    public function add()
    {
        $seach=[];
        $param=request()->param(false);
        $this->qs('添加新用户');
        unset($param['adminToken']);
        $data=[];
        $table='';
        $rand=getRandomString(6);
        $param['cxrand']=$rand;
        $param['cxregtime']=Now();
        if(!(empty($param['cxpassword']))){
            $param['cxpassword']=md5(md5($param['cxpassword']).$param['cxrand']);
        }

        $data=Db::name('cxuser')->insertGetId($param);

        if($data){
            jsonencode(200,'添加成功',$data);
        }else{
            jsonencode(300,'添加失败',$data);

        }
    }
    /**
     * @return  修改
     */
    public function edit(){

        $seach=[];
        $param=request()->param(false);
        $data=[];
        $table='';


        $table='cxuser';
        $cid=$param['cxid'];
        unset($param['cxid']);

//        if($param['cxusername']=='adminzy'){
//            jsonencode(300,'无权限','');
//            exit;
//        }
        $this->qs('修改用户信息');
        unset($param['adminToken']);

        $admin=$this->CheckToKenAdmin($param['adminToken']);
        if($this->instr($admin['cxMenu'],'adminUser.html')==-1){
            jsonencode(300,'无权限','');
            exit;
        }


        if(!(empty($param['cxpassword']))){
            $rs=Db::name($table)->find($cid);
            $rand=$rs['cxrand'];
            $param['cxpassword']=md5(md5($param['cxpassword']).$rand);
        }else{
            unset($param['cxpassword']);
        }

        $data=Db::name($table)->where('cxid',$cid)->update($param);
        if($data){
            jsonencode(200,'修改成功',$data);
        }else {
            jsonencode(300, '修改失败', $data);
        }


    }




    /**
     * @return  删除
     */
    public function del()
    {
        $param=request()->param();
        $table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=Db::name($table)->where('cxid','in',$param['cxid'])->delete();
        if($data){
            jsonencode(200,'删除成功',$data);
        }else{
            jsonencode(300,'删除失败',$data);

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
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxlock' => $param['cxlock']]);
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
        unset($param['bm']);
        $data = Db::name($table)->where('cxid', 'in', $param['cxid'])->update(['cxuserlevel' => $param['cxuserlevel']]);
        if ($data) {
            jsonencode(200, '变更成功', $data);
        } else {
            jsonencode(300, '变更失败', $data);

        }


    }


    public function d1(){
        echo '设置session成功';
        session('name', '11111');
//
        //echo Session::get('name');
//
    }
    public function d2(){

        echo session('name');
//
    }









}
?>