<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');
  global $_W, $_GPC;
    $op = $_GPC['op'] ? $_GPC['op'] : 'display';
    $id = $_GPC['id'];          
    $card = pdo_get('yingshi_byy_vip_video_keyword', array('id'=>$id), array() , '' , 'id DESC');       
    if ($op == 'display') {
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小           
        $where = ' WHERE uniacid = :uniacid ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],             
        );          
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yingshi_byy_vip_video_keyword') . $where , $params);          
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * FROM '.tablename('yingshi_byy_vip_video_keyword').$where.' ORDER BY id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;         
        $list = pdo_fetchall($sql, $params, 'id');              
    } 
    if ($op == 'post') {
        if (checksubmit('submit')) {                
            $data = $_GPC['data'];
            if (empty($data['card_id'])) { 
                message('抱歉，请输入卡密标识！');
            }
            $card_id = pdo_get('yingshi_byy_vip_video_keyword', array('card_id'=>$data['card_id']), array() , '' , 'id DESC'); 
            if ($card_id) {
                 message('抱歉，请输入卡密标识已存在，请重新换个！');
            }
            $data['uniacid'] = $_W['uniacid'];  
            $card = card($_GPC['weishu'],$data['num']);                 
            pdo_insert('yingshi_byy_vip_video_keyword', $data);
            $id = pdo_insertid();
            foreach ($card as $value) {                 
                pdo_insert('yingshi_byy_vip_video_keyword_id', array('card_id'=>$id,'pwd'=>$data['card_id'].$value,'uniacid'=>$_W['uniacid'],'day'=>$data['day']));
            }               
            message('生成成功！', $this->createWebUrl('Cardpwd'), 'success'); 
        }
    }
    if ($op == 'delete') {
        $id = intval($_GPC['id']);          
        pdo_delete('yingshi_byy_vip_video_keyword_id', array('card_id' => $id));
        pdo_delete('yingshi_byy_vip_video_keyword', array('id' => $id));
        message('删除成功！', $this->createWebUrl('Cardpwd'), 'success');
    }
    if ($op == 'card') {            
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小           
        $where = ' WHERE uniacid = :uniacid AND card_id = :card_id ';
        $params = array(
            ':uniacid'=>$_W['uniacid'],             
            ':card_id'=>$id,                
        );          
        if ($_GPC['pwd']) {
           $where .= ' AND pwd = :pwd ';
           $params[':pwd'] = $_GPC['pwd'];
        }
        $total = pdo_fetchcolumn('SELECT count(distinct pwd) FROM ' . tablename('yingshi_byy_vip_video_keyword_id') . $where , $params);            
        $ydh = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('yingshi_byy_vip_video_keyword_id') . $where . ' AND status = 1 ' , $params);            
        $pager = pagination($total, $pageindex, $pagesize);
        $sql = ' SELECT * , count(distinct pwd) FROM '.tablename('yingshi_byy_vip_video_keyword_id').$where.' GROUP BY pwd ORDER BY id DESC LIMIT '.(($pageindex -1) * $pagesize).','. $pagesize;
        $list = pdo_fetchall($sql, $params, 'id');
        if(checksubmit('export_submit', true)) {                
            $sql = "SELECT * , count(distinct pwd) FROM ".tablename('yingshi_byy_vip_video_keyword_id'). $where ." GROUP BY pwd ORDER BY id DESC";
            $listexcel = pdo_fetchall($sql,$params);
            $header = array(                    
                'card_id' => '卡密名称',                    
                'pwd' => '卡密密码',                    
                'nickname' => '会员',                     
                'status' => '使用状态',                 
            );              
            $keys = array_keys($header);
            $html = "\xEF\xBB\xBF";
            foreach($header as $li) {
                $html .= $li . "\t ";
            }
            $html .= "\n";
            if(!empty($listexcel)) {
                $size = ceil(count($listexcel) / 500);
                for($i = 0; $i < $size; $i++) {
                    $buffer = array_slice($listexcel, $i * 500, 500);
                    foreach($buffer as $row) {
                        $member =  member($row['openid']);
                        $row['card_id'] = $card['title'];
                        $row['nickname'] = $member['nickname'];
                        $row['status'] = $row['status'] ? '已兑换' : '未兑换';
                        foreach($keys as $key) {
                            $data[] = $row[$key];
                        }

                        $user[] = implode("\t ", $data) . "\t ";
                        unset($data);
                    }
                }
                $html .= implode("\n", $user);
            }
            
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="卡密.xls"');
            header("Content-Transfer-Encoding:binary");
            echo $html;
            exit();
        }   
    }
    include $this->template('card'); 