<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');

 global $_W, $_GPC;

    $ad = ad();
    // var_dump($ad);
    $op = $_GPC['op'] ? $_GPC['op'] : 'list';
    if($op == 'list'){ 
    $list = pdo_fetchall("SELECT * FROM " . tablename('yingshi_byy_vip_video_ad') . " WHERE uniacid=:uniacid $condition ORDER BY sort DESC,id DESC", array(":uniacid" => $_W['uniacid']));
    }elseif ($op == 'post'){
        $id = intval($_GPC['id']);
        $adv = pdo_fetch("select * from " . tablename('yingshi_byy_vip_video_ad') . " where id=:id and uniacid=:uniacid limit 1", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        if(checksubmit('submit')){            
            $data = array(
                        'uniacid' => $_W['uniacid'],
                        'sort' => $_GPC['sort'],                        
                        'title' => $_GPC['title'],                        
                        'thumb' => $_GPC['thumb'], 
                        'second' => $_GPC['second'],
                        'link' => $_GPC['link'],                        
                        'status' => $_GPC['status'],                        
                        'type' => $_GPC['type'],                        
                        'insert' => $_GPC['insert'],                        
            ); 
            $data['end_time'] = strtotime($_GPC['end_time']);  
            if (!empty($id)) {
                pdo_update('yingshi_byy_vip_video_ad', $data, array('id' => $id));
            } else {
                pdo_insert('yingshi_byy_vip_video_ad', $data);
                $id = pdo_insertid();
            }
            message('更新成功！', $this->createWebUrl('ad', array('op' => 'list')), 'success');
        }
        
    } elseif ($op == 'delete') {
        $id = intval($_GPC['id']);
        $adv = pdo_fetch("SELECT id  FROM " . tablename('yingshi_byy_vip_video_ad') . " WHERE id = ".$id." AND uniacid=" . $_W['uniacid'] . "");
        if (empty($adv)) {
            message('抱歉，不存在或是已经被删除！', $this->createWebUrl('hdp', array('op' => 'display')), 'error');
        }
        pdo_delete('yingshi_byy_vip_video_ad', array('id' => $id));
        message('删除成功！', $this->createWebUrl('ad', array('op' => 'list')), 'success');
    }
    include $this->template('ad'); 