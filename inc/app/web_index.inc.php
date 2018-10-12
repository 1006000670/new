
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
//
// $Id:$

defined("IN_IA") or exit("Access Denied");
	global $_W, $_GPC;		
		$settings = $this->module['config'];
		$setting = setting1();
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

		$op = $_GPC['op'] ? $_GPC['op'] : 'index';
		$jilu = jilu($openid);	 	
				
		if ($site_name) {  
	        $hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$op,'site_name'=>$site_name['site_name']), array() , '' , 'sort DESC , id DESC');
	    }else{
	        if (pdo_tableexists('yingshi_byy_agent_site_member')) {
	            $hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$op,'site_name'=>''), array() , '' , 'sort DESC , id DESC'); 
	        }else{
	           $hdp = pdo_getall("yingshi_byy_vip_video_hdp", array(
                "uniacid" => $_W["uniacid"],
                "type" => $op
            ) , array() , '', "sort DESC , id DESC"); 
	        }
	    }		
		if(empty($hdp)){
			
			$hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$op), array() , '' , 'sort DESC , id DESC'); 	
		}
	
		$category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = ".$_W['uniacid']." AND parentid = 0 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
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
	        $url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'member','m' => 'super_mov')) , '.');
	        $acc->sendTplNotice($member['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
	    }
		$time = cache_load('yingshi_byy_video_pc:time'.$_W['uniacid']);
		$data = pdo_getall('yingshi_byy_vip_video_manage', array('uniacid'=>$_W['uniacid'],'display !=' => 1), array() , '' ,array('sort DESC','time DESC','id DESC'));
		if ($setting['ziyuan'] == 1) {			
			$url = "http://caiji.thecook.com.cn/caiji/api.php?op=today&pageno=20"; 
	        $response = ihttp_get($url); 
	        $today = json_decode($response['content'],true);
		}
		if ((TIMESTAMP - $time) > 3600 ) {	 
			if ($setting['ziyuan'] == 1) {
                $dianying = index_list('dianying',$setting['ziyuan'],$setting['dianying_rank'] ? $setting['dianying_rank'] : 'rankhot');
                $dianshi = index_list('dianshi',$setting['ziyuan'],'rankhot');
                $zongyi = index_list('zongyi',$setting['ziyuan'],$setting['zongyi_rank'] ? $setting['zongyi_rank'] : 'rankhot');
                $dongman = index_list('dongman',$setting['ziyuan'],$setting['dongman_rank'] ? $setting['dongman_rank'] : 'rankhot');  
           	}else{				 		
				$dianying = index_list($url,$settings['dianying_rank'] ? $setting['dianying_rank'] : 'rankhot','dianying',$setting['screen_name']);  
				$dianshi = index_list($url,'rankhot','dianshi',$setting['screen_name']);
				$zongyi = index_list($url,$settings['zongyi_rank'] ? $setting['zongyi_rank'] : 'rankhot','zongyi',$setting['screen_name']);
				$dongman = index_list($url,$settings['dongman_rank'] ? $setting['dongman_rank'] : 'rankhot','dongman',$setting['screen_name']); 
			}
			cache_write('yingshi_byy_video_pc:time'.$_W['uniacid'], TIMESTAMP);				
			cache_write('yingshi_byy_video_pc:dianying'.$_W['uniacid'], $dianying);  
			cache_write('yingshi_byy_video_pc:dianshi'.$_W['uniacid'], $dianshi);  
			cache_write('yingshi_byy_video_pc:zongyi'.$_W['uniacid'], $zongyi);   
			cache_write('yingshi_byy_video_pc:dongman'.$_W['uniacid'], $dongman);  
		}else{
			$dianying = cache_load('yingshi_byy_video_pc:dianying'.$_W['uniacid']);	
			$dianshi = cache_load('yingshi_byy_video_pc:dianshi'.$_W['uniacid']);	
			$zongyi = cache_load('yingshi_byy_video_pc:zongyi'.$_W['uniacid']);	
			$dongman = cache_load('yingshi_byy_video_pc:dongman'.$_W['uniacid']); 			
		}
		// $discover_time = cache_load('index:time');
		// if ((TIMESTAMP - $discover_time) > 86400) {
		// 	$category_dianying = category_index('dianying');
		// 	$category_dianshi = category_index('dianshi');
		// 	$category_zongyi = category_index('zongyi');
		// 	$category_dongman = category_index('dongman');
		// 	$index_rank_dianying = index_rank('dianying');
		// 	$index_rank_dianshi = index_rank('dianshi');
		// 	$index_rank_zongyi = index_rank('zongyi');
		// 	$index_rank_dongman = index_rank('dongman');				
		// 	cache_write('index:time'.$_W['uniacid'], TIMESTAMP);				
		// 	cache_write('category_dianying'.$_W['uniacid'], $category_dianying);  
		// 	cache_write('category_dianshi'.$_W['uniacid'], $category_dianshi);  
		// 	cache_write('category_zongyi'.$_W['uniacid'], $category_zongyi);   
		// 	cache_write('category_dongman'.$_W['uniacid'], $category_dongman);  
		// 	cache_write('index_rank_dianying'.$_W['uniacid'], $index_rank_dianying);  
		// 	cache_write('index_rank_dianshi'.$_W['uniacid'], $index_rank_dianshi);  
		// 	cache_write('index_rank_zongyi'.$_W['uniacid'], $index_rank_zongyi);   
		// 	cache_write('index_rank_dongman'.$_W['uniacid'], $index_rank_dongman);  
		// }else{					
		// 	$category_dianying = cache_load('category_dianying'.$_W['uniacid']); 	
		// 	$category_dianshi = cache_load('category_dianshi'.$_W['uniacid']);
		// 	$category_zongyi = cache_load('category_zongyi'.$_W['uniacid']);
		// 	$category_dongman = cache_load('category_dongman'.$_W['uniacid']);
		// } 		
		include $this->template('web_index'); 