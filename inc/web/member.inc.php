<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');
    global $_W, $_GPC;
    // load()->func('communication');
    // $account_api = WeAccount::create();
    // $token = $account_api->getAccessToken();
    // $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid=o2fEW0fG5Uj7GIKd7q1eBH3e0DnQ&lang=zh_CN";
    // $response = ihttp_get($url);    
    // var_dump(json_decode($response['content'],true)); 
    $op = $_GPC['op'] ? $_GPC['op'] : 'member';
    if ($op == 'member') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND old_time >= :starttime AND old_time <= :endtime';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime, 
            ':endtime' => $endtime
        );
        if ($_GPC['keyword']) {
            $where .= ' AND nickname LIKE :keyword OR phone LIKE :keyword '; 
            $params[':keyword'] = "%{$_GPC['keyword']}%"; 
        }
        if ($_GPC['is_pay']) {
            $where .= ' AND is_pay = :is_pay ';
            $params[':is_pay'] = "{$_GPC['is_pay']}";
        }
        if ($_GPC['is_pay'] == 2) {
            $where .= ' AND is_pay = :is_pay ';
            $params[':is_pay'] = 0;
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_member') . $where , $params);
        $today_member = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_member') . $where , array(':uniacid'=>$_W['uniacid'],':starttime' => strtotime(date('Y-m-d')),':endtime' => TIMESTAMP));
        $today_click = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video') . ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime  ' , $params);     
        $total_member = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_member') . " WHERE uniacid = :uniacid AND is_pay = :is_pay ",array(':uniacid'=>$_W['uniacid'],':is_pay'=>1));
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_member').$where.' ORDER BY old_time DESC , id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;          
        $list = pdo_fetchall($sql, $params, 'id');
    } 
    if ($op == 'add') {
        $id = $_GPC['id'];
        if ($id) {              
            $item = pdo_get('yingshi_byy_vip_video_member', array('id'=>$id)); 
        }
        if (checksubmit()) {
            $data = $_GPC['data'];
            $data['uniacid'] = $_W['uniacid'];
            $data['time'] = TIMESTAMP; 
            if ($_GPC['password']) {
               $data['password'] = md5($_GPC['password']);
            }
            $data['avatar'] = $_GPC['avatar']; 
            $data['end_time'] = strtotime($_GPC['end_time']);
            if ($item) {
                $res = pdo_update('yingshi_byy_vip_video_member',$data,array('id'=>$id));
            }else{
                $res = pdo_insert('yingshi_byy_vip_video_member',$data); 
            }
            if ($res) {
                message('更新成功',$this->createWebUrl('member'),'success');
            }else{
                message($res); 
            }
            
        }
    }
    if ($op == 'delete') {
        $id = $_GPC['id'];
        pdo_delete('yingshi_byy_vip_video_member',array('id'=>$id));
        message('删除成功',$this->createWebUrl('member'),'success');
    }
    if ($op == 'blacklist') {
        $id = $_GPC['id'];
        pdo_update('yingshi_byy_vip_video_member',array('is_pay'=>3),array('id'=>$id));
        message('设置成功',$this->createWebUrl('member'),'success');
    }
    if ($op == 'blacklistopen') {
        $id = $_GPC['id'];
        pdo_update('yingshi_byy_vip_video_member',array('is_pay'=>''),array('id'=>$id));
        message('设置成功',$this->createWebUrl('member'),'success');
    }
    include $this->template('member'); 