<?php
namespace app\index\controller;
 
use think\Controller;
 
class Register extends Controller
{
    public function index()
    {
    	return $this->fetch();
    }
  
     //处理注册逻辑
    public function doRegister()
    {
    	$param = input('post.');
      
           //判断输入是否为空
        if(empty($param['r_user_name']) || empty($param['r_user_pwd']) || empty($param['r_user_rpwd'])){    		
    		$this->error('输入不能为空');
    	}
          
    	  //验证用户名是否已存在
    	$has = db('users')->where('user_name', $param['r_user_name'])->find();
    	if(!empty($has)){
    		$this->error('该用户名已经存在！请输入其它用户名');
    	}
      
         //验证两次输入密码是否一致    
    	if($param['r_user_pwd'] != $param['r_user_rpwd']){
    		$this->error('两次输入密码不一致！');
    	}
      
      	  //往数据库中插入数据
        $data = ['user_name'=>$param['r_user_name'], 'user_pwd'=>md5($param['r_user_pwd'])];
        $ok = db('users')->insert($data);
        if($ok){
            $this->success('注册成功,开始登录系统吧', 'login/index');
        }else{
            $this->error('注册失败！');
        }
    }
}
