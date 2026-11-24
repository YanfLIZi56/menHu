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

        },

    },
    template: `
        
		
<footer >
    
            <div class="foots mt">
                <div class="banQuan w100" v-html="footerInfo.cxcontentWapcn"></div>
            </div>
            
            <div>
        <a id="gotoTop" href="#" onclick="gotoTop();return false;" class="totop" title="回到顶部" ></a>
    </div>
</footer>
        
    `
})