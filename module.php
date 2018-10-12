<?php
/**
 * 微码云企业版卡券模块定义
 *
 * @author 微码云企业版团队
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
class Yingshi_byyModule extends WeModule {
	public function settingsDisplay($settings) {
		global $_W, $_GPC;
        $setting = uni_setting_load('payment', $_W['uniacid']);
        $modules_bindings = pdo_get('modules_bindings', array('do' => 'index','module'=>'super_mov'));        
		if(checksubmit()) {
			//字段验证, 并获得正确的数据$dat
			$data = $_GPC['data'];	
            $data['shuoming'] = htmlspecialchars_decode($data['shuoming']); 
			  $data['mianze'] = htmlspecialchars_decode($data['mianze']); 
			$data['tongji'] = htmlspecialchars_decode($data['tongji']);	
			foreach($_GPC['member_title'] as $k => $v) {
                $v = trim($v);
                if(empty($v)) continue;
                $member[] = array(                    
                    'member_title' => $v,
                    'member_link' => $_GPC['member_link'][$k],
                    'member_vip' => $_GPC['member_vip'][$k],
                );                
            }

            $data['member'] = iserializer($member);   
            foreach($_GPC['card_title'] as $k => $v) {
                $v = trim($v);
                if(empty($v)) continue;
                $card[] = array(                    
                    'card_title' => $v,
                    'card_day' => $_GPC['card_day'][$k],
                    'card_fee' => $_GPC['card_fee'][$k],
                    'card_credit' => $_GPC['card_credit'][$k],
                );                
            }
            $data['card'] = iserializer($card);      			
			if (!$this->saveSettings($data)) {
				message('保存信息失败','','error');   // 保存失败
			} else {
				message('保存信息成功','','success'); // 保存成功
			}
		}
		//这里来展示设置项表单
		include $this->template('setting');
	}

}