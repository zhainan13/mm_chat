<?php

namespace Home\Controller;

use Common\Controller\HomebaseController;

/**
 * 聊天首页Controller
 * @author meiyf
 */
class IndexController extends HomebaseController
{
	
	/**
	 * 融云用户
	 */
	public function user()
	{
		if (! isset($_SESSION['user']))
		{
			echo '<script>alert("请先登录!");';
			echo "location='/';";
			echo '</script>';
			exit();
			exit();
		}
		
		// 获取融云key
		$rong_key_secret = get_rong_key_secret();
		$token = get_rongcloud_token($_SESSION['user']['id']); // 获取融云token
		                                                       
		// 判断是否已经被禁言
		if ($token === '000')
		{
			echo '<script>alert("您已经被禁言，您可以跟管理员申请解禁!");';
			echo "location='/';";
			echo '</script>';
			exit();
		}
		$assign = array (
				'id' => $_SESSION['user']['id'], // 用户id
				'avatar' => $_SESSION['user']['avatar'], // 头像
				'user_nicename' => $_SESSION['user']['user_nicename'], // 用户名
				'rong_key' => $rong_key_secret['key'], // 融云key
				'rong_token' => $token 
		);
		
		$this->assign($assign);
		
		// 刷新该用户在融云的数据
		refresh_rongcloud_token($_SESSION['user']['id']);
		
		$public_source = array (
				'public_source' => '/' . C('SP_TMPL_PATH') . '/' . C('SP_DEFAULT_THEME') . '/Public' 
		);
		$this->assign($public_source);
		
		$alluser = array (
				'alluser' => $this->alluser() 
		);
		$this->assign($alluser);
		
		$chat_file = '/public/chat/' . date('Ymd') . '.js?version=' . rand(0, 9999);
		
		$this->assign(array (
				'char_file' => $chat_file 
		));
		
		$this->display();
	}
	
	/**
	 * 获得其他的用户(暂不实现在线人情况)
	 * 另外在js内记录下来这些授权可以聊天的用户编号
	 */
	private function alluser()
	{
		//授权js的地址，每天生成，假如生成失败，可以复制之前文件改成今天的文件名的先顶着用
		//文件路径为/public/chat/20160704.js
		$chat_filename = SITE_PATH . '/public/chat/' . date('Ymd') . '.js';
		
		if (! file_exists($chat_filename))
		{
			$flag = true;
		}
		
		$uids = array ();
		
		$user_list = M('Users')->field('cms_users.id,cms_users.user_nicename,cms_users.avatar')->join('RIGHT JOIN cms_oauth_user ON cms_users.id = cms_oauth_user.uid')->select();
		
		foreach ($user_list as $key => $user)
		{
			//记录下用户编号
			if ($flag)
			{
				$uids[] = $user['id'];
				// refresh_rongcloud_token($user['id']);
			}
			//遍历之后，排除出自己
			if ($user['id'] == $_SESSION['user']['id'])
			{
				unset($user_list[$key]);
				break;
			}
		}
		// 生成当日可以允许聊天的用户编号，格式为   var authusers =new Array('13','12','1','6');
		if ($uids)
		{
			$str = 'var authusers =new Array(';
			foreach ($uids as $uid)
			{
				$str .= "'" . $uid . "',";
			}
			$str = substr($str, 0, - 1) . ');';
			//生成文件
			file_put_contents($chat_filename, $str);
		}
		
		return $user_list;
	}
	
	/**
	 * 
	 * @method 根据用户编号，查询出用户的聊天权限情况
	 */
	public function getUserStatus()
	{
		$uid = intval($_GET['uid']);
		$user_status = M('OauthUser')->getUserStatus($uid);
		return $user_status;
	}
	
	/**
	 *
	 * @method 保存聊天消息记录到我们的数据库，避开因为融云历史消息的收费，也方便我们本地后台查询
	 *        
	 */
	public function save_content()
	{
		$data = array ();
		$data['fromid'] = (int) I('post.fromid');
		$data['toid'] = (int) I('post.uid');
		$data['content'] = trim(I('post.content'));
		$data['sendtime'] = time();
		if ($data['fromid'] > 0 && $data['toid'] > 0 && ! empty($data['content']))
		{
			M('ChatLog')->add($data);
			$data = array ();
			ajax_return($data, '保存成功', 0);
		}
		else
		{
			$data = array ();
			ajax_return($data, '数据错误', 0);
		}
	}
	
	/**
	 * @method 获取本人与uid2用户的聊天记录
	 * @return multitype:
	 */
	public function get_contents()
	{
		$uid1 = $_SESSION['user']['id'];
		$uid2 = I('post.uid');
		
		if ($uid2 <= 0)
		{
			$uid2 = I('get.uid');
		}
		if ($uid2 <= 0 || $uid1 == $uid2)
		{
			die('what is your name?');
		}
		$num = intval(I('post.num'));
		
		if ($num <= 0)
		{
			$num = 100;
		}
		
		// 取出最后的100条聊天记录
		$where = '(fromid=' . $uid1 . ' AND toid=' . $uid2 . ') OR (fromid=' . $uid2 . ' AND toid=' . $uid1 . ')';
		
		$logs = M('ChatLog')->where($where)->order('sendtime desc')->limit($num)->select();
		// 将这些聊天记录正序排序，方便展示
		$logs = array_reverse($logs);
		
		if (IS_AJAX)
		{
			ajax_return($logs, '查找完毕！', 0);
		}
		else
		{
			return $logs;
		}
	}
}

