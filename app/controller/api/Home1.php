<?php
//namespace app\controller\api;
//
//use app\BaseController;
//use think\facade\Db;
//
//class Home extends Config
//{
//
//
//    public function __construct(){
//        parent::__construct();
//
//    }
//
//    public function jss(){
//        $data['cxtitlecn']= 'thinkphp';
//        $data['cxMdatacn'] = [
//            'c'=>'11211',
//            'd'=>'2222'
//        ];
//        Db::name('cxproduct')->json(['cxMdatacn'])->insert($data);
//
//        $s=Db::name('cxproduct')->json(['cxMdatacn'])->wherelike('cxMdatacn->d','%2222%')->select();
//
//        return json(['msg'=>'完成','data'=>$s,'sql'=>Db::name('cxproduct')->getlastsql()]);
//    }
//
//    /**
//     * @return 信息列表（cxsortid）
//     */
//    public function getProlist()
//    {
//        $seach=[];
//        $param=request()->param();
//        $data=[];
//        $KeywordWhere=[];
//        $SortWhere=[];
//        if(!isset($param['page'])){$param['page']=1;}//定位页面
//        if(!isset($param['limit'])){$param['limit']=100;} //每页分页数
//        if(!isset($param['order'])){$param['order']='cxSorting asc,cxupdatetime desc,cxid desc';} //排序条件
//        if(!empty($param['keyword'])){
//            $KeywordWhere['cxtitlecn|cxcontentcn']=['like','%'.$param['keyword'].'%'];
//        }
//        if(!empty($param['bianMa'])){
//            //$KeywordWhere['cxMdatacn']=['like','%'.$param['bianMa'].'%'];
//            $KeywordWhere['cxMdatacn']=['like','%\"content\"\:\"'.$param['bianMa'].'%'];
//        }
//        //[{"title":"性别     ","content":"男    "},{"title":"证件号码","content":"234423"},{"title":"培训课程","content":"有害生物防制员 - 初级"},{"title":"结业成绩","content":"99.0"},{"title":"证书编号","content":"SDL-2362365475"},{"title":"发证单位","content":"北京市朝阳区卫健功成职业技术培训学校"},{"title":"发证时间","content":"2022-1-5"}]
//
//        if(!empty($param['cxsortid'])){
//            $allSortid=Db::name('cxproductsort')
//                ->field('cxid')
//                ->whereLike('cxsort',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%')
//                ->select();
//            if(Db::name('cxproductsort')->getNumRows()>0){
//                $alls=[];
//                foreach($allSortid as $k=>$v){
//                    if(!empty($v)) {
//                        $alls[$k]=$v['cxid'];
//                    }
//                }
//
//                $alls=implode(',',$alls);
//                $SortWhere['cxsortid']=['in',$alls];
//            }else{
//                $SortWhere['cxsortid']='';
//            }
//        }
//        unset($SortWhere['cxsort']);
//
//
//        $data['rows']=Db::name('cxproduct')
//            //->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting,cxMdatacn')
//            ->where($SortWhere)
////                ->whereNotNull('cxzwArr')
////                ->where('cxzwArr','<>','')
//            ->where($KeywordWhere)
//            ->page($param['page'],$param['limit'])
//            ->order($param['order'])
//            ->select()->toArray();
//        $data['sql'] = Db::name('cxproduct')->getlastsql();
//
//
//
//        if($data['rows']){
//
//
//            foreach($data['rows'] as $k=>$v){
//                if(!empty($v)) {
//                    $data['rows'][$k]['cxsortcn'] = openrs_('cxproductsort', 'cxsortcn', ['cxid' => $data['rows'][$k]['cxsortid']], '');
//                    $data['rows'][$k]['cxsorten'] = openrs_('cxproductsort', 'cxsorten', ['cxid' => $data['rows'][$k]['cxsortid']], '');
//                }
//            }
//
//
//
//            $data['rows']=Db::query("select * from cxexcelcontent where 1=1 and cxcontractid in (select cxid from cxcontract where cxpassed=1) and cxuid=81 and cxzt=0 order by cxpassed asc,cxupdatetime desc,cxid desc");
//            $data['sql'] = Db::name('cxExcelContent')->getlastsql();
//
//            $data['Count'] = count($data['rows']);;
//            $data['pageCur']=$param['page'];
//            $data['pageSize']=$param['limit'];
//            $data['pageCount'] =ceil((Db::name('cxproduct')->where($SortWhere)->where($KeywordWhere)->count())/$param['limit']);
//
//            jsonencode(200,'获取列表数据成功',$data);
//        }else{
//            jsonencode(300,'查无数据',$data);
//        }
//
//    }
//
//
//    /**
//     * @return 获取详情（cxid）
//     */
//    public function getProInfo()
//    {
//        $param=request()->param();
//        if(empty($param['cxid'])){jsonencode(400,'要查询的信息ID不能为空');}
//        $data=[];
//
//        $data=Db::name('cxproduct')
//            ->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxcontenten,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting,cxMdatacn')
//            ->find($param['cxid']);
//        $sort=Db::name('cxproductsort')->find($data['cxsortid']);
//        $data['cxsortcn'] = $sort['cxsortcn'];
//        $data['cxsorten'] =  $sort['cxsorten'];;
//        if($data){
//            jsonencode(200,'获取详情数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//
//
//
//
//    }
//
//    /**
//     * @return 分类ID下第一个文章内容（cxsortid）
//     */
//    public function getFirstProInfo()
//    {
//        $param=request()->param();
//        if(empty($param['cxsortid'])){jsonencode(400,'要查询的信息ID不能为空');}
//        $data=[];
//
//        $data=Db::name('cxproduct')
//            ->field('cxid,cxtitlecn,cxtitleen,cxcontentcn,cxcontenten,cxcontenten,cxsortid,cxlink,cximg0,cximg1,cxupdatetime,cxSorting,cxMdatacn,cxcontentWapcn,cxcontentWapen')
//            ->where('cxsortid',$param['cxsortid'])
//            ->limit(1)
//            ->find();
//
//        $data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');
//
//        if($data){
//            jsonencode(200,'获取详情数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//
//
//
//
//    }
//
//
//    /**
//     * @return 系统数据
//     */
//    public function getSys()
//    {
//        $param=request()->param();
//        $data=[];
//
//        $data=Db::name('cxconfig')
//            ->field('cxwebtitlecn,cxjson')
//            ->find(1);
//
//        $data['cxjson']=json_decode($data['cxjson']);
//
//
//        if($data){
//            jsonencode(200,'获取系统数据成功',$data);
//        }else{
//            jsonencode(300,'获取系统数据失败',$data);
//        }
//
//
//
//
//    }
//
//
//    /**
//     * @return 获取对应分类信息（cxsortid）
//     */
//    public function getProSort()
//    {
//        $param=request()->param();
//        $data=[];
//
//        $data=Db::name('cxproductsort')
//            //->field('cxid,cxsortcn,cxSorting,cximg0')
//            ->order('cxSorting asc,cxsort asc,cxid desc')
//            ->find($param['cxsortid']);
//
//
//        if($data){
//            jsonencode(200,'获取对应分类数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//
//
//
//
//    }
//
//
//    /**
//     * @return 获取全部分类数据
//     */
//    public function getAllProSort()
//    {
//        $param=request()->param();
//        $data=[];
//
//        $data=Db::name('cxproductsort')
//            ->field('cxid,cxsortcn,cxSorting,cxpid,cxurl')
//            ->order('cxSorting asc,cxsort asc,cxid desc')
//            ->select();
//
//
//        if($data){
//            jsonencode(200,'获取分类列表数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');
//
//
//
//
//    }
//
//    /**
//     * @return 获取对应ID分类的子类别（cxsortid）
//     */
//    public function getProSortSun()
//    {
//        $param=request()->param();
//        if(empty($param['cxsortid'])){jsonencode(400,'要查询的分类ID不能为空');}
//        $data=[];
//
//        $SortWhere=[];
//
//        $SortWhere['cxsort']=['like',openrs_('cxproductsort','cxsort','cxid='.$param['cxsortid']).'%'];
//
//        if(isset($param['noSelf'])){
//            $SortWhere['cxid']=['<>',$param['cxsortid']];
//        }
//
//        $data=Db::name('cxproductsort')
//            ->where($SortWhere)
//            //->field('cxid,cxsort,cxsortcn,cxsorten,cxSorting,cximg0,cximg1,cxpid')
//            ->order('cxSorting asc,cxsort asc')
//            ->select();
//
//
//
//        if($data){
//            jsonencode(200,'获取单一分类及子分类列表数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');
//
//
//
//
//    }
//
//
//
//    /**
//     * @return 获取对应ID分类的父类别（cxsortid）
//     */
//    public function getProSortParent()
//    {
//        $param=request()->param();
//        if(empty($param['cxsortid'])){jsonencode(400,'要查询的分类ID不能为空');}
//        $paranetSortId=Db::name('cxproductsort')->field('cxpids')->find($param['cxsortid']);
//        $ss=explode(",",$paranetSortId['cxpids']);
//        //将,1,2,3, 转为根据，分隔的数组，目前是取第2个，即数组下标1的值
//        $data=Db::name('cxproductsort')->field('cxid,cxsortcn,cxsorten,cximg0,cxcontentcn,cxcontenten')->find($ss[1]);
//
//
//        if($data){
//            jsonencode(200,'获取单一分类及子分类列表数据成功',$data);
//        }else{
//            jsonencode(300,'查无此信息',$data);
//        }
//        //$data['cxsortcn'] = openrs_('cxproductsort','cxsortcn',['cxid'=>$data['cxsortid']],'');
//
//
//
//
//    }
//
//
//    /**
//     * @return 留言
//     */
//    public function guestAdd()
//    {
//        $param=request()->param();
//        $data=[];
//
//        if(empty($param['cxtitle'])){jsonencode(400,'标题不能为空');}
//        if(empty($param['cxphone'])){jsonencode(400,'电话不能为空');}
//        if(empty($param['cxcontent'])){jsonencode(400,'内容不能为空');}
//
//        $param['cxtype']=1;
//        $data=Db::name('cxguestbook')->insert($param);
//
//
//        if($data){
//            jsonencode(200,'留言提交成功',$data);
//        }else{
//            jsonencode(300,'提交失败',$data);
//        }
//
//
//
//
//    }
//
//}
