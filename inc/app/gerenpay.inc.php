<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $settings = $this->module['config']; 
    $op = $_GPC['op'] ? $_GPC['op'] : 'pay';
    if ($op == 'return') {
        $acc = WeAccount::create(); 
        $tid = $_GPC['orderid'];
        $order_id = $_GPC['orderid']; 
        $order = pdo_get('yingshi_byy_vip_video_order', array('uniacid'=>$_W['uniacid'],'tid'=>$tid)); 
        if ($order['status'] == 1) {
            message('订单已经支付过了','','error');
        }
        pdo_update('yingshi_byy_vip_video_order', array('status'=>1), array('tid' => $tid));
        $openid = $order['openid'];
        $member = member($openid);
        if ($member['end_time']) {  
            $day = $order['day'];               
            pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days",$member['end_time'])), array('openid' => $order['openid'],'uniacid'=>$order['uniacid']));
            $time = date('Y-m-d H:i:s',strtotime("+".$day." days",$member['end_time'])); 
        }else{
            $day = $order['day'];
            pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days")), array('openid' => $order['openid'],'uniacid'=>$order['uniacid']));
            $time = date('Y-m-d H:i:s',strtotime("+".$day." days"));
        } 
        if($_W['account']['level'] == 4 && $settings['tpl_id']) {
                    if ($order['desc']) {
                        $data = array(
                            'first' => array(
                                'value' => '您好,'.$member['nickname'],
                                'color' => '#ff510'
                            ) ,
                            'keyword1' => array(
                                'value' => '感谢打赏',
                                'color' => '#ff510'
                            ) ,
                            'keyword2' => array(
                                'value' => $order['fee'],
                                'color' => '#ff510'
                            ) ,   
                            'keyword3' => array(
                                'value' => date('Y-m-d H:i:s',$order['time']),
                                'color' => '#ff510'
                            ) ,                 
                            'remark' => array(
                                'value' => '感谢您的打赏！', 
                                'color' => '#ff510'
                            ) ,
                        );
                        $url = link_url('member');
                        $acc->sendTplNotice($order['openid'], $settings['exceptional_id'], $data, $url, $topcolor = '#FF683F');
                        $data = array(
                            'first' => array(
                                'value' => $member['nickname'].'打赏',
                                'color' => '#ff510'
                            ) ,
                            'keyword1' => array(
                                'value' => '感谢打赏',
                                'color' => '#ff510'
                            ) ,
                            'keyword2' => array(
                                'value' => $order['fee'],
                                'color' => '#ff510'
                            ) ,   
                            'keyword3' => array(
                                'value' => date('Y-m-d H:i:s',$order['time']),
                                'color' => '#ff510'
                            ) ,                    
                            'remark' => array(
                                'value' => '打赏金额【'.$order['fee'].'】元，请进入后台查看',
                                'color' => '#ff510'
                            ) ,
                        );
                        $acc->sendTplNotice($settings['kf_id'], $settings['exceptional_id'], $data, $url, $topcolor = '#FF683F');
                    }else{
                        $data = array(
                            'first' => array(
                                'value' => '您好,'.$member['nickname'],
                                'color' => '#ff510'
                            ) ,
                            'keyword1' => array(
                                'value' => $params['tid'],
                                'color' => '#ff510'
                            ) ,
                            'keyword2' => array(
                                'value' => '支付成功',
                                'color' => '#ff510'
                            ) ,   
                            'keyword3' => array(
                                'value' => date('Y-m-d H:i:s',$order['time']),
                                'color' => '#ff510'
                            ) ,    
                            'keyword4' => array(
                                'value' => $_W['uniaccount']['name'],
                                'color' => '#ff510'
                            ) ,  
                            'keyword5' => array(
                                'value' => $order['fee'],
                                'color' => '#ff510'
                            ) ,              
                            'remark' => array(
                                'value' => '到期时间：'.$time.'',
                                'color' => '#ff510'
                            ) ,
                        ); 
                        $url = link_url('member');
                        $acc->sendTplNotice($order['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
                        $data = array(
                            'first' => array(
                                'value' => $member['nickname'].'开通了'.$day.'天会员',
                                'color' => '#ff510'
                            ) ,
                            'keyword1' => array(
                                'value' => $params['tid'],
                                'color' => '#ff510'
                            ) ,
                            'keyword2' => array(
                                'value' => '支付成功',
                                'color' => '#ff510'
                            ) ,   
                            'keyword3' => array(
                                'value' => date('Y-m-d H:i:s',$order['time']),
                                'color' => '#ff510'
                            ) ,    
                            'keyword4' => array(
                                'value' => $_W['uniaccount']['name'],
                                'color' => '#ff510'
                            ) ,  
                            'keyword5' => array(
                                'value' => $order['fee'],
                                'color' => '#ff510'
                            ) ,              
                            'remark' => array(
                                'value' => '会员到期时间'.$time.'请进入后台查看',
                                'color' => '#ff510'
                            ) ,
                        );
                        $acc->sendTplNotice($settings['kf_id'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');   
                    }
                } 
                if ($_W['os'] == 'windows' && !is_weixin()) {
                    $url = $_W['siteroot'] . 'app/index.php?i='.$order['uniacid'].'&c=entry&do=index&m=yingshi_byy_video_pc';
                    message('您已支付成功！', $url , 'success'); 
                }
                message('您已支付成功！', link_url('member') , 'success');
    }
    