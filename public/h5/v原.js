
var that;
var app = new Vue({
    el: '#app',
    data: {
        msg: 'vue启动成功',
        cxfullname:'',
        cxshenFenZheng:'',
        userInfo:{cxusername:'',cxpassword:'',cxfullname:'',cxpassword1:'',cxpassword2:'',},
        cxZu:'',
        nopic:'https://mp-908027cd-0849-4303-ae20-d3bed9830e3a.cdn.bspapp.com/cloudstorage/7e2faa85-1726-494a-91fe-f8dff46f1576.gif',
        listZu:[],
        List:[{cxid:'',cxtitlecn:'',cximg0:''}],
        adList:[{cxid:'',cxtitlecn:'',cximg0:''}],
        tongZhiList:[{cxid:'',cxtitlecn:'',cximg0:''}],
        List2:[{cxid:'',cxtitlecn:'',cximg0:''}],
        List3:[{cxid:'',cxtitlecn:'',cximg0:''}],
        List4:[{cxid:'',cxtitlecn:'',cximg0:''}],
        List5:[
            {
                cxid:'',cxtitlecn:'',cximg0:'',cxjson:[{title:'',content:''},{title:'',content:''},{title:'',content:''}]
            }
        ],
        List6:[{cxid:'',cxtitlecn:'',cximg0:''}],
        List7:[{cxid:'',cxtitlecn:'',cximg0:''}],
        search:{
            table:'cxnews',
            cxsortid:436,
            limit:25
        },
        curClass:[],
        sideClass:[{
            cxsortcn:''
        }],
        Info:'',

        menu:[
            {title:'首页',link:'index.html',},
            {title:'机构简介',link:'newInfo.html?cxsortid=413&cxid=1300',
                nav:[
                    {title:'机构简介',link:'newInfo.html?cxsortid=446&cxid=1300'},
                    {title:'领导机构',link:'newInfo.html?cxsortid=444&cxid=1496'},
                    {title:'内设机构',link:'newInfo.html?cxsortid=445&cxid=1455'},
                ]
            },
            {title:'新闻资讯',link:'newList.html?cxsortid=412',
                nav:[
                    {title:'媒体报道',link:'newList.html?cxsortid=418'},
                    {title:'红会动态',link:'newList.html?cxsortid=419'},
                    {title:'交流合作',link:'newList.html?cxsortid=420'},
                    {title:'党建专栏',link:'newList.html?cxsortid=452'},
                    {title:'党史专栏',link:'newList.html?cxsortid=459'},
                ]
            },
            {title:'红会事业',link:'newList.html?cxsortid=411',
                nav:[
                    {title:'人道救助',link:'newList.html?cxsortid=424'},
                    {title:'救灾救援',link:'newList.html?cxsortid=425'},
                    {title:'应急救护',link:'newList.html?cxsortid=426'},
                    {title:'无偿献血',link:'newList.html?cxsortid=427'},
                    {title:'造血干细胞捐献',link:'newList.html?cxsortid=428'},
                    {title:'遗体和人体器官捐献',link:'newList.html?cxsortid=429'},
                    {title:'志愿服务',link:'newList.html?cxsortid=453'},
                    {title:'红十字青少年',link:'newList.html?cxsortid=423'},
                    {title:'红十字会员',link:'newList.html?cxsortid=454'},
                ]
            },
            {title:'政策法规',link:'newList.html?cxsortid=410',
                nav:[
                    {title:'政策法规',link:'newList.html?cxsortid=410'},
                ]
            },
            {title:'网上办事',link:'newList.html?cxsortid=414',
                nav:[
                    {title:'我要求助',link:'newInfo.html?cxsortid=432&cxid=1327'},
                    {title:'我要捐款',link:'newInfo.html?cxsortid=433&cxid=1319'},
                    {title:'加入会员',link:'http://gztyy.gd.icbc.com.cn/rcsmob/'},
                    {title:'成为志愿者',link:'https://st.izyz.org/login/fillMessageForOtherSystem'},
                ]
            },
            {title:'公告公示',link:'newList.html?cxsortid=415',
                nav:[
                    {title:'通知公告',link:'newList.html?cxsortid=436'},
                    {title:'预决算公开',link:'newList.html?cxsortid=437'},
                    {title:'捐赠公开',link:'newList.html?cxsortid=438'},
                    {title:'志愿服务队',link:'newList.html?cxsortid=460'},
                    {title:'活动公告',link:'newList.html?cxsortid=456'},
                ]
            },
            {title:'联系我们',link:'newInfo.html?cxsortid=416&cxid=1271',},
        ],
        liuYan:{
            cxtitlecn:'',
            cxemail:'',
            cxcontentcn:'',
            cxRenShu:0,
            cxphone:'',
            cxYuYueTime:[],
        },
        liuYan1:{
            yuYueTime:'',//预约时间
            mobile:'',//手机号

        },
        wangShangBanShiArr:[
            {name:'我要加入会员',name2:'(红十字会会员)',link:'http://gztyy.gd.icbc.com.cn/rcsmob/',icon:'iconfont icon-shixishengzhaomu'},
            {name:'成为志愿者',name2:'(加入志愿者)',link:'https://st.izyz.org/login/fillMessageForOtherSystem',icon:'iconfont icon-jiarubanji'},
            {name:'我要捐赠',name2:'(捐款捐物)',link:'newInfo.html?cxsortid=433&cxid=1319',icon:'iconfont icon-airudiantubiaohuizhi-zhuanqu_xianaixin'},
            {name:'我要求助',name2:'(申请救助)',link:'newInfo.html?cxsortid=432&cxid=1327',icon:'iconfont icon-hear-half'},
            {name:'我要报名',name2:'(救护员培训)',link:'guestBook.html',icon:'iconfont icon-dingdan2'},
        ],
        xinXiGongKaiArr:[
            {name:'通知公告',link:'newList.html?cxsortid=436',icon:'iconfont icon-xiangmu'},
            {name:'志愿服务队',link:'newList.html?cxsortid=442',icon:'iconfont icon-HRrenshirenshiguanli'},
            {name:'预决算公开',link:'newList.html?cxsortid=437',icon:'iconfont icon-chanpin1'},
            {name:'领导机构',link:'newInfo.html?cxsortid=444&cxid=1496',icon:'iconfont icon-renyuanbaoming'},
            {name:'内设机构',link:'newInfo.html?cxsortid=445&cxid=1455',icon:'iconfont icon-biangeng-daiquan'},
            {name:'联系方式',link:'newInfo.html?cxsortid=416&cxid=1271',icon:'iconfont icon-gongkai'},
        ],
        hongHuiShiYeArr:[
            {name:'救援救灾',link:'newList.html?cxsortid=425'},
            {name:'应急救护',link:'newList.html?cxsortid=426'},
            {name:'人道救助',link:'newList.html?cxsortid=424'},
            {name:'无偿献血',link:'newList.html?cxsortid=427'},
            {name:'造血干细胞捐献',link:'newList.html?cxsortid=428'},
            {name:'遗体器官捐献',link:'newList.html?cxsortid=429'},
        ],
        indexVideoUrl:'http://www.stredcross.org.cn/uploads/video/%E9%80%A0%E8%A1%80%E5%B9%B2%E7%BB%86%E8%83%9E%E5%AE%A3%E4%BC%A002.mp4',

    },
    mounted() {
        that = this;
        console.log(that.msg);
        if(getQV('cxid')){
            that.getInfo();
            that.getSideCLass();
        }else if(getQV('cxsortid')){
            that.search.cxsortid=getQV('cxsortid');
            that.getList();
            that.getSideCLass();
        }else {
            that.getMain();
        }
    },
    methods: {
        guestbook(){
            let postD={};
            Object.assign(postD,that.liuYan);
            console.log(postD);
            postD.cxYuYueTime=JSON.stringify(postD.cxYuYueTime);
            PostUrl('/api.Home/guestAdd', postD,function(e) {
                if (e.codes == 200) {
                    alert(e.mess);
                    //location.href='index.html';
                } else {
                    alert(e.mess);
                }
            },'本页 - 留言');
        },
        getInfo(){

            PostUrl('/api.Home/info', {table:'cxnews',cxid:getQV('cxid')},function(e){
                layer.closeAll();
                that.Info=e.data;

            },'本页 - 详情');

        },
        getList(){

            if(localStorage.cxExpTime){
                if(timeDaoQi(localStorage.cxExpTime,30)){
                    //最后登陆的时间
                    localStorage.clear();
                    alert('登陆超时,请重新登陆 ');
                    location.href='login.html';
                }else{
                }
            }

            PostUrl('/api.Home/getlist', that.search,function(e){
                layer.closeAll();
                that.List=e.data.rows;
                that.count=e.data.count;
                that.pageCount=e.data.pageCount;
                fenye();
            },'本页 - 列表');
        },
        getSideCLass(){
            PostUrl('/api.Home/getProSortSun', {cxsortid:getQV('cxsortid'),cxpassed:'1,2'},function(e){
                that.sideClass=e.data.rows;
            },'本页 - 侧边分类');
        },
        getMain(){
            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:440},function(e){
                layer.closeAll();
                that.adList=e.data.rows;
                that.$nextTick(()=>{

                    var mySwiper = new Swiper ('.ad1', {
                        direction: 'horizontal', // 垂直切换选项
                        effect: 'flip',
                        slidesPerView: 1,
                        centeredSlides: true,
                        autoHeight:true,
                        autoplay: {
                            delay: 3000,
                            stopOnLastSlide: false,
                            disableOnInteraction: true,
                        },
                        loop: true, // 循环模式选项

                        // 如果需要分页器
                        pagination: {
                            el: '.swiper-pagination',
                        },

                        // 如果需要前进后退按钮
                        navigation: {
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                        },

                        // // 如果需要滚动条
                        // scrollbar: {
                        //     el: '.swiper-scrollbar',
                        // },
                    })

                });
            },'本页 - 列表 - 首页横幅海报');

            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:412,limit:6},function(e){
                that.tongZhiList=e.data.rows;
            },'本页 - 列表 - 新闻资讯');


            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:426,limit:4},function(e){
                that.List2=e.data.rows;
            },'本页 - 列表 - 应急救护');


            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:453,limit:3},function(e){
                that.List3=e.data.rows;
            },'本页 - 列表 - 志愿者风采');

            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:441,limit:6},function(e){
                that.List4=e.data.rows;
            },'本页 - 列表 - 首页专题专栏图片');

            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:438,limit:10},function(e){
                that.List5=e.data.rows;
                // if(that.List5.length>0)
                // that.List5.forEach((v,k)=>{
                //     that.List5[k].cxjson=JSON.parse(that.List5[k].cxjson);
                // })
            },'本页 - 列表 - 首页捐赠公开');

            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:417,limit:10},function(e){
                layer.closeAll();
                that.List6=e.data.rows;
            },'本页 - 列表 - 友情链接');

            PostUrl('/api.Home/getlist', {table:'cxnews',cxsortid:436,limit:4},function(e){
                layer.closeAll();
                that.List7=e.data.rows;
            },'本页 - 列表 - 通知公告');



            // var mySwiper2 = new Swiper ('.ad2', {
            //     direction: 'horizontal', // 垂直切换选项
            //     autoplay: {
            //         delay: 4000,
            //         stopOnLastSlide: false,
            //         disableOnInteraction: true,
            //     },
            //     loop: true, // 循环模式选项
            //
            //     // 如果需要分页器
            //     pagination: {
            //         el: '.swiper-pagination',
            //     },
            //
            //     // 如果需要前进后退按钮
            //     // navigation: {
            //     //     nextEl: '.swiper-button-next',
            //     //     prevEl: '.swiper-button-prev',
            //     // },
            //
            //     // 如果需要滚动条
            //     scrollbar: {
            //         el: '.swiper-scrollbar',
            //     },
            // })
        },
        logins() {
            if(!that.userInfo.cxusername){alert('姓名不能为空');return false;}
            if(!that.userInfo.cxpassword){alert('密码不能为空');return false;}


            PostUrl('/api.Login/', that.userInfo, function (e) {
                alert(e.mess);
                if(e.codes==200){
                    localStorage.token=e.data.token;
                    localStorage.level=e.data.level;
                    localStorage.id=e.data.id;
                    localStorage.name=e.data.name;
                    localStorage.img=e.data.img;
                    localStorage.passed=e.data.passed;
                    localStorage.cxExpTime=Now();
                    setTimeout(()=>{
                        location.href='index.html';
                    },1000)
                }else{
                }
            },'登陆')

        },
        loginOut(){
            localStorage.clear();
            alert('退出登陆成功');
            location.href='index.html';
        },
        reset(){
            that.userInfo.cxusername='';
            that.userInfo.cxpassword='';
            that.userInfo.cxpassword1='';
            that.userInfo.cxpassword2='';
            that.userInfo.cxfullname='';
        },
        regs(){
            if(!that.userInfo.cxusername){alert('姓名不能为空');return false;}
            if(!that.userInfo.cxpassword1){alert('密码不能为空');return false;}
            if(!that.userInfo.cxpassword2){alert('再次输入密码不能为空');return false;}


            if(that.userInfo.cxpassword1!=that.userInfo.cxpassword2){
                alert('两次密码不一致，请检查');return false;
            }


            if(!that.userInfo.cxfullname){alert('姓名不能为空');return false;}

            var pwdRegex = new RegExp('(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{8,30}');

            if (!pwdRegex.test(that.userInfo.cxpassword1)) {
                alert("您的密码复杂度太低（密码中必须包含字母、数字、特殊字符），请及时修改密码！");return false;
            }

            PostUrl('/api.Login/reg', that.userInfo, function (e) {
                alert(e.mess);
                if(e.codes==200){
                    localStorage.token=e.data.token;
                    localStorage.level=e.data.level;
                    localStorage.id=e.data.id;
                    localStorage.name=e.data.name;
                    localStorage.passed=e.data.passed;
                    setTimeout(()=>{
                        location.href='index.html';
                    },1000)
                }else{
                }
            },'注册')

        },

    }
})

//
// new Vue({el: ".public-header",});
// new Vue({el: ".public-footer",});