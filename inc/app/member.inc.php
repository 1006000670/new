<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
  global $_W, $_GPC;          

    load()->model('mc');    
    $session_id = session_id();  
    $acc = WeAccount::create();       
    $setting = setting();   
    $site_name = $setting['member']; 
    $settings = $this->module['config'];  
	
    if ($setting['member']['card']) {
        $settings['card'] = $setting['member']['card'];
        $settings['pay_settings'] = 1;
        $settingss = iunserializer($site_name['setting']);
        $settings['site_title'] = $settingss['site_title'];
    } 
    if ($settings['is_pc'] == 2 && is_weixin()) {       
        $url = link_url('index'); 
        Header("Location: ".$url);
        exit();  
    }  
    $op = $_GPC['op'] ? $_GPC['op'] : 'member';
    if (!is_weixin() && pdo_tableexists('yingshi_byy_video_pc_site') && $_GPC['phone']) {
        $_W['openid'] = $_GPC['phone'];
    }   
	 
    if (pdo_tableexists('yingshi_byy_video_pc_site') && !is_weixin() && $_W['oauth_account']['level'] == 4) {
        $openid = $_GPC['phone'];
        $member = member($openid,'is_weixin');        
        if ($member['nickname']) {
            $openid = $member['openid'];
            $member = member($openid);
        }
    }elseif(pdo_tableexists('yingshi_byy_video_pc_site') && is_weixin() && $_W['oauth_account']['level'] < 4 ){
        $openid = $_GPC['phone'];
        $member = member($openid,'is_weixin');
        if ($member['nickname']) {
            $openid = $member['openid'];
            $member = member($openid);
        }
    }else{
        $openid = $_W['openid'];
        $member = member($openid);
    }    

    if (TIMESTAMP > $member['end_time'] && $member['is_pay'] == 1) {
        pdo_update('yingshi_byy_vip_video_member',array('end_time'=>null,'is_pay'=>0),array('openid'=>$member['openid']));
        $data = array(
                'first' => array(
                    'value' => '您好,'.$member['nickname'].'您的会员已到期',
                    'color' => '#ff510'
                ) ,
                'keyword1' => array(
                    'value' => '会员到期',
                    'color' => '#ff510'
                ) ,
                'keyword2' => array(
                    'value' => date('Y-m-d H:i:s',$member['end_time']),
                    'color' => '#ff510'
                ) ,                   
                'remark' => array(
                    'value' => '欢迎继续使用',
                    'color' => '#ff510'
                ) ,
            );
        $url = link_url('member');
        $acc->sendTplNotice($member['openid'], $settings['due_id'], $data, $url, $topcolor = '#FF683F');
    }
    if($_W['oauth_account']['level'] < 4 || !is_weixin()) {
        if (!pdo_tableexists('yingshi_byy_video_pc_site')) {
           checkauth();
        }elseif(!$member && $op != 'login' && $op != 'create' && $op != 'check' ){
           message('您还未登陆，即将跳转!',link_url('member',array('op'=>'login')),'success');    
        }       
    }
    $credit = mc_credit_fetch($member['uid']);
    if ($op == 'open') {
        $day = $_GPC['day'];
        $fee = $_GPC['card_fee'];
        $day = $_GPC['card_day'];
        $jifen = $_GPC['card_credit'];
        if (checksubmit('credit')) {
            $fee = $jifen;                      
            if ($fee > $credit['credit1']) {
                message('积分不足','','error');
            }
            if (empty($fee)) {
                message('管理员未设置积分，请使用微信支付兑换','','error');
            }
            $data = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $member['openid'],
                'uid' => $member['uid'],
                'tid' => '积分兑换',
                'fee' => $fee,              
                'status' => 1,
                'day' => $day,
                'time' => TIMESTAMP
            );      
            if (pdo_tableexists('yingshi_byy_agent_site_member')) {
                $url="http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
                preg_match("#http://(.*?)\.#i",$url,$match);
                $site_name = $match['1'];
                $site_name = pdo_get('yingshi_byy_agent_site_member',array('site_name'=>$site_name));
                if ($site_name) {
                   $data['site_name'] = $match['1'];
                } 
            }           
            pdo_insert('yingshi_byy_vip_video_order',$data);
            mc_credit_update($member['uid'], 'credit1', -$fee, array($member['uid'], '视频会员开通-'.$fee.'积分','super_mov'));
            if ($member['end_time']) { 
                pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days",$member['end_time'])), array('openid' => $data['openid'],'uniacid'=>$data['uniacid']));
                $time = date('Y-m-d H:i:s',strtotime("+".$day." days",$member['end_time']));
            }else{                      
                pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days")), array('openid' => $data['openid'],'uniacid'=>$data['uniacid']));
                $time = date('Y-m-d H:i:s',strtotime("+".$day." days"));
            } 
            if($settings['tpl_id']) {
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
                            'value' => date('Y-m-d H:i:s',TIMESTAMP),
                            'color' => '#ff510'
                        ) ,    
                        'keyword4' => array(
                            'value' => $_W['uniaccount']['name'],
                            'color' => '#ff510'
                        ) ,  
                        'keyword5' => array(
                            'value' => $fee.'积分',
                            'color' => '#ff510'
                        ) ,              
                        'remark' => array(
                            'value' => '到期时间：'.$time.'',
                            'color' => '#ff510'
                        ) ,
                    ); 
                    $url = link_url('member');
                    $acc->sendTplNotice($data['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
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
                            'value' => date('Y-m-d H:i:s',TIMESTAMP),
                            'color' => '#ff510'
                        ) ,    
                        'keyword4' => array(
                            'value' => $_W['uniaccount']['name'],
                            'color' => '#ff510'
                        ) ,  
                        'keyword5' => array(
                            'value' => $fee.'积分',
                            'color' => '#ff510'
                        ) ,              
                        'remark' => array(
                            'value' => '会员到期时间'.$time.'请进入后台查看',
                            'color' => '#ff510'
                        ) ,
                    );
                    $acc->sendTplNotice($settings['kf_id'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');   
                
            }
            message('会员兑换成功',link_url('member'),'success');
        }
        if (checksubmit('submit')) {
            $url = link_url('member',array('op'=>'pay','day'=>$day));               
            Header("Location: ".$url); 
            exit();
        }
		
    }
    if ($op == 'my') {          
        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $member['openid'],
        );
        $list = pdo_getall('yingshi_byy_vip_video', $data, array() , '' , 'id DESC'); 
    }
    if ($op == 'shoucang') {            
        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $member['openid'],
            'type' => 'shoucang', 
        );
        $list = pdo_getall('yingshi_byy_vip_video', $data, array() , '' , 'id DESC'); 
    }
    if ($op == 'out_login') {    
       isetcookie('phone','');
       message('退出成功',link_url('member',array('op'=>'login')),'success');
    }
    if ($op == 'card') {
         if (checksubmit()) {               
            $data = array(
                'uniacid' => $_W['uniacid'],
                'pwd' => $_GPC['card'],                 
            );
            $card = pdo_get('yingshi_byy_vip_video_keyword_id', $data, array() , '' , 'id DESC');
            if (!$card) {
                message('兑换码无效','','error');  
            }elseif ($card['status']) {
                message('兑换码已使用','','error');   
            }else{
                $res = pdo_update('yingshi_byy_vip_video_keyword_id', array('status'=>1,'openid'=>$member['openid']), array('id'=>$card['id']));                    
                if($res){
                    if ($member['end_time']) { 
                        pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$card['day']." days",$member['end_time'])), array('openid' => $member['openid'],'uniacid'=>$data['uniacid']));
                        $time = date('Y-m-d H:i:s',strtotime("+".$card['day']." days",$member['end_time']));
                    }else{                      
                        pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$card['day']." days")), array('openid' => $member['openid'],'uniacid'=>$data['uniacid']));
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
                        $url = link_url('member');
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
                    pdo_insert('yingshi_byy_vip_video_order',$data);
                    message('兑换成功',link_url('member'),'success');
                }
            }
        }
    }
    if ($op == 'order') {           
        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $member['openid'],
        );
        $list = pdo_getall('yingshi_byy_vip_video_order', $data, array() , '' , 'id DESC'); 
    }
    if ($op == 'login') {
        if (checksubmit()) {                
            $data = $_GPC['data'];
            $data['password'] = md5($data['password']);
            $data['uniacid'] = $_W['uniacid'];
            if (!$data['phone']) {
              message('请填写手机号','','error');
            }
            if (!$data['password']) {
              message('请填写密码','','error');
            }
            $member = pdo_get('yingshi_byy_vip_video_member', $data); 
            if ($member) {
                $data['old_time'] = TIMESTAMP;
                isetcookie('phone',$member['phone'],3600*24*24); 
                pdo_update('yingshi_byy_vip_video_member', $data,array('id'=>$member['id']));
                // pdo_delete('yingshi_byy_vip_video_member_online',array('id'=>$online_all['0']['id']));
                message('登陆成功',link_url('member'),'success');
            }else{
                message('登陆失败,请输入正确的手机号和密码！','','success');
            }
        }
        include $this->template('login'); 
        exit();      
    }
    if ($op == 'ad') {
        $phone = $_GPC['phone'];
        $code = $_GPC['v_code'];
        if (checksubmit()) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'phone' => $phone,
                'time' => TIMESTAMP, 
                'old_time' => TIMESTAMP,
                'password' => md5($_GPC['password']),
                'openid' => $phone,
            );
        } 
    }
    if ($op == 'create') {
        $phone = $_GPC['phone'];
        $code = $_GPC['v_code'];
        $member = pdo_get('yingshi_byy_vip_video_member', array('phone'=>$phone,'uniacid'=>$_W['uniacid']));
        if ($member) {            
            message('手机号已注册，请直接登录','','error');
        }
        if (checksubmit()) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'phone' => $phone,
                'time' => TIMESTAMP, 
                'old_time' => TIMESTAMP,
                'password' => md5($_GPC['password']),
                'openid' => $phone,
            );
            if (is_weixin() && $_W['oauth_account']['level'] == 4) {
                $data['openid'] = $_W['openid'];
            }
            if (!$_GPC['phone']) {
              message('请填写注册的手机号','','error');
            }
            if (!$_GPC['password']) {
              message('请填写密码','','error');
            } 
            if (!$_GPC['password1']) {
              message('请填写确认密码','','error');
            } 
            if ($_GPC['password'] != $_GPC['password1']) {
                 message('确认密码不正确','','error');
            }
            if ($settings['sms'] != 3) {
               if (!$_GPC['code']) {
                  message('请填写验证码','','error');
                } 
                if ($_GPC['code'] != $code) {
                     message('验证码不正确','','error');
                }
            }            
            if($member && is_weixin()) {
                $res = pdo_update('yingshi_byy_vip_video_member', $data,array('id'=>$member['id'])); 
            }else{
                $res = pdo_insert('yingshi_byy_vip_video_member', $data);
            }            
            $id = pdo_insertid(); 
            if ($res) {
                if (is_weixin() && $_W['oauth_account']['level'] < 4) {
                    isetcookie('phone',$phone,3600*24*24); 
                }
                message('注册成功',link_url('member'),'success');
            }

        } 
        include $this->template('login'); 
        exit();   
    }
    if ($op == 'check') {
        $phone = $_GPC['phone'];
        $member = pdo_get('yingshi_byy_vip_video_member', array('phone'=>$phone,'uniacid'=>$_W['uniacid']));
        if ($member) {
            echo "手机号已注册，请直接登录";
            exit();            
        }
        $settings = $this->module['config'];
        if ($settings['sms'] == 1) {
            $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
            $code = mt_rand(1000,9999); 
            $smsConf = array(
                'key'   => $settings['appkey'], //您申请的APPKEY
                'mobile'    => $phone, //接受短信的用户手机号码
                'tpl_id'    => $settings['smstpl_id'], //您申请的短信模板ID，根据实际情况修改
                'tpl_value' =>'#code#='.$code //您设置的模板变量，根据实际情况修改
            );   
            $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信    
            if($content){
                $result = json_decode($content,true);
                $error_code = $result['error_code'];
                if($error_code == 0){
                    //状态为0，说明短信发送成功
                    isetcookie('v_code',$code,1800); 
                    echo "短信发送成功";
                }else{
                    //状态非0，说明失败
                    $msg = $result['reason'];
                    echo "短信发送失败(".$error_code.")：".$msg;
                }
            }else{
                //返回内容异常，以下可根据业务逻辑自行修改
                echo "请求发送短信失败";
            }
        }
        if ($settings['sms'] == 2) {
            $appkey = $settings['jisuappkey'];//你的appkey
            $code = mt_rand(1000,9999); 
            $mobile = $phone;//手机号 超过1024请用POST
            $content = $settings['jisusmstpl_id'];//utf8
            $content = str_replace("@", $code, $content);
            $url = "http://api.jisuapi.com/sms/send?appkey=".$appkey."&mobile=".$mobile."&content=".$content;
            $result = curlOpen($url);
            $jsonarr = json_decode($result, true);
            if($jsonarr['status'] != 0)
            {
                echo "短信发送失败".$jsonarr['msg'];
                exit();
            }
            $result = $jsonarr['result'];
            isetcookie('v_code',$code,1800); 
            echo "短信发送成功";
        } 
        exit(); 
       
    }     
    if ($op == 'pay') {
        $_W['page']['title'] = '会员支付';
        $acc = WeAccount::create();  
        $settings = $this->module['config']; 
        $card = iunserializer($settings['card']);
        if ($setting['member']['card']) {
            $card = iunserializer($setting['member']['card']);
            $card = $card['card'];
        }
        foreach ($card as $value) {
            $card_day = $value['card_day'];
            $new_card[$card_day] = array(
                'card_day' => $value['card_day'],
                'card_fee' => $value['card_fee'],
            );
        }
        $day = $_GPC['day'];
        $id = $_GPC['id'];  
        $video_id = $_GPC['video_id'];  
        if (is_weixin()) {
           if(empty($member['openid'])){message('非法进入');}  
        }   
        $new_card = $new_card[$day]; 
        if ($new_card) {
            $amount = $new_card['card_fee'];     
        }else{
            $amount = $settings['fee']*$day;
        }     
        if ($id) {
            $order = pdo_fetch("SELECT * FROM " . tablename('yingshi_byy_vip_video_order') . " WHERE id = :id", array(':id' => $id));
            if ($member['openid'] != $order['openid']) {
                message('非法进入');
            }
            $day = $order['day'];
            $snid = $order['tid'];
            $amount = $order['fee'];     
        }else{
            $snid = date('YmdHis') . str_pad(mt_rand(1, 99999),6, '0', STR_PAD_LEFT);
        }   
        if ($_GPC['type'] == 'shang') {
            $amount = $_GPC['fee']; 
        }
        if ($_GPC['type'] == 'charge') { 
            $video = pdo_get('yingshi_byy_vip_video_manage',array('id'=>$video_id));
            $amount = $video['charge']; 
        }
        if($amount < 0.01) {
            message('支付错误, 金额小于0.01'); 
        }        
        if ($_GPC['type'] == 'shang') {        
            
            $data = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $member['openid'],
                'uid' => $member['uid'],
                'tid' => $snid,
                'fee' => $amount,               
                'status' => 0,
                'day' => $day,      
                'time' => TIMESTAMP
            );
            $data['desc'] = '视频打赏';
            $title = '视频打赏';
        }elseif ($_GPC['type'] == 'charge') { 
            $data = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $member['openid'],
                'uid' => $member['uid'],
                'tid' => $snid,
                'fee' => $amount,               
                'video_id' => $video_id,               
                'status' => 0,
                'time' => TIMESTAMP
            );
            $data['desc'] = '视频收费';
            $title = '视频收费';
        }else{
            $data = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $member['openid'],
                'uid' => $member['uid'],
                'tid' => $snid,
                'fee' => $amount,               
                'status' => 0,
                'day' => $day,      
                'time' => TIMESTAMP
            );
            $title = '会员开通';
        }
        if ($setting['member']['site_name']) {
               $data['site_name'] = $setting['member']['site_name']; 
        }
		
        if ($id) {
            pdo_update('yingshi_byy_vip_video_order',$data,array('id'=>$id));
        }else{
            pdo_insert('yingshi_byy_vip_video_order',$data);
            $id = pdo_insertid();
        }
        $params = array(
            'id' => $id,
            'tid' => $snid,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码20位
            'ordersn' => $snid,  //收银台中显示的订单号
            'title' => $title,          //收银台中显示的标题
            'fee' => $amount,      //收银台中显示需要支付的金额,只能大于 0
            'user' => $member['uid'],     //付款用户, 付款的用户名(选填项) 
        );
		
	
        if ($settings['payment'] == 4) {
	        $this->codepay($params);
        }else{
            $this->pay($params);
        }
        exit(); 
    } 
    if ($op == 'share') {
        $acc = WeAccount::create(); 
        $settings = $this->module['config'];
        $member = member($_W['openid']);  
        $uid = $member['uid'];  
        $day = $settings['share_day'];   
        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $member['openid'],
            'uid' => $uid,
            'time' => TIMESTAMP
        );  
        if ($settings['is_credit'] == 1) {
            $share = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yingshi_byy_vip_video_share')." WHERE uniacid = :uniacid AND openid = :openid ", array(':uniacid' => $_W['uniacid'],':openid'=>$_W['openid'])); 
            if ($share >=  $settings['share_click']) {      
                exit();
            } 
        }
        if ($settings['is_credit'] == 2) {
            $share = pdo_fetch("SELECT * FROM ".tablename('yingshi_byy_vip_video_share')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC", array(':uniacid' => $_W['uniacid'],':openid'=>$_W['openid']));
            if (date('Y-m-d') == date('Y-m-d',$share['time'])) {        
                exit();
            } 
        }
        pdo_insert('yingshi_byy_vip_video_share',$data);
        $data = array(
            'uniacid' => $_W['uniacid'],
            'openid' => $_W['openid'],
            'uid' => $uid,
            'tid' => '分享营销',
            'fee' => $fee,              
            'status' => 1,
            'day' => $day,
            'time' => TIMESTAMP
        );                  
        pdo_insert('yingshi_byy_vip_video_order',$data);
        if ($member['end_time']) { 
            pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days",$member['end_time'])), array('openid' => $data['openid'],'uniacid'=>$data['uniacid']));
            $time = date('Y-m-d H:i:s',strtotime("+".$day." days",$member['end_time']));
        }else{                      
            pdo_update('yingshi_byy_vip_video_member', array('is_pay'=>1,'end_time'=>strtotime("+".$day." days")), array('openid' => $data['openid'],'uniacid'=>$data['uniacid']));
            $time = date('Y-m-d H:i:s',strtotime("+".$day." days"));
        } 
        if($settings['tpl_id']) {
            $data = array(
                'first' => array(
                    'value' => '您好,'.$member['nickname'].'获得'.$day.'天会员',
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
                    'value' => '到期时间'.$time,
                    'color' => '#ff510'
                ) ,
            );
            $url = link_url('member');
            $acc->sendTplNotice($member['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
            $data = array(
                'first' => array(
                    'value' => $member['nickname'].'恭喜！开通了'.$day.'天会员',
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
                    'value' => '到期时间'.$time.'请进入后台查看',
                    'color' => '#ff510'
                ) ,
            );                  
            $acc->sendTplNotice($settings['kf_id'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
        }
        exit();      
    }  
    include $this->template('news/member'); 