<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
  global $_W, $_GPC; 
	
    $acc = WeAccount::create(); 

    $setting = setting();
    $setting = $setting['member']; 
    $settings = $this->module['config'];
    if ($setting) {
        $setting = iunserializer($setting['setting']);
        $settings['site_title'] = $setting['site_title'];
        $settings['logo'] = $setting['logo'];
        $settings['subscribe_title'] = $setting['subscribe_title'];
        $settings['subscribe_url'] = $setting['subscribe_url'];
        $settings['index_gg'] = $setting['index_gg'];
        $settings['copyright'] = $setting['copyright'];
        $settings['guanzhu_ewm'] = $setting['guanzhu_ewm'];
    }

    if ($_GPC['do'] == 'search' && !$_GPC['eid']) {
        $modules_bindings = pdo_get('modules_bindings', array('do' => $_GPC['do'],'module'=>$_GPC['m'])); 
        $url = $_W['siteroot'] . 'app/index.php?i='.$_W['uniacid'].'&c=entry&eid='.$modules_bindings['eid'];
        Header("Location: ".$url);
        exit(); 
    }
    if ($settings['is_pc'] == 2 && is_weixin()) {       
        $url = link_url('index'); 
        Header("Location: ".$url);
        exit();  
    }       
    $member = member($_W['openid']); 
	
    $hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$_GPC['do']), array() , '' , 'sort DESC , id DESC');
     $category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = :uniacid AND parentid = :parentid AND status = :status ORDER BY parentid ASC, displayorder ASC, id ASC ", array(':uniacid'=>$_W['uniacid'],':parentid'=>0,':status'=>0), 'id');
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
    $where = ' WHERE uniacid = :uniacid AND display != 1 ';
    $params[':uniacid'] = $_W['uniacid'];
    $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_manage').$where.' ORDER BY id DESC LIMIT 50';         
    $video = pdo_fetchall($sql, $params, 'id');             
    $op = $_GPC['op'] ? $_GPC['op'] : 'search';
    $key = $_GPC['key'];

    if ($key) { 
        $where = ' WHERE uniacid = :uniacid ';          
        $where .= ' AND title LIKE :title ';
        $params[':uniacid'] = $_W['uniacid'];   
        $params[':title'] = "%".$_GPC['key']."%";             
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_manage').$where.' ORDER BY id DESC ';         
        $search = pdo_fetchall($sql, $params, 'id');
        if ($settings['ziyuan'] == 1) { 
            $url = "http://caiji.thecook.com.cn/caiji/api.php?key=".$key;
            $response = ihttp_get($url); 
            $list = json_decode($response['content'],true);
        }else{                      
            $list = caiji_list($key);
        } 
        
    }
    if ($op == 'json') {
        $url = 'https://video.360kan.com/suggest.php?kw='.$_GPC['kw'];
        $url = ihttp_get($url);
        $url =  $url['content']; 
        echo $url;
        exit();
    }
    if (checksubmit()) {
        if ($settings['is_qiupian_members'] == 1 && $member['is_pay'] == 0 ) {
            message('只有会员才能求片！','','error');
        }
        $forum = pdo_getall('yingshi_byy_vip_video_forum', array('uniacid' => $_W['uniacid'],'openid' => $member['openid'],), array() , '' , 'id DESC');
        $title = $_GPC['title'];
        $data = array(
            'uniacid' => $_W['uniacid'],
            'time' => TIMESTAMP,
            'openid' => $member['openid'],            
            'title' => $title, 
        );      
        $forum_time = TIMESTAMP - $forum['0']['time'];
        if (!$title) {
            message('请输入片名','','error');
        }
        if ($forum_time < 3600 * 24) {
           message('每天只能一次求片！！','','error');
        }
        
        $res = pdo_insert('yingshi_byy_vip_video_forum', $data);
        if ($res) {
            $data = array(
                'first' => array(
                    'value' => '会员,'.$member['nickname'].'求片提醒',
                    'color' => '#ff510'
                ) ,
                'keyword1' => array(
                    'value' => '片名：'.$title,
                    'color' => '#ff510'
                ) ,
                'keyword2' => array(
                    'value' => '求片提醒',
                    'color' => '#ff510'
                ) ,                   
                'remark' => array(
                    'value' => '请进入后台查看',
                    'color' => '#ff510'
                ) ,
            );
            $acc->sendTplNotice($settings['kf_id'], $settings['tpl_id'], $data,  $topcolor = '#FF683F');
            message('求片成功','','success');  
        }
        
    }
    include $this->template('search'); 

