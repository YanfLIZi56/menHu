
const _charStr = 'abacdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789';

/**
 *
 * @return 字符串处理模块
 */
function stringFun(){
    return {
        /**
         *
         * @return 转为数组
         * @param strings 要传入的字符串
         * @param fenGeFu 分隔符
         */
        goArray:function(strings,fenGeFu=','){

            if(strings){
                if(strings.indexOf(fenGeFu)>-1){
                    strings=strings.split(fenGeFu);
                }else{
                    strings=[strings];
                }
            }else{
                strings=[];
            }
            return strings;
        },
        goDay:function (strings) {
            if(strings){
                strings=strings.replace('+','');
                strings=strings.replace('-0','今');
            }
            return strings+'天';

        },
        /**c
         * @return 跟进日期样式
         * @param strings 要传递的字符
         */
        showGenJinTimeStyle:function(strings){
            if(strings=='今'){
                return '<span style="color:#36EC4A">' + strings + '</span>';
            }else{
                if(strings>0){
                    return strings;
                }else{
                    return '<span style="color:#f00">' + strings + '</span>';

                }
            }
        },
        /**c
         * @return json显示格式化
         * @param json 要传递的字符
         */
        formatJson:function(json, pretify=true) {
            if (typeof json !== 'string') {
                if(pretify){
                    json = JSON.stringify(json, undefined, 4);
                }else{
                    json = JSON.stringify(json);
                }
            }
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
                function (match) {
                    let cls = "<span>";
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = "<span class='key'>";
                        } else {
                            cls = "<span class='string'>";
                        }
                    } else if (/true|false/.test(match)) {
                        cls = "<span class='boolean'>";
                    } else if (/null/.test(match)) {
                        cls = "<span class='null'>";
                    }
                    return cls + match + "</span>";
                }
            );
        },

        /**c
         * @return 获取网址 (curWwwPath 完整网址，localhostPath 域名，pathName 主机后面的路径)
         * @param curWwwPath 完整网址
         * @param localhostPath 域名
         * @param pathName 主机后面的路径
         */
        getRootPath:function() {
            //获取当前网址，如： http://localhost:8083/uimcardprj/share/meun.jsp
            var curWwwPath = window.document.location.href;
            //获取主机地址之后的目录，如： /uimcardprj/share/meun.jsp
            var pathName = window.document.location.pathname;
            var pos = curWwwPath.indexOf(pathName);
            //获取主机地址，如： http://localhost:8083
            var localhostPath = curWwwPath.substring(0, pos);
            //获取带"/"的项目名，如：/uimcardprj
            var projectName = pathName.substring(0, pathName.substr(1).indexOf('/') + 1);
            return  {
                curWwwPath:curWwwPath,
                pathName:pathName,
                localhostPath:localhostPath,
                projectName:projectName,
                urlArr:pathName.split('/'),
                lastFile:pathName.split('/')[pathName.split('/').length-1],
                allLastFile:curWwwPath.split('/')[curWwwPath.split('/').length-1],
            };
        }

    }
}


/**
 *
 * @param obj
 * @return {boolean} 判断是否数字
 */
function isNumber(obj) {
    return obj === +obj
}


/**
 *
 * @param ids 类ID
 * @param sortArr sort集合数组
 * @returns 根据SORTID输出类名
 */
function sortId_to_sortName(ids,sortArr){
    let returnD=''
    if(ids>0){
        //console.log(ids,sortArr);
        for(var i=0;i<sortArr.length;i++){
            if(sortArr[i].cxid==ids){
                returnD= sortArr[i];
                break;
            }
        }
    }
    return returnD;
}



/**
 * @return 获取最末级页面地址URL+PARAM
 */
function urlHtmls(){
    return location.href.split('/').slice(-1)[0];
}



/**
 * @return 弹出图片
 * @param picUrl 图片路径
 * @param picTitle 图片标题
 */
function openImg(picUrl,picTitle='查看图片',w){
    //
    // layer.open({
    //     type: 1,
    //     shadeClose:true,
    //     area: [w?w:'90%','90%'],
    //     title:picTitle,
    //     content: '<img src="'+picUrl+'" style="max-width:100%;">' //这里content是一个普通的String
    // });

    let jsons={
        "title": "", //相册标题
        "id": 1, //相册id
        "start": 0, //初始显示的图片序号，默认0
        "data": [   //相册包含的图片，数组格式
            {
                "alt": picTitle,
                "pid": 666, //图片id
                "src": picUrl, //原图地址
                "thumb": "" //缩略图地址
            }
        ]
    };
    layer.photos({
        photos: jsons
        //,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });

}


/**
 * @return 弹出框架
 * @param src SRC路径
 */
function openSrc(src,w){

    layer.open({
        type: 1
        ,title: false //不显示标题栏
        ,closeBtn: true
        ,area: [w?w:'90%','90%']
        ,shade: 0.8
        ,id: 'LAY_layuipro1' //设定一个id，防止重复弹出
        ,moveType: 1 //拖拽模式，0或者1
        ,content: '<iframe id="mapPage" width="100%" height="100%" frameborder=0    src="'+src+'"></iframe>'
        , success: function(layero, index){

        }
    });
}

/**
 *
 * @param nS
 * @return 将时间戳转为本地时间
 */
function getLocalTime(nS){
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
}

/**
 * @return 是否到期,计算A增加多少分钟后是否到期
 * @param a 时间 例:'2001-2-2'
 * @param expTimeAdd 增加的分钟数,
 */
function timeDaoQi(a,expTimeAdd=0){
    var date1 = a?a:'2001-2-2';


    var timestamp1 = moment(a).add(expTimeAdd, 'minute').format('X');

    var timestamp2 = moment().format('X');

    console.log('local到期时间戳:',timestamp1,moment.unix(timestamp1).format());
    console.log('-----现在时间戳:',timestamp2,moment.unix(timestamp2).format());
    if (timestamp1 < timestamp2){
        console.log('到期了')
        return true;
    }else{
        console.log('未到期')
        return false;
    }
}

function uploadFun(inputId,inputFile,types,fileExt,maxFile,callback){
    //图片上传
    console.log(inputId+':'+inputFile);
    layui.use('upload', function() {
        var upload = layui.upload;
        upload.render({
            elem: inputId //绑定元素
            , url: '/api/v2.Uploads?file='+inputFile //上传接口
            , accept: types
            , exts: fileExt
            , size: maxFile //最大允许上传的文件大小    50MB


            , before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                console.log(obj)
                layer.load(1, {shade: 0.5}); //上传loading
            }
            , done: function (res) {
                //上传完毕回调
                inputFile='';
                layer.closeAll();
                layer.msg(res.mess);
                console.log(JSON.stringify(res));
                callback(res.data.filepath);
                return false;
            }
            , error: function (es) {
                console.log(es);
                //请求异常回调
                layer.msg(JSON.stringify(es));
                return false;
            }
        });
    });
}


//上传文件
function uploadFilePublic(name,v) {
    layer.load();
    // let oldLen = this.filLen;
    let sql='app.file = app.$refs.'+name+'.files[0]';
    eval(sql);
    let len = app.file.length;
    let size = Math.floor(app.file.size / 1024);
    if (size > 20 * 1024 * 1024) {
        alert("请选择20M以内的图片！");
        return false;
    }

    let formData= new FormData();
    formData.append('file',app.file);
    let urls="/api/v2.Uploads";
    PostFileUrl(urls,formData,function(e) {
        if(e.code==200){
            let sql='app.'+v+' = e.data.filepath';
            eval(sql);
            console.log('上传图片完成 ',sql);
            layer.msg('上传完成');
            setTimeout(function () {
                layer.closeAll();
            },1000);


        }else{
            alert(e.mess);
        }
        //formData=new FormData();
    });

}

/**
 * @return 时间格式化
 * @param times 原时间
 * @param formats 要格式化的格式YYYY/MM/DD
 */
function timeToFormat(times,formats){
    //依赖：    <script src="https://cdn.bootcdn.net/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    if(formats==''){formats='YYYY/MM/DD';}
    if(times){
        return moment(times).format(formats);
    }else{
        return '';
    }
}

/**
 *
 * @return 根据cxpids里的逗号多少返回前置占位符
 * @param e cxpids
 */
function sortl(e){
    let a=e;
    let s=0;
    if (a){
        s=a.match(/,/g) ? a.match(/,/g).length : 0;
    }
    let ss='';
    //if(s==1){ss='   └────';}
    if(s==2){ss='         └────';}
    if(s==3){ss='         └────────';}
    return ss;

}

/**
 * @return OBJECT里获取对应标题里的值
 * @param mdata 完整字符串
 * @param n 要取值的对象
 */
function  getAtt(mdata,n){
    if(mdata!=undefined && mdata!=''){
        let s=JSON.parse(mdata);
        for(var i=0;i<s.length;i++){
            if(s[i].title==n){
                return s[i].content;
                break;
            }
        }
    }else{
        return '';
    }
}

/**
 * @return js提取数组中的某个属性组成一个新数组
 */
function arrToArr(arr,field){
    return arr.map(item=>{
        return item[field];
    })
}

function CheckToken(callback){
    //localStorage.href=location.href;

    PostUrl('/api/v2.Login/checklogin',{token:localStorage.token},function(e){
        if(e.codes==200){
            // localStorage.id=e.data.id;
            // localStorage.name=e.data.name;
            // localStorage.sfz=e.data.sfz;
            // localStorage.img=e.data.img;
            //console.log('TOKEN验证通过');
            callback();
        }else{
            console.log('验证不通过');
            localStorage.clear();
            //location.href = 'https://yewu.bcwhkj.cn/api/v2.Wechat/PublicGoWeChat?urls=' + location.href;
            location.href='index.html';
            return false;
        }

    },'TOKEN校验')
}

//
// function CheckToken(callback){
//     //localStorage.href=location.href;
//     if(getQV('openid')){
//
//         console.log('有openid')
//
//         let postD={
//             openid:getQV('openid'),
//             nickname:getQV('nickname'),
//             headimgurl:getQV('headimgurl'),
//         }
//         PostUrl('/api/v2.Login/register',postD,function(e){
//             if(e.codes==500){
//                 location.href='close.html';
//                 return false;
//             }
//             //layer.msg('登陆成功');
//             localStorage.id=e.data.cxid;
//             localStorage.username=e.data.cxusername;
//             localStorage.token=e.data.cxtoken;
//             localStorage.level=e.data.cxuserlevel;
//             localStorage.img=e.data.cximg0;
//             localStorage.name=e.data.cxfullname;
//             localStorage.lastlogintime=e.data.cxlastlogintime;
//             localStorage.sortid=e.data.cxsortid;
//             location.href='/';
//             callback();
//         });
//
//     }else{
//         PostUrl('/api/v2.Login/checklogin',{token:localStorage.token},function(e){
//             if(e.codes==200){
//                 localStorage.sortid=e.data.cxsortid;
//                 localStorage.name=e.data.cxfullname;
//                 console.log('TOKEN验证通过');
//                 callback();
//             }else if(e.codes==400){
//                 console.log(e.mess);
//                 layer.msg(e.mess);
//                 setTimeout(function () {
//                     location.href='/api/v2.Wechat/PublicGoWeChat?urls='+location.href;
//                 },2000)
//                 return false;
//             }else{
//                 console.log('验证不通过');
//                 localStorage.clear();
//                 //location.href = 'https://yewu.bcwhkj.cn/api/v2.Wechat/PublicGoWeChat?urls=' + location.href;
//                 location.href='/api/v2.Wechat/PublicGoWeChat?urls='+location.href;
//                 return false;
//             }
//
//         })
//     }
// }



function CheckTokenPublic(callback){
    if(getQV('ok')){
        let  postD={
            openid:getQV('openid'),
            nickname:getQV('nickname'),
            headimgurl:getQV('headimgurl'),
        };
        PostUrl('/api/v2.Wechat/regUser',postD,function(e){
            console.log('登陆成功');
            localStorage.id=e.data.cxid;
            localStorage.username=e.data.cxusername;
            localStorage.token=e.data.cxtoken;
            localStorage.level=e.data.cxuserlevel;
            localStorage.img=e.data.cximg0;
            localStorage.name=e.data.cxfullname;
            localStorage.userid=e.data.userid;
            localStorage.lastlogintime=e.data.cxlastlogintime;
            callback();
        });
    }else {

        PostUrl('/api/v2.Login/checklogin', {token: localStorage.token}, function (e) {
            if (e.codes == 200) {
                console.log('TOKEN验证通过');
                callback();
            } else {
                localStorage.clear();
                console.log('TOKEN不匹配，请重新登陆 ');
                location.href = 'https://yewu.bcwhkj.cn/api/v2.Wechat/PublicGoWeChat?urls=' + location.href;
                return false;
            }

        });
    }

}

/**
 * @return kimi加密
 * @param strs 要加密的字符串
 */
function kimi(strs){
    if(strs){
        var encrypt = new JSEncrypt();
        //公钥
        encrypt.setPublicKey("MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2wBHOsFxwrvCWqwofc4xpXInIM6eNBKQMTHPhd4J0Fr3HyAyiz70OKdgEGK4sszC06x+izUqYunQByKDCCSbGjC1KM2aocbyDcfbK3e6t//YQhe4aygXj+6as2HLbqKmzniR5bqGaVAM9RHgsuWchIBgNFq4ke/0hdsiW1BtO3QIDAQAB");
        return encrypt.encrypt(strs);
    }else{
        return '';
    }
}

/**
 * @return 清除HTML
 */
function filterHTMLTag(msgHtml){
    if(msgHtml){
        let msg = msgHtml.replace(/<\/?[^>]*>/g, ''); //去除HTML Tag
        msg = msg.replace(/[|]*\n/, '') //去除行尾空格
        msg = msg.replace(/&npsp;/ig, ''); //去掉npsp
        msg = msg.replace(/&nbsp;/ig, ''); //去掉npsp
        return msg;
    }
}


function uploadFun(inputId,inputFile,types,fileExt,maxFile,callback){
    //图片上传
    console.log(inputId+':'+inputFile);
    layui.use('upload', function() {
        var upload = layui.upload;
        upload.render({
            elem: inputId //绑定元素
            , url: '/api/v2.Uploads?filePath='+inputFile //上传接口
            , accept: types
            , exts: fileExt
            , size: maxFile //最大允许上传的文件大小    50MB
            , before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                layer.load(1, {shade: 0.5}); //上传loading
            }
            , done: function (res) {
                //上传完毕回调
                layer.closeAll();
                layer.msg(res.mess);
                console.log(JSON.stringify(res));
                callback(res.data.filepath);
                return false;
            }
            , error: function (es) {
                //请求异常回调
                layer.msg(JSON.stringify(es));
                console.log(es);
                return false;
            }
        });
    });
}

/**
 *
 * @return 输出变量类型
 */
function getType(param) {
    var type = Object.prototype.toString.call(param);
    type = type.split(' ')[1].replace(']','');
    console.log(type);
}

function Now(){
    return moment().get('year')+'-'+
        (moment().get('month')+1)+'-'+
        moment().get('date')+' '+
        moment().get('hour')+':'+
        moment().get('minute')+':'+
        moment().get('second');
}

/**
 *
 * @return 日期格式化
 */
function datatimeFormat(d) {
}

function getQV(variable)//获取get参数
{

    //获取get参数
    var getV;
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == variable){
            getV=decodeURI(pair[1]);
            return decodeURI(pair[1]);
        }
    }
    return(false);
}
function getPage(variable)//获取get参数
{

    //获取get参数
    var getV;
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == variable){
            getV=decodeURI(pair[1]);
            return decodeURI(pair[1]);
        }
    }
    if(!getV){
        if(location.hash.replace('#!page=', '')){
            getV=location.hash.replace('#!page=', '');
            return getV;
        }
    }
    return(false);
}

/**
 * @return 随机生成字符串
 * @param len 指定生成字符串长度
 */
function getRandomString(len) {

    let min = 0, max = _charStr.length - 1, _str = '';
    //判断是否指定长度，否则默认长度为15
    len = len || 15;
    //循环生成字符串
    for (var i = 0, index; i < len; i++) {
        index = RandomIndex(min, max, i);
        _str += _charStr[index];
    }
    return _str;

}

/**
 * @return 随机生成字符串
 * @param len 指定生成字符串长度
 */
function getRandomString(len){
    let min = 0, max = _charStr.length-1, _str = '';
    //判断是否指定长度，否则默认长度为15
    len = len || 15;
    //循环生成字符串
    for(var i = 0, index; i < len; i++){
        index = RandomIndex(min, max, i);
        _str += _charStr[index];
    }
    return _str;
}


/**
 * @return 随机生成索引
 * @param min 最小值
 * @param max 最大值
 * @param i 当前获取位置
 */
function RandomIndex(min, max, i){
    let index = Math.floor(Math.random()*(max-min+1)+min),
        numStart = _charStr.length - 10;
    //如果字符串第一位是数字，则递归重新获取
    if(i==0&&index>=numStart){
        index = RandomIndex(min, max, i);
    }
    //返回最终索引值
    return index;
}

/**
 * @return 随机数
 * @param min 最小值
 * @param max 最大值
 */
function sjs(min, max){
    //生成随机数
    return Math.floor(Math.random() * (max - min) ) + min;
}
function DateDiff(times){
    var new_date = new Date(moment().format('YYYY/MM/DD')); //新建一个日期对象，默认现在的时间
    var old_date = new Date(moment(times).format('YYYY/MM/DD')); //设置过去的一个时间点，"yyyy-MM-dd HH:mm:ss"格式化日期

    var difftime;
    if(new_date>old_date){
        difftime = (new_date-old_date)/1000; //计算时间差,并把毫秒转换成秒
    }else{
        difftime = (old_date-new_date)/1000; //计算时间差,并把毫秒转换成秒

    }

    var days = parseInt(difftime/86400); // 天  24*60*60*1000
    var hours = parseInt(difftime/3600)-24*days;    // 小时 60*60 总小时数-过去的小时数=现在的小时数
    var minutes = parseInt(difftime%3600/60); // 分钟 -(day*24) 以60秒为一整份 取余 剩下秒数 秒数/60 就是分钟数
    var seconds = parseInt(difftime%60);  // 以60秒为一整份 取余 剩下秒数

    //return (days+"天, "+hours+"小时, "+minutes+"分钟, "+seconds+"秒");
    s='';
    if(new_date>old_date){s='-';days++;}
    s += days;
    if(days=='0'){s='今';}
    return s;
}

function DateAdd(interval,number,date){
    // date 可以是时间对象也可以是字符串，
    // 如果是后者，形式必须为: yyyy-mm-dd hh:mm:ss 其中分隔符不定。"2006年12月29日 16点01分23秒" 也是合法的
    //这个函数已经尽量模仿 vbscript 中的 dateadd 函数
    //三个参数，第一个是变化的时间间隔，可以是年、月、日、周、时、分、秒、毫秒(扩展)，
    //第三个参数可以是时间对象，也可以是字符串（形式必须为：2006-12-29 14:32:57 或 2006年12月29日 14点32分57秒），
    //函数的返回值是新的时间对象。
    number = parseInt(number);
    if (typeof(date)=="string"){
        date = date.split(/\D/);
        --date[1];
        eval("var date = new Date("+date.join(",")+")");
    }
    if (typeof(date)=="object"){
        var date = date
    }
    switch(interval){
        case "y": date.setFullYear(date.getFullYear()+number); break;
        case "m": date.setMonth(date.getMonth()+number); break;
        case "d": date.setDate(date.getDate()+number); break;
        case "w": date.setDate(date.getDate()+7*number); break;
        case "h": date.setHours(date.getHour()+number); break;
        case "n": date.setMinutes(date.getMinutes()+number); break;
        case "s": date.setSeconds(date.getSeconds()+number); break;
        case "l": date.setMilliseconds(date.getMilliseconds()+number); break;
    }
    return date;
}

function jAjax(urls, postData,callback) {

    $.ajax({
        url: 'https://apis.map.qq.com/ws/location/v1/ip?output=jsonp&key=MOKBZ-EZE6D-ITC4K-HVE23-ZF7SS-YLF3I',
        type: 'GET',
        dataType:'jsonp',
        success: function (data) {
            console.log('return ajax');
            callback(data);
        }
    });
}
/**
 * @return 上传文件
 */
function PostFileUrl(urls, postData,callback) {
    axios.post(urls, postData)
        .then(function (res) {
            let data = res.data;
            console.log('---------------------');
            console.log('上传成功，提交路径:'+urls,'提交值:',postData,'返回内容：',data);
            callback(data);

        })
        .catch(function (error) {
            console.log('---------------------');
            console.log('上传报错，提交路径:'+urls,'提交值:',postData,'返回内容：',error);
            callback(error);

        });
}

function PostUrl(urls, postData,callback,title='') {
    // if(postData.token){
    //     postData.token=kimi(postData.token);
    // }
    // var tsName='计时器'+sjs(111111,999999);

    //urls='http://hsz.bcwhkj.cn/'+urls;
    let start = new Date().getTime()
// call your function
    axios.post(urls, postData)
        .then(function (res) {
            let data = res.data;
            let end = new Date().getTime();
            let useTime =end - start;
            if(data.mess=='内部错误'){
                console.log('%c'+data.data.main,'background-color:red;padding:3px;color:#fff','PostReturn:',data,'PostUrl:'+urls,'PostData',postData,'计时:',useTime,' ms');
            }else{
                console.log('%cPostReturn - '+ title,'background-color:green;padding:3px;color:#fff',data,'PostUrl:'+urls,'PostData',postData,'计时:',useTime,' ms');
                // console.timeEnd(tsName)
            }
            callback(data);


        })
        .catch(function (error) {

            let allUrls=stringFun().getRootPath().localhostPath+urls+ '?'+window.Qs.stringify(postData);
            console.log('%cErrorReturn - '+ title,'background-color:#D56422;padding:3px;color:#fff',error,'PostUrl:'+urls,'PostData',postData,'QS转码后的地址',allUrls);
            callback(error);
        });
}


function GetUrl(urls, callback,title='') {
    //console.log('GetUrl:'+urls);
    axios.get(urls)
        .then(function (res) {
            let data = res.data;
            console.log('%cPostReturn - '+ title,'background-color:green;padding:3px;color:#fff',data,'PostUrl:'+urls);
            //console.log(data);
            callback(data);

        })
        .catch(function (error) {
            console.log('Get[ '+urls+' ] Return ↓↓↓ ');
            console.log(error);
        });
}

//$.post(urls,
//   {
//        data: postData
//    },
//    function (data, status) {
//       console.log('status:'+status);
//       callback( data);
//   });


function formatN(v,l,is45){
    //要格式化的数字，要保留的小数点后几位，是否要4舍5入
    if(v=='' || v==undefined){return 0; return false;}
    let zeroLen='0';
    for (i = 1; i < l; i++) {
        zeroLen=zeroLen+'0';
    }
    let vs;
    if(is45){
        vs=numeral(v).format('0.'+zeroLen);
        for (i = 1; i < l; i++) {
            if(right(vs,1)=='0'){vs=left(vs,vs.length-1);}
        }
    }else{
        vs=v.toString();
        if(vs.indexOf('.')>0){
            vs=vs.substr(0,vs.indexOf('.'))+'.'+vs.substr(vs.indexOf('.')+1,l);
        }
        vs=parseFloat(vs);
    }
    return vs;


}

function checkPrice(data){
    //限制参数变更为最长小数点后2位
    let thisData = '';
    if(data){
        thisData = data.replace(/[^\d.]/g,"");  //清除“数字”和“.”以外的字符
        thisData = thisData.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
        thisData = thisData.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
        thisData = thisData.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');//只能输入两个小数
        thisData = thisData.replace(/-/g,'');
        thisData = thisData.replace("-","");
        console.log(thisData.substr(0,1));
        if(thisData.substr(0,1)=='.' || thisData=='.'){
            thisData='0' + thisData;
        }
        if(thisData.indexOf(".")< 0 && thisData !=""){
            //以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额
            thisData = parseFloat(thisData);
        }

    }
    return thisData;
}

function bcJiSuan(a,operation,b){
    //高精度数字函数

    if(a==0 && b==0){return 0;}
    //console.info('高精度数字计算:'+a+operation+b);
    let result;

    a = (a*10000).toFixed(2);
    b = (b*10000).toFixed(2);
    a=formatN(a,0,false);
    b=formatN(b,0,false);
    if(operation=='+'){
        result=(a+b)/10000;
    }
    if(operation=='-'){
        result=(a-b)/10000;
    }
    if(operation=='*'){
        result=(a*b)/100000000;
    }
    if(operation=='/'){
        result=(a/b)/100000000;
    }
    console.log((a/10000)+operation+(b/10000)+'计算结果 ：'+result);
    //result=_this.formatN(result,5,false);
    return result;
}


function left(mainStr,lngLen){
    if(mainStr){
        if(lngLen>0){return mainStr.substring(0,lngLen)}
        else{return null;}
    }else{
        return null;
    }
}
function right(mainStr,lngLen){
    if(mainStr){
        //alert(mainStr.length)
        if(mainStr.length-lngLen>=0&&mainStr.length>=0&&mainStr.length-lngLen<=mainStr.length){
            return mainStr.substring(mainStr.length-lngLen,mainStr.length)}
        else{return null}
    }else{
        return null;
    }
}
function mid(mainStr,starnum,endnum){
    if(mainStr.length>=0){
        return mainStr.substr(starnum,endnum)
    }else{return null}
    //mainStr.length
}

//头像处理
function imgVal(e){
    let result;
    if(e){
        if(e.substr(0,4)=='http'){
            result=e;
        }
        if(e.substr(0,8)=='/public/'){
            result=e;
        }
        if(e.substr(0,8)=='uploads/'){
            result='/public/'+e;
        }
    }else{
        result='https://vkceyugu.cdn.bspapp.com/VKCEYUGU-908027cd-0849-4303-ae20-d3bed9830e3a/e007bff3-50f4-452a-81f6-f27c1b6cb439.jpg';

    }
    return result;
}



/**
 * @return 删除(前置变量：count,search 前置函数:getList)
 */
function fenye(){
    app.$nextTick(function () {

        layui.use('laypage', function(){
            var laypage = layui.laypage;

            //执行一个laypage实例
            laypage.render({
                elem: 'page1' //注意，这里的 test1 是 ID，不用加 # 号
                ,count: app.count //数据总数，从服务端得到
                ,limit: app.search.limit
                ,curr: location.hash.replace('#!page=', '') //获取起始页
                ,limits:[10, 20, 30, 40, 50]
                ,layout: ['count', 'prev', 'page', 'next', 'limit']
                //,layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
                ,prev:'<'
                ,next:'>'
                ,hash: 'page' //自定义hash值
                ,jump: function(obj, first){
                    //obj包含了当前分页的所有参数，比如：
                    console.log('当前页',obj.curr); //得到当前页，以便向服务端请求对应页的数据。
                    console.log('每页显示的条数',obj.limit); //得到每页显示的条数
                    app.search.page=obj.curr;
                    app.search.limit=obj.limit;


                    //首次不执行
                    if(!first){
                        //do something

                        app.getList();
                    }
                }
            });
        });
    })
}



/**
 * @return 删除(前置变量：count,search 前置函数:getList)
 */
function fenyeEn(){
    app.$nextTick(function () {

        layui.use('laypage', function(){
            var laypage = layui.laypage;

            //执行一个laypage实例
            laypage.render({
                elem: 'page1' //注意，这里的 test1 是 ID，不用加 # 号
                ,count: app.count //数据总数，从服务端得到
                ,limit: app.search.limit
                ,curr: location.hash.replace('#!page=', '') //获取起始页
                ,limits:[10, 20, 30, 40, 50]
                ,layout: ['prev', 'page', 'next']
                ,prev:'Prev'
                ,next:'Next'
                ,hash: 'page' //自定义hash值
                ,jump: function(obj, first){
                    //obj包含了当前分页的所有参数，比如：
                    console.log('当前页',obj.curr); //得到当前页，以便向服务端请求对应页的数据。
                    console.log('每页显示的条数',obj.limit); //得到每页显示的条数
                    app.search.page=obj.curr;
                    app.search.limit=obj.limit;


                    //首次不执行
                    if(!first){
                        //do something

                        app.getList();
                    }
                }
            });
        });
    })
}