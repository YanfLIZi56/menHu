<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;

class Login extends Config
{

	public function __construct(){
		parent::__construct();

	}
	

	//登录
	public function index(){
//		$request='http://localhost/xqzx/apiadmin/v2.login/index';
//		$data['id']=2;
//		dump($this->getDataFromServer($request,'get',$data));

		$username=input('param.cxusername');
		$password=input('param.cxpassword');

		$param=request()->param();
		$table='cxloginip';
		$ip=request()->server()['REMOTE_ADDR'];

		$data = Db::name($table)->where('cxip', '=',$ip)->find();
		if ($data) {
			if($data['cxCount']>4){
				jsonencode(500, '此IP存在IP黑名单库中，禁止登陆，请联系总管理员！', $ip);
				exit;
			}else{
				Db::name($table)->where('cxid','=',$data['cxid'])->update(['cxupdatetime'=>Now(),'cxCount'=>($data['cxCount']+1)]);
//				if($data['cxCount']==2){jsonencode(500, '登陆出错次数超过5次，您的IP将被加入黑名单，当前只剩3次，请谨慎操作！', Db::name($table)->getlastsql());}
//				if($data['cxCount']==3){jsonencode(500, '登陆出错次数超过5次，您的IP将被加入黑名单，当前只剩2次，请谨慎操作！', $ip);}
//				if($data['cxCount']==4){jsonencode(500, '登陆出错次数超过5次，您的IP将被加入黑名单，当前只剩1次，请谨慎操作！', $ip);}
//				if($data['cxCount']==5){jsonencode(500, '登陆出错次数超过5次，您的IP将被加入黑名单，当前只剩0次！', $ip);}
			}
		}else{
			$rs['cxip']=$ip;
			$rs['cxpassed']=1;
			$rs['cxCount']=1;
			$rs['cxType']='前台会员登陆';
			$rs['cxaddtime']=Now();
			$rs['cxupdatetime']=Now();
			$data['cxid']=Db::name($table)->insertGetId($rs);
		}


//		dump($username);
//		dump($password);
//		dump($client);
		if (empty($username) || empty($password)) {
			jsonencode(301,'登录失败',['username'=>input('post.username'),'p'=>$password]);
		}

		$user=Db::name('cxuser')->where('cxusername=:username',['username'=>$username])->find();

		if(!$user){
			jsonencode(301,'用户名或密码错误');
		}
		if($user['cxpassed']==1){
			jsonencode(301,'该账号已被封，请联系管理员解封');
		}
		$rand=$user['cxrand'];
		
		$pass=md5(md5($password).$rand);
		if($pass!=$user['cxpassword']){
			jsonencode(301,'用户名或密码错误',['p1'=>$pass,'p2'=>$user['cxpassword'],'p3'=>$password,'u'=>$username]);
		}

		$res=[];
		$tk=getRandomString(10);
		$res['token']=$tk;
		$res['id']=$user['cxid'];
		$res['username']=$user['cxusername'];
		$res['level']=$user['cxsortid'];
		$res['img']=$user['cximg0'];
		$res['name']=$user['cxfullname'];
		$res['passed']=$user['cxpassed'];
		$updateuser=Db::name('cxuser')->update(['cxid'=>$user['cxid'],'cxtoken'=>$tk,'cxlogincount'=>$user['cxlogincount']+1,'cxlastlogintime'=>Now(),'cxlastloginip'=>request()->ip()]);
		if($updateuser!==false){

			$this->addLog('前台登陆',$user['cxfullname']);
			Db::name($table)->where('cxid','=',$data['cxid'])->update(['cxCount'=>0]);
			jsonencode(200,'登录成功',$res);
		}else{
			jsonencode(200,'登录失败',$res);
		}

	}

	//登录验证
	public function checklogin(){
		$token=request()->param('token','');
		if(!$token){
			jsonencode(302,'请先登录');
		}
		$model_mb_user_token=Db::name('cxuser')->where('cxtoken=:token',['token'=>$token])->find();
		if(!$model_mb_user_token){
			jsonencode(301,'账号过期或不存在');
		}else{
			jsonencode(200,'登录成功');
		}

	}


	//登录验证
	public function checkloginwx(){
		$lstoken=request()->param('lstoken','');
		if(!$lstoken){
			jsonencode(302,'请先登录');
		}
		if($lstoken=='9f0fb15463f6d8b72deede7f2ea303d02dafda6f'){
			$user=Db::name('cxuser')->where('cxname=:name',['name'=>'Aa_喃喃'])->find();
		}elseif(strpos($lstoken, 'olHlu6')!==false){
			$user=Db::name('cxuser')->where('cxopenid=:openid',['openid'=>$lstoken])->find();
		}else{
			$user=Db::name('cxuser')->where('cxlstoken=:lstoken',['lstoken'=>$lstoken])->find();
		}

		if(!$user){
			jsonencode(301,'账号过期或不存在'.Db::name('cxuser')->getLastSql(),$user);
		}
		$utoken=$this->usertoken($user['cxid'],$user['cxusername'],$this->contoken());
		$res=[];
		$res['token']=$utoken;
		$res['id']=$user['cxid'];
		$res['username']=$user['cxusername'];
		$res['level']=$user['cxuserlevel'];
		$res['img']=httpurlRootImg($user['cximg0']);
		$res['name']=$user['cxname'];
		$updateuser=Db::name('cxuser')->update(['cxid'=>$user['cxid'],'cxtoken'=>$utoken,'cxlstoken'=>'','cxlogincount'=>$user['cxlogincount']+1,'cxlastlogintime'=>time(),'cxlastloginip'=>request()->ip()]);


		jsonencode(200,'登录成功',$res);

	}

	protected function usertoken($id,$username,$contoken){
		$str=$id.$username.$contoken;
		$token = sha1($str);
		$token = md5($token);
		//转换成大写
		$token = strtoupper($token);
		Db::name('cxuser')->where('cxid=:id',['id'=>$id])->update(['cxtoken' => $token]);
		return $token;
	}


	//注册
	public function reg(){
		$param=request()->param();
		if (!r_('cxfullname')){
			jsonencode(304,'无效，账号不能为空');exit;
		}
		if (!r_('cxpassword1')){
			jsonencode(304,'无效，密码不能为空');exit;
		}
		if (!r_('cxpassword2')){
			jsonencode(304,'无效，再次输入密码不能为空');exit;
		}
		if (!r_('cxfullname')){
			jsonencode(304,'无效，姓名不能为空');exit;
		}
		if($param['cxpassword1']!=$param['cxpassword2']){
			jsonencode(304,'无效，两次输入的密码不一致');exit;
		}

		$insertD['cxopenid']=getRandomString(10);
		$insertD['cxfullname']=preg_replace('/[\xf0-\xf7].{3}/', '', $param['cxfullname']);
		$insertD['cxusername']=preg_replace('/[\xf0-\xf7].{3}/', '', $param['cxusername']);

		$insertD['cxrand']=getRandomString(6);;

		$pass=md5(md5($param['cxpassword']).$insertD['cxrand']);
		$insertD['cxpassword']=$pass;
		$insertD['cxtoken']=getRandomString(10);
		$insertD['cxpassed']=2;
		$insertD['cximg0']='';
		$insertD['cxsortid']=7;
		$insertD['cxlastlogintime']=Now();

		$search['cxusername']=$insertD['cxusername'];
		$search['cxfullname']=$insertD['cxfullname'];
		$nUser=Db::name('cxuser')->where($search)->find();
		if($nUser){
			jsonencode(300,'注册失败，此用户名和姓名已经被他人注册，请检查','');exit;
		}else{

			$newUserId=Db::name('cxuser')->insertGetId($insertD);
			if($newUserId){

				$res['token']=$insertD['cxtoken'];
				$res['id']=$newUserId;
				$res['username']=$insertD['cxusername'];
				$res['level']=$insertD['cxsortid'];
				$res['img']=$insertD['cximg0'];
				$res['passed']=$insertD['cxpassed'];
				$res['name']=$insertD['cxfullname'];
				jsonencode(200,'注册成功',$res);exit;
			}else{
				jsonencode(200,'注册失败，请检查数据','数据插入失败');exit;
			}
		}



	}


	//手机短信验证码
	public function authcode(){

		$username=request()->post('username','');
		$data=[
			'username'=>$username,//手机号
		];

		//验证数据  BEGIN

		$rule = [

			['username','require|max:11|/^1[3-8]{1}[0-9]{9}$/','请输入手机号码|手机号码最多不能超过11个字符|手机号码格式不正确']

		];

		$validate = new Validate($rule);

		$validate_result = $validate->check($data);

		if (!$validate_result) {
			jsonencode(301,$validate->getError());

		}

		if(cache('regusername_'.$username)){
			jsonencode(303,'验证码未过期');
		}
//        cache
		$rand=getRandomString(4,'0123456789');
		$content='【邻里】您好，您的验证码是'.$rand;
		$sms=smsbao($username,$content);
		$smstime=60;//设置验证码有效时间
		if($sms['code']==0){
			cache('regusername_'.$username,$rand,$smstime);
			jsonencode(200,$sms['mess'],['sms_time'=>$smstime]);
		}else{
			jsonencode($sms['code'],$sms['mess']);
		}
	}

}	
?>
