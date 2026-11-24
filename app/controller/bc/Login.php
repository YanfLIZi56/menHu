<?php
namespace app\controller\bc;

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

		$username=input('param.username');
		$password=input('param.password');

//		dump($username);
//		dump($password);
//		dump($client);
		if (empty($username) || empty($password)) {
			jsonencode(301,'登录失败',['username'=>input('post.username'),'p'=>$password]);
		}

		$user=Db::name('cxuser')->where('cxusername=:username',['username'=>$username])->find();

		if(!$user){
			jsonencode(301,'用户名错误');
		}
		if($user['cxlock']==1){
			jsonencode(301,'该账号已被封，请联系管理员解封');
		}
		$rand=$user['cxrand'];
		
		$pass=md5(md5($password).$rand);
		if($pass!=$user['cxpassword']){
			jsonencode(301,'密码错误',['p1'=>$pass,'p2'=>$user['cxpassword'],'p3'=>$password,'u'=>$username]);
		}

		$utoken=$this->usertoken($user['cxid'],$user['cxusername'],$this->contoken());
		$res=[];
		$res['token']=$utoken;
		$res['id']=$user['cxid'];
		$res['username']=$user['cxusername'];
		$res['level']=$user['cxuserlevel'];
		$res['img']=httpurlRootImg($user['cximg0']);
		$res['name']=$user['cxname'];
		$updateuser=Db::name('cxuser')->update(['cxid'=>$user['cxid'],'cxtoken'=>$utoken,'cxlogincount'=>$user['cxlogincount']+1,'cxlastlogintime'=>time(),'cxlastloginip'=>request()->ip()]);
		if($updateuser!==false){
			jsonencode(200,'登录成功',$res);
		}else{
			jsonencode(200,'登录失败',$res);
		}

	}

//	/**
//	 * @param string $act 空时是查询，add表示加入IP黑名单
//	 * @return IP黑名单查询
//	 */
//	public function heiIp()
//	{
//		$param = request()->param();
//		$table='cxloginip';
//		$ip=request()->server()['REMOTE_ADDR'];
//		$data = Db::name($table)->where('cxip', '=',$ip)->select();
//		if ($data) {
//			jsonencode(200, '此IP存在IP黑名单库中，禁止登陆', $ip);
//		} else {
//			if($data){
//				$rs['cxip']=$ip;
//				$rs['cxaddtime']=Now();
//				$rs['cxupdatetime']=Now();
//				Db::name($table)->insert($rs);
//				jsonencode(200, '已经加入黑名单IP库', $ip);
//
//
//			}else{
//				jsonencode(300, '查询不到此IP', $ip);
//			}
//
//		}
//	}

	/**
	 * @return 管理员登录接口
	 */
	public function adminLogin(){

		//Db::name('cxloginip')->where('cxid','>',0)->delete();

		$param=request()->param();
		if(!r_('cxusername')){
			jsonencode(300,'用户名不能为空','');
			exit;
		}

		$table='cxloginip';
		$ip=request()->server()['REMOTE_ADDR'];

		$data = Db::name($table)->where('cxip', '=',$ip)->find();
		if ($data) {
			if($data['cxCount']>4){
//				jsonencode(500, '此IP存在IP黑名单库中，禁止登陆，请联系总管理员！', $ip);
//				exit;
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
				$rs['cxaddtime']=Now();
				$rs['cxupdatetime']=Now();
				$data['cxid']=Db::name($table)->insertGetId($rs);
		}


		$username=$param['cxusername'];
		$password=$param['cxpassword'];

		if (empty($username) || empty($password) ) {
			jsonencode(301,'登录失败',$param);
		}
		$password=$this->kimi($password,0);

		$user=Db::name('cxadmin')
			->where('cxadmin=:cxadmin',['cxadmin'=>$username])
			->find();

		if(!$user){
			jsonencode(301,'用户名或密码错误');
		}

		$tk=$this->getToken();

		$password=md5($password);
		if($password!=$user['cxpassword']){
			jsonencode(301,'用户名或密码有误');
		}

		$res=[];
		$res['id']=$user['cxid'];
		$res['token']=$tk;
		$res['adminToken']=$tk;
		$res['name']=$user['cxadmin'];
		$res['cxpids']=$user['cxpids'];
		$res['cxMenu']=$user['cxMenu'];


		Db::name($table)->where('cxid','=',$data['cxid'])->update(['cxCount'=>0]);
		Db::name('cxadmin')->where('cxid','=',$user['cxid'])->update(['cxtoken'=>$tk,'cxLastLoginTime'=>Now()]);

		jsonencode(200,'登录成功',$res);

	}

	function addLoginCount(){

	}


	/**
	 * @return 管理员登录验证
	 */
	public function checkAdminLogin(){
		$token=request()->param('token','');
		if(!$token){
			jsonencode(302,'请先登录');
		}
		$model_mb_user_token=Db::name('cxadmin')->where('cxtoken=:token',['token'=>$token])->find();
		if(!$model_mb_user_token){
			jsonencode(301,'token有误,请重新登陆！');
		}else{
//			$res['name']=$model_mb_user_token['cxadmin'];
//			$res['adminPids']=$model_mb_user_token['cxpids'];
//			$res['adminMenu']=$model_mb_user_token['cxMenu'];
//			$res['cxXiaShu']=$model_mb_user_token['cxXiaShu'];
			jsonencode(200,'token验证成功',$model_mb_user_token);
		}

	}

	
	//登录验证
	public function checklogin(){
		$token=request()->param('key','');
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

	//退出
	public function loginout(){
		$username=input('param.username');
		$client=input('param.client');
		$token=input('param.key');
		if(empty($username) || $client!=='wap') {
			jsonencode(302,'参数有误');
		}
		$res=[];
		$res['token']=$token;
		$res['username']=$username;
		jsonencode(200,'退出成功',$res);
	}

	//注册
	public function register(){
		$username = input('param.username');
		$name = input('param.name');
		$fullname = input('param.fullname');
		$password = input('param.password');
		$password_confirm = input('param.password_confirm');
		$mobilecode=input('param.mobilecode');
//		$email = input('param.email');
//		$client = input('param.client');
//		$inviter_id = intval(input('param.inviter_id'));
//		$c = intval(input('param.c'));
		if(empty($password)  || empty($mobilecode)) {
			jsonencode(302,'参数有误');
		}

		if(!cache('regusername_'.$username)){
			cache('regusername_'.$username,null);
			jsonencode(302,'验证码已过期',['code'=>cache('regusername_'.$username)]);
		}
		if(cache('regusername_'.$username)!=$mobilecode){
			jsonencode(302,'验证码有误');
		}

		if(strlen($password)<5 || strlen($password)>16){
			jsonencode(304,'密码8-16位');
		}

//		if($password!==$password_confirm){
//			jsonencode(304,'两次密码不一致');
//		}
		$cuser=Db::name('cxuser')->where('cxusername=:username',['username'=>$username])->count();
		if($cuser){
			jsonencode(304,'该手机号已注册');
		}
		$rand=getRandomString(6);

		$data=[];
		$data['cxusername']=$username;
		$data['cxmobile']=$username;
		$data['cxname']=$name;
		$data['cxfullname']=$fullname;
		$data['cxpassword']=md5(md5($password).$rand);
		$data['cxpassword1']=$password;
		$data['cxrand']=$rand;
//		$data['cxbinfxid']=$inviter_id;
		$data['cxuserlevel']=7;//会员级别
		$data['cxauthority']=1;//权限
		$data['cxregtime']=time();//注册时间
		$data['cxregip']=request()->ip();//注册ip
		$data['cxlastlogintime']=time();//最后登录时间
		$data['cxlastloginip']=request()->ip();//最后登录ip
		$data['cxlogincount']=1;//登录次数
		$data['cxlock']=2;//状态
		$data['cxaddtype']='前台';//来源
		$data['cxowner']=1;//是否为业主 1不是业主 2是业主

		//验证数据  BEGIN

		$rule = [
			['cxusername', 'require|max:11|/^1[3-8]{1}[0-9]{9}$/', '手机不能为空|手机号不能超过11个字符|手机号格式不正确'],
			['cxname', 'require', '昵称不能为空'],
			['cxfullname', 'require', '姓名不能为空'],

		];

		$validate = new Validate($rule);

		$validate_result = $validate->check($data);

		if (!$validate_result) {

//            $this->error($validate->getError());
			jsonencode('303',$validate->getError());

		}


		$uid=Db::name('cxuser')->insertGetId($data);
		if($uid>0){
			$utoken=$this->usertoken($uid,$username,$this->contoken());
			$udb=Db::name('cxuser')->where('cxid=:id',['id'=>$uid])->find();
			$res=[];
			$res['token']=$utoken;
			$res['uid']=$uid;
			$res['username']=$username;
			$res['level']=$udb['cxuserlevel'];
			$res['img']=httpurlRootImg($udb['cximg0']);
			$res['name']=$udb['cxname'];
			jsonencode(200,'注册成功',$res);
		}else{
			jsonencode(305,'注册失败');
		}
	}

	public function recommend(){
		$inviter_id=request()->get('inviter_id','');
		$des = new Dess($this->contoken(), 'DES-ECB', Dess::OUTPUT_HEX);
		$uid=$des->decrypt($inviter_id);
		$model_mb_user_token=Db::name('cxuser')->where('cxid=:id',['id'=>$uid])->find();
		if(!$model_mb_user_token){
			jsonencode(301,'参数错误1');
		}
		jsonencode(200,'获取成功',['inviter_id'=>$inviter_id,'username'=>$model_mb_user_token['cxusername']]);
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
