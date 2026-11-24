<?php
//namespace app\controller\api;
//
//use app\BaseController;
//use think\facade\Db;
//
//class Data extends Config
//{
//    /**
//     * @return 所有表
//     */
//    public function allBiao(){
//        $rs=$this->showdb('',2);
//        foreach ($rs as $k=>$v) {
//            $rs[$k]['fieldsName']=Db::getTableInfo($v['Name'], 'fields');
//            $rs[$k]['fieldsType']=Db::getTableInfo($v['Name'], 'type');
//            $rs[$k]['comment']=Db::getTableInfo($v['Name'], 'comment');
//        }
//        jsonencode(200,'摘取所有表成功',$rs);
//    }
//    /**
//     * @return 清空表内容
//     */
//    public function clearBiao(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        $rs=db($param['biaoMing'])->where('cxid>0')->delete();
//        jsonencode(200,'清空表'.$param['biaoMing'].'所有内容成功',$rs);
//    }
//
//    /**
//     * @return 所有字段
//     */
//    public function fieldList(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        $rs=Db::getTableInfo($param['biaoMing']);
//        jsonencode(200,'摘取表'.$param['biaoMing'].'所有字段成功',$rs);
//    }
//
//
//
//
//    /**
//     * @return 添加字段
//     */
//    public function fieldAdd(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //增加字段（在特定的字段后增加）
//        //alter table 表名 add 需要修改/增加的信息 afer 字段名(在哪个字段后增加)
//        //alter table dept Add column name varchar(20) not null default 0 AFTER sex;
//        //
//        //增加字段（把字段添加在第一个位置）
//        //alter table 表名 add 需要修改/增加的信息 first;
//        //alter table dept Add column name varchar(20) not null default 0 first;
//        $sqls='alter table '.$param['biaoMing'].' Add column '.$param['fieldName'].' '.$param['fieldType'].'('.$param['fieldLength'].') '.$param['fieldIsNull'].' default '.$param['fieldDefault'];
//        if(r_('AFTER')){$sqls.=' AFTER '.$param['AFTER'];} //添加在指定字段名后面
//        if(r_('sqls')){$sqls=$param['sqls'];} //直接执行语句
//        $d=Db::execute($sqls);
//        jsonencode(200,'添加字段完成',$d);
//
//    }
//    /**
//     * @return 修改字段
//     */
//    public function fieldEdit(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //alter table 表名 change 原字段 要修改的字段名 字段类型
//        //alter table dept change name newname varchar(30);
//        $sqls='alter table '.$param['biaoMing'].' change  '.$param['fieldName'].' '.$param['fieldNameNew'].' '.$param['fieldType'].'('.$param['fieldLength'].') '.$param['fieldIsNull'].' default '.$param['fieldDefault'].' ';
//        if(r_('sqls')){$sqls=$param['sqls'];} //直接执行语句
//        $d=Db::execute($sqls);
//        jsonencode(200,'修改字段完成',$d);
//
//    }
//    /**
//     * @return 删除字段
//     */
//    public function fieldDel(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //alter table 表名 drop column 列名
//        $sqls='alter table '.$param['biaoMing'].' drop column '.$param['fieldName'];
//        $d=Db::execute($sqls);
//        jsonencode(200,'删除字段'.$param['fieldName'].'完成',$d);
//
//    }
//    /**
//     * @return 调整字段顺序
//     */
//    public function fieldPos(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //ALTER TABLE user_movement_log CHANGE 字段A 字段A int not null default 0 AFTER 字段B ;
//        $sqls='alter table '.$param['biaoMing'].' change  '.$param['fieldName'].' '.$param['fieldName'] .' '.$param['fieldType'].'('.$param['fieldLength'].') '.$param['fieldIsNull'].' default '.$param['fieldDefault'].' AFTER '.$param['AFTER'];
//        $d=Db::execute($sqls);
//        jsonencode(200,'调整字段顺序：'.$param['fieldName'].'到字段'.$param['AFTER'].'前面完成',$d);
//
//    }
//
//
//    /**
//     * @return 添加表
//     */
//    public function biaoAdd(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //CREATE TABLE ;
//        $sqls="CREATE TABLE  ".$param['biaoMing']." (cxid int(11) not null auto_increment";
//        $sqls.=",`cxsortid` int(10) DEFAULT '0'";
//        $sqls.=",`cxtitlecn` varchar(255)";
//        $sqls.=",`cxtitleen` varchar(255)";
//        $sqls.=",`cxcontentcn` longtext";
//        $sqls.=",`cxcontenten` longtext";
//        $sqls.=",`cximg0` longtext";
//        $sqls.=",`cximg1` longtext";
//        $sqls.=",`cximg2` longtext";
//        $sqls.=",`cxpassed` int(11) DEFAULT '1'";
//        $sqls.=",`cxaddtime` datetime DEFAULT NULL";
//        $sqls.=",`cxupdatetime` datetime DEFAULT NULL";
//        $sqls.=",PRIMARY KEY (`cxid`) USING BTREE,  KEY `cxid` (`cxid`) USING BTREE)";
//        $sqls.=" ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='".$param['bz']."'";
//
//        $d=Db::execute($sqls);
//        jsonencode(200,'添加表：'.$param['biaoMing'].'完成',$d);
//
//        //
////        DROP TABLE IF EXISTS `aaaa`;
////CREATE TABLE `aaaa` (
////        `cxid` int(11) NOT NULL AUTO_INCREMENT,
////  `sss1` varchar(20) NOT NULL DEFAULT '0',
////  `sss2` int(20) DEFAULT '0',
////  `cxuserid` int(11) DEFAULT NULL COMMENT '会员',
////  `cxsortid` int(11) DEFAULT '0' COMMENT '分类id',
////  `cxtitlecn` longtext,
////  `cxtitleen` longtext,
////  `cximg0` longtext,
////  `cximg1` longtext,
////  `cximg2` longtext,
////  `cximg3` longtext,
////  `cximg4` longtext,
////  `cxkeywordscn` longtext,
////  `cxkeywordsen` longtext,
////  `cxcontentcn` longtext,
////  `cxcontenten` longtext,
////  `cxlink` longtext,
////  `cxhits` int(11) DEFAULT '0',
////  `cxpassed` int(11) DEFAULT '0',
////  `cxSorting` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
////  `cxaddtime` datetime DEFAULT NULL,
////  `cxupdatetime` datetime DEFAULT NULL,
////  PRIMARY KEY (`cxid`) USING BTREE,
////  KEY `cxid` (`cxid`) USING BTREE,
////  KEY `cxmemberid` (`cxuserid`) USING BTREE,
////  KEY `cxsortid1` (`cxsortid`) USING BTREE
////) ENGINE=InnoDB AUTO_INCREMENT=2091 DEFAULT CHARSET=utf8 COMMENT='表单 模板';
//
//    }
//    /**
//     * @return 删除表
//     */
//    public function biaoDel(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        //CREATE TABLE ;
//        $sqls="drop TABLE  ".$param['biaoMing'];
//        $d=Db::execute($sqls);
//        jsonencode(200,'删除表：'.$param['biaoMing'].'完成',$d);
//
//    }
//    /**
//     * @return 修改表名
//     */
//    public function biaoEdit(){
//        $param = request()->param();
//        if(!r_('biaoMing')){jsonencode(500,'表名不能为空！',$param); exit;}
//        $sqls="alter  TABLE  ".$param['biaoMing']." rename to ".$param['biaoMingNew'];
//        $d=Db::execute($sqls);
//        jsonencode(200,'修改表名：'.$param['biaoMing'].'改为'.$param['biaoMingNew'].' 完成',$d);
//
//    }
//
//
//
//    public function showdb($table_name,$mode=1)
//    {
//        if($mode==1){
//            //查询指定表信息
//            $table_info = Db::query('SHOW TABLE STATUS LIKE ' . "'" . $table_name . "'");
//        }
//        if($mode==2){
//            //查询所有表信息
//            $table_info = Db::query('SHOW TABLE STATUS ');
//        }
//        return    $table_info;
//    }
//
//
//}
//
//?>