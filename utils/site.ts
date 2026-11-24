/**
 * 站点静态配置：主导航、首页各版块的入口。
 * 这部分在老站是写死在 v.js / header.js 里的，重构后收敛到一处，
 * 后续如需后台可视化配置，可改成 category/category/list 下发。
 */

export interface MenuChild {
  title: string
  link: string
  external?: boolean
}

export interface MenuItem {
  title: string
  link: string
  external?: boolean
  children?: MenuChild[]
}

/** 顶部主导航（顺序、文案与老站一致；链接指向新站路由） */
export const mainMenu: MenuItem[] = [
  { title: '首页', link: '/' },
  {
    title: '机构简介',
    link: '/article/1300',
    children: [
      { title: '机构简介', link: '/article/1300' },
      { title: '领导机构', link: '/article/1458' },
      { title: '内设机构', link: '/article/1455' },
    ],
  },
  {
    title: '新闻资讯',
    link: '/list/412',
    children: [
      { title: '媒体报道', link: '/list/418' },
      { title: '红会动态', link: '/list/419' },
      { title: '交流合作', link: '/list/420' },
      { title: '党建专栏', link: '/list/452' },
      { title: '党史专栏', link: '/list/459' },
    ],
  },
  {
    title: '红会事业',
    link: '/list/411',
    children: [
      { title: '人道救助', link: '/list/424' },
      { title: '救灾救援', link: '/list/425' },
      { title: '应急救护', link: '/list/426' },
      { title: '无偿献血', link: '/list/427' },
      { title: '造血干细胞捐献', link: '/list/428' },
      { title: '遗体和人体器官捐献', link: '/list/429' },
      { title: '志愿服务', link: '/list/453' },
      { title: '红十字青少年', link: '/list/423' },
      { title: '红十字会员', link: '/list/454' },
    ],
  },
  {
    title: '政策法规',
    link: '/list/410',
    children: [{ title: '政策法规', link: '/list/410' }],
  },
  {
    title: '网上办事',
    link: '/list/414',
    children: [
      { title: '我要求助', link: '/article/1327' },
      { title: '我要捐款', link: '/article/1319' },
      {
        title: '加入会员',
        link: 'http://gztyy.gd.icbc.com.cn/rcsmob/',
        external: true,
      },
      {
        title: '成为志愿者',
        link: 'https://st.izyz.org/login/fillMessageForOtherSystem',
        external: true,
      },
    ],
  },
  {
    title: '公告公示',
    link: '/list/415',
    children: [
      { title: '通知公告', link: '/list/436' },
      { title: '预决算公开', link: '/list/437' },
      { title: '捐赠公开', link: '/list/438' },
      { title: '志愿服务队', link: '/list/460' },
      { title: '活动公告', link: '/list/456' },
    ],
  },
  { title: '联系我们', link: '/article/1271' },
]

/** 首页「网上办事」五项：老站为静态数据（不走接口） */
export const serviceEntries = [
  {
    name: '我要加入会员',
    sub: '(红十字会会员)',
    link: 'http://gztyy.gd.icbc.com.cn/rcsmob/',
    icon: 'iconfont icon-shixishengzhaomu',
    external: true,
  },
  {
    name: '成为志愿者',
    sub: '(加入志愿者)',
    link: 'https://st.izyz.org/login/fillMessageForOtherSystem',
    icon: 'iconfont icon-jiarubanji',
    external: true,
  },
  {
    name: '我要捐赠',
    sub: '(捐款捐物)',
    link: '/article/1319',
    icon: 'iconfont icon-airudiantubiaohuizhi-zhuanqu_xianaixin',
  },
  {
    name: '我要求助',
    sub: '(申请救助)',
    link: '/article/1327',
    icon: 'iconfont icon-hear-half',
  },
  {
    name: '我要报名',
    sub: '(救护员培训)',
    link: '/guestbook',
    icon: 'iconfont icon-dingdan2',
  },
]

/** 首页「信息公开」六个入口 */
export const infoPublicEntries = [
  { name: '通知公告', link: '/list/436', icon: 'iconfont icon-xiangmu' },
  { name: '志愿服务队', link: '/list/460', icon: 'iconfont icon-HRrenshirenshiguanli' },
  { name: '预决算公开', link: '/list/437', icon: 'iconfont icon-chanpin1' },
  { name: '领导机构', link: '/article/1458', icon: 'iconfont icon-renyuanbaoming' },
  { name: '内设机构', link: '/article/1455', icon: 'iconfont icon-biangeng-daiquan' },
  { name: '联系方式', link: '/article/1271', icon: 'iconfont icon-gongkai' },
]

/** 首页「红会事业」六个入口 */
export const redCrossEntries = [
  { name: '救援救灾', link: '/list/425' },
  { name: '应急救护', link: '/list/426' },
  { name: '人道救助', link: '/list/424' },
  { name: '无偿献血', link: '/list/427' },
  { name: '造血干细胞捐献', link: '/list/428' },
  { name: '遗体器官捐献', link: '/list/429' },
]

/** 侧栏「联系我们」 */
export const contactInfo = {
  tel: '0754-88295834',
  fax: '0754-88295834',
  zip: '515000',
  address: '汕头市金平区中山路130号协华大厦11A',
}
