<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC; 
    load()->model('mc');
    $op = $_GPC['op'];
    $settings = $this->module['config'];        
    $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
    $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
    $pindex = max(intval($_GPC['page']), 1); // 当前页码
    $psize = 20; // 设置分页大小
    $condition = ' WHERE uniacid=:uniacid AND time >= :starttime AND time <= :endtime ';        
    $params = array(
        ':uniacid'=>$_W['uniacid'],
        ':starttime' => $starttime,
        ':endtime' => $endtime
    );
    if ($_GPC['status']) {
       $condition .= ' AND status = :status '; 
       $params[':status'] = $_GPC['status'];
    }        
    $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_order') . $condition . ' ORDER BY id DESC LIMIT '.(($pindex -1) * $psize).','. $psize;
    $list = pdo_fetchall($sql, $params, 'id');        
    $total_amount = pdo_fetchcolumn('SELECT SUM(fee) as fee FROM ' . tablename('yingshi_byy_vip_video_order') . $condition . " AND status = 1 AND tid != '积分兑换' ",$params);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_order') . $condition,$params);
    $pager = pagination($total, $pindex, $psize);        
    if ($op == 'qingli') {
           pdo_delete('yingshi_byy_vip_video_order',array('status'=>0,'uniacid'=>$_W['uniacid']));
           message('清理成功',$this->createWebUrl('order'),'success');
    }   
    if ($op == 'partner') {
        global $_W, $_GPC;        
        $order =  pdo_get('yingshi_byy_vip_video_order',array('tid'=>$_GPC['out_trade_no']));
        $settings = pdo_get('uni_account_modules',array('uniacid'=>$order['uniacid'],'module'=>'super_mov'));
        $settings = iunserializer($settings['settings']); 
        if ($settings['payment'] == 2) {
            $key = $settings['shop_key'];
        }
        if ($settings['payment'] == 3) {
            $key = $settings['caihong_key'];
        }
        
        $data = array(
            'uniacid'=>$order['uniacid'],
            'key'=>$key,
        ); 
        echo json_encode($data);
        exit();
    } 
    include $this->template('order');   