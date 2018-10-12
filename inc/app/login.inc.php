 <?php
/**
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;     
    $settings = $this->module['config'];  
    $op = $_GPC['op'] ? $_GPC['op'] : 'login'; 
    $member = member($_W['openid']);   
    
    include $this->template('login');