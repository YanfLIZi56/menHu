<?php
namespace app\api\controller\bc;

use app\data\service\Dess;

use think\Controller;
use think\Validate;


class Main extends Base
{

	public function __construct(){
		parent::__construct();
	}


    public function getlist()
    {
    	$param=request()->param();
    	$wheres=[];
    	//if(!isset($param['keyword'])){$param['keyword']='';} //搜索关键词
    	if(!isset($param['page'])){$param['page']=1;}//定位页面
    	if(!isset($param['limit'])){$param['limit']=20;} //每页分页数
    	if(!isset($param['bm'])){$param['bm']='cxproduct';}//要查询的表名
    	if(!isset($param['order'])){$param['order']='cxupdatetime desc,cxid desc';} //排序条件
        //if(isset($param['cxsortid'])){$wheres['cxsortid']=['in',$param['cxsortid']];}

        $alls=[];
//    	$allSortid=db('cxproductsort')
//            ->field('cxid')
//            ->where('cxsort','like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%')
//            ->select();

        if(isset($param['keyword'])){
            $wheres['cxtitlecn|cxcontentcn']=['like','%'.$param['keyword'].'%'];
        }

        if(isset($param['cxsortid'])){
            $SortWhere['cxsort']=['like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%'];
            $allSortid=db('cxproductsort')
                ->field('cxid')
                ->where($SortWhere)
                ->select();
            foreach($allSortid as $k=>$v){
                $alls[$k]=$v['cxid'];
            }

            $alls=implode(',',$alls);
        }

            $data=db($param['bm'])
                ->where($wheres)
                ->where('cxsortid','in',$alls)
            ->page($param['page'],$param['limit'])
            ->order($param['order'])
            ->select();

            return json(['status'=>0,'msg'=>'OK','data'=>$data]);

                //jsonencode(200,'获取列表数据成功'.db($param['bm'])->getlastsql(),$data);
    }



    /**
     * @return 获取详情（cxid）
     */
    public function getProInfo()
    {
        $param=request()->param();
        if(empty($param['cxid'])){jsonencode(400,'要查询的信息ID不能为空');}
        $data=[];

        $data=db('cxproduct')
            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->find($param['cxid']);

        $data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');

        if($data){
            jsonencode(200,'获取详情数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }




    }

    /**
     * @return 分类ID下第一个文章内容（cxsortid）
     */
    public function getFirstProInfo()
    {
        $param=request()->param();
        if(empty($param['cxsortid'])){jsonencode(400,'要查询的信息ID不能为空');}
        $data=[];

        $data=db('cxproduct')
            ->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting')
            ->where('cxsortid',$param['cxsortid'])
            ->limit(1)
            ->find();

        $data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');

        if($data){
            jsonencode(200,'获取详情数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }




    }


    /**
     * @return 系统数据
     */
    public function getSys()
    {
        $param=request()->param();
        $data=[];

        $data=db('cxconfig')
            ->field('cxwebtitlecn,cxjson')
            ->find(1);

        $data['cxjson']=json_decode($data['cxjson']);


        if($data){
            jsonencode(200,'获取系统数据成功',$data);
        }else{
            jsonencode(300,'获取系统数据失败',$data);
        }




    }


    /**
     * @return 获取对应分类信息（cxsortid）
     */
    public function getProSort()
    {
        $param=request()->param();
        $data=[];

        $data=db('cxproductsort')
            ->field('cxid,cxsortcn,cxSorting,cximg0')
            ->order('cxSorting asc,cxsort asc,cxid desc')
            ->find($param['cxsortid']);


        if($data){
            jsonencode(200,'获取对应分类数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }




    }


    /**
     * @return 获取全部分类数据
     */
    public function getAllProSort()
    {
        $param=request()->param();
        $data=[];

        $data=db('cxproductsort')
            ->field('cxid,cxsortcn,cxSorting,cxSort')
            ->order('cxsort asc,cxid desc')
            ->select();


        if($data){
            jsonencode(200,'获取分类列表数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }
        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');




    }

    /**
     * @return 获取对应ID分类的子类别（cxsortid）
     */
    public function getProSortSun()
    {
        $param=request()->param();
        if(empty($param['cxsortid'])){jsonencode(400,'要查询的分类ID不能为空');}
        $data=[];

        $SortWhere=[];

        $SortWhere['cxsort']=['like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%'];

        if(isset($param['noSelf'])){
            $SortWhere['cxid']=['<>',$param['cxsortid']];
        }

        $data=db('cxproductsort')
            ->where($SortWhere)
            ->field('cxid,cxsort,cxsortcn,cxSorting,cximg0,cxpid')
            ->order('cxSorting asc,cxsort asc')
            ->select();


        if($data){
            jsonencode(200,'获取单一分类及子分类列表数据成功',$data);
        }else{
            jsonencode(300,'查无此信息',$data);
        }
        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');




    }


    /**
     * @return 留言
     */
    public function guestAdd()
    {
        $param=request()->param();
        $data=[];

        if(empty($param['cxtitle'])){jsonencode(400,'标题不能为空');}
        if(empty($param['cxphone'])){jsonencode(400,'电话不能为空');}
        if(empty($param['cxcontent'])){jsonencode(400,'内容不能为空');}

        $param['cxtype']=1;
        $data=db('cxguestbook')->insert($param);


        if($data){
            jsonencode(200,'留言提交成功',$data);
        }else{
            jsonencode(300,'提交失败',$data);
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
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->insertGetId($param);
        if($data){
                jsonencode(200,'修改成功',$data);
        }else{
                jsonencode(300,'修改失败',$data);

        }
    }
    /**
     * @return  批量添加
     */
    public function addAll()
    {
    	$seach=[];
    	$param=request()->param();
    	$data=[];
    	$table='';
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->insertAll($param);
        if($data){
                jsonencode(200,'修改成功',$data);
        }else{
                jsonencode(300,'修改失败',$data);

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
        if($table=='cxsjshenqing'){
            if($param['cxpassed']==1){
                $dbuserData=db('cxuser')->find($param['cxuserid']);
                if(DateDiff('d',$dbuserData['cxsjEndTime'],Now())>0){
                    $Vipdate=Now();
                }else{
                    $Vipdate=$dbuserData['cxsjEndTime'];

                }
                $Vipdate=DateAdd('d',pubSjTime(),$Vipdate);
                $s=db('cxuser')->where('cxid',$param['cxuserid'])->update(['cxuserlevel'=>3,'cxsjEndTime'=>$Vipdate]);
            }
        }

        $data=db($table)->where('cxid',$cid)->update($param);
        if($data){
                jsonencode(0,'修改成功',$param);
        }else {
            jsonencode(300, '修改失败', $param);
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
        $data=db($table)->where('cxid','in',$param['cxid'])->delete();
        if($data){
            jsonencode(200,'删除成功',$data);
        }else{
            jsonencode(300,'删除失败',$data);

        }


    }
    /**
     * @return  更新排序
     */
    public function upTime()
    {
        $param=request()->param();
        $table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->where('cxid','in',$param['cxid'])->update(['cxupdatetime'=>Now()]);
        if($data){
            jsonencode(200,'更新成功',$data);
        }else{
            jsonencode(300,'更新失败',$data);

        }
    }
    /**
     * @return  批量更新状态
     */
    public function upZt()
    {
        $param=request()->param();
        $table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->where('cxid','in',$param['cxid'])->update(['cxpassed'=>$param['cxpassed']]);
        if($data){
            jsonencode(200,'更新状态成功',$data);
        }else{
            jsonencode(300,'更新状态失败',$data);

        }
    }

    /**
     * @return  批量变更分类
     */
    public function changeSort()
    {
    	$param=request()->param();
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->where('cxid','in',$param['cxid'])->update(['cxsortid'=>$param['cxsortid']]);
        if($data){
                jsonencode(200,'变更成功',$data);
        }else{
                jsonencode(300,'变更失败',$data);

        }


    }
    /**
     * @return  清空表
     */
    public function clearBiao()
    {
    	$param=request()->param();
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->where('cxid','>',0)->delete();
        if($data){
                jsonencode(200,'清空成功',$data);
        }else{
                jsonencode(300,'清空失败',$data);

        }


    }


    // 获取初始化数据
    public function getSystemInit(){
        $homeInfo = [
            'title' => '首页',
            'href'  => 'page/welcome-1.html?t=1',
        ];
        $logoInfo = [
            'title' => 'LAYUI MINI',
            'image' => 'images/logo.png',
        ];
        $menuInfo = $this->getMenuList();
        $systemInit = [
            'homeInfo' => $homeInfo,
            'logoInfo' => $logoInfo,
            'menuInfo' => $menuInfo,
        ];
        return json($systemInit);
    }



    // 获取菜单列表
    private function getMenuList(){
        $menuList = db('system_menu')
            ->field('id,pid,title,icon,href,target')
            ->where('status', 1)
            ->order('sort', 'desc')
            ->select();
        $menuList = $this->buildMenuChild(0, $menuList);
        return $menuList;
    }

    //递归获取子菜单
    private function buildMenuChild($pid, $menuList){
        $treeList = [];
        foreach ($menuList as $v) {
            if ($pid == $v['pid']) {
                $node = $v;
                $child = $this->buildMenuChild($v['id'], $menuList);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                // todo 后续此处加上用户的权限判断
                $treeList[] = $node;
            }
        }
        return $treeList;
    }











}	
?>