<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;      
    $res = pdo_delete('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid']));
    echo "清空成功";
    exit(); 