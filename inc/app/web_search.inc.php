<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
  global $_W, $_GPC; 
		$acc = WeAccount::create();        
		$settings = $this->module['config'];		
		$setting =setting1();		
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
		$hdp = pdo_getall('cyl_video_pc_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$_GPC['do']), array() , '' , 'sort DESC , id DESC');
		$category = pdo_fetchall("SELECT * FROM ".tablename('cyl_vip_video_category')." WHERE uniacid = ".$_W['uniacid']." AND parentid = 0 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');	
	    $where = ' WHERE uniacid = :uniacid ';
		$params[':uniacid'] = $_W['uniacid'];
		$sql = ' SELECT * FROM '.tablename('cyl_vip_video_manage').$where.' ORDER BY id DESC LIMIT 50';			
		$video = pdo_fetchall($sql, $params, 'id'); 			
		$op = $_GPC['op'] ? $_GPC['op'] : 'search';
		$key = $_GPC['key'];
		if ($key) {
			if ($setting['ziyuan'] == 1) { 
	            $url = "http://caiji.thecook.com.cn/caiji/api.php?key=".$key;
	            $response = ihttp_get($url); 
	            $list = json_decode($response['content'],true);
	        }else{
				$where = ' WHERE uniacid = :uniacid '; 			
				$where .= ' AND title LIKE :title ';
				$params[':uniacid'] = $_W['uniacid'];	
				$params[':title'] = "%".$_GPC['key']."%";				
				$sql = ' SELECT * FROM '.tablename('cyl_vip_video_manage').$where.' ORDER BY id DESC ';			
				$search = pdo_fetchall($sql, $params, 'id'); 			
				$list = caiji_list($key);
			}
		}	
		include $this->template('web_search'); 

