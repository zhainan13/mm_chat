<?php

/**
 * 会员
 */
namespace User\Controller;

use Common\Controller\AdminbaseController;

class IndexadminController extends AdminbaseController
{
	function index()
	{
		$users_model = M("Users");
		
		$count = $users_model->where(array (
				"user_type" => 2 
		))->count();
		$page = $this->page($count, 20);
		
		$lists = $users_model->field('cms_users.*,cms_oauth_user.`status`')->join('LEFT JOIN cms_oauth_user ON cms_users.id = cms_oauth_user.uid' )->where(array (
				"user_type" => 2 
		))->order("cms_users.create_time DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign('lists', $lists);
		$this->assign("page", $page->show('Admin'));
		
		$this->display(":index");
	}
	function ban()
	{
		$id = intval($_GET['id']);
		if ($id)
		{
// 			$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','0');
			$rst = M("OauthUser")->where(array ("uid" => $id))->setField('status', '0');
			if ($rst)
			{
				$this->success("会员禁言成功！", U("indexadmin/index"));
			}
			else
			{
				
				$this->error('会员禁言失败！');
			}
		}
		else
		{
			$this->error('数据传入失败！');
		}
	}
	function cancelban()
	{
		$id = intval($_GET['id']);
		if ($id)
		{
// 			$rst = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status','1');
			$rst = M("OauthUser")->where(array ("uid" => $id))->setField('status', '1');
			if ($rst)
			{
				$this->success("会员启用成功！", U("indexadmin/index"));
			}
			else
			{
				$this->error('会员启用失败！');
			}
		}
		else
		{
			$this->error('数据传入失败！');
		}
	}
}
