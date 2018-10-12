<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
    $op = $_GPC['op'];
    if(!pdo_tableexists('yingshi_byy_video_pc_site')){
        exit();
    }
    $weixin_member = member($_W['openid']); 
    $settings = $this->module['config'];
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
        if ($weixin_member['phone']) {
            message('绑定失败,您的手机号已绑定过或者您的微信已绑定过手机号！','','error'); 
        }
        if ($member) {  
            $data['openid'] = $_W['openid'];
            if ($member['is_pay'] == 1) {
                $end_time = $member['end_time'] - TIMESTAMP;
                $data['end_time'] = $weixin_member['end_time'] + $end_time;
                if ($weixin_member['is_pay'] == 1) {
                    $end_time = $member['end_time'] - TIMESTAMP;
                    $data['end_time'] = $weixin_member['end_time'] + $end_time;
                }else{
                    $data['is_pay'] = 1;
                    $data['end_time'] = $member['end_time']; 
                }
            }
            pdo_update('yingshi_byy_vip_video_member',$data,array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid'])); 
            pdo_delete('yingshi_byy_vip_video_member',array('id'=>$member['id']));         
            message('绑定成功',link_url('member'),'success');
        }else{
            message('绑定失败,请输入正确的手机号和密码！','','error'); 
        }
    } 
    if ($op == 'jiechu') {
          pdo_update('yingshi_byy_vip_video_member',array('phone'=>'','password'=>''),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
          message('解除绑定成功',link_url('member'),'success'); 
    }
    include $this->template('bangding'); 