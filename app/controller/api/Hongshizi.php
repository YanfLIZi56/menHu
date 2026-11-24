<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;

class Hongshizi extends Config
{

    public function __construct()
    {
        parent::__construct();
    }

    public function showOld(){
        $allSort=Db::name('old_newsort')->select()->toArray();
        $rs=Db::name('old_new')->limit(100)->select()->toArray();
        foreach($rs as $k=>$v){
            $rs[$k]['sortName']='';
            foreach($allSort as $kk=>$vv){
                if($vv['cxid']==$v['cxsortid']){
                    $rs[$k]['sortName']=$vv['cxsortcn'];
                }
            }
        }


        foreach($rs as $k=>$v){
            if($v['sortName']){
                if($v['sortName']==$v['cxsortid']) {
                    $rs[$k]['sortName'] = $vv['cxsortcn'];
                }
            }
        }

        jsonencode(200,'OK',$rs);
    }


    public function daoRu(){
        $rsOld=Db::name('old_newsort')->distinct('cxsortcn,cxid')->limit(100)->select()->toArray();
        $rsNew=Db::name('cxnews_sort')->distinct('cxsortcn,cxid')->limit(100)->select()->toArray();
        Db::name('old_newsort')->where('cxid','>',0)->update(['cxback'=>'']);
        $i=0;
        foreach($rsNew as $k=>$v){
            foreach($rsOld as $kOld=>$vOld){
                if($vOld['cxsortcn']==$v['cxsortcn']){
                    $q[$i]['name']=$v['cxsortcn'];
                    $q[$i]['id']=$vOld['cxid'];
                    $i++;

                    Db::name('old_newsort')->where('cxid','=',$vOld['cxid'])->update(['cxback'=>'已导入']);
                    $news=Db::name('old_new')->where('cxsortid',$vOld['cxid'])->select()->toArray();

                    foreach($news as $rk=>$rv){

                        $data=[];
                        $data['cxtitlecn']=$rv['cxtitlecn'];
                        $data['cxcontentcn']=$rv['cxcontentcn'];
                        $data['cximg0']=$rv['cximg0'];
                        $data['cxpassed']=1;
                        $data['cxaddtime']=date("Y-m-d H:i",$rv['cxaddtime']);
                        $data['cxupdatetime']=date("Y-m-d H:i",$rv['cxupdatetime']);
                        $data['cxsortid']=$v['cxid'];
                        $newExist=Db::name('cxnews')->where('cxtitlecn',$data['cxtitlecn'])->find();
                        if(!$newExist){
                            Db::name('cxnews')->insert($data);
                        }
                    }

                    //$temp=Db::name('old_newsort')->where('cxsortcn',$vOld['cxsortcn'])->find()
                }
            }
        }
//        foreach($rs as $k=>$v){
//            $rs[$k]['sortName']='';
//            foreach($allSort as $kk=>$vv){
//                if($vv['cxid']==$v['cxsortid']){
//                    $rs[$k]['sortName']=$vv['cxsortcn'];
//                }
//            }
//        }
        jsonencode(200,'OK',$q);
    }




}
