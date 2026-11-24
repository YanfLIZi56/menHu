Vue.component('public-header', {
    data() {
        return {
            advs: '',
            keyword: '11',
            proSort: '',
            menu: '',
            menu2: '',
            active: 0,
            curAdminUser: '',
            menuId: '',
            show: true,
            showAllGongNeng: false,
            showSide:true,
            newMess:0,
            kuCunBaoJing:0,
        }
    },
    mounted() {
        var that;
        that = this;
        if (getQV('menuId')) {

            that.menuId = getQV('menuId');
        } else {
            //that.show=false;
        }

        var cxpids;
        if (localStorage.curAdminUser) {
            that.curAdminUser = JSON.parse(localStorage.curAdminUser);
        }
        if (localStorage.adminName != 'admin') {

            PostUrl('/bc.Home/getlist', {
                //'cxMenu': that.curAdminUser.cxMenu,
                bm:'cxzy',
                cxpassed: 1,
                limit: 999,
                order: 'cxupdatetime desc,cxid desc',
                adminToken:localStorage.adminToken
            }, function (e) {
                that.menu2 = e.data.rows;
                localStorage.menu2 = JSON.stringify(that.menu2);
                that.$nextTick(function () {
                    layui.use(['element'], function (element) {
                        element.render({});
                    });
                });
            },'头部数据：后台菜单拉取完成')

        } else {
            if (localStorage.cxpids.indexOf(location.href.split("/").slice(-1)) > 0) {

            }
            // PostUrl('/bc.Home/getAllProSort', {'cxsortid': that.curAdminUser.cxpids}, function (e) {
            //     that.menu = e.data;
            //     localStorage.menu = JSON.stringify(that.menu);
            // },'头部数据：产品分类拉取完成')
            PostUrl('/bc.Home/getlist?bm=cxzy', {
                cxpassed: 1,
                limit: 999,
                order: 'cxupdatetime desc,cxid desc',
                adminToken:localStorage.adminToken
            }, function (e) {
                that.menu2 = e.data.rows;
                localStorage.menu2 = JSON.stringify(that.menu2);
                that.$nextTick(function () {
                    layui.use(['element'], function (element) {
                        element.render({});
                    });
                });
            },'头部数据：后台菜单拉取完成')

        }


        if (!localStorage.config) {
            localStorage.config = '{cxjson:[""]}';
        }
        // PostUrl('/bc.Home/getSys', {}, function (e) {
        //     localStorage.config = JSON.stringify(e.data);
        // },'公共数据：拉取完成')

        console.log('当前接收的网址:', urlHtmls());

        // that.getNewOrder();
        // setInterval(()=>{
        //     that.getNewOrder();
        // },10000)

    },
    methods: {
        getQueryVariable(variable) {
            let query = window.location.search.substring(1);
            let vars = query.split("&");
            for (let i = 0; i < vars.length; i++) {
                let pair = vars[i].split("=");
                if (pair[0] === variable) {
                    return pair[1];
                }
            }
            return (false);
        },
        checkCurPage(e) {
            if (e == location.href.split("/").slice(-1)) {
                return 'layui-nav-item layui-nav-itemed';
            } else {
                return 'layui-nav-item'
            }
        },
        urlToMenuUrl(e, sortId = 0) {
            if (e.indexOf('?') >= 0) {
                return e + '&menuId=' + sortId;
            } else {
                return e + '?menuId=' + sortId;
            }
        },
        loginOut(){

            layer.confirm('确定要退出登陆吗?', {icon: 3, title:'提示'}, function(index){
                //do something
                location.href='loginOut.html';
                layer.close(index);
            });
        },
        getNewOrder(){
            let that=this;
            // PostUrl('/bc.Home/getNewMess', {table:'cxproductorders'}, function (e) {
            //     that.newOrderCount=e.data;
            // },'摘取是否有新订单');
            PostUrl('/bc.Home/getNewMess', {table:'cxpingfen',searchSql:'cxisRead=0'}, function (e) {
                that.newMess=e.data;
            },'摘取是否有新的未读评分');
        },
        changeMianBan(){
            that=this;
            that.showAllGongNeng=!that.showAllGongNeng;
        },
        changeSide(){
            that=this;
            that.showSide=!that.showSide;
            if(that.showSide){
                $("#app").animate({
                    width: "85%",
                }, 1 );
            }else{
                $("#app").animate({
                    width: "100%",
                }, 1000 );

            }

        },


    },
    template: `
    <div v-show="show">
        <div style="height: 60px;"></div>
           <div style="background: #fff;position: fixed; left: 0; top: 0px; width: 100%; height:62px; z-index: 9;border-bottom:1px solid #eee;">
                <ul class="layui-nav">
                            <div style="float:left">
                                <img src="https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/25eaeef6-ccce-4b08-9caa-de04ecef681a.png" style="width:40px; border-radius: 50%; margin:10px">
                            </div>
                            <div style="color: #000;  float:left;  line-height:48px;    padding: 5px 0px;       font-size: 18px;    text-align: left; cursor:pointer;" @click="changeMianBan()">数据管理中心 
                                <i class="layui-icon layui-icon-windows" v-if="showAllGongNeng==false" style="margin-left:5px; "></i>
                                <i class="layui-icon layui-icon-close" v-if="showAllGongNeng==true" style="margin-left:5px; "></i>

                            </div>
                </ul>
                <ul class="layui-nav layui-layout-right layui-nav-white">
                        <li class="layui-nav-item" style="display: none;" >
                            <a @click="changeSide()">
                                <i class="layui-icon layui-icon-left" v-if="showSide==true" style="margin-left:5px; "></i>
                                <i class="layui-icon layui-icon-right" v-if="showSide==false" style="margin-left:5px; "></i>
                                </a>
</li>
                      <li class="layui-nav-item"  style="display:none;">
                        <a href="pingFenList.html?menuId=235">新的评分<span class="layui-badge-dot" v-if="newMess>0"></span></a>
                      </li>
                      <li class="layui-nav-item">
                        <a href="/" target="_blank">红十字会首页</a>
                      </li>
                      <li class="layui-nav-item">
                        <a href="http://up.bcwhkj.cn" target="_blank">
                        <img src="https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/143dea85-ca09-4439-aae2-9faad9825adb.jpg" class="layui-nav-img">云上传</a>
                      </li>
                      <li class="layui-nav-item">
                        <a href="" >退出</a>
                        <dl class="layui-nav-child">
                          <dd><a href="adminPass.html?menuId=120">{{localStorage.adminName}}</a></dd>
                          <dd><a href="adminPass.html?menuId=120">修改信息</a></dd>
                          <dd><a @click="loginOut()">退出登陆</a></dd>
                        </dl>
                      </li>
                </ul>
        <div class="clearfix"></div>
        </div>
        <div style="width:15%; float:left; height: 100px;"  v-show="showSide"></div>
        <div style="width:15%; position: fixed; left: 0px; top:62px; " v-show="showSide">
                    <ul class="layui-nav layui-nav-tree layui-anim layui-anim-up" lay-shrink="all"  lay-filter="test" style="width: 100%;">
                        <!-- 侧边导航: <ul class="layui-nav layui-nav-tree layui-nav-side"> -->
                          <li :class="item.cxid==menuId?'layui-nav-item layui-nav-itemed':'layui-nav-item'" v-for="item,key in menu2"   v-if="item.cxpid==0">
                            <a href="javascript:;"><i :class="item.cxicon" style="margin-right:5px; "></i>   {{item.cxsortcn}}</a>
                            <dl class="layui-nav-child">
                                  <dd v-for="items,keys in menu2" v-if="items.cxpid==item.cxid" @click="location.href=items.cxurl">
                                        <a :href="urlToMenuUrl(items.cxurl,item.cxid)" v-if="urlToMenuUrl(items.cxurl,item.cxid)==urlHtmls()" style="padding-left:42px; color:#fff; font-weight: 800">{{items.cxsortcn}} </a>
                                        <a :href="urlToMenuUrl(items.cxurl,item.cxid)" v-else  style="padding-left:42px">{{items.cxsortcn}} </a>
                                   </dd>
                            </dl>
                          </li>
                          

                          <li class="layui-nav-item"><a href="http://up.bcwhkj.cn" target="_blank">
                          <i class="layui-icon layui-icon-upload-drag" style="margin-right:5px; "></i> 
                          云上传</a></li>
                        </ul>
           
        </div>
        
        
        <!--功能大全面板-->
        <div v-show="showAllGongNeng" style="position: fixed; left: 20%; top:10%; width: 60%;  z-index: 999; background: #fff; border:1px solid #eee; padding:15px; background: url(https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/1deb8fa9-a5b3-41f4-a537-2e63e33f578c.png); background-size:100% 100%;box-shadow: rgb(0 0 0) 5px 5px 38px;" class="layui-anim layui-anim-up">
                      <div style="font-size: 24px; color: #fff; border-bottom: 1px solid #eee; padding:15px 0; margin-bottom:10px;">功能导航
                      <span style="float: right; cursor: pointer" @click="showAllGongNeng=!showAllGongNeng">【关闭】</span></div>
                <div  v-for="item,key in menu2"   v-if="item.cxpid==0" style="padding-bottom:25px;">
                      <div style="font-size: 22px; color: #999999;">{{item.cxsortcn}}</div>
                      <span style="padding:5px 45px 9px 0; width:25%;float: left;" v-for="items,keys in menu2" v-if="items.cxpid==item.cxid">
                        <a :href="items.cxurl+'?menuId='+item.cxid" style="font-size: 18px;color: #fff;" >{{items.cxsortcn}}</a>
                      </span>
                      <div class="clearfix"></div>
                </div>
        </div>
        

    </div>
        
    `
})



