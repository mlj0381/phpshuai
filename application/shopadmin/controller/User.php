<?php
namespace app\shopadmin\controller;
use app\shopadmin\Admin;
class User extends Admin
{
	public function index()
	{
		$map = array();
		$user = D('user');
		$roles = D('role')->getField('id,name');
		$count=$user->count();
		$page = new \com\Page($count, 15);
		$users = $user->where($map)
		->order("last_login_time DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		$status=array("0"=>L('USER_STATUS_BLOCKED'),"1"=>L('USER_STATUS_ACTIVATED'),"2"=>L('USER_STATUS_UNVERIFIED'));
		$this->assign("status",$status);
		$this->assign("page", $page->show());
		$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->display();
	}

	public function add()
	{
		if(IS_POST){
			!($username = I('post.username')) && $this->error(L('ERROR_EMPTY_USERNAME'));
			!($password = I('post.password')) && $this->error(L('ERROR_EMPTY_PASSWORD'));
			!($role = I('post.role/a')) && $this->error(L('ERROR_EMPTY_ROLE'));
			$status = I('post.status');$remark = I('post.remark');
			$check_unique = D('user')->where(['username'=>$username])->find();
			$check_unique && $this->error(L('ERROR_ALREADY_EXIST'));
			$result = D('user')->add(['username'=>$username,'password'=>md5($password),'role'=>$role[0],'last_login_time'=>time(),'status'=>$status,'remark'=>$remark]);
			M("role_user")->add(['user_id'=>D("user")->getLastInsID(),'role_id' => $role[0]]);
			D("user_action")->saveAction(session("userid"),12,$result,"添加管理员". $username);
			$result ? $this->success(L('MSG_SUCCESS')):$this->error(L('MSG_ERROR'));
		}
		$users = D('role')->getAllRole();
		$this->assign("roles",$users);
		$this->display();
	}
	public function del()
	{
		$id = (int)I('get.id');
		D("user_action")->saveAction(session("userid"),12,$id,"删除管理员".D("user")->getUserName($id));
		D('user')->where(['id'=>$id])->delete() ? $this->success(L('MSG_DEL_SUCCESS')):$this->error(L('MSG_DEL_ERROR'));
	}

	public function edit()
	{
		if(IS_POST){
			$user_id = (int) I('post.user_id');
			!($username = I('post.username')) && $this->error(L('ERROR_EMPTY_USERNAME'));
			!($role = I('post.role/a')) && $this->error(L('ERROR_EMPTY_ROLE'));
			$data['username'] = $username;
			$data['role'] = $role[0];
			I('post.password') && $data['password'] = md5(I('post.password'));
			$data['last_login_time'] = time();
			$data['status'] = I('post.status');
			$data['remark'] = I('post.remark');
			$user_count = M("role_user")->where(['user_id' => $user_id])->count();
			if($user_count == 0){
				M("role_user")->add(['user_id'=>$user_id,'role_id' => $role[0]]);
			}else{
				M("role_user")->where(['user_id' => $user_id])->save(['role_id' =>$role[0]]);
			}
			D("user_action")->saveAction(session("userid"),12,$user_id,"修改管理员".$username);
			D('user')->where(['id'=>$user_id])->save($data) ? $this->success(L('MSG_EDIT_SUCCESS')):$this->error(L('MSG_EDIT_ERROR'));
		}else{
			$id = (int)I('get.id');
			$info = D('user')->where(['id'=>$id])->find();
			$users = D('role')->getAllRole();
		}
		$this->assign("roles",$users);
		$this->assign("info",$info);
		$this->display();
	}

	public function disable()
	{
		$id = (int)I('get.id');
		D("user_action")->saveAction(session("userid"),12,$id,"禁止管理员".D("user")->getUserName($id));
		D('user')->where(['id'=>$id])->save(['status'=>0]) ? $this->success(L('MSG_DISABLE_SUCCESS')):$this->error(L('MSG_DISABLE_ERROR'));
	}

	public function able()
	{
		$id = (int)I('get.id');
		D("user_action")->saveAction(session("userid"),12,$id,"开启管理员".D("user")->getUserName($id));
		D('user')->where(['id'=>$id])->save(['status'=>1]) ? $this->success(L('MSG_ABLE_SUCCESS')):$this->error(L('MSG_ABLE_ERROR'));
	}

	public function info()
	{
		if(IS_POST){
			$data['id'] = (int)I('post.id');
			$data['nickname'] = I('post.nickname');
			$data['birthday'] = I('post.birthday');
			$data['sex'] = I('post.sex');
			$data['url'] = I('post.url');
			$data['note'] = I('post.note');
			$data['sid'] = session("sellerid");
			D("seller_action")->saveAction(session("sellerid"),12,$data['id'],"管理员修改信息".$data['nickname']);
			if ($data['id']){
			    $res = M('seller_user_profile')->where(['id' => $data['id']])->save($data);
			    if ($res === false){
			        $this->error('失败');
			    }
			    $this->success('成功');
			}else {
			    M('seller_user_profile')->add($data) ? $this->success('成功') : $this->error('失败');
			}
		}else{
			$uid = session('sellerid');
			$info = M('seller_user')->alias('su')->join('seller_user_profile sup','su.seller_id=sup.sid')->where(['sup.sid'=>$uid])->find();
			if(!$info){
				$info['id'] = '';
				$info['nickname'] = '';
				$info['birthday'] = '';
				$info['sex'] = '';
				$info['url'] = '';
				$info['note'] = '';
			}
			$this->assign("info",$info);
		}
		$this->display();
	}

	public function password()
	{
		if(IS_POST){
			$uid = session('sellerid');
			!($old = I('post.old_password')) && $this->error('请输入原始密码');
			!($new = I('post.password')) && $this->error('请输入新密码');
			!($again = I('post.repassword')) && $this->error('请再次输入新密码');
			if($new != $again) $this->error('两次密码不一致');
			$seller_user = M('seller_user')->where(['seller_id' => $uid])->find();
			!M('seller_user')->where(['seller_id' => $uid , 'password'=>md5(md5($old).$seller_user['ec_salt'])])->find() && $this->error('旧密码错误');
			$this->log("修改密码");
			M('seller_user')->where(['seller_id'=>$uid])->save(['password'=> md5(md5($new)), 'ec_salt' => NULL]);
			session(null);
			$this->success("修改成功");
		}
		$this->display();
	}
	
	
}