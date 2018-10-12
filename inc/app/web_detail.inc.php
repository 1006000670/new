<?php
/**
 */
defined('IN_IA') or exit('Access Denied');
	global $_W, $_GPC;
		$settings = $this->module['config'];
		$setting = setting1();	
		 $site_name = $this->setting3();
	    $site_name = $site_name['member']; 
	    if ($site_name['setting']) {
	        $site_name = iunserializer($site_name['setting']);
	        $settings['site_title'] = $site_name['site_title'];
	        $settings['logo'] = $site_name['logo'];
	        $settings['subscribe_title'] = $site_name['subscribe_title'];
	        $settings['subscribe_url'] = $site_name['subscribe_url'];
	        $settings['index_gg'] = $site_name['index_gg'];
	        $settings['copyright'] = $site_name['copyright'];
	        $settings['guanzhu_ewm'] = $site_name['guanzhu_ewm'];
	        $settings['free_num'] = $site_name['free_num'];
	    }
		$op = $_GPC['op'];
		$id = $_GPC['id'];
		$url = $_GPC['url'];
		$yurl = $_GPC['url'];
		$password = $_COOKIE['password']; 
		$category = pdo_fetchall("SELECT * FROM ".tablename('cyl_vip_video_category')." WHERE uniacid = ".$_W['uniacid']." AND parentid = 0 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');		
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
		if ($_GPC['openid']) {
	    	$openid = $_GPC['openid']; 
	    	$member = member($openid);
	    }else{
	    	$openid = $_GPC['phone'];
	       	$member = member($openid,'is_weixin');
	        $data['openid'] = $member['openid'];
	       	if ($member['nickname'] || $member['avatar']) {
	       		$openid = $member['openid'];
	       		$member = member($openid);
	       	} 
	    }
		if (TIMESTAMP > $member['end_time'] && $member['is_pay'] == 1 && $member) {
	        pdo_update('cyl_vip_video_member',array('end_time'=>null,'is_pay'=>0),array('openid'=>$member['openid']));
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
	                    'value' => '到期提醒',
	                    'color' => '#ff510'
	                ) ,                   
	                'remark' => array(
	                    'value' => '点击详情开通',
	                    'color' => '#ff510'
	                ) ,
	            );
	        $url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('do' => 'member','m' => 'cyl_vip_video')) , '.');
	        $acc->sendTplNotice($member['openid'], $settings['tpl_id'], $data, $url, $topcolor = '#FF683F');
	    }
		if (empty($openid)) {
		 	$openid = $_W['clientip'];
		}		
		$jilu = jilu($openid);	
		$ad = pdo_fetch("SELECT * FROM " . tablename('cyl_video_pc_ad') . " WHERE uniacid = :uniacid  AND status = 0 ORDER BY rand() DESC LIMIT 1",array(':uniacid'=>$_W['uniacid']),'id');
	    if (TIMESTAMP > $ad['end_time']) {
	        pdo_update('cyl_video_pc_ad',array('status'=>1),array('id'=>$ad['id']));
	    } 
	    $num = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('cyl_vip_video') . " WHERE uniacid = :uniacid AND openid = :openid ", array(':uniacid' => $_W['uniacid'],':openid' => $openid));
		if ($num >= $settings['free_num'] && $member['is_pay'] == 0) {
	    	message($settings['warn_font'] ? $settings['warn_font'] : '您的免费观看次数已用完，请点击确定开通会员，无限制观看',$this->createMobileUrl('member',array('op'=>'open')),'error'); 
	    } 
		if ($id) {
			$content = pdo_fetch("SELECT * FROM ".tablename('cyl_vip_video_manage')." WHERE id=:id",array(':id'=>$id));
            if (checksubmit('submit')) {
                if ($_GPC['password'] == $content['password']) {
                    setcookie("password",$_GPC['password'],time()+2*7*24*3600);
                    $url = $this->createMobileUrl('index',array('mov'=>'detail','id'=>$id));
                    Header("Location: $url");
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
            $is_vip = is_vip($id,'id');
            pdo_update('cyl_vip_video_manage', array('click +=' => 1), array('id' => $id)); 				
		}else{	
			if ($setting['ziyuan'] == 1) { 
				$site = array(); 
	            $vip_url = $_W['siteurl'];     
	            $is_vip = is_vip($vip_url,'url');
	            $url_time = cache_load('pc_caiji_detail:'.$_GPC['d_id']); 
	            if ((TIMESTAMP - $url_time) > 3600 ) {
	                $url = "http://caiji.thecook.com.cn/caiji/api.php?id=".$_GPC['d_id'];   
	                $response = ihttp_get($url); 
	                $content = json_decode($response['content'],true); 
	                $tuijian = $content['tuijian'];  
	                cache_write('pc_caiji_detail:'.$_GPC['d_id'], TIMESTAMP);                                
	                cache_write('content:'.$_GPC['d_id'], $content);  
	                cache_write('tuijian:'.$_GPC['d_id'], $tuijian);  
	            }else{
	                $content = cache_load('content:'.$_GPC['d_id']);
	                $tuijian = cache_load('tuijian:'.$_GPC['d_id']); 
	            }       
	            $play_list = $content['play_list'];
	            $link = $content['site']; 
	            $url = $link['0'];
	            if ($_GPC['site']) {
	                $site_title = $_GPC['site'];
	            }else{
	                $site_title = $link['0'];
	            }                          
	            $playurl = $content['playurl'];
	            $jishu = $_GPC['jishu'];
	            if ($_GPC['site']) {
	                $first_val = $play_list[$_GPC['site']];
	            }else{
	                $first_val = $play_list[$link['0']];
	            } 
	            if ($jishu) {
	                $url = $first_val['urls'][$jishu]['url'];
	            }else{
	                $jishu = 1;
	                $url = $first_val['urls'][$jishu]['url'];
	            }
	            $juji = $first_val['urls'];
	            if ($first_val['urls']['1']['name'] == $first_val['url_count']) {
	                $jishu_zidong = $jishu - 1;
	            }else{
	                $jishu_zidong = $jishu + 1;
	            }   
	             
	            isetcookie ('shangci', $_W['siteurl']);
	            isetcookie ('shangci_title', $content['title']);
	            isetcookie ('shangci_jishu', $_GPC['jishu']);    
	            $click = pdo_fetchcolumn('SELECT * FROM ' . tablename('cyl_vip_video') . " WHERE uniacid = :uniacid AND video_id = :video_id ",array(':uniacid'=>$_W['uniacid'],':video_id'=>$video_id));   
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
							$site_title = $link['0']['title'];
						}				
					}								
				}	 		
				if ($op == 'dianshi') {				
					$link = dianshi_url($url);
				    if ($_GPC['site']) {
				      $site = array_keys($site,$_GPC['site']);  
				    }else{
				    if (strexists($link['0'], '腾讯') && count($link) > 1 && !$settings['tengxun']) {         
				      	$site_title = $link['1'];
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
				    if (strexists($link['0'], '腾讯') && count($link) > 1 && !$settings['tengxun']) {         
				      	$site_title = $link['1'];
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
			$click = pdo_fetchcolumn('SELECT * FROM ' . tablename('cyl_vip_video') . " WHERE uniacid = :uniacid AND yvideo_url = :yvideo_url ",array(':uniacid'=>$_W['uniacid'],':yvideo_url'=>$yurl));	
		}
		$video = pdo_get('cyl_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'video_url'=>$url));
		if (!$video) {
			if ($id) {
				pdo_insert('cyl_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'title'=>$content['title'],'video_url'=>$url,'video_id'=>$id,'type'=>$op,'time'=>TIMESTAMP,'share'=>$_GPC['jishu']));
			}else{				
				pdo_insert('cyl_vip_video', array('uniacid'=>$_W['uniacid'],'openid'=>$openid,'title'=>$content['title'],'video_url'=>$url,'yvideo_url'=>$yurl,'type'=>$op,'time'=>TIMESTAMP,'share'=>$_GPC['jishu']));
			}
		}
		if ($setting['ziyuan'] == 1) {
			if ($op == 'dianying') {
				$index_rank = cache_load('cyl_video_pc:dianying'.$_W['uniacid']);
			}elseif ($op == 'dianshi') {
				$index_rank = cache_load('cyl_video_pc:dianshi'.$_W['uniacid']);	
			}elseif ($op == 'zongyi') {
				$index_rank = cache_load('cyl_video_pc:zongyi'.$_W['uniacid']);	
			}else{
				$index_rank = cache_load('cyl_video_pc:dongman'.$_W['uniacid']);  
			}
		}else{
			if ($op == 'dianying') {
				$index_rank = cache_load('index_rank_dianying');
			}elseif ($op == 'dianshi') {
				$index_rank = cache_load('index_rank_dianshi');
			}elseif ($op == 'zongyi') {
				$index_rank = cache_load('index_rank_zongyi');
			}else{
				$index_rank = cache_load('index_rank_dongman');	
			}
		}
			
			
		$Hurl = isset($_SERVER['HTTPS'])?'https':'http';
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
					if ($Hurl == 'https') {
						$api = 'https://jiexi.thecook.com.cn/vip/pc.php?url='.$url.'&link='.$_GPC['link'];
					}else{
						$api = 'http://jiexi.thecook.com.cn/vip/pc.php?url='.$url.'&link='.$_GPC['link'];
					}
				}
			}else{
				if ($Hurl == 'https') {
					$api = 'https://jiexi.thecook.com.cn/vip/pc.php?url='.$url.'&link='.$_GPC['link'];
				}else{
					$api = 'http://jiexi.thecook.com.cn/vip/pc.php?url='.$url.'&link='.$_GPC['link'];
				}
			}	
		}
		include $this->template('web_detail');  