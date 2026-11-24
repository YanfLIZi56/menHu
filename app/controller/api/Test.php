<?php
namespace app\api\controller\bc;

use app\data\service\Dess;

use think\Validate;

use app\data\model\cxproductshoppingModel;

use app\data\extend\weixin\Service;

use app\data\extend\weixin\Wxpay;
use app\data\model\cxuser;
use app\data\model\cxuserlevel;
use app\data\model\cxlogjifen;
use app\data\model\cxsys;

class Test extends Base
{

    protected $cxproductshoppingModel;
    protected $tempservice;
    protected $wxpays;
	public function __construct(){
		parent::__construct();
        $this->productshopping=new cxproductshoppingModel();
        $this->tempservice=new Service();
        $this->wxpays=new Wxpay();
	}


	public function index(){
//	    dump($notifyUrl=request()->root(true).'/index/pay/notifyurl');exit;
        $data=[];
        $advs1=$this->uswiper(1,3);
        $data['advs1']=$advs1;

        //首页前台分类
        $data['shangJiaList']=[];
        $indexsort=db('cxuser')->where('cxid>0')->order('cxid desc')->limit(4)->select();
        foreach ($indexsort as $k=>$v){
            $data['shangJiaList'][$k]['cxtitle']=$v['cxname'];
            $data['shangJiaList'][$k]['cxaddress']=$v['cxaddress'];
            $data['shangJiaList'][$k]['image']=httpurlRootImg($v['cximg0']);
        }


        $proIndexList=db('cxproduct')->where('cxpassed>1')->order('cxupdatetime desc,cxid desc')->limit(4)->select();
        $i=0;
        $data['proIndexList']=[];
        foreach ($proIndexList as $k=>$v){
            $data['proIndexList'][$i]['id']=$v['cxid'];
            $data['proIndexList'][$i]['title']=$v['cxtitlecn'];
            $data['proIndexList'][$i]['price']=$v['cxprice'];
            $data['proIndexList'][$i]['cxdesc']=$v['cxdesc']; //描述
            $data['proIndexList'][$i]['image']=httpurlRootImg($v['cximg0']);
            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }


    public function category(){
	    $psort=db('cxproductsort')->select();
	    $data=[];
	    $i=0;
	    foreach ($psort as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['name']=$v['cxsortcn'];
            if($v['cxpid']){
                $data[$i]['pid']=$v['cxpid'];
            }
            if($v['cximg0']){
                $data[$i]['picture']=httpurlRootImg($v['cximg0']);
            }
	        $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 产品列表页
     * @author NN
     * @return bool
     */

    public function prolist(){
        $param=request()->param();


        $pageSize=$param['pageSize'];//一页最多显示几条数据
        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $seach=[];
        $seach['cxsortid']=$param['tid'];
        $proList=db('cxproduct')->where($seach)->limit($p)->order('cxupdatetime desc,cxSorting asc,cxid desc')->select();
//        dump(db('cxproduct')->getLastSql());
        $i=0;
        $data=[];
        foreach ($proList as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['title']=$v['cxtitlecn'];
            $data[$i]['price']=$v['cxprice'];
            $data[$i]['sales']=$v['cxsales'];
            $data[$i]['image']=httpurlRootImg($v['cximg0']);
            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 产品列表页
     * @author NN
     * @return bool
     */

    public function prosearch(){
        $param=request()->param();
        $pageSize=$param['pageSize'];//一页最多显示几条数据
        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $seach=[];
        $seach['cxtitlecn']=['like','%'.$param['keyword'].'%'];
        $proList=db('cxproduct')->where($seach)->limit($p)->order('cxupdatetime desc,cxSorting asc,cxid desc')->select();
//        dump(db('cxproduct')->getLastSql());
        $i=0;
        $data=[];
        foreach ($proList as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['title']=$v['cxtitlecn'];
            $data[$i]['price']=$v['cxprice'];
            $data[$i]['sales']=$v['cxsales'];
            $data[$i]['image']=httpurlRootImg($v['cximg0']);
            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**产品详细
     * @author NN
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function proinfo(){
        $id=request()->post('id',0);
        $res=db('cxproduct')->find($id);
        if(!$res){
            return jsonencode(301,'操作有误');
        }

        //图片
        $imgArr=[];
        for($i=0;$i<9;$i++){
            if($res['cximg'.$i]){
                array_push($imgArr,httpurlRootImg($res['cximg'.$i]));
            }
        }
        //规格
        $specifications=$this->specifications($res['cxid']);

        //计算总库存
        $proTolStock=0;
        foreach ($specifications as $k=>$v){
            $proTolStock+=$v['stock'];
        }

        //查询评论
        $commentlimit=db('cxcomment')
            ->where('cxtype=:type and cxtypeid=:typeid',['type'=>1,'typeid'=>$res['cxid']])
            ->order('cxaddtime desc')
            ->limit(5)
            ->select();

        $commentarr=[];
        $ci=0;
        foreach ($commentlimit as $k=>$v){
            $commentarr['data'][$ci]['uimg']=$v['cxuimg'];
            $commentarr['data'][$ci]['uname']=$v['cxuname'];
            $commentarr['data'][$ci]['content']=$v['cxcontent'];
            $commentarr['data'][$ci]['yixuan']=$v['cxyixuan'];
            $commentarr['data'][$ci]['time']=date('Y-m-d H:i',$v['cxaddtime']);
            $ci++;
        }

        $commentarr['tol']=openrscount_('cxcomment','',['cxtype'=>1,'cxtypeid'=>$res['cxid']],'');

        $data=[];
        $data['prodata']['id']=$res['cxid'];//标题
        $data['prodata']['title']=$res['cxtitlecn'];//标题
        $data['prodata']['price']=$res['cxprice'];//现价
        $data['prodata']['priceyj']=$res['cxpriceyj'];//原价
        $data['prodata']['discount']=$res['cxpriceyj']?$res['cxprice']/$res['cxpriceyj']*10:0;//计算打几折
        $data['prodata']['sales']=$res['cxsales'];//销量
        $data['prodata']['hits']=$res['cxhits'];//浏览
        $data['prodata']['stock']=$proTolStock;//库存
        $data['prodata']['imgList']=$imgArr;//产品图片
        $data['prodata']['contentcn']=str_replace("<img","<img width='100%'",editorurlimg($res['cxcontentcn']));//详细
        $data['prodata']['spec']=$specifications;//规格
        $data['prodata']['spec1']=$this->specifications1($res['cxid']);//规格前台选择
        $data['prodata']['tolshopnum']=$this->tolshopnum();//统计购物车的总数量
        $data['prodata']['comment']=$commentarr;//评论


//        dump($this->specifications1($res['cxid']));
        //浏览量
        db('cxproduct')->update(['cxid'=>$res['cxid'],'cxhits'=>($res['cxhits']+1)]);

        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 评论列表
     * @author NN
     */
    public function commentinfo(){
        $user=$this->model_mb_user_token;
        $param=request()->param();

        $pageSize=$param['pageSize'];
        $id=$param['id'];//产品id

        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        //查询评论
        $commentlimit=db('cxcomment')
            ->where('cxtype=:type and cxtypeid=:typeid',['type'=>1,'typeid'=>$id])
            ->order('cxaddtime desc')
            ->limit($p)
            ->select();
        $commentarr=[];
        $ci=0;
        foreach ($commentlimit as $k=>$v){
            $commentarr['data'][$ci]['uimg']=$v['cxuimg'];
            $commentarr['data'][$ci]['uname']=$v['cxuname'];
            $commentarr['data'][$ci]['content']=$v['cxcontent'];
            $commentarr['data'][$ci]['yixuan']=$v['cxyixuan'];
            $commentarr['data'][$ci]['time']=date('Y-m-d H:i',$v['cxaddtime']);
            $ci++;
        }

        return jsonencode(200,'获取数据成功',$commentarr);
    }

    public function cartTolNumber(){
        $data=[];
        $data['num']=$this->tolshopnum();//统计购物车的总数量
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 修改资料
     * @author NN
     */
    public function userinfo(){
        $user=$this->model_mb_user_token;
        $data['uinfo']['name']=$user['cxname'];
        $data['uinfo']['mobile']=$user['cxmobile'];
        $data['uinfo']['portrait']=$user['cximg0'];
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 修改资料提交页面
     * @author NN
     */
    public function userinfoconfirm(){
        $user=$this->model_mb_user_token;
        $post=request()->post();
        $data=[];
        $data['cxid']=$user['cxid'];
        $data['cxname']=$post['name'];
        $data['cxmobile']=$post['mobile'];

        $edit=db('cxuser')->update($data);
        if($edit!==false){
            return jsonencode(200,'修改数据成功');
        }else{
            return jsonencode(301,'修改数据失败');
        }

    }


    /**
     * 三级联动
     * @author NN
     * "AreaId":"110000","ParentId":"0","AreaName":"北京市"
     */
    public function linkage(){
//        $user=$this->model_mb_user_token;
        $post=request()->post();
        $search=[];
        $search['cxpid']=isset($post['AreaId'])?$post['AreaId']:0;
        $list=db('cxcommunity')->where($search)->select();

        $data=[];
        $i=0;
        foreach ($list as $k=>$v){
            $data[$i]['AreaId']=$v['cxid'];
            $data[$i]['ParentId']=$v['cxpid'];
            $data[$i]['AreaName']=$v['cxsortcn'];
            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 三级联动
     * @author NN
     *
     */
    public function linkage1(){
        $list=db('cxcommunity')->select();

        dump(get_tree1($list,['cxid','cxsortcn','cxcheckloginwxpid']));

//        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 计算购物车总数量
     * @author NN
     */
    protected function tolshopnum(){
        $user=$this->model_mb_user_token;
        $shop=$this->productshopping->where('cxorderid=:orderid and cxuserid=:uid',['orderid'=>0,'uid'=>$user['cxid']])->select();
        $num=0;
        foreach ($shop as $k=>$v){
            $num+=$v['cxproductcount'];
        }
        return $num;
    }

    //获取默认收货地址
    public function addressDataDef(){
        $user=$this->model_mb_user_token;
        $rs=db('cxaddress')->where("cxuserid={$user['cxid']} and cxdef=2")->find();
        $data=[];
        if($rs){
            $data['id']=$rs['cxid'];
            $data['name']=$rs['cxname'];//名称
            $data['mobile']=$rs['cxmobile'];//手机
            $data['address']=$rs['cxaddress'];//详细地址
			$data['region']=$rs['cxregion'];//地区
			
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 地址簿列表
     * @author NN
     */
    public function addressList(){
        $user=$this->model_mb_user_token;
        $addressList=db('cxaddress')->where("cxuserid={$user['cxid']}")->order('cxdef desc,cxupdatetime desc')->select();
        $data=[];

        $i=0;

        if($addressList){
            foreach ($addressList as $k=>$v){
                $data[$i]['id']=$v['cxid'];
                $data[$i]['name']=$v['cxname'];//名称
                $data[$i]['mobile']=$v['cxmobile'];//电话
				$data[$i]['region']=$v['cxregion'];//地区
                $data[$i]['address']=$v['cxaddress'];//详细地址
                $data[$i]['default']=$v['cxdef']==2?true:false;//是否是默认地址
				
                $i++;
            }

        }
        return jsonencode(200,'获取数据成功',$data);
    }

    //获取默认收货地址
    public function addressDataAdd(){
        $user=$this->model_mb_user_token;
        $address=request()->post('address','');
		$mobile=request()->post('mobile','');
		$name=request()->post('name','');
		$default=request()->post('default','');
		$region=request()->post('region','');
        $id=request()->post('id',0);

        if($default=="true"){
            $def=2;
        }else{
            $def=1;
        }


        $lists=db('cxaddress')->where(['cxuserid'=>$user['cxid']])->select();
        if($lists){
            if($def==2){
                db('cxaddress')->where(['cxuserid'=>$user['cxid']])->update(['cxdef' => 1]);
            }
        }else{
            $def=2;
        }


        $data=[];
        $data['cxname']=$name;
        $data['cxmobile']=$mobile;
        $data['cxaddress']=$address;
        $data['cxuserid']=$user['cxid'];
        $data['cxdef']=$def;
		$data['cxregion']=$region;
		$data['cxupdatetime']=time();
        $mess='';
        if($id){
            $mess='修改';
            $data['cxid']=$id;
            $addedit=db('cxaddress')->update($data);

        }else{
            $mess='添加';
            $data['cxaddtime']=time();
            $addedit=db('cxaddress')->insert($data);

        }

        if($addedit===false){
            return jsonencode(301,$mess.'失败');
        }
        return jsonencode(200,$mess.'成功');

    }
	
	

    //提交订单
    public function orderSubmit(){
        $user=$this->model_mb_user_token;
        $addressData=request()->post('addressData','');
        $prodata=request()->post('prodata','');
        $desc=request()->post('desc','');//备注

        if(!$addressData || !$prodata){
            return jsonencode(301,'提交有误');
        }

        $addressDataArr=json_decode(htmlspecialchars_decode($addressData),true);
        $prodataArr=json_decode(htmlspecialchars_decode($prodata),true);
//        dump($prodata);
        $this->startTrans();
        try{
            //生成订单
            $no_=orderno();
            $order['cxcontactname']=$addressDataArr['name'];
            $order['cxaddressid']=$addressDataArr['id'];
            $order['cxmobile']=$addressDataArr['mobile'];
            $order['cxaddress']=$addressDataArr['address'];
            $order['cxregion']=$addressDataArr['region'];
            $order['cxuserid']=$user['cxid'];
            $order['cxnote']=$desc;
            $order['cxno']=$no_;
            $order['cxip']=request()->ip();
    //        $order['cxtolprice']=$tol;
            $order['cxtime']=date('Y-m-d H:i:s');


            $order_id=db('cxproductorders')->insertGetId($order);

            $shop=[];
            $i=0;
            $tolpric=0;

            foreach ($prodataArr as $k=>$v){
                $isprostock=$this->isprostock($v['proid'],$v['specid']);
                if($isprostock['state']!==0){
                    $this->rollback();
                    return jsonencode(301,$this->statearr($isprostock['state']));
                }
                if(!isset($v['id'])){//立即购买
                    $shop[$i]['cxorderid']=$order_id;//订单id
                    $shop[$i]['cxuserid']=$user['cxid'];//会员id
                    $shop[$i]['cximg0']=$v['img'];//规格图片
                    $shop[$i]['cxproductcount']=$v['number_'];//产品数量
                    $shop[$i]['cxprice']=$v['price'];//价格
                    $shop[$i]['cxproductid']=$v['proid'];//产品id
                    $shop[$i]['cxspecid']=$v['specid'];//规格id
                    $shop[$i]['cxtitle']=$v['title'];//产品名称
                    $shop[$i]['cxyixuan']=$v['yixuan'];//选择的规格
                    $shop[$i]['cxaddtime']=time();//选择的规格

                }else{//购物车购买
                    $shop[$i]['cxid']=$v['id'];//购物车id
                    $shop[$i]['cxorderid']=$order_id;//会员id
                }
                $tolpric+=$v['price']*$v['number_'];
                $i++;
            }

            $this->productshopping->allowField(true)->saveAll($shop);

            db('cxproductorders')->where("cxid={$order_id}")->update(['cxtolprice'=>$tolpric]);

// 提交事务
            $this->commit();
            $returns=1;
        } catch (\Exception $e) {//try
            // 回滚事务
            $this->rollback();
//			$mess=$e->getMessage();
//			dump($mess);
            $returns=$e->getMessage();
        }

        if($returns==1){
            return jsonencode(200,'提交成功',['no'=>$no_,'price'=>$tolpric]);
        }else{
            return jsonencode(301,'提交失败',['data'=>$returns]);
        }

    }

    //加入购物车
    public function addCart(){
        $user=$this->model_mb_user_token;
        $prodata=request()->post('prodata','');

        if(!$prodata){
            return jsonencode(301,'提交有误');
        }

        $prodataArr=json_decode(htmlspecialchars_decode($prodata),true);

        $seach=[];
        $seach['cxorderid']=0;
        $seach['cxuserid']=$user['cxid'];
        $seach['cxspecid']=$prodataArr['specid'];
        $rs=$this->productshopping->where($seach)->find();
        if($rs){
            return jsonencode(201,'该商品已经加入购物车');
        }
        $this->startTrans();
        try{
            $shop=[];
            $shop['cxorderid']=0;//订单id
            $shop['cxuserid']=$user['cxid'];//会员id
            $shop['cximg0']=$prodataArr['img'];//规格图片
            $shop['cxproductcount']=$prodataArr['number_'];//产品数量
            $shop['cxprice']=$prodataArr['price'];//价格
            $shop['cxpricecart']=$prodataArr['price'];//价格
            $shop['cxproductid']=$prodataArr['proid'];//产品id
            $shop['cxspecid']=$prodataArr['specid'];//规格id
            $shop['cxtitle']=$prodataArr['title'];//产品名称
            $shop['cxyixuan']=$prodataArr['yixuan'];//选择的规格
            $shop['cxaddtime']=time();//选择的规格

            $this->productshopping->allowField(true)->save($shop);
            // 提交事务
            $this->commit();
            $returns=1;
        } catch (\Exception $e) {//try
            // 回滚事务
            $this->rollback();
//			$mess=$e->getMessage();
//			dump($mess);
            $returns=$e->getMessage();
        }

        if($returns==1){
            $data=[];
            $data['tolnum']=$this->tolshopnum();//统计购物车的总数量
            return jsonencode(200,'加入购物车成功',$data);
        }else{
            return jsonencode(301,'加入购物车失败',['data'=>$returns]);
        }

    }

    //购物车
    public function cart(){
        $user=$this->model_mb_user_token;

        $seach=[];
        $seach['cxorderid']=0;
        $seach['cxuserid']=$user['cxid'];
        $list=$this->productshopping->where($seach)->select();

        $data=[];
        $i=0;
        foreach ($list as $k=>$v){
            $isprostock=$this->isprostock($v['cxproductid'],$v['cxspecid']);

            $updataShop=[];
            $updataShop['cxid']=$v['cxid'];
            $updataShop['cxprice']=$isprostock['specArr']['price'];
            $updataShop['cximg0']=$isprostock['specArr']['pic'];
            $v1Val='';
            foreach ($isprostock['specArr']['spec'] as $k1=>$v1){
                if(!$v1Val){
                    $v1Val.=$v1['val'];
                }else{
                    $v1Val.=' '.$v1['val'];
                }

            }
            $updataShop['cxyixuan']=$v1Val;
            $num=$v['cxproductcount']>$isprostock['specArr']['stock']?$isprostock['specArr']['stock']:$v['cxproductcount'];
            $updataShop['cxproductcount']=$num;

            $this->productshopping->allowField(true)->isUpdate(true)->save($updataShop);
            $data[$i]['id']=$v['cxid'];
            $data[$i]['image']=httplocalimg($isprostock['specArr']['pic']);
            $data[$i]['attr_val']=$v1Val;
            $data[$i]['stock']=(int)$isprostock['specArr']['stock'];
            $data[$i]['state']=$isprostock['state'];//0为正常 1商品删除 2商品下架 3规格不存在  4商品已售完（库存为0）
            $data[$i]['title']=$v['cxtitle'];
            $data[$i]['price']=$isprostock['specArr']['price'];//现价
            $data[$i]['pricecart']=$v['cxpricecart'];//加入购物车时间价格
            $data[$i]['number']=$num;
            $data[$i]['proid']=$v['cxproductid'];
            $data[$i]['specid']=$v['cxspecid'];

            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 加减购物车数量
     * @author NN
     * @return bool
     */
    public function cartnumber(){
        $user=$this->model_mb_user_token;
        $id=request()->post('id',0);
        $number=request()->post('number',0);
        $rs=$this->productshopping->find($id);
        if(!$rs){
            return jsonencode(301,'非法操作');
        }
        if($rs['cxuserid']!=$user['cxid']){
            return jsonencode(301,'非法操作');
        }
        $updataShop=[];
        $updataShop['cxid']=$rs['cxid'];
        $updataShop['cxproductcount']=$number;
        $edit=$this->productshopping->allowField(true)->isUpdate(true)->save($updataShop);
        if($edit!==false){
            $data=[];
            $data['tolnum']=$this->tolshopnum();//统计购物车的总数量
            return jsonencode(200,'操作成功',$data);
        }else{
            return jsonencode(301,'操作失败');
        }

    }

    /**
     * 删除购物车
     * @author NN
     */
    public function delcart(){
        $user=$this->model_mb_user_token;
        $id=request()->post('id',0);
        $rs=$this->productshopping->find($id);

        if(!$rs){
            return jsonencode(301,'购物车没有此商品');
        }

        if($user['cxid']!=$rs['cxuserid']){
            return jsonencode(301,'非法操作');
        }

        $del=$this->productshopping->destroy($rs['cxid']);

        if($del!==false){
            $data=[];
            $data['tolnum']=$this->tolshopnum();//统计购物车的总数量
            return jsonencode(200,'删除成功',$data);
        }else{
            return jsonencode(301,'删除失败');
        }

    }

    /**
     * 清空购物车
     * @author NN
     * @return bool
     */
    public function clearcart(){
        $user=$this->model_mb_user_token;
        $shopList=$this->productshopping->where('cxorderid=:orderid and cxuserid=:uid',['orderid'=>0,'uid'=>$user['cxid']])->select();
        $ids=[];
        foreach ($shopList as $k=>$v){
            $ids[]=$v['cxid'];
        }
        $del=$this->productshopping->destroy($ids);
        if($del!==false){
            $data=[];
            $data['tolnum']=$this->tolshopnum();//统计购物车的总数量
            return jsonencode(200,'清空购物车成功',$data);
        }else{
            return jsonencode(301,'清空购物车失败');
        }
    }


    /**
     * 会员中心
     * @author NN
     * @return bool
     */
    public function user(){
        $rs=$this->model_mb_user_token;
        $data=[];
        $data['user']=[];
        $data['user']['portrait']=httplocalimg($rs['cximg0']);
        $data['user']['nickname']=$rs['cxname'];
        return jsonencode(200,'获取数据成功',$data);
    }

    public function orderinfo(){
        $user=$this->model_mb_user_token;
        $param=request()->post();
        $rs=db('cxproductorders')->where('cxno=:no',['no'=>$param['no']])->find();
        if(!$rs){
            return jsonencode(301,'操作有误');
        }
        if($user['cxid']!=$rs['cxuserid']){
            return jsonencode(301,'非法操作');
        }

        $data=[];
        $data['id']=$rs['cxid'];
        $data['no']=$rs['cxno'];
        $data['price']=$rs['cxtolprice'];
        $data['time']=$rs['cxtime'];
        $data['state']=$rs['cxupstatu'];
        $data['logistics']=$rs['cxlogistics'];//快递名称
        $data['logisticsno']=$rs['cxlogisticsno'];
        $data['logisticsurl']="https://www.kuaidi100.com/chaxun?com={$rs['cxlogistics']}&nu={$rs['cxlogisticsno']}";//快递接口
        $data['contactname']=$rs['cxcontactname'];//收货联系人
        $data['mobile']=$rs['cxmobile'];//收货联系电话
        $data['address']=$rs['cxaddress'];//收货联系详细地址
        $data['goodsList']=$this->goodsList($rs['cxid']);

        return jsonencode(200,'获取数据成功',$data);

    }

    /**
     * 订单列表
     * @author NN
     * @return bool
     */
    public function order(){
        $user=$this->model_mb_user_token;
        $param=request()->post();


        $pageSize=$param['pageSize'];//一页最多显示几条数据
        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $seach=[];
        $seach['cxuserid']=$user['cxid'];
        if($param['state']){
            $seach['cxupstatu']=$param['state'];
        }

        $proList=db('cxproductorders')->where($seach)->limit($p)->order('cxtime desc,cxid desc')->select();
//        dump(db('cxproduct')->getLastSql());
        $i=0;
        $data=[];
        foreach ($proList as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['no']=$v['cxno'];
            $data[$i]['price']=$v['cxtolprice'];
            $data[$i]['time']=$v['cxtime'];
            $data[$i]['state']=$v['cxupstatu'];
            $data[$i]['logistics']=$v['cxlogistics'];
            $data[$i]['logisticsno']=$v['cxlogisticsno'];
            $data[$i]['logisticsurl']="https://www.kuaidi100.com/chaxun?com={$v['cxlogistics']}&nu={$v['cxlogisticsno']}";
            $data[$i]['goodsList']=$this->goodsList($v['cxid']);

            $i++;
        }
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 支付
     * @author NN
     * @return bool
     *
     */
    public function pay(){
        $user=$this->model_mb_user_token;

        $param=request()->post();
        $payType=$param['payType'];//1微信支付 2货到付款 3余额支付
        $no=$param['no'];
        $price=$param['price'];
        $rs=db('cxproductorders')->where('cxno=:no',['no'=>$no])->find();
        if(!$rs){
            return jsonencode(301,'订单有误');
        }
        if($price!=$rs['cxtolprice']){
            return jsonencode(301,'订单有误');
        }
        if($rs['cxuserid']!=$user['cxid']){
            return jsonencode(301,'非法操作');
        }
        if($rs['cxpay']!=1){
            return jsonencode(301,'该订单无法支付');
        }

        $data=[];
        $data['no']=$rs['cxno'];

        //处理不同支付类型的支付
        $paydata=$this->payType($rs,$payType);
        if($paydata['code']==203){
            $data['jsApiParam']=$paydata['data'];
            return jsonencode(200,$paydata['mess'],$data);
        }

        if($paydata['code']==200){
//            $shop=db('cxproductshopping')->where('cxorderid=:orderid',['orderid'=>$rs['cxid']])->select();
//            $ptitle=',';
//            $ptitleid=',';
//            foreach ($shop as $k=>$v){
//                $ptitle.=$v['cxtitle'].',';
//                $ptitleid.=$v['cxproductid'].',';
//            }
//            $chatArr=[
//                'myid'=>0,
//                'uid'=>$rs['cxuserid'],
//                'content'=>'订单支付成功',
//                'porid'=>$ptitleid,
//                'url'=>'http://jiwuyouxuan.com/h5/#/pages/order/order?state=0',
//                'tem'=>[
//                    "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
//                    "openid"=>$user['cxopenid'],
//                    "url"=>"http://jiwuyouxuan.com/h5/#/pages/order/order?state=0",
//                    "param"=>[
//                        "first" =>'订单支付成功',//带颜色写法
//                        "keyword1" =>date('Y-m-d'),     //时间
//                        "keyword2" =>trim($ptitle,','),     //商品名称
//                        "keyword3" =>$rs['cxno'],    //订单号
//                        "remark"=>'点击查看详细',
//                    ]
//
//                ],
//
//
//            ];
//
//            $t=$this->tempservice->temsend($chatArr);
//            if($t['codes']!==200){
//                return jsonencode(301,'失败');
//            }

            return jsonencode(200,'支付成功',$data);
        }else{
            return jsonencode(301,$paydata['mess']);
        }

    }

    /**
     * 支付成功后进行消息推送
     * @author NN
     */
    public function paysuccess(){
        $user=$this->model_mb_user_token;
        $post=request()->post();
        //判断是否有推送（查询订单状态为2）



        $rs=db('cxproductorders')->where('cxno=:no',['no'=>$post['no']])->find();


        $paydata=[];
        $paydata['cxupstatu']=2;
        $paydata['cxpay']=2;
        $paydata['cxpaytime']=date('Y-m-d H:i:s');
        $paydata['cxpaytype']=1;
        db('cxproductorders')->where('cxid=:id',['id'=>$rs['cxid']])->update($paydata);

        if(!$rs){
            return jsonencode(301,'非法操作');
        }
        $shop=db('cxproductshopping')->where('cxorderid=:orderid',['orderid'=>$rs['cxid']])->select();
            $ptitle=',';
            $ptitleid=',';
            foreach ($shop as $k=>$v){
                $ptitle.=$v['cxtitle'].',';
                $ptitleid.=$v['cxproductid'].',';
            }

            //会员支付成功发送消息
            $chatArr=[
                'myid'=>0,
                'uid'=>$rs['cxuserid'],
                'content'=>'订单支付成功',
                'porid'=>$ptitleid,
                'url'=>request()->root(true).'/h5/#/pages/order/orderinfo?no='.$rs['cxno'],
                'tem'=>[
                    "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
                    "openid"=>$user['cxopenid'],
                    "url"=>request()->root(true).'/h5/#/pages/order/orderinfo?no='.$rs['cxno'],
                    "param"=>[
                        "first" =>'订单支付成功',//带颜色写法
                        "keyword1" =>date('Y-m-d'),     //时间
                        "keyword2" =>trim($ptitle,','),     //商品名称
                        "keyword3" =>$rs['cxno'],    //订单号
                        "remark"=>'点击查看详细',
                    ]

                ],


            ];


            $t=$this->tempservice->temsend($chatArr);
            if($t['codes']!==200){
                return jsonencode(301,'失败');
            }


            $adminchat=[
                [
                    'myid'=>$rs['cxuserid'],
                    'uid'=>0,
                    'content'=>'管理员您好，客户'.openrs_('cxuser','cxname',['cxid'=>$rs['cxuserid']],'').'下了一笔订单，请到后台处理',
                    'porid'=>$ptitleid,
                    'url'=>request()->root(true).'/zyadmin/porder/index',
                    'tem'=>[
                        "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
                        "openid"=>'olHlu6CeSbznBwYNu1PPKfpVEtas',//我
//                "url"=>request()->root(true).'/zyadmin/porder/index',
                        "url"=>'',
                        "param"=>[
                            "first" =>'管理员您好，客户'.openrs_('cxuser','cxname',['cxid'=>$rs['cxuserid']],'').'下了一笔订单，请到后台处理',//带颜色写法
                            "keyword1" =>date('Y-m-d'),     //时间
                            "keyword2" =>trim($ptitle,','),     //商品名称
                            "keyword3" =>$rs['cxno'],    //订单号
//                    "remark"=>'点击查看详细',
                        ]

                    ],
                ],
                [
                    'myid'=>$rs['cxuserid'],
                    'uid'=>0,
                    'content'=>'管理员您好，客户'.openrs_('cxuser','cxname',['cxid'=>$rs['cxuserid']],'').'下了一笔订单，请到后台处理',
                    'porid'=>$ptitleid,
                    'url'=>request()->root(true).'/zyadmin/porder/index',
                    'tem'=>[
                        "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
                        "openid"=>'olHlu6Gn5q1XEEHIIaf2ny3xq5i0',//stdanM
//                "url"=>request()->root(true).'/zyadmin/porder/index',
                        "url"=>'',
                        "param"=>[
                            "first" =>'管理员您好，客户'.openrs_('cxuser','cxname',['cxid'=>$rs['cxuserid']],'').'下了一笔订单，请到后台处理',//带颜色写法
                            "keyword1" =>date('Y-m-d'),     //时间
                            "keyword2" =>trim($ptitle,','),     //商品名称
                            "keyword3" =>$rs['cxno'],    //订单号
//                    "remark"=>'点击查看详细',
                        ]

                    ],
                ]
            ];
        //支付成功发送消息给管理员
        $tt1=0;
       foreach ($adminchat as $k=>$v){
           $t1=$this->tempservice->temsend($v);
           $tt1++;
       }

//        $t1=$this->tempservice->temsend($chatArr1);
//        if($t1['codes']!==200){
//            return jsonencode(301,'失败');
//        }


        return jsonencode(200,'操作成功');

    }

    /**
     * 查询会员余额
     * @author NN
     */
    public function balance(){
        $user=$this->model_mb_user_token;
        $rs=db('cxuser')->find($user['cxid']);
        $data=[];
        $data['price']=$rs['cxprice']?$rs['cxprice']:0;
        return jsonencode(200,'获取数据成功',$data);
    }

    /**
     * 模板消息
     * @author NN
     * @return bool
     */
    public function tempsend(){
        $chatArr=[
            'myid'=>0,
            'uid'=>'59',
            'content'=>'订单测试',
            'porid'=>83,
            'url'=>'http://jiwuyouxuan.com/h5',
            'tem'=>[
                "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
                "openid"=>"olHlu6CeSbznBwYNu1PPKfpVEtas",
                "url"=>"http://jiwuyouxuan.com/h5",
                "param"=>[
                    "first" =>'订单测试',//带颜色写法
                    "keyword1" =>date('Y-m-d'),     //时间
                    "keyword2" =>'测试商品',     //商品名称
                    "keyword3" =>'2545555454554',    //订单号
                    "remark"=>'点击查看详细',
                ]

            ],


        ];

        $t=$this->tempservice->temsend($chatArr);
        if($t['codes']!==200){
            return jsonencode(301,'失败');
        }
        return jsonencode(200,'操作成功');
    }

    /**
     * 站内消息
     * @author NN
     */
    public function notice(){
        $user=$this->model_mb_user_token;


        $param=request()->post();


        $pageSize=$param['pageSize'];//一页最多显示几条数据
        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $where=[];
        $where['cxuid']=$user['cxid'];
        $list=db('cxchat')->where($where)->limit($p)->order('cxaddtime desc')->select();
        $data=[];
        $i=0;
        foreach ($list as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['time']=$v['cxaddtime'];
            $data[$i]['content']=$v['cxcontent'];
            $data[$i]['url']=$v['cxurl'];
            $i++;
        }
        return jsonencode(200,'操作成功',$data);

    }

    /**
     * 充值列表页面
     * @author NN
     */
    public function rechargelist(){
        $user=$this->model_mb_user_token;


        $param=request()->post();


        $pageSize=$param['pageSize'];//一页最多显示几条数据
        if(isset($param['page']) && $param['page']){
            $page=$param['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $where=[];
        $where['cxuserid']=$user['cxid'];
        $list=db('cxrecharge')->where($where)->limit($p)->order('cxaddtime desc')->select();
        $data=[];
        $i=0;
        foreach ($list as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['time']=date('Y-m-d H:i');
            $data[$i]['price']=$v['cxprice'];
            $data[$i]['state']=$v['cxstate'];
            $data[$i]['no']=$v['cxno'];
            $i++;
        }
        return jsonencode(200,'操作成功',$data);

    }

    /**
     * 提交评论
     * @author NN
     */
    public function confirmcomment(){
        $user=$this->model_mb_user_token;
        $post=request()->post();
        $id=$post['id'];//购物车id
        $content=$post['content'];//评论内容


        $shop=db('cxproductshopping')->find($id);

        if(!$shop){
            return jsonencode(301,'数据有误');
        }

        if($user['cxid']!=$shop['cxuserid']){
            return jsonencode(301,'无权操作');
        }

        if($shop['cxiscomment']==2){
            return jsonencode(301,'商品已评论');
        }
        $this->startTrans();
        try{
            //添加评论
            $add=[];
            $add['cxcontent']=$content;
            $add['cxtype']=1;
            $add['cxtypeid']=$shop['cxproductid'];
            $add['cxstatus']=1;
            $add['cxaddtime']=time();
            $add['cxupdatetime']=time();
            $add['cxuserid']=$shop['cxuserid'];
            $add['cxuimg']=openrs_('cxuser','cximg0',['cxid'=>$shop['cxuserid']],'');
            $add['cxuname']=openrs_('cxuser','cxname',['cxid'=>$shop['cxuserid']],'');
            $add['cxspecid']=$shop['cxspecid'];
            $add['cxyixuan']=$shop['cxyixuan'];
            db('cxcomment')->insert($add);
            //修改购物车表评论状态
            db('cxproductshopping')->update(['cxid'=>$shop['cxid'],'cxiscomment'=>2]);

            // 提交事务
            $this->commit();
            $returns=1;
        } catch (\Exception $e) {//try
            // 回滚事务
            $this->rollback();
//			$mess=$e->getMessage();
//			dump($mess);
            $returns=$e->getMessage();
        }

        if($returns==1){
            return jsonencode(200,'评论成功');
        }else{
            return jsonencode(30,'评论失败');
        }
    }

    /**
     * 支付类型
     * @author NN
     * @param $order
     * @param $payType
     * @return array
     */
    protected function payType($order,$payType){
        $user=$this->model_mb_user_token;
        $paydata=[];
        switch ($payType)
        {
            case 1://1微信支付
                $paydata_['no']=$order['cxno'];
                $token=encode(json_encode($paydata_),$this->contoken());
                //跳转支付decode($z, $this->contoken());
                $notifyUrl=request()->root(true).'/index/pay/notifyurl';
                if($user['cxid']==170 || $user['cxid']==171){
                    $price=0.01;
                }else{
                    $price=$order['cxtolprice'];
                }
                // 支付订单
                if($order['cxpayno']){
                    $newno=explode("-",$order['cxpayno'])[1];
                    $outTradeNo=$order['cxno'].'-'.($newno+1);
                }else{
                    $outTradeNo=$order['cxno'].'-1';
                }
                $edit=db('cxproductorders')->update(['cxid'=>$order['cxid'],'cxpayno'=>$outTradeNo]);
                if($edit!==false){

                    $jsApiParameters=$this->wxpays->createJsBizPackage($user['cxopenid'], $price,$outTradeNo, '极物优选', $notifyUrl);
                    return ['code'=>203,'mess'=>'获取数据成功','data'=>$jsApiParameters];
                }else{
                    return ['code'=>301,'mess'=>'支付失败'];
                }

                exit;
                $paydata['cxid']=$order['cxid'];
                $paydata['cxupstatu']=2;
                $paydata['cxpay']=2;
                $paydata['cxpaytime']=date('Y-m-d H:i:s');
                $paydata['cxpaytype']=$payType;
                $up=db('cxproductorders')->update($paydata);
                if($up!==false){
                    return ['code'=>200,'mess'=>'支付成功'];
                }else{
                    return ['code'=>301,'mess'=>'支付失败'];
                }
                break;
            case 2://2货到付款
                $paydata['cxid']=$order['cxid'];
                $paydata['cxupstatu']=2;
                $paydata['cxpay']=2;
                $paydata['cxpaytime']=date('Y-m-d H:i:s');
                $paydata['cxpaytype']=$payType;
                $up=db('cxproductorders')->update($paydata);
                if($up!==false){
                    return ['code'=>200,'mess'=>'支付成功'];
                }else{
                    return ['code'=>301,'mess'=>'支付失败'];
                }
                break;
            case 3://3余额支付
                if($user['cxprice']<$order['cxtolprice']){
                    return ['code'=>301,'mess'=>'余额不足，请先充值'];
                }

                $paydata['cxid']=$order['cxid'];
                $paydata['cxupstatu']=2;
                $paydata['cxpay']=2;
                $paydata['cxpaytime']=date('Y-m-d H:i:s');
                $paydata['cxpaytype']=$payType;
                $up=db('cxproductorders')->update($paydata);
                if($up!==false){
                    $userdata=[];
                    $userdata['cxid']=$user['cxid'];
                    $userdata['cxprice']=$user['cxprice']-$order['cxtolprice'];
                    db('cxuser')->update($userdata);
                    return ['code'=>200,'mess'=>'支付成功'];
                }else{
                    return ['code'=>301,'mess'=>'支付失败'];
                }

            break;
        }
    }


    public function recharge(){
        $user=$this->model_mb_user_token;
        $price=request()->post('price',0);
        if($price<=0){
            return jsonencode(301,'金额有误');
        }
        //生成订单
        $no_=orderno();

        $paydata['cxuserid']=$user['cxid'];
        $paydata['cxprice']=$price;
        $paydata['cxstate']=1;
        $paydata['cxno']=$no_;
        $paydata['cxip']=request()->ip();
        $paydata['cxaddtime']=time();

        $id=db('cxrecharge')->insertGetId($paydata);
        if(!$id){
            return jsonencode(301,'提交失败');
        }


        $recharge=db('cxrecharge')->find($id);


        $notifyUrl=request()->root(true).'/index/pay/notifyurlcz';
        if($user['cxid']==170 || $user['cxid']==171){
            $price_=0.01;
        }else{
            $price_=$recharge['cxprice'];
        }
        // 支付订单
        if($recharge['cxpayno']){
            $newno=explode("-",$recharge['cxpayno'])[1];
            $outTradeNo=$recharge['cxno'].'-'.($newno+1);
        }else{
            $outTradeNo=$recharge['cxno'].'-1';
        }
        $edit=db('cxrecharge')->update(['cxid'=>$recharge['cxid'],'cxpayno'=>$outTradeNo]);
        $jsApiParameters=$this->wxpays->createJsBizPackage($user['cxopenid'], $price_,$outTradeNo, '极物优选', $notifyUrl);
        $jsApiParameters["no"]=$no_;
        return jsonencode(200,'获取数据成功',$jsApiParameters);
    }


    public function recharge_(){
        $user=$this->model_mb_user_token;
        $no=request()->post('no',0);
        if(!$no){
            return jsonencode(301,'操作有误');
        }
        $recharge=db('cxrecharge')->where('cxno=:no',['no'=>$no])->find();
        if(!$recharge){
            return jsonencode(301,'操作有误');
        }



        $notifyUrl=request()->root(true).'/index/pay/notifyurlcz';
        if($user['cxid']==170 || $user['cxid']==171){
            $price_=0.01;
        }else{
            $price_=$recharge['cxprice'];
        }
        // 支付订单
        if($recharge['cxpayno']){
            $newno=explode("-",$recharge['cxpayno'])[1];
            $outTradeNo=$recharge['cxno'].'-'.($newno+1);
        }else{
            $outTradeNo=$recharge['cxno'].'-1';
        }
        $edit=db('cxrecharge')->update(['cxid'=>$recharge['cxid'],'cxpayno'=>$outTradeNo]);
        $jsApiParameters=$this->wxpays->createJsBizPackage($user['cxopenid'], $price_,$outTradeNo, '极物优选', $notifyUrl);
        $jsApiParameters["no"]=$recharge['cxno'];
        return jsonencode(200,'获取数据成功',$jsApiParameters);
    }

    public function paysuccesscz(){
        $user=$this->model_mb_user_token;
        $post=request()->post();

        $rs=db('cxrecharge')->where('cxno=:no',['no'=>$post['no']])->find();
        if(!$rs){
            return jsonencode(301,'操作有误');
        }
        //会员支付成功发送消息
        $chatArr=[
            'myid'=>0,
            'uid'=>$user['cxid'],
            'content'=>'充值成功',
            'porid'=>$rs['cxid'],
            'url'=>request()->root(true).'/h5/#/pages/money/rechargelist?no='.$rs['cxno'],
            'tem'=>[
                "temid"=>"pARs5gCsSMn6fiaTIdV_F1Qrh8oBhOMHpXfj48PW6Kk",
                "openid"=>$user['cxopenid'],
                "url"=>request()->root(true).'/h5/#/pages/money/rechargelist?no='.$rs['cxno'],
                "param"=>[
                    "first" =>'充值成功',//带颜色写法
                    "keyword1" =>date('Y-m-d'),     //时间
                    "keyword2" =>'充值',     //商品名称
                    "keyword3" =>$rs['cxno'],    //订单号
                    "remark"=>'点击查看详细',
                ]

            ],


        ];


        $t=$this->tempservice->temsend($chatArr);
        if($t['codes']!==200){
            return jsonencode(301,'失败');
        }

        return jsonencode(200,'充值成功');
    }

    /**
     * 查询订单下的所有商品
     * @author NN
     * @param $orderid
     */
    protected function goodsList($orderid){
        $shop=$this->productshopping->where('cxorderid=:orderid',['orderid'=>$orderid])->select();
        $data=[];
        $i=0;
        foreach ($shop as $k=>$v){
            $data[$i]['id']=$v['cxid'];
            $data[$i]['title']=$v['cxtitle'];
            $data[$i]['price']=$v['cxprice'];
            $data[$i]['image']=httplocalimg($v['cximg0']);
            $data[$i]['number']=$v['cxproductcount'];
            $data[$i]['attr']=$v['cxyixuan'];//购买类型
            $data[$i]['specid']=$v['cxspecid'];//购买类型id
            $data[$i]['iscomment']=$v['cxiscomment'];//是否评论 1为否 2已评论
            $i++;
        }
        return $data;
    }


    /**
     * 判断商品状态
     * @author NN
     * @param $proid
     * @param $specid
     * @return array
     */
    protected function isprostock($proid,$specid){
        $state=0;
        $prs=db('cxproduct')->find($proid);
        if(!$prs){
            $state=1;//商品删除
        }
        if($prs['cxpassed']==0){
            $state=2;//商品下架
        }

        $specrs=db('cxspec')->find($specid);
        if(!$specrs){
            $state=3;//规格不存在
        }
        if(!$specrs['cxstock']){
            $state=4;//商品已售完（库存为0）
        }
        $specArr=json_decode($specrs['cxspec'],true);

        return ['state'=>$state,'specArr'=>$specArr];

    }

    protected function statearr($index){
        $arr=[
            '正常','商品已删除','商品下架','规格不存在','商品已售完'
        ];
        return $arr[$index];
    }
    /**
     * 计算总库存
     * @author NN
     * @param $proid
     * @return array
     */
    protected function proTolStock($proid){
        $speclist=db('cxspec')->where('cxproid=:proid',['proid'=>$proid])->select();
        $data=[];
        $i=0;
        foreach ($speclist as $k=>$v){
            $data[$i]=json_decode($v['cxspec'],true);
            $i++;
        }
        return $data;
    }


    /**
     * @author NN
     * @param $proid
     * @return array
     */
    protected function specifications($proid){
        $speclist=db('cxspec')->where('cxproid=:proid',['proid'=>$proid])->select();
        $data=[];
        $i=0;
        foreach ($speclist as $k=>$v){
            $spec=json_decode($v['cxspec'],true);

//            dump($spec);
            $spec['specid']=$v['cxid'];
            $spec['proid']=$proid;
            $data[$i]=$spec;
            $i++;
        }
        return $data;

    }

    /**
     * 产品规格
     * @author NN
     */
    protected function specifications1($proid){
        $speclist=db('cxspec')->where('cxproid=:proid',['proid'=>$proid])->select();
        $data=[];
        $data['specList']=[];
        $data['specChildList']=[];
        if($speclist){
            $specList_=json_decode($speclist[0]['cxspec'],true);
            if($specList_){
                $i=0;
                foreach ($specList_['spec'] as $k=>$v){
                    $data['specList'][$i]['id']=$i+1;
                    $data['specList'][$i]['name']=$v['name'];
                    $i++;
                }
            }
        }

        $i=0;
        $iii=0;
        foreach ($speclist as $k=>$v){
            $spec=json_decode($v['cxspec'],true);


            $ii=0;
            foreach ($spec['spec'] as $k=>$v){
                $data['specChildList'][$iii]['id']=$i+1;
                $data['specChildList'][$iii]['pid']=$ii+1;
                $data['specChildList'][$iii]['name']=$v['val'];
                $ii++;
                $iii++;
            }

            $i++;
        }




        $duplicateRemoval = array();
        foreach($data['specChildList'] as $key => &$value){
            if(in_array($value['name'],$duplicateRemoval)){
                unset($data['specChildList'][$key]);
            }else{
                $duplicateRemoval = $value;
            }
        }



        $arrNews=duplicateRemoval($data['specChildList']);
        $data['specChildList']=$arrNews;
//        dump($data);
        return $data;

    }

    /**
     * 通用api接口
     * @author NN
     * @return bool
     */
    public function Getdata(){
        /**
         * 1 产品表 2 产品分类表 3 用户表 4 购物车表 5 订单表 6 设置表 7 广告图片表
         */
        $tab=['cxproduct','cxproductsort','cxuser','cxproductshopping','cxproductorders','cxconfig','cxadvs'];

        $post=request()->param();
        $pageSize=$post['pageSize'];//一页最多显示几条数据
        if(isset($post['page']) && $post['page']){
            $page=$post['page']-1;
            $p1=$page*$pageSize;
            $p2=$pageSize;
        }else{
//            $page=0;
            $p1=0;
            $p2=$pageSize;
        }
        $p=$p1.','.$p2;

        $search=[];

        if($tab[($post['tab']-1)]){
            if(isset($post['page']) && $post['page']){
                $list=db($tab[($post['tab']-1)])->where($search)->limit($p)->order('cxupdatetime desc,cxSorting asc,cxid desc')->select();
            }else{
                $list=db($tab[($post['tab']-1)])->where($search)->order('cxupdatetime desc,cxSorting asc,cxid desc')->select();
            }


            $zd=json_decode(htmlspecialchars_decode($post['zd']),true);

            if($zd){
                $data=[];
                $i=0;

                foreach ($list as $k=>$v){
                    foreach ($zd as $k=>$v1){
                        if(strpos($v1, 'cximg')===false){
                            $data[$i][$v1]=$v[$v1];
                        }else{
                            $data[$i][$v1]=httpurlRootImg($v[$v1]);
                        }

                    }

                    $i++;
                }
            }

        }

        return jsonencode(200,'获取数据成功',$data);


    }




    /**
     * 滚动图片
     * @author NN
     */
    protected function uswiper($sortid,$limit){
        $advdb=db('cxadvs')->where('cxsortid=:sortid and cxpassed>1',['sortid'=>$sortid])->order('cxSorting asc,cxupdatetime desc')->limit($limit)->select();
        $i=0;
        $data=[];
        foreach ($advdb as $k=>$v){
            $data[$i]['title']=$v['cxtitlecn'];
            $data[$i]['image']=httpurlRootImg($v['cximg0']);
            $data[$i]['url']=$v['cxurl'];
            $i++;
        }
       return $data;
    }



}	
?>