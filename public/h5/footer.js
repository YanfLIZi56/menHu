Vue.component('public-footer', {
    props:['menu'],
    data () {
        return {
            footerInfo:'',
        }
    },
    mounted () {
        this.getFoot();



    },
    methods: {
        getFoot(){
            let that=this;

            PostUrl('/api.Home/info', {table:'cxnews',cxid:1328},function(e){
                layer.closeAll();
                that.footerInfo=e.data;
            },'底部版权信息');

            
            layui.use(['util'], function() {
                var util = layui.util;
                //固定块
                util.fixbar({
                });
            });

        },

    },
    template: `
        
		
<footer >
    
    
    
    
    
    
    
    
    

            <div class="foots mt">
                <div class="bottomNav w1200" style="display: none">
                    <ul>
                    <li  v-for="item,key in menu">
                        <a :href="item.link">{{item.title}}</a>
                    </li></ul>
                    <div class="clearfix"></div>
                </div>
                <div class="banQuan w1200" v-html="footerInfo.cxcontentcn"></div>
            </div>
            
            <div>
        <a id="gotoTop" href="#" onclick="gotoTop();return false;" class="totop" title="回到顶部" ></a>
    </div>
</footer>
        
    `
})