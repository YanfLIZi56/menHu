


function adminCheckToken(callback){
    //localStorage.href=location.href;
    if(localStorage.cxAdminExpTime){
        if(timeDaoQi(localStorage.cxAdminExpTime,30)){
            //最后登陆的时间
            localStorage.clear();
            alert('登陆超时,请重新登陆 ');
            location.href='index.html';
        }else{

        }
    }
    if(localStorage.adminToken){
        PostUrl('/bc.Login/checkAdminLogin',{token:localStorage.adminToken},function(e){
            console.log(e.mess);
            if(e.codes==200){

                //身份验证通过，表示是其中一个管理员
                //if(e.data.cxadmin!='hsz'){
                    //let cxpids=e.data.cxpids;
                    //if(!checkQuanXian(cxpids)){console.log('权限不足,无法管理此栏目');return false;}
                    let cxMenu=e.data.cxMenu;
                    if(checkQuanXian(cxMenu)){
                        console.log('有此页面的管理权限！')
                    }else{
                        //alert('权限不足,无法管理此菜单');return false;
                    }
                // }else{
                //     console.log('当前admin登陆状态!')
                // }
                callback();
            }else{
                alert(e.mess);
                localStorage.clear();
                location.href='index.html';
                return false;
            }

        },'管理员身份验证')

    }else{
        localStorage.clear();
        location.href='index.html';
        return false;
    }
}



function checkQuanXian(userUrls){
    let curUrls=stringFun().getRootPath().lastFile;
    console.log('用户可管理路径',userUrls);
    console.log('当前路径',curUrls);
    console.log('路径判断：',userUrls.indexOf(curUrls));
    if(userUrls.indexOf(curUrls)>0){
        return true;
    }else{
        return false;
    }

}


//上传文件
function uploadFile(name,v) {
    layer.load();
    // let oldLen = this.filLen;
    let sql='app.file = app.$refs.'+name+'.files[0]';
    console.log(sql);
    eval(sql);
    let len = app.file.length;
    let size = Math.floor(app.file.size / 1024);
    if (size > 20 * 1024 * 1024) {
        alert("请选择20M以内的图片！");
        return false;
    }

    if(size==0){return false;}

    const fd = new FormData();
    fd.append('file', app.file);
    PostFileUrl("/bc.Uploads",fd,function(e) {
        if(e.code==0){
            let sql='app.'+v+' = e.filepath';
            eval(sql);
            console.log('上传图片完成 ');
            layer.msg('上传完成');
            setTimeout(function () {
                layer.closeAll();
            },1000);


        }else{
            console.log(e);
            alert(e.msg);
            layer.closeAll();
        }
        //formData=new FormData();
    });

    // // 提取文件
    // const file = app.file;
    //
    //
    ////Compressor  需引入https://api.bcwhkj.cn/Js/compressorjs-main/dist/compressor.min.js

    // new Compressor(file, {
    //     quality: 0.6, // 压缩质量 可以查看文档获取明细
    //     maxWidth:1500,
    //     maxHeight:5000,
    //     success(result) { //压缩成功后
    //         // 成功后上传
    //         const fd = new FormData();
    //         fd.append('file', result);
    //         PostFileUrl("/bc.Uploads",fd,function(e) {
    //             if(e.code==0){
    //                 let sql='app.'+v+' = e.filepath';
    //                 eval(sql);
    //                 console.log('上传图片完成 ');
    //                 layer.msg('上传完成');
    //                 setTimeout(function () {
    //                     layer.closeAll();
    //                 },1000);
    //
    //
    //             }else{
    //                 console.log(e);
    //                 alert(e.msg);
    //             }
    //             //formData=new FormData();
    //         });
    //     },
    //     error(err) {
    //         layer.closeAll(); // 失败
    //         layer.alert(`图片文件压缩失败`, { title: '发生错误，请稍后重试！' });
    //     }
    // });


    // let formData= new FormData();
    // formData.append('file',app.file);


}