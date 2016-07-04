<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class UserChatController extends AdminbaseController{
	
	
	/**
	 * @method 实现1.聊天信息列表 2.聊天信息模糊查询 3.聊天信息具体到某人的查询
	 * @author meiyf
	 * 
	 */
	function index(){
			
		$where=' 1=1 ';
		
		if ($_POST){
			//查询类型，0为全部，1为查询用户，2为查询聊天内容
			$type = intval(I('post.term'));
// 			var_dump($type);exit;
			//查询关键词
			$key = trim(I('post.keyword'));
			//查询条件，如果有查询关键字，则去查询（发送者用户名/接收者用户名/聊天内容）
			if ($key){
				switch ($type){
					case 0:
						//全部
						$where .=" AND (cms_chat_log.content LIKE '%$key%' OR u.user_nicename LIKE '%$key%' OR c.user_nicename LIKE '%$key%')";
							
						break;
					case 1:
						//查询人员
						$where .=" AND (u.user_nicename LIKE '%$key%' OR c.user_nicename LIKE '%$key%')";
							
						break;
					case 2:
						//查询内容
						$where .=" AND (cms_chat_log.content LIKE '%$key%') ";
						break;
					default:
						die('非法数据');
				}
			}
			//拼接出开始日期和结束日期的时间戳
			$start_time = strtotime(I('post.start_time').' 00:00:00');
			$end_time = strtotime(I('post.end_time')." 23:59:59");
			
			$where .=' AND cms_chat_log.sendtime >='.$start_time;
			$where .=' AND cms_chat_log.sendtime <='.$end_time;
			$formget = array('term'=>$type,'keyword'=>$key,'start_time'=>I('post.start_time'),'end_time'=>I('post.end_time'));
			$this->assign('formget',$formget);
		}
		
		//计算总数，计算分页
		$count =  M('ChatLog')->field('cms_chat_log.*,u.user_nicename,c.user_nicename')
		->join('LEFT JOIN cms_users AS u ON cms_chat_log.fromid=u.id  LEFT JOIN cms_users AS c ON cms_chat_log.toid=c.id')
		->where($where)->count();
		$page = $this->page($count,20);
		//查询具体的聊天列表，查询跟上面的一样确保分页数正常
		$chat_logs = M('ChatLog')->field('cms_chat_log.*,u.user_nicename fuser_nicename,c.user_nicename tuser_nicename')
		->join('LEFT JOIN cms_users AS u ON cms_chat_log.fromid=u.id  LEFT JOIN cms_users AS c ON cms_chat_log.toid=c.id') 
		->where($where)->order('cms_chat_log.sendtime desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//error_log(M('ChatLog')->getLastSql()."\n",3,'1.log');
		//分页显示
		$this->assign('page',$page->show('Admin'));
		$this->assign('chatlogs',$chat_logs);
		$this->display();
	}

}