<?php
namespace app\api\controller\bc;

use app\data\service\Dess;

use think\Controller;
use think\Validate;


class Pufu extends Base
{

	public function __construct(){
		parent::__construct();
	}


    /**
     * @return  查询列表
     */
    public function getlist()
    {
    	$search=[];
    	$param=request()->param();
    	$data=[];
    	$table='';
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
    	if(!isset($param['keyword'])){$param['keyword']='';} //搜索关键词
    	if(!isset($param['page'])){$param['page']=1;}//定位页面
    	if(!isset($param['limit'])){$param['limit']=20;} //每页分页数
    	if(!isset($param['order'])){$param['order']='cxid desc';} //排序条件

        if(!empty($param['keywordName']) && !empty($param['keyword'])){
            $search[$param['keywordName']]=['like','%'.$param['keyword'].'%'];
        }
        if(!empty($param['sortName']) && !empty($param['sortValue'])){
            $search[$param['sortName']]=['=',$param['sortValue']];
        }


        $data['data']=db($table)
            ->where($search)
            ->page($param['page'],$param['limit'])
            ->order($param['order'])
            ->select();

    	$data['msg']='获取列表成功';
    	$data['code']=0;

        $data['count'] = db($table)
            ->where($search)
            ->count();
        $data['pageCur']=$param['page'];
        $data['pageSize']=$param['limit'];
        $data['pageCount'] =ceil($data['count']/$param['limit']);



        return json($data);
    }
    /**
     * @return  查询详情
     */
    public function getInfo()
    {
    	$param=request()->param();
    	$data=[];
    	$table='';
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
    	$data['msg']='获取详情成功';
    	$data['code']=0;
        $data['data']=db($table)->find($param['cxid']);

        return json($data);
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
    	//dump($param);
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);

        foreach ($param as $k=>$v){
            $checkCZ=db('cxuser')->where('cxusername',$v['cxusername'])->find();
            if($checkCZ){
                return jsonencode(200,'账号已经存在：'.$checkCZ['cxfullname'],$checkCZ);
                exit;
            }
            //$rand=getRandomString(6);
            $rand='123456';
            $param[$k]['cxrand']=$rand;
            $param[$k]['cxtoken']=createNoncestr();
            $param[$k]['cxpassword']=md5(md5('123456').$rand);
        }

        $data=db($table)->insertAll($param);
        if($data){
                return jsonencode(200,'批量添加完成',$data);
        }else{
                return jsonencode(300,'批量添加失败',$data);

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

        $data=db($table)->where('cxid',$cid)->update($param);
        if($data){
                jsonencode(200,'修改成功',$param);
        }else {
            jsonencode(300, '修改失败', $param);
        }


    }

    /**
     * @return  删除
     */
    public function del()
    {
    	$seach=[];
    	$param=request()->param();
    	$data=[];
    	$table='';
    	$table=$param['bm'];//要查询的表名
        unset($param['bm']);
        $data=db($table)->where('cxid',['in',$param['cxid']])->delete();
        if($data){
                jsonencode(200,'删除成功',$data);
        }else{
                jsonencode(300,'删除失败',$data);

        }


    }


    /**
     * @return  查询地区
     */
    public function getArea()
    {
    	$search=[];
    	$param=request()->param();
    	$data=[];
    	$table='';
    	$table=$param['table'];//要查询的表名
        unset($param['table']);

        if(!empty($param['sortName']) && !empty($param['sortValue'])){

            if($param['sortName']=='cxarea1'){
                $tt=db('cxarea1')->where('cxarea1',$param['sortValue'])->find();
                $tt=$tt['cxid'];
                $data['list']=db('cxarea2')->where('cxarea1',$tt)->select();

            }
            if($param['sortName']=='cxarea2'){
                $tt=db('cxarea2')->where('cxarea2',$param['sortValue'])->find();
                $tt=$tt['cxid'];
                $data['list']=db('cxarea3')->where('cxarea2',$tt)->select();
            }
            if($param['sortName']=='cxarea3'){
                $s=db('cxarea3')->where('cxarea3','=',$param['sortValue'])->find();
                $data['list']=db('cxarea4')
                    ->where('cxarea3',$s['cxid'])
                    ->select();
            }

        }else{
                $data['list']=db($table)->select();
        }


    	$data['sql']=db($table)->getlastsql();
    	$data['msg']='获取列表成功';
    	$data['code']=0;

//        $data['count'] = db($table)
//            ->where($search)
//            ->count();
//        $data['pageCur']=$param['page'];
//        $data['pageSize']=$param['limit'];
//        $data['pageCount'] =ceil($data['count']/$param['limit']);



        return json($data);
    }

    public function addMenu(){
	    $param=request()->param();
	    if($param){
	        $data=db('system_menu')->insert($param);
	        if($data){
	            jsonencode(200,'添加成功',$data);
            }else{
	            jsonencode(300,'添加失败',$data);
            }
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