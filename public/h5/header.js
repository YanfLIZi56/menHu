Vue.component('public-header', {
    props:['menu'],
    data () {
        return {
            advs:'',
            keyword:'',
            proSort:'',
            allClass:[],


        }
    },
    mounted () {
        var that;
        that=this;
        that.goPage();
        that.getAllClass();



    },
    methods: {

        getAllClass(){
            PostUrl('/api.Home/getlist', {table:'cxnews_sort',limit:999,order:'cxupdatetime desc'},function(e){
                that.allClass=e.data.rows;
            },'本页 - 全部分类');
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
            console.log(navigator.userAgent);
            if ((navigator.userAgent.match(/(ipad)/i))) {
                console.log('ipad');


            }else if ((navigator.userAgent.match(/(phone|pod|iPhone|ios|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))){
                /*window.location.href="你的手机版地址";*/
                console.log('手机版');
                location.href = '/mobile/';
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
        <header style="background: url(https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/ca58b5a2-dcd8-4642-9548-b6dacf8d986f.gif) repeat-x top;">  
            <div style="background: url('https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/0f5bf815-2dc5-47a2-9701-ff207c56119e.gif') no-repeat center; height: 180px;">
                <div class="w1200">
                    <div class="  wow fadeIn" style="height: 34px; line-height: 34px;">
                        欢迎您访问汕头市红十字会官方网站! {{Now()}}
                        <div class="rights wow fadeIn" v-if="localStorage.id">
                            <a href="login.html" class="layui-btn layui-btn-sm  layui-btn-primary">尊敬的{{localStorage.name}}，你好！【会员中心】</a>
                        </div>
                        <div class="rights wow fadeIn" v-else>
                            <a href="reg.html" class="layui-btn layui-btn-sm layui-btn-warm">注册</a>
                            <a href="login.html" class="layui-btn layui-btn-sm  layui-btn-primary">登陆</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="logins">
                        <div @click="location.href='/h5/'"><img src="https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/79fda02d-c72b-4a07-b1b4-e430cdd8935e.gif" alt=""></div>
                    </div>
                </div>
            </div>
            <div style="background: #EE0000">
                     <div class="nav w1200 ">
                    <ul class="layui-nav" lay-filter="">
                        <li class="layui-nav-item" v-for="item,key in menu">
                            <a v-if="item.title=='首页' || item.title=='联系我们'" :href="item.link">{{item.title}}</a>
                            <a v-else>{{item.title}}</a>
                            <dl v-if="item.nav" class="layui-nav-child"> <!-- 二级菜单 -->
                                <dd><a :href="items.link" v-for="items,keys in item.nav">{{items.title}}</a></dd>
                            </dl>
                        </li>
                    </ul>
    
                </div>    
            </div>
               
        </header>
    </div>
        
    `
})



