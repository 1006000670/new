<?php
/**
 * 青岛淘码互联网科技有限公司
 *
 * @author 青岛淘码互联网科技有限公司
 * @author  mir Lian shuai
 */
defined('IN_IA') or exit('Access Denied');

include IA_ROOT . "/addons/yingshi_byy/function.php"; 
class Yingshi_byyModuleSite extends WeModuleSite {

	public function __construct(){      
    global $_W, $_GPC;   
	
    load()->model('mc');  
    $session_id = session_id(); 
    $account_api = WeAccount::create();
    $setting = uni_setting($_W['uniacid']);
    $settings = $_W['current_module']['config']; 
	$settings1 = $settings;
    if ($settings['is_open'] == 1 && !strexists($_W['siteurl'], 'web')) {
        message($settings['reason'],'','error');  
    }
  /*   if ($settings['sealing_url']) {
        if (strexists($settings['sealing_url'] , $_SERVER['HTTP_HOST']) && !strexists($_W['siteurl'], 'web')) {
            header("HTTP/1.1 404 Not Found");  
            header("Status: 404 Not Found");    
            exit;  
        }
    }     */
	
  /*   if (strexists($_W['siteurl'], 'yingshi_byy') && $_GPC['do'] != 'notify_wx' && $_GPC['do'] != 'codepaynotify_url' && $_GPC['do'] != 'pay' && $_GPC['do'] != 'Blypay'  && $_GPC['do'] != 'gerenpay'   && $_GPC['do'] != 'partner' && $_GPC['do'] != 'credit' && !strexists($_W['siteurl'], 'web')) {  
        header("HTTP/1.1 404 Not Found");  
        header("Status: 404 Not Found");   
        exit;  
    } */
		
    if (pdo_tableexists('yingshi_byy_video_pc_site') && !$settings['weixin_h5']) {
        $wxpay = $setting['payment']['wechat'];     
        $this->wxpay = array(
            'appid' => $_W['account']['key'],
            'mch_id' => $wxpay['mchid'],
            'key' => $wxpay['apikey'],
            'notify_url' => $_W['siteroot'] . 'app/index.php?i='.$_W['uniacid'].'&c=entry&do=notify_wx&m=yingshi_byy',
        );    
    }
    if (empty($_W['fans']['nickname']) && $_W['oauth_account']['level'] == 4 ) {
        $fans = mc_oauth_userinfo(); 
    } 
    if (!strexists($_W['siteurl'], 'web') && is_weixin()) {
       $fans_info = $account_api->fansQueryInfo($_W['openid']); 
    }    
    // var_dump($_W['fans']);
    $data = array(  
            'uniacid' => $_W['uniacid'],
            'openid' => $_W['openid'],            
            'nickname' => $_W['fans']['nickname'], 
            'avatar' => $fans_info['headimgurl'],
            'time' => TIMESTAMP, 
            'ip' => $_W['clientip'], 
            'old_time' => TIMESTAMP
    );
    if($_W['account']['level'] == 4) { 
        $data['uid'] = $_W['fans']['uid'];
    }
    if (empty($data['avatar'])) {
            $data['avatar'] = $fans['headimgurl'];
    }
    if (empty($data['nickname'])) {
            $data['nickname'] = $fans['nickname'];
    }
    if (pdo_tableexists('yingshi_byy_video_pc_site') && !is_weixin() ) {
        $_W['openid'] = $_GPC['phone'];
        // $online = pdo_get('yingshi_byy_vip_video_member_online',array('openid'=>$_W['openid'],'session_id'=>$session_id));
        $member = member($_W['openid'],'is_weixin');
        $data['openid'] = $member['openid'];
        if ($member['nickname'] || $member['avatar']) {
            $_W['openid'] = $member['openid'];
            $member = member($_W['openid']);
            $data['avatar'] = $member['avatar'];
            $data['nickname'] = $member['nickname'];
            $data['openid'] = $member['openid'];
            $data['uid'] = $member['uid'];
        }
    }else{
        $member = member($_W['openid']); 
    }  
    if ($member['is_pay'] == 3 && $member) {
        message('您已禁止访问！');  
    }    
    if ($_W['openid'] && !is_weixin()){
        // pdo_insert('yingshi_byy_vip_video_member_online', array('openid'=>$_W['openid'],'session_id'=>$session_id));
    } 
    if ($_W['openid']) {
        if ($member) {
            unset($data['time']);
            $setting = setting();
            if ($setting['member']) {
                $data['site_name'] = $setting['member']['site_name'];
            }
            pdo_update('yingshi_byy_vip_video_member', $data,array('id'=>$member['id']));
        }else{
            if (is_weixin()) {
               pdo_insert('yingshi_byy_vip_video_member', $data);
            }
        }
    }
	//判断是否手机端
   /*  if (pdo_tableexists('yingshi_byy_video_pc_site') && $_W['os'] != 'mobile' && !is_weixin() && !strexists($_W['siteurl'], 'web') && $_GPC['op'] !='web_index'&& $_GPC['op'] !='detail') { 
        $url = $_W['siteroot'].'app/index.php?i='.$_W['uniacid'].'&c=entry&do=web_index&m=yingshi_byy';  
        header("location:".$url); 
    }   */

}
	
	
	//版权限制方法 预留！！！
	private function config()
	{
		
	}
	//web 路由
	public function __web($f_name)
	{
		$this->config();
		include_once 'inc/web/' . strtolower(substr($f_name, 5)) . '.inc.php';
	}
	//app  路由
	public function __app($f_name)
	{
		
		$this->config();
		if ($this->from_user && $_SESSION['get_user_unionid_have'] == '') {
			$this->get_user_unionid($this->from_user);
		}
		include_once 'inc/app/' . strtolower(substr($f_name, 8)) . '.inc.php';
	}
	/******************************WAP*******************************/
	//首页
	public function doMobileIndex() {
	 // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	global $_W; 
	 
		 if ($_W['os'] == 'mobile' && $_GPC['do'] != 'qrcode' && !is_weixin() && !strexists($_W['siteurl'], 'web')) {	
			
				$this->__app('doMobileindex');
		}elseif(is_weixin()){
			$this->__app('doMobileindex');
		}else{
				$this->__app('doMobileWeb_index');
		}

	}
	//会员中心
	public function doMobileMember() {
		
		 if ($_W['os'] == 'mobile' && $_GPC['do'] != 'qrcode' && !is_weixin() && !strexists($_W['siteurl'], 'web')) {	
				$this->__app(__FUNCTION__);
				}elseif(is_weixin()){
			$this->__app('doMobileMember');
			}elseif(is_weixin()){
			$this->__app('doMobileMember');
		}else{
		
				$this->__app('doMobileWeb_member');
		}
	}
	//会员绑定
	public function doMobileDiscover() {
		
		$this->__app(__FUNCTION__);
	}
	//PC入口
	public function doMobileWeb_index() {
		
		$this->__app(__FUNCTION__);
	}
	//发现
	public function doMobileBangding() {
		
		$this->__app(__FUNCTION__);
	}
	//每日播放排行
	public function doMobileBofang() {
		
		$this->__app(__FUNCTION__);
	}
	//搜索
	public function doMobileSearch() {
		
		 if ($_W['os'] == 'mobile' && $_GPC['do'] != 'qrcode' && !is_weixin() && !strexists($_W['siteurl'], 'web')) {	
				$this->__app(__FUNCTION__);
				}elseif(is_weixin()){
			$this->__app('doMobileindex');
		}else{
		
				$this->__app('doMobileWeb_search');
		}
	}
	//电视
	public function doMobileTv() {
	
		$this->__app(__FUNCTION__);
	}
		//首页
	public function doMobileDetail() {
	 if ($_W['os'] == 'mobile' && $_GPC['do'] != 'qrcode' && !is_weixin() && !strexists($_W['siteurl'], 'web')) {	
				$this->__app(__FUNCTION__);
				}elseif(is_weixin()){
			$this->__app('doMobileindex');
		}else{
		
				$this->__app('doMobileWeb_detail');
		}
	
	}
			
			
	/********************************END*****************************/
	public function doMobileGetLocation() {
    global $_W, $_GPC;      
    $settings = $this->module['config'];
    $url="http://api.map.baidu.com/geocoder?location=".$_GPC['latitude'].",".$_GPC['longitude']."&output=json&key=28bcdd84fae25699606ffad27f8da77b";
    $str=file_get_contents($url);
    $res=json_decode($str,true);    
    $city = $res['result']['addressComponent']['city']; 
    if (strpos($settings['area'], $city)){  
        echo "1";   
    }else{      
        $label = 'warn';
        $msg = '您的地区限制观看';
         include $this->template('message'); 
    }
	}
	public function doMobileQrcode() {
		global $_W, $_GPC; 
		$settings = $this->module['config'];
		$token = $_GPC['token'];
		if ($token) {
			$data = array(
		            'uniacid' => $_W['uniacid'],
		            'token' => $token, 
		    );
			pdo_insert('yingshi_byy_video_pc_token', $data);			
		}		
	}
	/******************************WEB*******************************/
	//设置
	public function doWebSite() {
		
		$this->__web(__FUNCTION__);
	}
	//幻灯片
	public function doWebHuandeng() {
		
		$this->__web(__FUNCTION__);
	}
	//广告
	public function doWebGuanggao(){
		
		$this->__web(__FUNCTION__);
		
	}
	//卡密
	public function doWebCardpwd(){
		$this->__web(__FUNCTION__);
		
	}
	//会员
	public function doWebMember(){
		$this->__web(__FUNCTION__);
		
	}
	//订单
	public function doWebOrder(){
		$this->__web(__FUNCTION__);
		
	}
	//视频管理
	public function doWebManage(){
		$this->__web(__FUNCTION__);
		
	}
	//分类
	public function doWebCategory(){
		$this->__web(__FUNCTION__);
		
	}
		public function setting3(){
		global $_W, $_GPC;
		$ym_site = pdo_fetch("select * from `ims_uni_account_modules` where `uniacid`=".$_W['uniacid']." and `module`='yingshi_byy'"); 
		$data['setting'] =iunserializer($ym_site['settings']);
		$url = $_SERVER['HTTP_HOST'];
		$array=explode('.', $url);
		$data['member'] = $array[0];
	
		return $data;
	}
	public function doMobileList() {
		
		global $_W, $_GPC;
		if ($_GPC['openid']) {
	    	$openid = $_GPC['openid']; 
	    	$member = member($openid);
	    }else{
	    	$openid = $_GPC['phone'];
	       	$member = member($openid,'is_weixin');
	        $data['openid'] = $member['openid'];
	       	if ($member['nickname'] || $member['avatar']) {
	       		$openid = $member['openid'];
	       		$member = member($openid);
	       	} 
	    }
		$settings = $this->module['config'];
		$setting = setting1();
		 $site_name = $this->setting3();
	
	    $site_name = $site_name['member']; 
	    if ($site_name['setting']) {
	        $site_name = iunserializer($site_name['setting']);
	        $settings['site_title'] = $site_name['site_title'];
	        $settings['logo'] = $site_name['logo'];
	        $settings['subscribe_title'] = $site_name['subscribe_title'];
	        $settings['subscribe_url'] = $site_name['subscribe_url'];
	        $settings['index_gg'] = $site_name['index_gg'];
	        $settings['copyright'] = $site_name['copyright'];
	        $settings['guanzhu_ewm'] = $site_name['guanzhu_ewm'];
	    }	 
		$op = $_GPC['op'];	
		$pid = $_GPC['pid']; 	
		$url = $_GPC['url'];					
		$num = $_GPC['num'] ? $_GPC['num'] : 0;		
		$rank = $_GPC['rank'] ? $_GPC['rank'] : 'rankhot';
		$jilu = jilu($openid);	
			
		$hdp = pdo_getall('yingshi_byy_video_pc_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$op), array() , '' , 'sort DESC , id DESC'); 		
		$category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = ".$_W['uniacid']." AND parentid = 0 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');		
		$parent = array();
		$children = array();
		if (!empty($category)) {
			$children = '';
			foreach ($category as $cid => $cate) {
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][] = $cate;
				} else {
					$parent[$cate['id']] = $cate;
				}
			}
		}
		
		if (TIMESTAMP > $member['end_time'] && $member['is_pay'] == 1 && $member) {
	        pdo_update('yingshi_byy_vip_video_member',array('end_time'=>null,'is_pay'=>0),array('openid'=>$member['openid']));
	        $data = array(
	                'first' => array(
	                    'value' => '您好,'.$member['nickname'].'您的会员已到期',
	                    'color' => '#ff510'
	                ) ,
	                'keyword1' => array(
	                    'value' => '会员到期',
	                    'color' => '#ff510'
	                ) ,
	                'keyword2' => array(
	                    'value' => '到期提醒',
	                    'color' => '#ff510'
	                ) ,                   
	                'remark' => array(
	                    'value' => '点击详情开通',
	                    'color' => '#ff510'
	                ) ,
	            );
	        $url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'member','m' => 'yingshi_byy_vip_video')) , '.');
	        $acc->sendTplNotice($member['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
	    }
		if ($op > 0) {
			$url = $category[$op]['url'];
			$where['uniacid'] = $_W['uniacid'];
                $where['cid'] = $op;
    			$where['display !='] = 1;
    			if ($pid > 0) {
    				$where['pid'] = $pid;		
    			}	
    			$data = pdo_getall('yingshi_byy_vip_video_manage', $where, array() , '' , 'sort DESC' ,'time DESC', 'id DESC');
    			$cat = pdo_getall('yingshi_byy_vip_video_category', array('uniacid'=>$_W['uniacid'],'parentid'=>$op), array() , '' , 'id DESC');					 
		}else{
			if ($_GPC['cat'] || $_GPC['act'] || $_GPC['year'] || $_GPC['area'] || $rank) {
                $url = "http://www.360kan.com/".$op."/list.php?rank=".$rank."&year=".$year."&area=".$area."&act=".$act."&cat=".$cat."&pageno=".$num;
            }else{
                $url = "http://www.360kan.com/".$op."/list.php?rank=".$rank."&cat=all&area=all&act=all&year=all&pageno=".$num;
            }
			$discover_time = cache_load('discover:time'.$op.$rank.$_W['uniacid']);
			$data = cache_load('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['cat_id'].$_GPC['year'].$_GPC['area'].$_W['uniacid']);			
			if (empty($data) || (TIMESTAMP - $discover_time) > 86400) {	
				cache_write('discover:time'.$op.$rank.$num.$_W['uniacid'], TIMESTAMP);
				if ($setting['ziyuan'] == 1) {
                    $url = "http://caiji.thecook.com.cn/caiji/api.php?op=".$op."&rank=".$rank."&year=".$year."&area=".$area."&act=".$act."&cat=".$cat."&pageno=".$num;  
                    $response = ihttp_get($url); 
                    $data = json_decode($response['content'],true);
                }else{
                    $loginurl = 'http://caiji.thecook.com.cn/caiji/pc_caiji.php?url='.$url;	
					$response = ihttp_get($loginurl);					
					$data = json_decode($response['content'],true); 
                } 
				cache_write('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['year'].$_GPC['area'].$_W['uniacid'],$data); 
			}else{
				$data = cache_load('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['year'].$_GPC['area'].$_W['uniacid']);  
			}			
			if ((TIMESTAMP - $discover_time) > 86400) {
				if ($setting['ziyuan'] == 1) {
                    $url = "http://caiji.thecook.com.cn/caiji/api.php?op=".$op."&do=1";
                    $response = ihttp_get($url); 
                    $category_list = json_decode($response['content'],true); 
                    $cat =  $category_list['0'];  
                    $area =  $category_list['1'];  
                    $year = $category_list['2'];  
                }else{
                    $category_list = category_list($url);
					$cat =  $category_list['0'];			
					$year = $category_list['1']; 
					$area = $category_list['2'];
					$star = $category_list['3'];
                }				
				cache_write('discover:time'.$op.$rank, TIMESTAMP);					
				cache_write('discover:cat'.$op.$rank, $cat);  
				cache_write('discover:year'.$op.$rank, $year);   
				cache_write('discover:area'.$op.$rank, $area);  
				cache_write('discover:star'.$op.$rank, $star);  
			}else{					
				$cat = cache_load('discover:cat'.$op.$rank);	
				$year = cache_load('discover:year'.$op.$rank);
				$area = cache_load('discover:area'.$op.$rank);
				$star = cache_load('discover:star'.$op.$rank);
			}			
		}
	
		if ($_GPC['type'] == 'json') {				
				if ($op > 0) {					
					$num = isset($_GPC['page']) ? $_GPC['page'] : 2;
					$pageindex = 50;					
					if (!empty($_GPC['keyword'])) {
					    $condition.= " AND title LIKE %".$_GPC['keyword']."%";
					}
					if (!empty($_GPC['pcate'])) {
					    $pcate = intval($_GPC['pcate']);
					    $condition.= " AND pcate = ".$pcate;
					}
					if (!empty($_GPC['ccate'])) {
					    $ccate = $_GPC['ccate'];
					    $condition.= " AND ccate = ".$ccate;
					}
					$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_manage') . " WHERE uniacid = ".$_W['uniacid'].$condition);
					$data = pdo_fetchall("SELECT * FROM " . tablename('yingshi_byy_vip_video_manage') . " WHERE uniacid = ".$_W['uniacid']." $condition ORDER BY id DESC LIMIT " . ($num - 1) * $pageindex . ',' . $pageindex); 
				}else{
					$url = $_GPC['url'];
					$num = $_GPC['num'];
					$rank = $_GPC['rank'] ? $_GPC['rank'] : 'rankhot';		
					if ($_GPC['cat'] || $_GPC['act'] || $_GPC['year'] || $_GPC['area'] || $rank) {
						$url = "http://www.360kan.com/".$op."/list.php?rank=".$rank."&year=".$_GPC['year']."&area=".$_GPC['area']."&act=".$_GPC['act']."&cat=".$_GPC['cat']."&pageno=".$num;
					}else{
						$url = "http://www.360kan.com/".$op."/list.php?rank=".$rank."&cat=all&area=all&act=all&year=all&pageno=".$num;
					}
					$discover_time = cache_load('discover:time'.$op.$rank.$num.$_W['uniacid']);
					$data = cache_load('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['year'].$_GPC['area'].$num.$_W['uniacid']);
					if (empty($data) || (TIMESTAMP - $discover_time) > 86400) {
						cache_write('discover:time'.$op.$rank.$num.$_W['uniacid'], TIMESTAMP);
						if ($setting['ziyuan'] == 1) {
		                    $url = "http://caiji.thecook.com.cn/caiji/api.php?op=".$op."&rank=".$rank."&year=".$_GPC['year']."&area=".$_GPC['area']."&act=".$_GPC['act']."&cat=".$_GPC['cat_id']."&pageno=".$num; 
		                    $response = ihttp_get($url); 
		                    $data = json_decode($response['content'],true);
		                }else{
		                    $data = discover($url);
		                } 
						
						cache_write('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['year'].$_GPC['area'].$num.$_W['uniacid'],$data);
					}else{
						$data = cache_load('discover:data'.$op.$rank.$_GPC['cat'].$_GPC['year'].$_GPC['area'].$num.$_W['uniacid']); 					 
					}	
				}				
				include $this->template('list_json'); 
				exit();
			}
		include $this->template('list'); 
	}

	/********************************END*****************************/
	
}
