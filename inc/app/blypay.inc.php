<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $op = $_GPC['op'] ? $_GPC['op'] : 'pay';
    $settings = $this->module['config']; 

    if ($op == 'pay') {
        if ($settings['payment'] == 2) {
            require_once(IA_ROOT . "/addons/super_mov/sdk/lib/epay_submit.class.php");
            $alipay_config['partner']       = $settings['shop_id'];
            //商户KEY
            $alipay_config['key']           = $settings['shop_key'];
            //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            //签名方式 不需修改
            $alipay_config['sign_type']    = strtoupper('MD5');
            //字符编码格式 目前支持 gbk 或 utf-8
            $alipay_config['input_charset']= strtolower('utf-8');
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
            $alipay_config['transport']    = $http_type;
            //支付API地址
            $alipay_config['apiurl']    = 'https://pay.blyzf.cn/';
            $notify_url =  $_W['siteroot'] . 'addons/super_mov/sdk/notify_url.php'; 
            //需http://格式的完整路径，不能加?id=123这类自定义参数
            //页面跳转同步通知页面路径
            $return_url = $_W['siteroot'] . 'addons/super_mov/sdk/return_url.php'; 
            //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        }
        if ($settings['payment'] == 3) {
            require_once(IA_ROOT . "/addons/super_mov/caihong/lib/epay_submit.class.php");
            $alipay_config['partner']       = $settings['caihong_id'];
            //商户KEY
            $alipay_config['key']           = $settings['caihong_key'];
            //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            //签名方式 不需修改
            $alipay_config['sign_type']    = strtoupper('MD5');
            //字符编码格式 目前支持 gbk 或 utf-8
            $alipay_config['input_charset']= strtolower('utf-8');
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
            $alipay_config['transport']    = $http_type;
            //支付API地址
            $alipay_config['apiurl']    = 'https://pay.v8jisu.cn/';
            $notify_url =  $_W['siteroot'] . 'addons/super_mov/caihong/notify_url.php'; 
            //需http://格式的完整路径，不能加?id=123这类自定义参数
            //页面跳转同步通知页面路径
            $return_url = $_W['siteroot'] . 'addons/super_mov/caihong/return_url.php'; 
            //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        } 
        
        //商户订单号
        $out_trade_no = $_GPC['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填
        //支付方式
        $type = $_GPC['type'];
        //商品名称
        $name = $_GPC['WIDsubject'];
        //付款金额
        $money = $_GPC['WIDtotal_fee'];
        //站点名称
        $sitename = $_W['account']['name'];
        $parameter = array(
            "pid" => trim($alipay_config['partner']),
            "uniacid" => $_W['uniacid'],
            "type" => $type,
            "notify_url"    => $notify_url,
            "return_url"    => $return_url,
            "out_trade_no"  => $out_trade_no,
            "name"  => $name,
            "money" => $money,
            "sitename"  => $sitename
        );
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter);
        echo $html_text;
        exit();
    }
    if ($op == 'return') {
        $acc = WeAccount::create(); 
        $tid = $_GPC['tid'];
        $order_id = $_GPC['order_id']; 
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