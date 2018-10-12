<?php 
/**
 */
defined('IN_IA') or exit('Access Denied');
 global $_W, $_GPC;
    $op = $_GPC['op'] ? $_GPC['op'] : 'list';

    if($op == 'list'){        
        if(checksubmit('submit')){
            if(!empty($_GPC['sort'])){
                foreach($_GPC['sort'] as $key=>$d){
                    pdo_update('yingshi_byy_vip_video_hdp', array('sort'=>$d), array('id'=>$_GPC['id'][$key]));
                }
                message('批量更新排序成功！', $this->createWebUrl('huandeng', array('op' => 'list')), 'success');
            }
        }
        $type = type();
        $list = pdo_fetchall("SELECT * FROM " . tablename('yingshi_byy_vip_video_hdp') . " WHERE uniacid =:uniacid $condition ORDER BY sort DESC,id DESC", array(":uniacid" => $_W['uniacid']));

    } elseif ($op == 'post'){
        $id = intval($_GPC['id']);
        $adv = pdo_fetch("select * from " . tablename('yingshi_byy_vip_video_hdp') . " where id=:id and uniacid=:uniacid limit 1", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        if(checksubmit('submit')){            
            $data = array(
                        'uniacid' => $_W['uniacid'],
                        'sort' => $_GPC['sort'],
                        'title' => $_GPC['title'],
                        'thumb' => $_GPC['thumb'], 
                        'type' => $_GPC['type'], 
                        'link' => $_GPC['link'],
                        'out_link' => $_GPC['out_link']
            );  
            $link = explode('http://www.360kan.com', $data['link']);                
            if (count($link) == 1) {
                $data['link'] = $data['link'];
            }else{
                $data['link'] = $link['1'];
            }                
            if (!empty($id)) {
                pdo_update('yingshi_byy_vip_video_hdp', $data, array('id' => $id));
            } else {
                pdo_insert('yingshi_byy_vip_video_hdp', $data);
                $id = pdo_insertid();
            }
            message('更新幻灯片成功！', $this->createWebUrl('huandeng', array('op' => 'list')), 'success');
        }
        
    } elseif ($op == 'delete') {
        $id = intval($_GPC['id']);
        $adv = pdo_fetch("SELECT id  FROM " . tablename('yingshi_byy_vip_video_hdp') . " WHERE id = ".$id." AND uniacid=" . $_W['uniacid'] . "");
        if (empty($adv)) {
            message('抱歉，幻灯片不存在或是已经被删除！', $this->createWebUrl('huandeng', array('op' => 'display')), 'error');
        }
        pdo_delete('yingshi_byy_vip_video_hdp', array('id' => $id));
        message('幻灯片删除成功！', $this->createWebUrl('huandeng', array('op' => 'list')), 'success');
    }
    include $this->template('hdp'); 