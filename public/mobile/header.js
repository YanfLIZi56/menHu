Vue.component('public-header', {
    props:['menu'],
    data:function() {
        return {
            advs:'',
            keyword:'123',
            proSort:'',
            allClass:[],
            showNav:false,

        }
    },
    mounted () {
        var that;
        that=this;
        that.goPage();
        that.showNav=false;
        console.log('头部',that.showNav);


        that.getAllClass();
        $(document).ready(function () {
            setTimeout(()=>{
                $('.headHeight').css('height',$('.heads').css('height'));
            },500);

        })


    },
    methods: {
        changeMenu(){
            console.log('23',that.showNav);
          that.showNav=!that.showNav;
        },
        getAllClass(){
            PostUrl('/api.Home/getlist', {table:'cxnews_sort',limit:999,order:'cxupdatetime desc'},function(e){
                that.allClass=e.data.rows;
            },'头部 - 全部分类');
        },

        userOut(){
            if(confirm('退出登陆，确认吗')){

                layer.load();
                location.href='userOut.html';
                layer.closeAll();
            }
        },
        menuOn (name) {
            let id = '';
            if(this.getQueryVariable("id")){
                id = this.getQueryVariable("id");
            }else{
                id = '0';
            }
            if(parseInt(id)===parseInt(name)){
                return true;
            }else{
                return false;
            }
        },
        getQueryVariable(variable){
            let query = window.location.search.substring(1);
            let vars = query.split("&");
            for (let i=0;i<vars.length;i++) {
                let pair = vars[i].split("=");
                if(pair[0] === variable){return pair[1];}
            }
            return(false);
        },
        goPage() {
            if (!(navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                /*window.location.href="你的手机版地址";*/
                location.href = '/h5/';
            }
        },
        getHead(){
            let _this = this;

        },
        search(){
            if(that.keyword){
                location.href='search.html?keyword='+keyword
            }else{
                layer.msg('关键词不能为空！')
            }
        },
        tsUrl(urls,cxpid,cxid){
            if(cxpid!=356){
                location.href=urls+'.html?cxsortid='+cxid
            }else{
                openDatabase(urls);
            }
        },
    },
    template: `
    <div>
        <header >  
        <div  class="headHeight" style="height: 180px;"></div>
            <div class="heads w100" style="position: fixed; top: 0px; left: 0;  background: #fff; z-index: 5;" >
                <div class="logins  wow fadeIn" style="height: 34px; line-height: 34px; font-size: 15px; background: #F2F2F2; padding-left:10px; position: relative;">
                    <div style="position: absolute; left:0px; top: 0px; width: 30%; height: 40px; display: none;">
                    <img src="https://mp-908027cd-0849-4303-ae20-d3bed9830e3a.cdn.bspapp.com/cloudstorage/a2ec8f6f-00d6-4587-8460-f9715c99ec02.gif" style="width: 30%;">
                    </div>
                    欢迎您访问汕头市红十字会官方网站!
                        <div class="rights wow fadeIn" v-if="localStorage.id">
                            <a href="login.html" class="layui-btn layui-btn-sm  layui-btn-primary">尊敬的{{localStorage.name}}，你好！【会员中心】</a>
                        </div>
                        <div class="rights wow fadeIn" v-else>
                            <a href="reg.html" class="layui-btn layui-btn-sm layui-btn-warm" style="font-size: 15px;">注册</a>
                            <a href="login.html" class="layui-btn layui-btn-sm  layui-btn-primary" style="font-size: 15px;">登陆</a>
                        </div>
                    <div class="clearfix"></div>
                </div>
                <div>
                    <div class="lefts" style="width: 40%; margin-left: 2%; margin-top: 10px;" @click="location.href='/mobile/'">
                        <img src="https://mp-908027cd-0849-4303-ae20-d3bed9830e3a.cdn.bspapp.com/cloudstorage/6a0ae2f4-cf80-4f2e-9b6e-2180a6051f3e.png" style="width: 100%;">
                    </div>
                    <div class="rights" style="width: 50%;">
                        <img src="https://mp-908027cd-0849-4303-ae20-d3bed9830e3a.cdn.bspapp.com/cloudstorage/455d2a4e-b6da-4a80-831d-578518d9e3b5.gif"  style="width: 100%;">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div style="background: #EE0000; line-height: 55px; font-size:22px; color: #fff; text-align: right;    padding-right: 16px;" @click="showNav=!showNav" style="display: none;">
                    栏目导航 <i class="iconfont icon-caidan" style="font-size:18px;"></i>
                </div>
                
                
                <div class="bottomNav w100">
                    <ul>
                    <li  v-for="item,key in menu">
                        <a  @click="showNav=!showNav">{{item.title}}</a>
                    </li></ul>
                    <div class="clearfix"></div>
                </div> 
            </div>
            <div style="background: #EE0000; position:fixed; top:0px; width: 100%; height: 100%; z-index: 999;" v-show="showNav" class="layui-anim layui-anim-fadein">
            
                <div style="padding:45px; background: #fff; position: relative;">
                    <div  style="text-align: center"  @click="showNav=!showNav">
                        <div><img src="https://mp-908027cd-0849-4303-ae20-d3bed9830e3a.cdn.bspapp.com/cloudstorage/6a0ae2f4-cf80-4f2e-9b6e-2180a6051f3e.png" style="width: 50%;"></div>
                    </div>
                    <div  style=" position: absolute; right: 25px; top:65px; border:1px solid #e00; text-align: center; line-height: 50px; border-radius: 50%; width: 50px;  height:50px; " @click="showNav=!showNav" ><i class="layui-icon layui-icon-right" style="color: #e00; font-size: 38px;"></i></span>
                    </div>
                </div>
                     <div class="nav w100 ">
                        <div v-for="item,key in menu" >
                            <a :href="item.link" class="m1"><i class="layui-icon layui-icon-add-1"></i> {{item.title}}</a>
                            <div v-if="item.nav" class="m2" > <!-- 二级菜单 -->
                                <a :href="items.link" v-for="items,keys in item.nav">{{items.title}}</a>
                            <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
    
                </div>    
            </div>
               
        </header>
    </div>
        
    `
})



