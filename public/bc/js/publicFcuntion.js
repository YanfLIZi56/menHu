/**
 * @return 获取全部人员列表  (前置变量：userList)
 */
function getAllUser(){
    PostUrl('/bc.Wanneng/getList', {table:'cxuser',limit:999999,adminToken:localStorage.adminToken}, function (e) {
        app.userList = e.data.rows;
    },'公共：拉取全部会员列表完成');
}
/**
 * @return 获取全部人员等级列表  (前置变量：userList)
 */
function getAllUserLevel(){
    PostUrl('/bc.Wanneng/getList', {table:'cxuserlevel',limit:9999,adminToken:localStorage.adminToken}, function (e) {
        app.userlevelList = e.data.rows;
    },'公共：拉取全部人员类型成功');
}


/**
 * @return 获取全部产品分类 (前置变量：sortList)
 */
function getAllSort(){
    PostUrl('/bc.Wanneng/getList',{table:'cxnews_sort',limit:99999,order:'cxpids asc',adminToken:localStorage.adminToken},function(e){
        app.sortList=e.data.rows;
    },'公共数据：拉取全部分类数据成功');
}


function changeS(key){
    console.log(key);
    // console.log(_this.ShowMore[key]);
    // _this.$set(_this.ShowMore,key,!_this.ShowMore[key]);
    // console.log(_this.ShowMore[key]);
}


function ShowAll(){
    // _this.ShowMore.forEach(function (data,key) {
    //     console.log(data);
    //     _this.ShowMore[key]=true;
    // })
}

/**
 * @return 全选(前置变量：checkAdd,aid)
 * @param Lists 变量（列表值,用来循环的,里面必须有cxid）
 */
function SelectAll(Lists){
    console.log(app.checkAll,app.aid,Lists);
    app.checkAll=!app.checkAll;
    m=[];
    if(app.checkAll==true){
        Lists.forEach(function (v,k) {
            m.push(v.cxid);
        })
    }
    app.aid=m;
}

/**
 * @return 复制(前置函数：getList)
 * @param bm 表名
 * @param cid 变量（要复制的ID）
 */
function copy(bm,cid){
    PostUrl('/bc.Home/copy', {bm:bm,cxid:cid,adminToken:localStorage.adminToken},function(e){
        layer.msg('复制成功');
        console.log('复制成功');
        app.getList();
    });
}
/**
 * @return 删除(前置变量：checkAll,aid)
 * @param bm 表名
 */
function del(bm){
    layer.confirm('确定删除?', function(index){
        layer.load();
        PostUrl('/bc.Home/del', {bm:bm,cxid:app.aid,adminToken:localStorage.adminToken},function(e){
            layer.closeAll();
            console.log('删除成功')
            app.checkAll=false;
            //app.getList();
            history.go(0);
        });
        layer.close(index);
    });
}


/**
 * @return 将勾选的项的的值进行更新(前置变量：aid ，前置函数 getList)
 * @param bm 表名
 * @param fieldName 要更新的字段名
 * @param value 要更新的值
 */
function upAttr(bm,fieldName,value){
    layer.load();
    let postD={
        bm:bm,
        fieldName:fieldName,
        value:value,
        cxid:app.aid,
        adminToken:localStorage.adminToken
    };
    PostUrl('/api.Home/upAttr', postD,function(e){
        layer.closeAll();
        app.getList();
        app.aid=[];
    });
}


/**
 * @return 将勾选的项的更新时间更新为现在(前置变量：aid ，前置函数 getList)
 * @param bm 表名
 */
function upTime(bm){
    // layer.confirm('确定更新勾选的数据最后更新时间?', function(index){
    //  layer.load();
    PostUrl('/bc.Home/upTime', {bm:bm,cxid:app.aid,adminToken:localStorage.adminToken},function(e){
        layer.closeAll();
        //console.log('删除成功')
        app.getList();
        app.aid=[];
    });
    //  layer.close(index);
    // });
}
/**
 * @return 将勾选的项的所属分类ID进行变更成新的分类ID(前置变量：aid ，前置函数 getList)
 * @param bm 表名
 */
function changeSort(bm,sid){
    layer.confirm('确定变更勾选的信息的分类吗?', function(index){
        layer.load();
        PostUrl('/bc.Home/changeSort', {bm:bm,cxid:app.aid,cxsortid:sid,adminToken:localStorage.adminToken},function(e){
            layer.closeAll();
            console.log('变更成功')
            app.getList();
            app.aid=[];
            sid='';
        });
        layer.close(index);
    });
}
/**
 * @return 将勾选的项的状态变更为新的状态(前置变量：aid ，前置函数 getList)
 * @param bm 表名
 */
function changeZt(bm,zt){
    layer.confirm('确定变更勾选的信息的状态吗?', function(index){
        layer.load();
        PostUrl('/bc.Home/upZt', {bm:bm,cxid:app.aid,cxpassed:zt,adminToken:localStorage.adminToken},function(e){
            layer.closeAll();
            console.log('变更成功')
            app.getList();
        });
        layer.close(index);
    });
}

