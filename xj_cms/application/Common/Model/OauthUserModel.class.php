<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 第三方用户
 */
class OauthUserModel extends BaseModel{

    // 自动验证
    protected $_validate=array(
        array('type','require','类型必填'),
        array('nickname','require','昵称必填'),
        array('head_img','require','头像必填'),
        array('access_token','require','access_token必填'),
        );

    // 自动完成
//     protected $_auto=array(
//         array('create_time','time',1,'function'),
//         array('last_login_time','time',3,'function'),
//         );

    // 添加数据
    public function addData($add_data){
    	$now =time();
        if($data=$this->create($add_data)){
        	$data['create_time'] = $data['last_login_time'] = $data['login_times']= date("Y-m-d H:i:s",$now);
        	$data['expires_date'] = $now+30*86400;
            $id=$this->add($data);
            return $id;
        }else{
            return false;
        }
    }

    /**
     * 获取token值
     * @param  integer  $uid  用户id
     * @param  integer $type  类型
     * @return string         token值
     */
    public function getToken($uid,$type=''){
        $map=array(
            'uid'=>$uid,
           );
        if ($type!=='')
        {
        	$map['status']= $type;
        }
        $row=$this->where($map)->find();
        
        $token = '';
        if (isset($row['status']))
        {
        	$token = '000';
        	if ($row['status']>0){
        		$token = $row['access_token'];
        	}
        }
       
        return $token;
    }

    public function getUserStatus($uid)
    {
    	$map=array(
    			'uid'=>$uid,
    	);
    	$status = $this->where($map)->getField('status');

    	return $status;
    	
    }
}