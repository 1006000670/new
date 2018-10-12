<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $uniacid = pdo_get('account_wechats');
    $order =  pdo_get('yingshi_byy_vip_video_order',array('tid'=>$_GPC['out_trade_no']));
    $settings = pdo_get('uni_account_modules',array('uniacid'=>$order['uniacid'],'module'=>'super_mov'));
    $settings = iunserializer($settings['settings']); 
    $key = $settings['shop_key'];
    $data = array(
        'uniacid'=>$uniacid['uniacid'],
        'key'=>$key,
    ); 
    return json_encode($data);