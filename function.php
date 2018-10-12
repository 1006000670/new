<?php
include IA_ROOT . "/addons/yingshi_byy/QL/QueryList.class.php";
use QL\QueryList;
load()->func("communication");

function setting1() {
	global $_W, $_GPC;
	$setting = pdo_get('uni_account_modules',array('uniacid'=>$_W['uniacid'],'module'=>'yingshi_byy'));
	$setting = iunserializer($setting['settings']);
	return $setting; 
}

function caiji_list($keyword)
{
    $url = "https://video.so.com/v?ie=utf-8&src=360sou_home&q=" . $keyword;
    $data = QueryList::Query($url, array("link" => array(".b-mainpic_new a", "href", '', function ($link) {
        $link = explode("http://www.360kan.com", $link);
        return $link["1"];
    }), "title" => array(".title a", "text", '', function ($title) { if ($title) { return $title; } }), "p1" => array("ul:first", "text"), "p2" => array("ul:eq(1)", "text"), "p3" => array("ul:eq(2) li:first", "text"), "actor" => array("ul:eq(2) .actor", "text"), "director" => array("ul:eq(2) .director", "text"), "btn" => array(".button-container", "text"), "img" => array("img", "src"), "type" => array("h3 span", "text"), "tabs-items" => array(".active a:eq(0)", "text")), "#js-longvideo .g-clear")->data;
    return $data;
}
function jilu($openid) {
	global $_W, $_GPC;
	$jilu = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC LIMIT 10", array(':uniacid'=>$_W['uniacid'],':openid'=>$openid));
	return $jilu;
}
function dianying($num, $type)
{
    $url = "http://m.360kan.com/list/" . $type . "Data?pageno={$num}";
    $html = file_get_contents($url);
    $htmll = json_decode($html, true);
    $data = QueryList::Query($htmll["data"]["list"], array("link" => array("li a", "href"), "html" => array("li a", "html")))->data;
    return $data;
}
function caiji($url)
{
    $url = "http://m.360kan.com" . $url;
    $html = file_get_contents($url);
    $data = QueryList::Query($html, array("nav" => array(".b-nav", "text"), "title" => array(".cp-detail-box", "html"), "play" => array(".btn-play", "href"), "desc" => array(".cp-detail-description", "html")), ".p-body")->data;
    return $data;
}
function pc_caiji_detail($url, $type)
{
    $url = "http://www.360kan.com" . $url;
    if ($type == "dianshi" || $type == "dongman") {
        $data = QueryList::Query($url, array("nav" => array(".b-nav", "text"), "title" => array("h1", "text"), "star" => array(".s", "text"), "thumb" => array(".s-top-left img", "src"), "director" => array("#js-desc-switch p:eq(3)"), "year" => array("#js-desc-switch p:eq(0)", "text"), "area" => array("#js-desc-switch p:eq(1)", "text"), "type" => array(".tag", "text"), "actor" => array("#js-desc-switch p:eq(2)", "text"), "desc" => array(".js-close-wrap", "text")), ".c-top-main-wrap")->data;
    } else {
        $data = QueryList::Query($url, array("nav" => array(".b-nav", "text"), "title" => array("h1", "text"), "star" => array(".s", "text"), "thumb" => array(".top-left img", "src"), "director" => array("#js-desc-switch p:eq(3)"), "year" => array("#js-desc-switch p:eq(0)", "text"), "area" => array("#js-desc-switch p:eq(1)", "text"), "type" => array(".tag", "text"), "actor" => array("#js-desc-switch p:eq(2)", "text"), "desc" => array(".js-close-wrap", "text")), ".p-top")->data;
    }
    return $data;
}
function pc_caiji_detail_tuijian($url)
{
    $url = "http://www.360kan.com" . $url;
    $data = QueryList::Query($url, array("title" => array(".s1", "text"), "link" => array("a", "href"), "thumb" => array("img", "data-src")), ".tuijian:eq(1) .tuijian-list li")->data;
    return $data;
}
function pc_caiji_detail_daoyan($url)
{
    $url = "http://www.360kan.com" . $url;
    $data = QueryList::Query($url, array("title" => array(".s1", "text"), "link" => array("a", "href"), "thumb" => array("img", "data-src")), ".tuijian:eq(0) .tuijian-list li")->data;
    return $data;
}
function str_substr($start, $end, $str)
{
    $temp = explode($start, $str, 2);
    $content = explode($end, $temp[1], 2);
    return $content[0];
}
function dianshi_url($url)
{
    $link = "caiji2.go8goo.com";
    $loginurl = "http://caiji.thecook.com.cn/caiji/dianshi_url.php?link=" . $link . "&url=" . $url;
    $response = ihttp_get($loginurl);
    return json_decode($response["content"], true);
}
function caiji_url($url)
{
    $url = "http://www.360kan.com" . $url;
    $data = QueryList::Query($url, array("link" => array(".top-list-zd:eq(1) a", "href"), "title" => array(".top-list-zd:eq(1) a", "text")))->data;
    if (empty($data)) {
        $data = QueryList::Query($url, array("link" => array(".top-list-zd a", "href"), "title" => array(".top-list-zd a", "text")))->data;
    }
    return $data;
}
function juji_url($url, $site)
{
    $id = explode("/", str_substr("/", ".", $url));
    $url = "http://www.360kan.com/cover/switchsite?site=" . $site["0"] . "&id=" . $id["1"] . "&category=2";
    $html = file_get_contents($url);
    $html = json_decode($html, true);
    $html = $html["data"];
    if (empty($html)) {
        $url = "http://www.360kan.com/cover/switchsite?site=leshi&id=" . $id["1"] . "&category=2";
        $html = file_get_contents($url);
        $html = json_decode($html, true);
        $html = $html["data"];
    }
    $data = QueryList::Query($html, array("link" => array(".num-tab-main:eq(1) a", "href")))->data;
    if (empty($data)) {
        $data = QueryList::Query($html, array("link" => array(".js-tab a", "href"), "jishu" => array(".js-tab a", "text"), "yugao" => array(".ico-yugao", "text")))->data;
    }
    return $data;
}
function dongman_url($url, $site)
{
    $id = explode("/", str_substr("/", ".", $url));
    if ($site["0"] == "levp") {
        $site["0"] = "leshi";
    }
    $url = "http://www.360kan.com/cover/switchsite?site=" . $site["0"] . "&id=" . $id["1"] . "&category=4";
    $html = file_get_contents($url);
    $html = json_decode($html, true);
    $html = $html["data"];
    $data = QueryList::Query($html, array("link" => array(".num-tab-main\t a", "href"), "jishu" => array(".num-tab-main:gt(0) a", "text")))->data;
    $enddata = end($data);
    if ($enddata["link"] != "#") {
        $data = QueryList::Query($html, array("link" => array(".num-tab-main a", "href")))->data;
    } else {
        $data = QueryList::Query($html, array("link" => array(".num-tab-main:gt(0) a", "href"), "jishu" => array(".num-tab-main:gt(0) a", "text")))->data;
    }
    return $data;
}
function zongyi_url($url)
{
    $url = "http://www.360kan.com" . $url;
    $data = QueryList::Query($url, array("link" => array(".zd-down a", "href"), "title" => array(".zd-down a", "text")))->data;
    if (empty($data)) {
        $data = QueryList::Query($url, array("link" => array(".ea-site", "href"), "title" => array("#js-siteact .ea-site", "text")))->data;
    }
    return $data;
}
function zongyi_year_url($url)
{
    $url = "http://www.360kan.com" . $url;
    $data = QueryList::Query($url, array("date" => array("#js-year a", "text")))->data;
    return $data;
}
function zongyi_juji_url($url, $site, $year = "false")
{
    $id = explode("/", str_substr("/", ".", $url));
    if ($site["0"] == "levp") {
        $site["0"] = "leshi";
    }
    if ($year == "false") {
        $year = "isByTime=false";
    } else {
        $year = "year=" . $year;
    }
    $url = "http://www.360kan.com/cover/zongyilist?id=" . $id["1"] . "&site=" . $site["0"] . "&do=switchyear&" . $year;
    $html = file_get_contents($url);
    $html = json_decode($html, true);
    $html = $html["data"];
    if (!$html) {
        if ($site["0"] == "leshi") {
            $site["0"] = "levp";
        }
        $url = "http://www.360kan.com/cover/zongyilist?id=" . $id["1"] . "&site=" . $site["0"] . "&do=switchyear&" . $year;
        $html = file_get_contents($url);
        $html = json_decode($html, true);
        $html = $html["data"];
    }
    if (!$html) {
        $url = "http://www.360kan.com" . $url;
        $html = file_get_contents($url);
    }
    $data = QueryList::Query($html, array("link" => array(".js-year-page a", "href"), "year" => array(".js-year-page li .w-newfigure-hint", "text"), "title" => array(".js-year-page li .title", "text")))->data;
    return $data;
}
function get_member($openid, $type = "weixin")
{
    global $_W, $_GPC;
	 if ($_W['openid']) {
	    	$openid = $_W['openid']; 
			 $data = array("uniacid" => $_W["uniacid"], "openid" => $openid);
	    	$member = pdo_get("yingshi_byy_vip_video_member", $data);
	    }else{
	    	$openid = $_GPC['phone'];
		 $data = array("uniacid" => $_W["uniacid"], "phone" => $openid);
	       	$member =pdo_get("yingshi_byy_vip_video_member", $data);
	       	if ($member['nickname'] || $member['avatar']) {
	       		$openid = $member['openid'];
				$datas = array("uniacid" => $_W["uniacid"], "openid" => $openid);
	       		$member = pdo_get("yingshi_byy_vip_video_member", $datas);
	       	} 
	    }
	
    return $member;
}
function member($openid, $type = "weixin")
{
    global $_W, $_GPC;
    if ($type == "weixin") {
        $data = array("uniacid" => $_W["uniacid"], "openid" => $openid);
    } else {
        $data = array("uniacid" => $_W["uniacid"], "phone" => $openid);
    }
    $member = pdo_get("yingshi_byy_vip_video_member", $data);
    return $member;
}
function isUrl($s)
{
    return preg_match("/^http[s]?:\\/\\/" . "(([0-9]{1,3}\\.){3}[0-9]{1,3}" . "|" . "([0-9a-z_!~*'()-]+\\.)*" . "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\\." . "[a-z]{2,6})" . "(:[0-9]{1,4})?" . "((\\/\\?)|" . "(\\/[0-9a-zA-Z_!~\\*'\\(\\)\\.;\\?:@&=\\+\$,%#-\\/]*)?)\$/", $s) == 1;
}
function category()
{
    $data = array("dianying" => "电影", "dianshi" => "电视", "zongyi" => "综艺", "dongman" => "动漫");
    return $data;
}
function site()
{
    $data = array("youku" => "优酷", "sohu" => "搜狐", "qq" => "腾讯", "pptv" => "pptv", "imgo" => "芒果TV", "levp" => "乐视", "fengxing" => "风行", "qiyi" => "爱奇艺", "huashu" => "华数TV", "tudou" => "土豆", "cntv" => "CNTV", "pptv" => "PPTV", "bilibili" => "哔哩哔哩", "kankan" => "看看", "zgltv" => "中国蓝TV", "pptv" => "PP视频");
    return $data;
}
function type()
{
    $data = array("banner" => "开屏轮播图", "index" => "首页轮播图", "detail" => "播放页轮播图", "dianying" => "电影频道", "dianshi" => "电视频道", "zongyi" => "综艺频道", "dongman" => "动漫频道", "yule" => "娱乐频道", "gaoxiao" => "搞笑频道");
    return $data;
}
function ad()
{
    $data = array("dianying" => "首页电影推荐下方", "dianshi" => "首页电视剧推荐下方", "zongyi" => "首页综艺推荐下方", "dongman" => "首页动漫推荐下方", "dianying_list" => "电影列表插入广告", "dianshi_list" => "电视剧列表插入广告", "zongyi_list" => "综艺列表插入广告", "dongman_list" => "动漫列表插入广告", "dumiao" => "视频播放读秒广告");
    return $data;
}
function m_url($segment, $params = array())
{
    global $_W, $_GPC;
    $url = $_W["siteroot"] . "app/";
    $url .= "index.php?i={$_W["uniacid"]}&c=entry&eid={$_GPC["eid"]}";
    $url .= "&do={$segment}&";
    if (!empty($params)) {
        $queryString = http_build_query($params, '', "&");
        $url .= $queryString;
    }
    return $url;
}
function header_url($op,$sp=null)
{
    global $_W, $_GPC;
    $modules_bindings = pdo_get("modules_bindings", array("do" => "index", "module" => "yingshi_byy"));
    $url = $_W["siteroot"] . "app/index.php?i=" . $_W["uniacid"] . "&c=entry&eid=" . $modules_bindings["eid"];
    if ($op) {
        $url .= "&op=" . $op;
    }
	if($sp){
		 $url .= "&rank=".$sp;
	}
    return $url;
}


function link_url($op, $params = array(), $siteroot = '')
{
    global $_W, $_GPC;
	
    $modules_bindings = pdo_get("modules_bindings", array("do" => $op, "module" => "yingshi_byy"));
    if (!$siteroot) {
        $siteroot = $_W["siteroot"];
    }
    $url = $siteroot . "app/index.php?i=" . $_W["uniacid"] . "&c=entry&eid=" . $modules_bindings["eid"];
	 
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $url .= "&" . $queryString;
    }
    return $url;
}
function discover($url)
{
    $link = "caiji2.go8goo.com";
    $loginurl = "http://caiji.thecook.com.cn/caiji/caiji.php?link=" . $link . "&url=" . $url;
    $response = ihttp_get($loginurl);
    return json_decode($response["content"], true);
}
function site_2345()
{
    $data = array("dianying" => "http://dianying.2345.com/list/------.html", "dianshi" => "http://tv.2345.com/---.html", "dongman" => "http://dongman.2345.com/lt/", "zongyi" => "http://kan.2345.com/zongyi/l/");
    return $data;
}
function discover_2345($url)
{
    $link = "caiji2.go8goo.com";
    $loginurl = "http://caiji.thecook.com.cn/caiji/caiji.php?link=" . $link . "&url=" . $url;
    $response = ihttp_get($loginurl);
    return json_decode($response["content"], true);
}
function category_list($url)
{
    $loginurl = "http://caiji.thecook.com.cn/caiji/category_list.php?url=" . $url;
    $response = ihttp_get($loginurl);
    return json_decode($response["content"], true);
}
function category_index($rank)
{
    $url = "http://www.360kan.com/" . $rank . "/list.php?cat=all&year=all&area=all&act=all&rank=";
    $category = QueryList::Query($url, array("link" => array(".s-filter dl:eq(1) dd a", "href"), "title" => array(".s-filter dl:eq(1) dd a", "text"), "on" => array(".s-filter dl:eq(1) dd b", "text")))->data;
    return $category;
}
function index_rank($rank)
{
    $url = "https://video.so.com/" . $rank;
    if ($rank == "dianying") {
        $data = QueryList::Query($url, array("link" => array("a", "href"), "title" => array("a", "text"), "click" => array(".vv", "text")), ".rank-content>ul>li")->data;
    } else {
        if ($rank == "dongman") {
            $data = QueryList::Query($url, array("link" => array("a", "href"), "title" => array(".s1", "text"), "click" => array(".w-newfigure-hint", "text")), ".content1>ul>li")->data;
        } else {
            $data = QueryList::Query($url, array("link" => array("a", "href"), "title" => array(".s1", "text"), "click" => array(".w-newfigure-hint", "text")), ".content:eq(1)>ul>li")->data;
        }
    }
    return $data;
}
function index_list($url, $r, $rank = null, $screen_name = null)
{
    if ($r == 1) {
        $loginurl = "http://caiji.thecook.com.cn/caiji/api.php?op=" . $url . "&rank=" . $rank;
        $response = ihttp_get($loginurl);
        $list = json_decode($response["content"], true);
    } else {
        $array = array();
        $url = "http://www.360kan.com/" . $rank . "/list.php?cat=all&year=all&area=all&act=all&rank=" . $r;
        $data = QueryList::Query($url, array("link" => array(".js-tongjic", "href"), "img" => array("img", "src"), "hint" => array(".hint", "text"), "s2" => array(".s2", "text"), "title" => array(".s1", "text"), "star" => array(".star", "text")), ".s-tab-main>ul>li")->data;
        $list = array();
        foreach ($data as $key => $value) {
            if (!stristr($screen_name, $value["title"])) {
                $list[] = array("link" => $value["link"], "img" => $value["img"], "hint" => $value["hint"], "s2" => $value["s2"], "title" => $value["title"], "star" => $value["star"]);
            }
        }
    }
    return $list;
}
function kan360_list($op, $cid = null, $page = null)
{
    if (!$page) {
        $url = "http://www.v1.cn/" . $op;
        $data = QueryList::Query($url, array("link" => array(".tit a", "href"), "img" => array("img", "src"), "hint" => array(".userName", "text"), "title" => array(".tit", "text")), ".colConBox>ul>li")->data;
    } else {
        $data = array();
        $loginurl = "http://www.v1.cn/index/getList4Ajax?cid=" . $cid . "&page=" . $page;
        $response = ihttp_post($loginurl);
        $response = $response["content"];
        $response = json_decode($response, true);
        $array = $response["list"];
        foreach ($array as $key => $value) {
            $data[] = array("title" => $value["title"], "img" => $value["pic"], "hint" => $value["nickname"], "link" => '');
        }
    }
    return $data;
}
function kan360($id)
{
    $url = "http://www.v1.cn" . $id;
    $data = QueryList::Query($url, array("title" => array("h2", "text"), "thumb" => array(".videoBox", "html", '', function ($link) {
        $link = str_substr("cover=\"", "\"", $link);
        return $link;
    }), "mp4" => array(".videoBox", "html", '', function ($link) {
        $link = str_substr("videoUrl=", "\"", $link);
        return $link;
    })), ".mainBox")->data;
    return $data["0"];
}
function is_weixin()
{
	
    $agent = $_SERVER["HTTP_USER_AGENT"];
    if (!strpos($agent, "icroMessenger")) {
        return false;
    }
	
	
    return true;
}
function is_vip($url, $op)
{
    global $_W, $_GPC;
    if ($op == "id") {
        $data = pdo_get("yingshi_byy_vip_video_manage", array("uniacid" => $_W["uniacid"], "id" => $url), array("cid"));
        $cid = $data["cid"];
        $data = pdo_get("yingshi_byy_vip_video_category", array("uniacid" => $_W["uniacid"], "id" => $cid), array("is_vip"));
    } else {
        $data = pdo_get("yingshi_byy_vip_video_manage", array("uniacid" => $_W["uniacid"], "out_link" => $url), array("cid"));
        $cid = $data["cid"];
        $data = pdo_get("yingshi_byy_vip_video_category", array("uniacid" => $_W["uniacid"], "id" => $cid), array("is_vip"));
    }
    return $data;
}
	function card($digit = 6, $num = 100)
{
    $numLen = $digit;
    $pwdLen = $digit;
    $c = $num;
    $sNumArr = range(0, 9);
    $sPwdArr = array_merge($sNumArr, range("a", "z"));
    $cards = array();
    $x = 0;
   /*  if (!($x < $c)) {
        array_unique($cards);
        return $cards;
    } else { */
       /*  $tempNumStr = array();
        $i = 0;
        while ($i < $numLen) {
            $tempNumStr[] = array_rand($sNumArr);
            $i++;
        } */
			 $tempPwdStr = array();
		for($s=0;$s<$num;$s++){
        $i = 0;
		  while ($i < $pwdLen) {
            $tempPwdStr[] = $sPwdArr[array_rand($sPwdArr)];
            $i++;
		   
        }
			  $cards[$s] = implode('', $tempPwdStr);
			  $tempPwdStr=null;
		}
       
   // }
    array_unique($cards);
	
    return $cards;
	
}
function trimall($str)
{
    $qian = array(" ", "　", "\t", "\r\n", "\r\n");
    $hou = array('', '', '', '', '');
    return str_replace($qian, $hou, $str);
}
function setting()
{
    if (pdo_tableexists("yingshi_byy_agent_site_member")) {
        global $_W, $_GPC;
        $setting = pdo_get("uni_account_modules", array("uniacid" => $_W["uniacid"], "module" => "agent_site"));
        $setting = iunserializer($setting["settings"]);
        $site_name = pdo_get("yingshi_byy_agent_site_member", array("duliyuming" => $_SERVER["HTTP_HOST"], "uniacid" => $_W["uniacid"]));
        if (!$site_name) {
            $site_name = explode("." . $setting["site"], $_SERVER["HTTP_HOST"]);
            $site_name = pdo_get("yingshi_byy_agent_site_member", array("site_name" => $site_name["0"], "uniacid" => $_W["uniacid"]));
        }
        $setting["member"] = $site_name;
        return $setting;
    } else {
        return false;
    }
}
function is_sitename()
{
    if (pdo_tableexists("yingshi_byy_agent_site_member")) {
        $setting = setting();
        $url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
        preg_match("#http://(.*?)\\.#i", $url, $match);
        $site_name = $match["1"];
        $site_name = pdo_get("yingshi_byy_agent_site_member", array("site_name" => $site_name));
        if ($site_name) {
            $data["site_name"] = $match["1"];
        }
    }
}
function juhecurl($url, $params = false, $ispost = 0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}
function curlOpen($url, $config = array())
{
    $arr = array("post" => false, "referer" => $url, "cookie" => '', "useragent" => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; customie8)", "timeout" => 20, "return" => true, "proxy" => '', "userpwd" => '', "nobody" => false, "header" => array(), "gzip" => true, "ssl" => false, "isupfile" => false);
    $arr = array_merge($arr, $config);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $arr["return"]);
    curl_setopt($ch, CURLOPT_NOBODY, $arr["nobody"]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $arr["useragent"]);
    curl_setopt($ch, CURLOPT_REFERER, $arr["referer"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, $arr["timeout"]);
    if ($arr["gzip"]) {
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
    }
    if ($arr["ssl"]) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if (!empty($arr["cookie"])) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $arr["cookie"]);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $arr["cookie"]);
    }
    if (!empty($arr["proxy"])) {
        curl_setopt($ch, CURLOPT_PROXY, $arr["proxy"]);
        if (!empty($arr["userpwd"])) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $arr["userpwd"]);
        }
    }
    if (!empty($arr["header"]["ip"])) {
        array_push($arr["header"], "X-FORWARDED-FOR:" . $arr["header"]["ip"], "CLIENT-IP:" . $arr["header"]["ip"]);
        unset($arr["header"]["ip"]);
    }
    $arr["header"] = array_filter($arr["header"]);
    if (!empty($arr["header"])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arr["header"]);
    }
    if ($arr["post"] != false) {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($arr["post"]) && $arr["isupfile"] === false) {
            $post = http_build_query($arr["post"]);
        } else {
            $post = $arr["post"];
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}