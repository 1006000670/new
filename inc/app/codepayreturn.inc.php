<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $id = $_GPC['tid'];
    $order = pdo_fetch("SELECT * FROM " . tablename('yingshi_byy_vip_video_order') . " WHERE id = :id", array(
        ':id' => $id
    ));
    if ($order['status'] == 1) {
       message('您已支付成功！', link_url('member').'&site_name='.$order['site_name'] , 'success');  
    }else{
       message('支付失败','','error');
    }