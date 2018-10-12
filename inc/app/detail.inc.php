<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
 
    $op = $_GPC['op'];
    $id = $_GPC['id'];  
    $acc = WeAccount::create(); 
    $password = $_COOKIE['password'];   
    $info = $acc->fansQueryInfo($_W['openid']);      
    $settings = $this->module['config'];
    if ($settings['is_pc'] == 2 && is_weixin()) {       
        $url = link_url('index'); 
        Header("Location: ".$url);
        exit();  
    }
    if (pdo_tableexists('yingshi_byy_video_pc_site') && !is_weixin()) {
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
    if (pdo_tableexists('yingshi_byy_video_pc_site') && $member['is_pay'] == 0 && !is_weixin() && !$openid) {
        $openid = $_W['clientip'];
    } 
    if (pdo_tableexists('yingshi_byy_video_pc_site') && $member['is_pay'] == 0 && is_weixin() && !$openid && $_W['oauth_account']['level'] < 4) {
        $openid = $_W['clientip'];
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
    $ad = pdo_fetch("SELECT * FROM " . tablename('yingshi_byy_vip_video_ad') . " WHERE uniacid = :uniacid  AND status = 0 ORDER BY rand() DESC LIMIT 1",array(':uniacid'=>$_W['uniacid']),'id');
    if (TIMESTAMP > $ad['end_time']) {
        pdo_update('yingshi_byy_vip_video_ad',array('status'=>1),array('id'=>$ad['id']));
    } 
    if (!pdo_tableexists('yingshi_byy_video_pc_site')) {
        if(!is_weixin()){ 
            message('暂时只支持微信,请使用微信观看视频');  
        } 
    }   
    if ($settings['is_pc'] == 1) {
        if(!is_weixin()){ 
            message('暂时只支持微信,请使用微信观看视频');  
        }  
    }
    
    //if (!$openid) message('暂时只支持微信,请使用微信观看视频'); 
    $hdp = pdo_getall('yingshi_byy_vip_video_hdp', array('uniacid'=>$_W['uniacid'],'type'=>$_GPC['do']), array() , '' , 'sort DESC , id DESC');
     $category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = :uniacid AND parentid = :parentid AND status = :status ORDER BY parentid ASC, displayorder ASC, id ASC ", array(':uniacid'=>$_W['uniacid'],':parentid'=>0,':status'=>0), 'id');
    $url = $_GPC['url'];
    $yurl = $_GPC['url'];
    if ($settings['everyday_free_num'] == 1) {
        $num = pdo_fetchcolumn("SELECT COUNT(*),time FROM " . tablename('yingshi_byy_vip_video') . " WHERE uniacid = :uniacid AND openid = :openid AND time >= :firsttime AND time <= :lasttime ", array(':uniacid' => $_W['uniacid'],':openid' => $openid,':firsttime'=>strtotime(date('Y-m-d 00:00:00')),':lasttime'=>strtotime(date('Y-m-d 23:59:59'))));
    }else{
        $num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('yingshi_byy_vip_video') . " WHERE uniacid = :uniacid AND openid = :openid ", array(':uniacid' => $_W['uniacid'],':openid' => $openid));
    }
    if ($num >= $settings['free_num'] && $member['is_pay'] == 0) {
        message($settings['warn_font'] ? $settings['warn_font'] : '您的免费观看次数已用完，请点击确定开通会员，无限制观看',link_url('member',array('op'=>'open')),'error'); 
    } 
    $jilu = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video')." WHERE uniacid = :uniacid AND openid = :openid ORDER BY id DESC LIMIT 10", array(':uniacid'=>$_W['uniacid'],':openid'=>$openid));
    $video_id = $_GPC['url'] ? $_GPC['url'] : $id;
    $comment = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_message')." WHERE uniacid = :uniacid AND video_id = :video_id AND status = 1 ORDER BY id DESC LIMIT 10", array(':uniacid'=>$_W['uniacid'],':video_id'=>$video_id)); 
    $shoucang = pdo_get('yingshi_byy_vip_video',array('uniacid' => $_W['uniacid'],'yvideo_url' => $video_id,'openid' => $openid,'type' => 'shoucang'            
    )); 
    if ($id) {        
        $content = pdo_fetch("SELECT * FROM ".tablename('yingshi_byy_vip_video_manage')." WHERE id=:id",array(':id'=>$id));
        if (checksubmit('submit')) {
            if ($_GPC['password'] == $content['password']) {
                setcookie("password",$_GPC['password'],time()+2*7*24*3600);
                $url = $this->createMobileUrl('detail',array('id'=>$id));
                Header("Location: ".$url);
            }else{
                message('密码不正确，请重新输入','','error');
            }           
        }
        $click = $content['click'];
        $juji = iunserializer($content['video_url']);
        if (count($juji) < 2) {
            $url = $juji['0']['link'];
        }else{
            $url = $_GPC['url'];
            if (!$url) {
                $url = $juji['0']['link'];
            }
        }
        $is_charge = pdo_get('yingshi_byy_vip_video_order',array('uniacid'=>$_W['uniacid'],'video_id'=>$id));
        $is_vip = is_vip($id,'id');        
        pdo_update('yingshi_byy_vip_video_manage', array('click +=' => 1), array('id' => $id));                 
    }elseif ($op == 'yule' || $op == 'gaoxiao') {
        $url = kan360($url);        
        $content['title'] = $url['title'];      
        $content['thumb'] = $url['thumb'];      
        $url = $url['mp4'];
        // $tuijian = pc_caiji_detail_tuijian($url);
    }else{          
        if ($settings['ziyuan'] == 1) {  
           
            $link = $content['site']; 
            
            if (count($link) > 1) {

                $url = $link['1'];
                $site_title = $link['1'];
            }else{
                $site_title = $link['0'];
            }     
            $playurl = $content['playurl'];
            if ($op == 'dianying') {
                $url = explode("$",$playurl);
                $url = $url['1'];
            }
            if ($op == 'dianshi' || $op == 'dongman') {
                $jishu = $_GPC['jishu'];
                $url_m3u8 = array();
                $url_list = explode("#",$playurl);                    
                foreach ($url_list as $key => $v) {
                   $value = explode("$",$v);
                   $key = $value['0'];
                   $url_m3u8[$key] = array($key,$value['1']);
                } 
                $juji = $url_m3u8;
                if ($jishu) {
                    $url = $url_m3u8[$jishu]['1'];  
                }else{
                    $first_val = reset($url_m3u8);                        
                    $url = $first_val['1'];
                } 
            } 
            if ($op == 'zongyi') {
                $jishu = $_GPC['jishu'];
                $url_m3u8 = array();
                $url_list = explode("#",$playurl);                    
                foreach ($url_list as $key => $v) {
                   $value = explode("$",$v);
                   $key = $value['0'];
                   $url_m3u8[$key] = array($key,$value['1']);
                } 
                $juji = $url_m3u8;
                if ($jishu) {
                    $url = $url_m3u8[$jishu]['1'];  
                }else{
                    $first_val = reset($url_m3u8);                        
                    $url = $first_val['1'];
                } 
            }
        }else{
            $url_time = cache_load('pc_caiji_detail:'.$url);        
            if ((TIMESTAMP - $url_time) > 86400 ) {
                $content = pc_caiji_detail($url,$op);
                $tuijian = pc_caiji_detail_tuijian($url);       
                $daoyan = pc_caiji_detail_daoyan($url); 
                cache_write('pc_caiji_detail:'.$url, TIMESTAMP);                
                cache_write('content:'.$url, $content);  
                cache_write('tuijian:'.$url, $tuijian);  
                cache_write('daoyan:'.$url, $daoyan); 
            }else{
                $content = cache_load('content:'.$url); 
                $tuijian = cache_load('tuijian:'.$url); 
                $daoyan = cache_load('daoyan:'.$url);               
            }       
            $site = site(); 
            $content = $content['0'];   
            $vip_url = $_W['siteurl'];     
            $is_vip = is_vip($vip_url,'url');
            if ($op == 'dianying') {                 
                if ((TIMESTAMP - $url_time) > 86400 ) {
                    $link = caiji_url($url);
                    cache_write('caiji_url:'.$url, $link);
                }else{
                    $link = cache_load('caiji_url:'.$url);
                }           
                if ($_GPC['link']) {
                    $url = $_GPC['link'];
                }else{
                    if (strpos($link['0']['link'], 'qq') && count($link) > 1 && !$settings['tengxun']) {
                        $url = $link['1']['link'];
                        $site_title = $link['1']['title'];
                    }else{
                        $url = $link['0']['link'];
                    }               
                }               
            }
            if ($op == 'dianshi') {             
                $link = dianshi_url($url);
                if ($_GPC['site']) {
                    $site = array_keys($site,$_GPC['site']);  
                }else{
                    if (count($link) > 1) {
                        if (strexists($link['0'], '腾讯') || strexists($link['0'], '华数TV') && !$settings['tengxun']) {
                          $site_title = $link['1'];
                        }elseif (strexists($site_title, '腾讯') || strexists($site_title, '华数TV') ) { 
                          $site_title = $link['2'];
                        }else{
                          $site_title = $link['0'];
                        }            
                    }else{
                        $site_title = $link['0'];
                    }
                    $site = array_keys($site,str_replace('(付费)','',$site_title));
                }      
                $juji = juji_url($_GPC['url'],$site);      
                if ($_GPC['link']) {
                  $url = $_GPC['link'];
                }else{        
                  $url = $juji['0']['link'];         
                } 
            } 
            
            if ($op == 'dongman') {
                $link = dianshi_url($url);
                if ($_GPC['site']) {
                    $site = array_keys($site,$_GPC['site']);  
                }else{
                    if (count($link) > 1) {
                        if (strexists($link['0'], '腾讯') || strexists($link['0'], '华数TV') && !$settings['tengxun']) {
                          $site_title = $link['1'];
                        }elseif (strexists($site_title, '腾讯') || strexists($site_title, '华数TV') ) { 
                          $site_title = $link['2'];
                        }else{
                          $site_title = $link['0'];
                        }            
                    }else{
                        $site_title = $link['0'];
                    }
                    $site = array_keys($site,str_replace('(付费)','',$site_title));
                } 
                $juji = dongman_url($url,$site);
                if ($_GPC['link']) {
                    $url = $_GPC['link'];
                }else{
                    $url = $juji['0']['link'];
                } 
            }
            if ($op == 'zongyi') {
                $link = zongyi_url($url);               
                if ($_GPC['site']) {
                    $site = array_keys($site,$_GPC['site']);  
                }else{
                    if (strexists($link['0']['title'], '腾讯') && count($link) > 1 && !$settings['tengxun']) {                    
                        $site_title = $link['1']['title'];
                    }else{
                        $site_title = $link['0']['title'];
                    }
                    $site = array_keys($site,str_replace('(付费)','',$site_title));
                } 
                $year = $_GPC['year'];
                if ($year) {
                    $ss = '/([\x80-\xff]*)/i';
                    $year = preg_replace($ss,'',$year);
                    $juji = zongyi_juji_url($url,$site,$year);      
                }else{                  
                    $juji = zongyi_juji_url($url,$site);
                }           
                $year = zongyi_year_url($url);
                // var_dump($year); 
                if (!$_GPC['year']) {
                    $_GPC['year'] = $year['0']['date'];
                }           
                if ($_GPC['link']) {
                    $url = $_GPC['link'];
                }else{
                    $url = $juji['0']['link'];
                }
            }
        }
        $click = pdo_fetchcolumn('SELECT * FROM ' . tablename('yingshi_byy_vip_video') . " WHERE uniacid = :uniacid AND yvideo_url = :yvideo_url ",array(':uniacid'=>$_W['uniacid'],':yvideo_url'=>$yurl));             
    }       
    $video = pdo_get('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'video_url'=>$url));
    if (!$video) {
        if ($id) {
            pdo_insert('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'title'=>$content['title'],'video_url'=>$url,'video_id'=>$id,'type'=>$op,'time'=>TIMESTAMP,'share'=>$_GPC['jishu']));
        }else{              
            pdo_insert('yingshi_byy_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'title'=>$content['title'],'video_url'=>$url,'yvideo_url'=>$yurl,'type'=>$op,'time'=>TIMESTAMP,'share'=>$_GPC['jishu']));
        }
    }   
    if ($settings['api']) {     
        if (strexists($url,'zhilian')) {
                $url = explode('&type=zhilian',$url);               
                $api = $url['0'];
        }elseif (strexists($url,'baidu')) {
            
            $api = $settings['baidu_api'].$url; 
        }else{
            $api = $settings['api'].$url.'&link='.$_GPC['link']; 
        }
    }else{
        if ($id) {          
            if (strexists($url,'zhilian')) {
                $url = explode('&type=zhilian',$url);               
                $api = $url['0'];
            }elseif ($settings['baidu_api'] && strexists($url,'baidu')) {
                $api = $settings['baidu_api'].$url;
            }else{
                $api = 'https://cyl.go8goo.com/vip/api.php?url='.$url.'&link='.$_GPC['link'];
            }
        }else{
            $api = 'https://cyl.go8goo.com/vip/vip.php?url='.$url.'&link='.$_GPC['link']; 
        }
    }           
    if ($_GPC['index'] == 1) {
        $id = $_GPC['id'];
        $data = array(
            'uniacid' => $_W['uniacid'], 
            'id' => $id,
        );
        $item = pdo_get('yingshi_byy_vip_video', $data);                 
        include $this->template('news/detail'); 
        exit();
    }       
    include $this->template('news/detail'); 