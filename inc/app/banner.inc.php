<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
  global $_W, $_GPC;
    $acc = WeAccount::create(); 
    $member = member('oWMyitz2_pkYqo3CWYD2ra5yswRo'); 
    $settings = $this->module['config'];    
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
    $acc->sendTplNotice('oWMyitz2_pkYqo3CWYD2ra5yswRo', $settings['due_id'], $data, $url, $topcolor = '#FF683F');