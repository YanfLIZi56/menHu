<?php
namespace app\api\controller\v2;

use app\data\service\Dess;

use think\Validate;




class User extends Base
{


    public function index()
    {
        $param = request()->param();
        if ($param) {
            $this->CheckToKen($param['token']);
            $search=[];
            $searchSql='';

            if(isset($param['cxid'])){
                $search['cxid']=['in',$param['cxid']];
            }


            if(isset($param['cxuserid'])){
                $search['cxuserid']=['in',$param['cxuserid']];
            }
            if(isset($param['keyword'])){
                $search['cxname|cxcompany|cxJieShao|cxaddress|cxYeWu']=['like','%'.$param['keyword'].'%'];
            }

            if(isset($param['cxZu'])){
                if($param['cxZu']){$search['cxZu']=['=',$param['cxZu']];}
            }

            if(isset($param['keywordName']) && isset($param['keywordValue'])){
                $search[$param['keywordName']]=['like','%'.$param['keywordValue'].'%'];
            }

            if(isset($param['selectSql'])){
                $searchSql=$param['selectSql'];
            }

            $search['cxShowOk']=['=','on'];
            $param['table']='cxuser';
            $rs = db($param['table'])
                ->where($searchSql)
                ->where($search)
                ->field('cxrand,cxpassword,cxfullname,cxregtime,cxregip',true)
                ->page($param['page'], $param['limit'])
                ->order('cxid desc')
                ->select();

            $data['code'] = 0;
            $data['msg'] = '拉取列表数据成功';
            $data['count'] = db($param['table'])
                ->where($searchSql)
                ->where($search)
                ->count();;
            $data['limit'] = $param['limit'];
            $data['pageCur'] = $param['page'];
            $data['pageCount'] = ceil($data['count']/$param['limit']);
            $data['data'] = $rs;
            $data['sql'] = db($param['table'])->getlastsql();


            return json($data);

        }
    }


    public function info()
    {
        $table='cxuser';
        $param = request()->param();
        if ($param) {
            $user=$this->CheckToKen($param['token']);
            $rs = db($table)->field('cxpassword,cxtoken,cxrand',true)->find($param['cxid']);
            $data['code'] = 0;
            $data['msg'] = '拉取单个用户数据成功';
            $data['data'] = $rs;


            return json($data);

        }
    }


    public function add()
    {
        $param = request()->param();
        if ($param) {
            $this->CheckToKen($param['token']);
            unset($param['token']);
            $data=[];
            $table=$param['table'];
            unset($param['table']);
            $rs = db($table)->insertGetId($param);


            if($rs) {
                $data['code'] = 0;
                $rs=db($table)->find($rs);
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
            $user=$this->CheckToKen($param['token']);
            unset($param['token']);
            $rs = db('cxuser')->where('cxid',$user['cxid'])->update($param);

            if($rs) {
                $data['code'] = 0;
            }else {
                $data['code'] = 500;
            }

            $data['data'] = $rs;
            $data['code'] = 0;
            $data['msg'] = '修改完成';


            return json($data);

        }
    }
}

?>
