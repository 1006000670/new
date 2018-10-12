<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $setting = setting();
    $setting = $setting['member'];       
    if ($setting) {
        $settings = iunserializer($setting['setting']);
    }else{
        $settings = $this->module['config'];
    }  
    if ($_GPC['do'] == 'tv' && !$_GPC['eid']) { 
        $modules_bindings = pdo_get('modules_bindings', array('do' => $_GPC['do'],'module'=>$_GPC['m']));        
        $url = $_W['siteroot'] . 'app/index.php?i='.$_W['uniacid'].'&c=entry&eid='.$modules_bindings['eid'];
        if ($_GPC['op']) {
           $url .= '&op='.$_GPC['op'];
        }
        Header("Location: ".$url);
        exit(); 
    }
    if ($settings['is_pc'] == 2 && is_weixin()) {       
        $url = link_url('index'); 
        Header("Location: ".$url);
        exit();  
    }   
    $url = $_GPC['url'];    
    $response = ihttp_get('http://api.diligulu.cc/iptv/tv_m3u8.xml');
    $xml = $response['content'];
    $data = xml2array($xml);    
    $data = $data['class'];
    $jilu = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC LIMIT 10", array(':uniacid'=>$_W['uniacid'],':openid'=>$_W['openid']));
     $category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = :uniacid AND parentid = :parentid AND status = :status ORDER BY parentid ASC, displayorder ASC, id ASC ", array(':uniacid'=>$_W['uniacid'],':parentid'=>0,':status'=>0), 'id');        
    include $this->template('tv'); 

