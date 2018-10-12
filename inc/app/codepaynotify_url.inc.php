<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $settings = $this->module['config'];
    ksort($_POST); //排序post参数
    reset($_POST); //内部指针指向数组中的第一个元素
    // var_dump($_GET);
    $codepay_key = $settings['codepay_appkey']; //这是您的密钥
    $sign = '';//初始化
    foreach ($_POST AS $key => $val) { //遍历POST参数
        if ($val == '' || $key == 'sign') continue; //跳过这些不签名
        if ($sign) $sign .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
        $sign .= "$key=$val"; //拼接为url参数形式
    }
    if (!$_POST['pay_no'] || md5($sign . $codepay_key) != $_POST['sign']) { //不合法的数据
        exit('fail');  //返回失败 继续补单
    } else { //合法的数据
        //业务处理
        $pay_id = $_POST['pay_id']; //需要充值的ID 或订单号 或用户名
        $money = (float)$_POST['money']; //实际付款金额
        $price = (float)$_POST['price']; //订单的原价
        $param = $_POST['param']; //自定义参数
        $pay_no = $_POST['pay_no']; //流水号
        $order = pdo_fetch("SELECT * FROM " . tablename('yingshi_byy_vip_video_order') . " WHERE tid = :tid", array(
            ':tid' => $pay_id
        ));
        $acc = WeAccount::create();
	    $member = pdo_get('yingshi_byy_vip_video_member', array('openid' => $order['openid'],'uniacid'=>$order['uniacid']));
	     //实例化消息类对象     
	    $setting = setting();
	    //根据参数params中的result来判断支付是否成功	             
        pdo_update('yingshi_byy_vip_video_order', array('status'=>1), array('tid' => $order['tid']));  
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
                        'value' => '感谢打赏'.$order['desc'],
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
                        'value' => $order['desc'],  
                        'color' => '#ff510'
                    ) ,
                );
                $url = link_url('member');
                $acc->sendTplNotice($order['openid'], $settings['exceptional_id'], $data, $url, $topcolor = '#FF683F');
                $data = array(
                    'first' => array(
                        'value' => $member['nickname'].$order['desc'],
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
                        'value' => $order['desc'].'金额【'.$order['fee'].'】元，请进入后台查看',
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
                        'value' => '到期时间：'.$time,
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
        exit('success'); //返回成功 不要删除哦
    }