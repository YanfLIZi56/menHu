<?php
namespace app\controller\bc;

use app\BaseController;
use think\facade\db;



class Admin extends Config
{

    public function getlist()
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

        if(r_('cxuserlevel')){
            $wheres['cxuserlevel']=['=',$param['cxuserlevel']];
        }

        $data=Db::name($param['table'])
            ->where($wheres)
            ->page($param['page'],$param['limit'])
            ->order($param['order'])
            ->select();

        foreach ($data as $k=>$v) {
            $data[$k]['cxuserlevelName'] = openrs_('cxuserlevel','cxuserlevel',['cxid'=>$v['cxuserlevel']],'');

        }



        jsonencode(200,'获取列表数据成功'.Db::name($param['table'])->getlastsql(),$data);
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

        $data=Db::name('cxuserlevel')
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
        if(empty($param['cxid'])){jsonencode(400,'要查询的信息ID不能为空');}
        $data=[];

        $data=Db::name('cxuser')
            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->find($param['cxid']);

        $data['cxuserlevelName'] = openrs_('cxuserlevel','cxuserlevel',['cxid'=>$data['cxuserlevel']],'');

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
        $data=[];
        $table='';
        $rand=getRandomString(6);
        unset($param['cxtoken']);
        $param['cxrand']=$rand;
        $param['cxtoken']=$rand;
        $param['cxaddtime']=Now();
        $param['cxupdatetime']=Now();

        if(r_('cxpassword1')){
            if($param['cxpassword1']!=$param['cxpassword2']){ jsonencode(500,'两次密码不一致','');}
            $param['cxpassword']=md5($param['cxpassword1']);
            unset($param['cxpassword1']);
            unset($param['cxpassword2']);
        }
        $table=$param['bm'];
        unset($param['bm']);

        $data=Db::name($table)->insertGetId($param);

        if($data){
            jsonencode(200,'添加成功',$data);
        }else{
            jsonencode(300,'添加失败',$data);

        }
    }
    /**
     * @return  修改
     */
    public function edit()
    {
        $seach=[];
        $param=request()->param(false);
        $data=[];
        $table='';

        $table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $cid=$param['cxid'];
        unset($param['cxid']);

        if(r_('cxpassword1')){
            if($param['cxpassword1']!=$param['cxpassword2']){ jsonencode(500,'两次密码不一致','');}
            $param['cxpassword']=md5($param['cxpassword1']);
            unset($param['cxpassword1']);
            unset($param['cxpassword2']);
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
     * @return  修改管理密码
     */
    public function editPass()
    {

        $param=request()->param();
        $admin = $this->CheckToKenAdmin($param['adminToken']);
        $this->qs('修改密码');
        unset($param['adminToken']);

        if(!r_('oldPass')){
            jsonencode(500,'旧密码不能为空','');
        }
        if(!r_('newPass')){
            jsonencode(500,'新密码不能为空','');
        }
        if(!r_('newPass1')){
            jsonencode(500,'确认密码不能为空','');
        }
        if($param['newPass']!=$param['newPass1']){
            jsonencode(500,'两次密码不一致','');
        }

        //$table=$param['bm'];//要查询的表名
        $table='cxadmin';
        unset($param['bm']);




        $search['cxpassword']=['=',md5($param['oldPass'])];
        $search['cxid']=['=',$admin['cxid']];

        //$search['cxtoken']=['=',$param['cxtoken']];
        $data=Db::name($table)->where($search)->find();
        if($data){
            $data=Db::name($table)->where($search)->update(['cxpassword'=>md5($param['newPass'])]);
            jsonencode(0,'修改成功',$data);
        }else{
            jsonencode(500,'TOKEN有误或旧密码错误',$data);
        }



    }




}	
?>