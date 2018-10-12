
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
		$op = $_GPC['op'] ? $_GPC['op'] : 'member';
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
	    if (!$member) {
	    	message('您还未登陆，请在首页点击右上角扫码登陆系统！', $this->createMobileUrl('index'), 'success');
	    } 
	     $site_name = $this->setting3();
	    $site_name = $site_name['member']; 
	    if ($site_name['setting']) {
	        $setting2 = iunserializer($site_name['setting']);
	        $settings['site_title'] = $setting2['site_title'];
	        $settings['logo'] = $setting2['logo'];
	        $settings['subscribe_title'] = $setting2['subscribe_title'];
	        $settings['subscribe_url'] = $setting2['subscribe_url'];
	        $settings['index_gg'] = $setting2['index_gg'];
	        $settings['copyright'] = $setting2['copyright'];
	        $settings['guanzhu_ewm'] = $setting2['guanzhu_ewm'];
	    }		
		$jilu = jilu($openid);			
		$card = iunserializer($setting['card']);
		$snid = TIMESTAMP . random(6, true);		
		if ($op == 'card') {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'pwd' => $_GPC['card'],					
			);

			$card = pdo_get('cyl_vip_video_keyword_id', $data, array() , '' , 'id DESC');
			if (!$member) {
				echo json_encode(array('card'=>2,'data'=>'还未登录'));
				exit();
			}elseif (!$card) {
				echo json_encode(array('card'=>2,'data'=>'兑换码无效'));				
				exit();
			}elseif ($card['status']) {
				echo json_encode(array('card'=>2,'data'=>'兑换码已使用'));				
				exit();				
			}else{
				$res = pdo_update('cyl_vip_video_keyword_id', array('status'=>1,'openid'=>$openid), array('id'=>$card['id']));					
				if($res){
					if ($member['end_time']) { 
	                 	pdo_update('cyl_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$card['day']." days",$member['end_time'])), array('openid' => $openid,'uniacid'=>$data['uniacid']));
	                 	$time = date('Y-m-d H:i:s',strtotime("+".$card['day']." days",$member['end_time']));
	                }else{	                	
	                	pdo_update('cyl_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$card['day']." days")), array('openid' => $openid,'uniacid'=>$data['uniacid']));
	                	$time = date('Y-m-d H:i:s',strtotime("+".$card['day']." days"));
	                }
	                if($settings['tpl_id']) {
	                	$data = array(
		                    'first' => array(
		                        'value' => '您好,'.$member['nickname'].'开通了'.$card['day'].'天会员',
		                        'color' => '#ff510'
		                    ) ,
		                    'keyword1' => array(
		                        'value' => '会员开通',
		                        'color' => '#ff510'
		                    ) ,
		                    'keyword2' => array(
		                        'value' => '开通提醒',
		                        'color' => '#ff510'
		                    ) ,                    
		                    'remark' => array(
		                        'value' => '卡密兑换'.$card['day'].'天,到期时间'.$time,
		                        'color' => '#ff510'
		                    ) ,
		                );
		                $url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'member','m' => 'super_mov')) , '.');
		                $acc->sendTplNotice($member['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
		                $data = array(
		                    'first' => array(
		                        'value' => $member['nickname'].'开通了'.$card['day'].'天会员',
		                        'color' => '#ff510'
		                    ) ,
		                    'keyword1' => array(
		                        'value' => '会员开通',
		                        'color' => '#ff510'
		                    ) ,
		                    'keyword2' => array(
		                        'value' => '开通提醒',
		                        'color' => '#ff510'
		                    ) ,                    
		                    'remark' => array(
		                        'value' => '卡密兑换'.$card['day'].'天，到期时间'.$time.'请进入后台查看',
		                        'color' => '#ff510'
		                    ) ,
		                );	                
		                $acc->sendTplNotice($settings['kf_id'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
	                }
	                $data = array(
						'uniacid' => $_W['uniacid'],
						'openid' => $member['openid'],
						'uid' => $uid,
						'tid' => '卡密兑换',
						'fee' => '',				
						'status' => 1,
						'day' => $card['day'],
						'time' => TIMESTAMP
					); 					
					pdo_insert('cyl_vip_video_order',$data);
					echo json_encode(array('card'=>1,'data'=>'兑换成功'));				
					exit();	
				}
			}
		}
		include $this->template('web_member'); 