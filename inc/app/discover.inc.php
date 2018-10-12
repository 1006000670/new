 <?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $settings = $this->module['config'];
    if ($settings['is_pc'] == 2 && is_weixin()) {       
        $url = link_url('index'); 
        Header("Location: ".$url);
        exit();  
    }
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
    $acc = WeAccount::create();     
    $member = member($_W['openid']); 
    $jilu = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC LIMIT 10", array(':uniacid'=>$_W['uniacid'],':openid'=>$_W['openid']));
    $num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('yingshi_byy_vip_video') . " WHERE uniacid = :uniacid AND openid = :openid ", array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $member['openid']               
            ));    
    $acc = WeAccount::create();
    $info = $acc->fansQueryInfo($_W['openid']);  
     $category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = :uniacid AND parentid = :parentid AND status = :status ORDER BY parentid ASC, displayorder ASC, id ASC ", array(':uniacid'=>$_W['uniacid'],':parentid'=>0,':status'=>0), 'id');            
    $hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$_GPC['do']), array() , '' , 'sort DESC , id DESC');
    $record = pdo_fetch("SELECT * FROM ".tablename('yingshi_byy_vip_video')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC", array(':uniacid' => $_W['uniacid'],':openid'=>$member['openid']));
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
    if (checksubmit()) {
        $url = $_GPC['url'];
        $c=explode('m.v.qq',$url);      
        if(count($c)>1){
            $url = 'https://v.qq'.$c['1']; 
        }
        if(!isUrl($url)) message('输入的网页地址错误，请重新输入,检查是否含有http://');
        if ($num >= $settings['free_num'] && $member['is_pay'] == 0) {
            message('您的免费观看次数已用完，请点击确定开通会员，无限制观看',link_url('member',array('op'=>'open')),'error'); 
        }    
        $video = pdo_get('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'video_url'=>$url)); 
        if(!$url) message('请输入链接'); 
        if($video) message('这个视频您之前提交过了，点击确定跳转继续观看',link_url('index',array('mov'=>'detail','url'=>$url,'index'=>1)),'success');
        $res = pdo_insert('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'uid'=>$_W['fans']['uid'],'title'=>$title,'video_url'=>$url,'time'=>TIMESTAMP,'share'=>$_GPC['share'],'index'=>1));
        $video_url = link_url('index',array('mov'=>'detail','url'=>$url,'index'=>1));               
        Header("Location: ".$video_url);
        exit();
    }
    include $this->template('discover'); 