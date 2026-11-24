Vue.component('public-footer', {
    data () {
        return {
            footData:333,
            proSort:[],
            ncSort:[],
        }
    },
    mounted () {
        this.getFoot();
        layui.use(['util'], function() {
            var util = layui.util;
            //固定块
            util.fixbar({
            });
        });
    },
    methods: {
        getFoot(){
            let this_=this;
            //console.log(that.footPic);
            // PostUrl('/api/v2.Home/getProSortSun',{cxsortid:289,noSelf:1},function (e) {
            //     this_.proSort=e.data;
            //     console.log('页脚产品列表拉取完成',this_.proSort);
            // })


        },

    },
    template: `
    <div>


        
    </div>

    `
})
