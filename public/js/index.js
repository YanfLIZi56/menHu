
//百度统计
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "https://hm.baidu.com/hm.js?c6bf05fb9cfad56aba880e4fc9fc987f";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
})();

var wow = new WOW({
        boxClass: 'wow',
        animateClass: 'animated',
        offset: 0,
        mobile: true,
        live: true
    });
    new WOW().init();

    $(document).ready(function() {
        setTimeout(function () {

            // $(".middelNav ul a:nth-child(1)>li").click(function () {
            //                 console.log('触发子菜单');
            //                 $(".childNav").addClass('childMenuShow');
            //             });
            // $(".middelNav ul a:nth-child(1)>li,.childNav").mouseenter(function () {
            //     console.log('触发子菜单');
            //     $(".childNav").addClass('childMenuShow');
            // }).stop(true,true);
            // $(".middelNav ul a:nth-child(1),.childNav").mouseleave(function () {
            //     //setTimeout(function () {
            //         $(".childNav").removeClass('childMenuShow');
            //     //},1000)
            // }).stop(true,true);
        },500)
    });