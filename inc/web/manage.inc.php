<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;   
    $acc = WeAccount::create();
    $settings = $this->module['config'];    
    $op = $_GPC['op'] ? $_GPC['op'] : 'list';           
    $category = pdo_fetchall("SELECT * FROM ".tablename('yingshi_byy_vip_video_category')." WHERE uniacid = :uniacid AND parentid = :parentid AND status = :status ORDER BY parentid ASC, displayorder ASC, id ASC ", array(':uniacid'=>$_W['uniacid'],':parentid'=>0,':status'=>0), 'id'); 
    $parent = array();
    $children = array(); 
    if (!empty($category)) {
        $children = '';
        foreach ($category as $cid => $cate) {
            if (!empty($cate['parentid'])) {
                $children[$cate['parentid']][] = $cate;
            } else {
                $parent[$cate['id']] = $cate;
            }
        }
    } 
    if ($op == 'list') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-180 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        );
        if ($_GPC['keyword']) {
            $where .= ' AND title LIKE :keyword OR title = :keyword '; 
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_manage') . $where , $params);
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_manage').$where.' ORDER BY sort DESC , time DESC , id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;          
        $list = pdo_fetchall($sql, $params, 'id');          
    }
    if ($op == 'record') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime AND length(title) <> 0 ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        );

        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video') . $where .' GROUP BY video_url ' , $params);
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT *,count(video_url) as num FROM '.tablename('yingshi_byy_vip_video').$where.' GROUP BY video_url ORDER BY num DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize; 
        $list = pdo_fetchall($sql, $params, 'id');          
    }
    if ($op == 'single') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime AND length(title) <> 0 ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        );

        if ($_GET['openid']) {
            $where .= ' AND openid = :openid ';
            $params[':openid'] = $_GET['openid'];
        }       
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video') . $where , $params);
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video') . $where . ' ORDER BY id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;         
        $list = pdo_fetchall($sql, $params, 'id');
    }
    if ($op == 'comment') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        );
        if ($_GPC['openid']) {
            $where .= ' AND openid = :openid ';
            $params[':openid'] = $_GPC['openid'];
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_message') . $where , $params);
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_message').$where.' ORDER BY id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;         
        $list = pdo_fetchall($sql, $params, 'id');          
    }
    if ($op == 'forum') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $starttime = empty($_GPC['time']['start']) ? strtotime('-90 days') : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['time']['end']) + 86399;
        $where = ' WHERE uniacid = :uniacid AND time >= :starttime AND time <= :endtime ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        );
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_forum') . $where , $params);
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_forum').$where.' ORDER BY id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;         
        $list = pdo_fetchall($sql, $params, 'id');          
    }
    if ($op == 'forum_huifu') {
        $id = $_GPC['id'];
        $forum = pdo_get('yingshi_byy_vip_video_forum', array('id' => $id));
        $member = member($forum['openid']);
        if (checksubmit()) {
            $res = pdo_update('yingshi_byy_vip_video_forum', array('video_url'=>$_GPC['video_url']) , array('id'=>$id));
            if ($res) {
                $data = array(
                    'first' => array(
                        'value' => '您好,'.$member['nickname'],
                        'color' => '#ff510'
                    ) ,
                    'keyword1' => array(
                        'value' => '您求的片子已经找到',
                        'color' => '#ff510'
                    ) ,
                    'keyword2' => array(
                        'value' => '求片提醒',
                        'color' => '#ff510'
                    ) ,                    
                    'remark' => array(
                        'value' => '感谢支持,点击详情直接观看', 
                        'color' => '#ff510'
                    ) ,
                );
                $url = $_GPC['video_url'];
                $acc->sendTplNotice($forum['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
                message('发送成功',$this->createWebUrl('manage',array('op'=>'forum')),'success');
            }
        }
    }
    if ($op == 'forum_del') {
        $id = $_GPC['id'];
        $res = pdo_delete('yingshi_byy_vip_video_forum', array('id'=>$id));
        if($res){
            message('删除成功！',$this->createWebUrl('manage',array('op'=>'forum')),'success');
        }
    } 
    if ($op == 'add') {
        $id = $_GPC['id'];
        if ($id) {              
            $item = pdo_get('yingshi_byy_vip_video_manage', array('id'=>$id)); 
            $pcate = $item['cid'];
            $ccate = $item['pid'];
        }               
        if (checksubmit()) {
            $data = $_GPC['data'];
            $data['cid'] = intval($_GPC['category']['parentid']);
            $data['pid'] = intval($_GPC['category']['childid']);    
            $data['thumb'] = $_GPC['thumb'];            
            $data['uniacid'] = $_W['uniacid'];          
            $data['time'] = TIMESTAMP;
            foreach($_GPC['link'] as $k => $v) {
            $v = trim($v);                
            if(empty($v)) continue;
                $video_url[] = array( 
                    'title' => $_GPC['title'][$k],                   
                    'link' => $v,        
                );                
            }       
            $data['video_url'] = iserializer($video_url); 
            $keyword = $data['keyword'];
            if (!empty($keyword)) {
                $rule['uniacid'] = $_W['uniacid'];
                $rule['name'] = '视频：' . $data['title'] . ' 自定义密码触发';
                $rule['module'] = 'reply';
                $rule['status'] = 1;
                $rule['containtype'] = 'basic,';
                $reply['uniacid'] = $_W['uniacid'];
                $reply['module'] = 'reply';
                $reply['content'] = $data['keyword'];
                $reply['type'] = 1;
                $reply['status'] = 1;               
            }       
                                    
            if ($item) {
                pdo_update('yingshi_byy_vip_video_manage',$data,array('id'=>$id));
                if (empty($keyword)) {
                    pdo_delete('rule', array('id' => $item['rid'], 'uniacid' => $_W['uniacid']));
                    pdo_delete('rule_keyword', array('rid' => $item['rid'], 'uniacid' => $_W['uniacid']));
                    pdo_delete('basic_reply', array('rid' => $item['rid']));
                    pdo_update('yingshi_byy_vip_video_manage',array('rid'=>''),array('id'=>$item['id']));
                }elseif($item['rid']){
                    pdo_update('rule', $rule,array('id'=>$item['rid']));
                    pdo_update('rule_keyword', $reply,array('rid'=>$item['rid']));
                    $reply_url =  link_url('index',array('mov'=>'detail','id'=>$item['id'],'pwd'=>$data['password'])); 
                    $li['content'] = '密码：'.$data['password'].'<br><a href="'.$reply_url.'">点击直达</a>';       
                    pdo_update('basic_reply', $li,array('rid'=>$item['rid']));                  
                }elseif($keyword){
                    pdo_insert('rule', $rule);
                    $rid = pdo_insertid();
                    $reply['rid'] = $rid;                   
                    pdo_insert('rule_keyword', $reply);
                    $li['rid'] = $rid;  
                    $reply_url =  link_url('index',array('mov'=>'detail','id'=>$item['id'],'pwd'=>$data['password'])); 
                    $li['content'] = '密码：'.$data['password'].'<br><a href="'.$reply_url.'">点击直达</a>';           
                    pdo_insert('basic_reply', $li);
                    pdo_update('yingshi_byy_vip_video_manage',array('rid'=>$rid),array('id'=>$id));
                }
            }else{              
                $res = pdo_insert('yingshi_byy_vip_video_manage', $data);
                $id = pdo_insertid();

                if (!empty($keyword)) {                 
                    pdo_insert('rule', $rule);
                    $rid = pdo_insertid();
                    $reply['rid'] = $rid;                   
                    pdo_insert('rule_keyword', $reply);
                    $li['rid'] = $rid;
                    $reply_url =  link_url('index',array('mov'=>'detail','id'=>$item['id'],'pwd'=>$data['password']));
                    $li['content'] = '密码：'.$data['password'].'<br><a href="'.$reply_url.'">点击直达</a>';                   
                    pdo_insert('basic_reply', $li);
                    pdo_update('yingshi_byy_vip_video_manage',array('rid'=>$rid),array('id'=>$id)); 
                }                   
            }               
            message('更新成功',$this->createWebUrl('manage'),'success');
        }           
    }
    if ($op == 'huoqu') {
        $url = $_GPC['url'];
        $url = explode('http://www.360kan.com', $url);          
        $data = pc_caiji_detail($url['1']);
        $data = $data['0'];
        echo json_encode($data);    
        exit(); 
    }
    if ($op == 'piliang') {
        $piliang = nl2br($_GPC['piliang']); 
        foreach ($data as $key => $value) {
                var_dump($value);
        }   
        include $this->template('piliang');     
        exit(); 
    }
    if ($op == 'delete') {
        $id = $_GPC['id'];
        $row = pdo_fetch("SELECT rid FROM ".tablename('yingshi_byy_vip_video_manage')." WHERE id = :id", array(':id' => $id));
        if (!empty($row['rid'])) {
            pdo_delete('rule', array('id' => $row['rid'], 'uniacid' => $_W['uniacid']));
            pdo_delete('rule_keyword', array('rid' => $row['rid'], 'uniacid' => $_W['uniacid']));
            pdo_delete('basic_reply', array('rid' => $row['rid']));
        }
        $res = pdo_delete('yingshi_byy_vip_video_manage', array('id'=>$id));        
        if($res){
            message('删除成功！',$this->createWebUrl('manage'),'success');
        }
    }
    if ($op == 'shenhe') {
        $id = $_GPC['id'];
        $res = pdo_update('yingshi_byy_vip_video_message',array('status'=>1), array('id'=>$id));
        if($res){
            message('审核成功！',$this->createWebUrl('manage',array('op'=>'comment')),'success');
        }
    }
    if ($op == 'comment_del') {
        $id = $_GPC['id'];
        $res = pdo_delete('yingshi_byy_vip_video_message', array('id'=>$id));
        if($res){
            message('删除成功！',$this->createWebUrl('manage',array('op'=>'comment')),'success');
        }
    }
    include $this->template('manage');   