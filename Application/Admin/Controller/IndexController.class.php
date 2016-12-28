<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
class IndexController extends Controller {
	public function _initialize(){
        // logger('访问IndexController！携带参数：'.var_export(I(),TRUE)); //debug
		header("content-type:text/html; charset=utf-8;");
		if(!session('?uid')){
			$this->redirect('Admin/login/index');
		}
	}
	//查询，显示账户中心
    public function index(){
        // 查询用户信息
        $user = D('admin');
        $where = array(
            'id' => session('uid')
        );
        $userinfo = $user->where($where)->field('pwd',TRUE)->find();
        // logger('查询用户信息'.var_export($userinfo,TRUE)); //debug
        //查询支付方式
        $where = array(
            'uid' => session('uid')
        );
        $alipay = D('alipayment');
        $alipayment = $alipay->where($where)->select();
        $weipay = D('weipayment');
        $weipayment = $weipay->where($where)->select();
        // logger(var_export($weipayment,TRUE)); //debug
        $all_sply = $this->finance_details();
        logger('所有申请'.var_export($all_sply,TRUE)); //debug
        $count = $this->finance_summary($all_sply);
        logger('支付统计'.var_export($count,TRUE)); //debug
        //前一分钟，统计 总收入 及 可提现金额
        $all_pay = 0;
        $take_back_money = 0;
        $timestamp = 0;
        $sply_ids = '';
        foreach($count as $k => $v){
            $timestamp = $v['timestamp']; //查询财务统计时的时间戳
            $sply_ids .= $v['sply_ids']; //统计内的订单的ID
            $all_pay += $v['sum']; //支付总金额
            if($v['payment_id'] == 1){ //系统默认支付收取的金额，可供提现
                $take_back_money += $v['sum'];
            }
        }
        // 查询申请提现金额
        $sply_take_money = $this->sply_take_back();
        $take_back_money = $take_back_money - $sply_take_money;
        //查询提现账号
        $account = $this->query_account();
        //查询全部提现申请
        $withdraw = D('withdraw');
        $all_withdraw = $withdraw->table('withdraw a,account b')->where('a.account_id = b.id')->field('a.id,a.sum,a.sply_time,a.modify_time,a.status,b.account')->select();
        $this->assign('userinfo',$userinfo);
        $this->assign('title','地区代理管理系统');
        $this->assign('user',session('user'));
        $this->assign('uid',session('uid'));
        $this->assign('ercodeurl',$userinfo['ercode']);
        $this->assign('alipayment',$alipayment);
        $this->assign('weipayment',$weipayment);
        $this->assign('sply_details',$all_sply);
        $this->assign('count',$count);
        $this->assign('mid',1); //序号1
        $this->assign('nid',1); // 序号2
        $this->assign('allpay',$all_pay); //支付总金额
        $this->assign('backmoney',$take_back_money); //可提现的金额
        $this->assign('timestamp',$timestamp);
        $this->assign('sply_ids',$sply_ids);
        $this->assign('account',$account);
        $this->assign('withdraw',$all_withdraw); //全部提现申请
        $this->display('index');
    }
    // 修改密码
    public function modify_user_pwd(){
        logger('修改密码-->');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        $new_pwd = $post['pwd'];
        $uid = $post['uid'];
        $user = $post['user'];
        if($new_pwd){
            if(!empty($uid)){
                $where = array(
                    'id' => $uid
                );
            }else{
                $where = array(
                    'id' => session('uid')
                );
            }
            $admin = D('admin');
            $updata = array(
                'pwd' => $new_pwd
            );
            $result = $admin->where($where)->save($updata);
            if($result){
                logger('修改成功！'."\n");
                $data = array(
                    'status' => 1,
                    'content' => '修改成功！'
                );
                $this->ajaxReturn($data);
            }else{
                logger('修改失败！'."\n");
                $data = array(
                    'status' => 0,
                    'content' => '修改失败！'
                );
                $this->ajaxReturn($data);
            }
        }else{
            logger('提交出错，请重试！'."\n");
            $data = array(
                'status' => 2,
                'content' => '提交出错，请重试！'
            );
            $this->ajaxReturn($data);
        } 
    }
    //修改二维码、客服qq、电话
    public function update_user(){
		logger('修改二维码，客服电话或qq');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        $updata = array(
            'qq' => $post['qq'],
            'phone' => $post['phone'],
            'ercode' => $post['url'],
        );
        $updata = array_filter($updata);
        $admin = D('admin');
        $where = array(
            'id' => session('uid')
        );
        $result = $admin->where($where)->save($updata);
        if($result){
            logger('修改成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '修改成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('修改失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '修改失败！'
            );
            $this->ajaxReturn($data);
        }
    }
    //增加支付方式 
    public function add_payment(){
        logger('添加支付方式');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        switch($post['payment']){
            case 'Alipay':
                logger('添加支付宝支付方式');
                $addata = array(
                    'payment_name' => $post['payname'],
                    'uid' => $post['uid'],
                    'account' => $post['account'],
                    'partner' => $post['partner'],
                    'key' => $post['key'],
                    'add_time' => time(),
                    'is_open' => 1
                );
                $payment = D('alipayment');
                break;
            case 'Weipay':
                logger('添加微信支付方式');
                $addata = array(
                    'payment_name' => $post['payname'],
                    'uid' => $post['uid'],
                    'appid' => $post['appid'],
                    'appsecret' => $post['secret'],
                    'mchid' => $post['mchid'],
                    'key' => $post['key'],
                    'add_time' => time(),
                    'is_open' => 1
                );
                $payment = D('weipayment');
                break;
            default:
                break;
        }
        logger('添加操作开始');
        // logger('要添加的数据：'.var_export($addata,TRUE)); //debug
        $result = $payment->add($addata);
        // $sql = $payment->getLastsql(); //debug
        // logger('查询语句：'.$sql); //debug
        if($result){
            logger('添加成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '添加成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('添加失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '添加失败！'
            );
            $this->ajaxReturn($data);
        }
    }  
    //修改支付设置
    public function update_payment(){
        logger('修改支付方式');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        switch($post['payment']){
            case 'Alipay':
                $updata = array(
                    'payment_name' => $post['payname'],
                    'account' => $post['account'],
                    'partner' => $post['partner'],
                    'key' => $post['key'],
                    'modify_time' => time(),
                    'is_open' => $post['open']
                );
                $payment = D('alipayment');
                break;
            case 'Weipay':
                $updata = array(
                    'payment_name' => $post['payname'],
                    'appid' => $post['appid'],
                    'appsecret' => $post['secret'],
                    'mchid' => $post['mchid'],
                    'key' => $post['key'],
                    'modify_time' => time(),
                    'is_open' => $post['open']
                );
                $payment = D('weipayment');
                break;
            default:
                break;
        }
        $where = array(
            'id' => $post['payid']
        );
        $result = $payment->where($where)->save($updata);
        if($result){
            logger('修改成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '修改成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('修改失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '修改失败！'
            );
            $this->ajaxReturn($data);
        }
	}
    //删除支付方式
    public function del_payment(){
        logger('删除支付方式');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        switch($post['payment']){
            case 'Alipay':
                $payment = D('alipayment');
                break;
            case 'Weipay':
                $payment = D('weipayment');
                break;
            default:
                break;
        }
        $where = array(
            'id' => $post['id']
        );
        $result = $payment->where($where)->delete();
        if($result){
            logger('删除成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '删除成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('删除失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '删除失败！'
            );
            $this->ajaxReturn($data);
        }
    }
    // 项目列表
    public function list_program(){
        logger('项目列表->');
        $program = D('program');
        $where = array(
            'uid' => session('uid')
        );
        $my_program = $program->where($where)->select();
        $this->assign('program',$my_program); 
        $this->assign('title','地区代理管理系统');
    	$this->assign('user',session('user'));
    	$this->display();
    }
    //发布新项目
    public function new_program(){
        //查询支付方式
        $where = array(
            'uid' => session('uid')
        );
        $alipay = D('alipayment');
        $alipayment = $alipay->where()->select();
        // $sql = $alipay->getLastsql(); //debug
        // logger('查询语句：'.$sql); //debug
        // logger('alipay:'.var_export($alipayment,TRUE)); //debug
        $weipay = D('weipayment');
        $weipayment = $weipay->where($where)->select();
        // logger('weipay:'.var_export($weipayment,TRUE)); //debug
        $this->assign('alipayment',$alipayment);
        $this->assign('weipayment',$weipayment);
        $this->assign('title','地区代理管理系统');
    	$this->assign('user',session('user'));
    	$this->display();
    }
    public function publish_program(){
        logger('发布新项目');
        $post = I();
        logger('传入参数：'.var_export($post,TRUE)); //debug 
        $program = D('program');
        $data = array(
            'pro_name' => $post['title'],
            'pro_title' => $post['title'],
            'pro_content' => $post['content'],
            'pro_thumb' => $post['cover'],
            'start_time' => strtotime($post['is_start']),
            'finish_time' => strtotime($post['is_finish']),
            'is_pay' => $post['pay'],
            'weipay_id' => $post['weipay'],
            'alipay_id' => $post['alipay'],
            // 'is_open' => $post['open'],
            'is_start' => $post['open'],
            'uid' =>  session('uid')
        );   
        $result = $program->add($data);
        $sql = $program->getLastsql(); //debug
        logger('SQL:'.$sql); //debug
        if($result){
            logger('添加成功！'."\n");
            //去添加转发地址
            $where = array(
                'id' => $result
            );
            $updata = array(
                'weixin_url' => 'http://'.$_SERVER['HTTP_HOST'].'/index.php/Home/?id='.$result.'&weixin=wx6',
                'other_url' => 'http://'.$_SERVER['HTTP_HOST'].'/index.php/Home/?id='.$result
            );
            $update = $program->where($where)->save($updata);
            if($update){
                logger('更新转发地址成功!');
            }else{
                logger('更新转发地址失败!');
            }
            $data = array(
                'status' => 1,
                'content' => '添加成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('添加失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '添加失败，请重试！'
            );
            $this->ajaxReturn($data);
        }
    }
    //编辑项目
    public function update_program(){
        logger('编辑修改项目设置');
        $post = I();
        logger('传入参数：'.var_export($post,TRUE)); //debug
        $program = D('program');
        //传入多个非proid参数时是修改提交
        if(!empty($post['cover']) || !empty($post['content']) || !empty($post['name'])){
            logger('修改项目设置');
            $program = D('program');
            $data = array(
                'pro_name' => $post['name'],
                'pro_content' => $post['content'],
                'pro_thumb' => $post['cover'],
                'start_time' => strtotime($post['start']),
                'finish_time' => strtotime($post['finish']),
                'is_pay' => $post['pay'],
                'weipay_id' => $post['weipay'],
                'alipay_id' => $post['alipay'],
                'is_open' => $post['open']
            );  
            logger('组织后数组：'.var_export($data,TRUE)); //debug
            $where = array(
                'id' => $post['proid']
            ); 
            $result = $program->where($where)->save($data);
            $sql = $program->getLastsql();  //debug
            logger('查询语句：'.$sql); //debug
            if($result){
                logger('修改项目设置成功！'."\n");
                $data = array(
                    'status' => 1,
                    'content' => '修改成功！'
                );
                $this->ajaxReturn($data);
            }else{
                logger('修改失败！'."\n");
                $data = array(
                    'status' => 0,
                    'content' => '修改失败，请重试！'
                );
                $this->ajaxReturn($data);
            }
        }else{ //仅传入proid的情形，显示该项目设置
            logger('显示项目设置');
            $where = array(
                'id' => $post['proid']
            );
            $my_program = $program->where($where)->find();
            $sql = $program->getLastsql(); //debug
            logger('查询语句：'.$sql); //debug
            // logger('项目内容:'.var_export($my_program,TRUE)); //debug
            //查询支付方式
            $where = array(
                'uid' => session('uid')
            );
            $alipay = D('alipayment');
            $alipayment = $alipay->where()->select();
            // $sql = $alipay->getLastsql(); //debug
            // logger('查询语句：'.$sql); //debug
            // logger('alipay:'.var_export($alipayment,TRUE)); //debug
            $weipay = D('weipayment');
            $weipayment = $weipay->where($where)->select();
            // logger('weipay:'.var_export($weipayment,TRUE)); //debug
            $this->assign('alipayment',$alipayment);
            $this->assign('weipayment',$weipayment);
            $this->assign('program',$my_program);
            $this->assign('user',session('user'));
            $this->assign('title','更新项目设置');
            $this->display('Index/show_program');
        }
    }
    //删除项目
    public function del_program(){
        logger('删除项目');
        $post = I();
        // logger('传入参数：'.var_export($post,TRUE)); //debug
        $program = D('program');
        $where = array(
            'id' => $post['id']
        );
        $result = $program->where($where)->delete();
        if($result){
            logger('删除成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '删除成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('删除失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '删除失败，请重试！'
            );
            $this->ajaxReturn($data);
        }
    }
    //显示申请数据
    public function sply_show(){
        logger('显示申请数据');
        $post = I();
        // logger('携带参数：'.var_export($post,TRUE)); //debug
        $program = D('program');
        if(empty($post['id'])){
            logger('显示全部申请');
            //先查询我开放的项目
            $where = array(
                'uid' => session('uid'),
                'is_start' => 1
            );
            $my_program = $program->field('id,pro_name')->where($where)->order('id asc')->select(); 
            //根据项目id查询其申请，并组合
            $sply = D('sply');
            $my_sply = array();
            foreach($my_program as $k => $v){
                $where = array(
                    'pro_id' => $v['id'],
                    'is_show' => 1
                );
                $sub_sply = $sply->where($where)->select();
                foreach($sub_sply as $key => $value){
                    $sub_sply[$key]['pro_name'] = $v['pro_name'];
                }
                $my_sply = array_merge($my_sply,$sub_sply);
            }
            // logger('查询结果：'.var_export($my_sply,TRUE)); //debug
            $this->assign('pro_id','all');
        }else{
            $where = array(
                'id' => $post['id']
            );
            $my_program = $program->field('id,pro_name')->where($where)->find();
            $where = array(
                'pro_id' => $post['id'],
                'is_show' => 1
            );
            $sply = D('sply');
            $my_sply = $sply->where($where)->select();
            foreach($my_sply as $k => $v){
                $my_sply[$k]['pro_name'] = $my_program['pro_name'];
            }
            $this->assign('pro_id',$post['id']);
        }
        $condition = array(
            'uid' => session('uid')
        );
        $programs = $program->where($condition)->select();
        $this->assign('program',$programs);
        $this->assign('sply',$my_sply);
        $this->assign('title','地区代理管理系统');
    	$this->assign('user',session('user'));
    	$this->display();
    }
    //中标
    public function update_sply(){
        logger('申请中标！');
        $post = I();
        // logger('携带参数：'.var_export($post,TRUE));   //debug
        $sply = D('sply');  
        $where = array(
            'id' => $post['id']
        ); 
        if($post['action'] == 'get'){
            $data = array(
                'is_qd' => 1
            );
        }else{
            $data = array(
                'is_qd' => 0
            );
        }
        $result = $sply->where($where)->save($data);
        if($result){
            logger('提交成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '提交成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('提交失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '提交失败，请重试！'
            );
            $this->ajaxReturn($data);
        }
    }
    //删除申请
    public function del_sply(){
        logger('删除申请数据');
        $post = I();
        logger('携带参数：'.var_export($post,TRUE));  
        $sply = D('sply');  
        $where = array(
            'id' => $post['id']
        ); 
        $data = array(
            'is_show' => 0
        );
        $result = $sply->where($where)->save($data);
        if($result){
            logger('删除成功！'."\n");
            $data = array(
                'status' => 1,
                'content' => '删除成功！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('删除失败！'."\n");
            $data = array(
                'status' => 0,
                'content' => '删除失败！'
            );
            $this->ajaxReturn($data);
        }
    }
    //导出申请数据
    public function export_sply(){
        logger('导出申请数据-->');
        $post = I();
        $proid = $post['proid'];
        $uid = session('uid');
        // logger('参数：'.var_export($post,TRUE));//debug
        //判断导出类型是否为空
        if($proid){
            $sply = D('sply');
            if($proid == 'all'){
                logger('导出全部申请用户信息-->');
                //先查询开放项目
                $program = D('program');
                $where = array(
                    'uid' => $uid,
                    'is_start' => 1
                );
                $my_program = $program->field('id,pro_name')->where($where)->select();
                 //根据项目id查询其申请，并组合
                $sply = D('sply');
                $my_sply = array();
                foreach($my_program as $k => $v){
                    $where = array(
                        'pro_id' => $v['id'],
                        'is_show' => 1
                    );
                    $sub_sply = $sply->where($where)->select();
                    foreach($sub_sply as $key => $value){
                        $sub_sply[$key]['pro_name'] = $v['pro_name'];
                    }
                    $my_sply = array_merge($my_sply,$sub_sply);
                }
                // logger('全部申请数据查询结果：'.var_export($my_sply,TRUE)); //debug
            }else{
                logger('导出项目'.$proid.'的申请用户信息-->');
                $where = array(
                    'id' => $proid
                );
                $my_program = $program->field('pro_name')->where($where)->select();
                $where = array(
                    'pro_id' => $proid,
                    'is_show' => 1
                );
                $my_sply = $sply->where($where)->select();
                $my_sply['pro_name'] = $my_program['pro_name']; 
            }  
            $str = iconv('utf-8','gb2312',"序号,项目ID,项目名称,申请地区,申请人,联系方式,QQ号码,影楼名称,是否支付,申请状态,申请时间\n"); 
            foreach($my_sply as $k => $v){
                $id = $k;
                $pid = $v['pro_id'];
                $proname = iconv('utf-8','gb2312',$v['pro_name']); //中文转码 
                $area = iconv('utf-8','gb2312',$v['sply_area']);
                $name = iconv('utf-8','gb2312',$v['sply_name']);
                $phone = $v['sply_phone'];
                $qq = $v['sply_qq'];
                $store = iconv('utf-8','gb2312',$v['sply_store_name']);
                switch($v['is_pay']){
                    case 1:
                        if($v['pay_is'] == 1){
                            $pay = iconv('utf-8','gb2312','已支付');
                        }else{
                            $pay = iconv('utf-8','gb2312','未支付');
                        }
                        break;
                    case 0:
                        $pay = iconv('utf-8','gb2312','未开启支付');
                        break;
                    default :
                        break;
                }  
                if($v['is_qd'] == 1){
                    $status = iconv('utf-8','gb2312','申请失败');
                }else{
                    $status = iconv('utf-8','gb2312','申请成功');
                } 
                $time = date('Y-m-d H:i:s',$v['sply_time']);
                $str .= $id.','.$pid.','.$proname.','.$area.','.$name.','.$phone.','.$qq.','.$store.','.$pay.','.$status.','.$time."\n";
            }
            $filename = '地区代理申请用户信息表-'.date('Ymdhis').'.csv'; //设置文件名
            logger("导出成功\n");
            $export = $this->export_csv($filename,$str); //导出 
        }else{
            logger("提交失败，未能成功导出！\n");
            $this->error('提交失败，未能成功导出！');
        }
    }
    //导出操作写入函数
    public function export_csv($filename,$data) { 
        header("Content-type:text/csv"); 
        header("Content-Disposition:attachment;filename=".$filename); 
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
        header('Expires:0'); 
        header('Pragma:public'); 
        echo $data;
        exit;//结束输入，否则会把HTML源码也存入了
    }        

    //上传图片
    public function uploadimg(){
        header("Content-Type:text/html;charset:utf-8");
        $post = I();
        // logger('文件系统:'.var_export($_FILES,TRUE)); //debug
        logger('上传文件开始--->');
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg','gif','png','jpeg');
        // $upload->rootPath = './Uploads/';
        $upload->savePath = '';
        $info = $upload->upload();
        // logger('info:'.var_export($info,TRUE)); //debug
        if(!$info){
            $data = array(
                'status' => 0,
                'content' => $upload->getError()
            );
            $this->ajaxReturn($data);
        }else{
            foreach ($info as $v) {
               $savepath=$v['savepath'];
               $savename=$v['savename'];
            }  
            //生成缩略图
            $img= new \Think\Image();
            $img->open('./Uploads/'.$savepath.$savename);
            $img->thumb(200,200)->save('./Uploads/'.$savepath.'S'.$savename);
            $imgurl = '/Uploads/'.$savepath.'S'.$savename;
            @unlink('./Uploads/'.$savepath.$savename);
            logger("产品封面图片上传处理成功");
            $data = array(
                'status' => 1,
                'content' => $imgurl
            );
            $this->ajaxReturn($data);
        }
    }
    //财务明细
    public function finance_details(){
        logger('查询财务明细');
        //先查询开放且需要支付的项目
        $program = D('program');
        $where = array(
            'uid' => session('uid'),
            'is_start' => 1,
            'is_pay' => 1
        );
        $my_program = $program->field('id,pro_name')->where($where)->select();
         //根据项目id查询其申请，并组合
        $sply = D('sply');
        $my_sply = array();
        foreach($my_program as $k => $v){
            $where = array(
                'pro_id' => $v['id'],
                'is_show' => 1
            );
            $sub_sply = $sply->where($where)->order('sply_time desc')->select();
            foreach($sub_sply as $key => $value){
                $sub_sply[$key]['pro_name'] = $v['pro_name'];
            }
            $my_sply = array_merge($my_sply,$sub_sply);
        }
        // logger('全部申请数据查询结果：'.var_export($my_sply,TRUE)); //debug
        return $my_sply;
    }
    // 财务汇总
    public function finance_summary($array){
        logger('统计订单收款');
        if(!$array){
            logger('不存在已查询查询的数组，重新查询！');
            //先查询所有需要支付的订单 已付和未付
            $all_pay_sply = $this->finance_details();
        }else{
            logger('存在已查询的数组');
            $all_pay_sply = $array;
        }
        $m = 0;
        foreach($all_pay_sply as $k => $v){
            if(($v['pay_is'] == 1) && ($v['pay_time'] < strtotime(date('Y-m-d H:i',time()))) && ($v['is_qd'] == 1) && ($v['withdraw_is'] == 0)){ //1、已支付的申请 2、“支付时间”在当前一分钟前订单 3、抢定的 4、提现状态为0的
                logger('订单符合统计标准'.$k);
                if($m == 0){ //第一条申请记录新建数据 记录支付方式和id及金额
                    $count = array(
                        '0' => array(
                            'payment' => $v['payment'],
                            'payment_id' => $v['payment_id'],
                            'sum' => $v['pay_money'],
                            'timestamp' => time(),
                            'sply_ids' => $v['id'].' '
                        )
                    );
                    // logger('统计初始数组：'.var_export($count,TRUE)); //debug
                    $m++;
                }else{ //之后申请数据，按支付方式和id入队，并累计支付金额
                    $m++;
                    $num = 1 ;
                    foreach($count as $key => $value){
                        if(($v['payment'] == $value['payment']) && ($v['payment_id'] == $value['payment_id'])){ //支付方式和id都相同，累计金额
                            $count[$key]['sum'] += $v['pay_money'];
                            break;
                        }else{
                            $size = count($count);
                            if($num = $size){
                                $count[$size] = array(
                                    'payment' => $v['payment'],
                                    'payment_id' => $v['payment_id'],
                                    'sum' => $v['pay_money'],
                                    'timestamp' => time(),
                                    'sply_ids' => $v['id'].' '
                                );
                            }
                        } 
                    }
                }
            }
        }
        //查询支付方式的名称
        $weipay = D('weipayment');
        $alipay = D('alipayment');
        foreach($count as $k => $v){
            $where = array(
                'id' => $v['payment_id']
            );
            switch($v['payment']){
                case 'alipay':
                    $alipay_info = $alipay->field('id,payment_name')->where($where)->find();
                    $count[$k]['payment_name'] = $alipay_info['payment_name'];
                    break;
                case 'weipay':
                    $weipay_info = $weipay->field('id,payment_name')->where($where)->find();
                    $count[$k]['payment_name'] = $weipay_info['payment_name'];
                    break;
                default:
                    break;
            }
        }
        return $count;
    }
    //检查收款账号是否已存在
    public function check_account(){
        logger('检查账号是否重复');
        $post = I();
        logger('传入参数：'.var_export($post,TRUE)); //debug
        $uid = empty($post['uid']) ? $post['uid'] : session('uid');
        $account = $post['account'];
        $model_account = D('account');
        $where = array(
            'uid' => $uid,
            'account' => $account
        );
        $result = $model_account->where($where)->find();
        if($result){
            logger('账号已存在！'."\n");
            $data = array(
                'status' => 0,
                'content' => '账号已存在！'
            );
            $this->ajaxReturn($data);
        }else{
            logger('账号不存在！'."\n");
            $data = array(
                'status' => 1,
                'content' => '账号不存在！'
            );
            $this->ajaxReturn($data);
        }
    }
    //添加收款账号
    public function add_account(){
        logger('添加提现账号');
        $post = I();
        logger('传入参数：'.var_export($post,TRUE)); //debug
        $bank = $post['bank'];
        $number = $post['number'];
        $name = $post['name'];
        $branch = $post['branch'];
        $uid = empty($post['uid']) ? $post['uid'] : session('uid');
        if($bank && $number && $name){
            $account = D('account');
            $data = array(
                'name' => $name,
                'account' => $number,
                'bank' => $bank,
                'branch' => $branch,
                'uid' => $uid,
                'add_time' => time(),
                'is_show' => 1
            ); 
            $data = array_merge($data);
            $result = $account->add($data);
            if($result){
                logger('添加账号成功！'."\n");
                $data = array(
                    'status' => 1,
                    'content' => '添加账号成功！',
                    'id' => $result
                );
                $this->ajaxReturn($data);
            }else{
                logger('添加账号失败！'."\n");
                $data = array(
                    'status' => 0,
                    'content' => '添加账号失败！'
                );
                $this->ajaxReturn($data);
            }
        }else{
            $data = array(
                'status' => 2,
                'content' => '参数不全，请重试！'
            );
            $this->ajaxReturn($data);
        }
        
    }
    //查询账号
    public function query_account(){
        logger('查询提现账号');
        $account = D('account');
        $where = array(
            'uid' => session('uid'),
            'is_show' => 1
        );
        $result = $account->where($where)->order('is_default desc,add_time desc')->select();
        if($result){
            $array = C('BANK');
            foreach($result as $k => $v){
                $result[$k]['bank'] = $array[$result[$k]['bank']];
            }
            return $result;
        }else{
            return FALSE;
        }
    }
    //提现
    public function take_money_back(){
        logger('提现申请');
        $post = I();
        logger('传入参数：'.var_export($post,TRUE)); //debug
        $sum = $post['backmoney'];
        $aid = $post['accountid'];
        $uid = isset($post['uid']) ? $post['uid'] : session('uid');
        $sply_ids = $post['splyids'];
        $timestamp = $post['timestamp']; 
        if($sum && $aid && $uid && $sply_ids && $timestamp){
            $withdraw = D('withdraw');
            $data = array(
                'uid' => $uid,
                'account_id' => $aid,
                'sum' => $sum,
                'sply_ids' => $sply_ids,
                'count_timestamp' => $timestamp,
                'sply_time' => time(),
                'status' => 0
            );
            $result = $withdraw->add($data);
            if($result){
                logger('提现申请成功！'."\n");
                $data = array(
                    'status' => 1,
                    'content' => '提现成功！<br>3个工作日内到账！',
                    'id' => $result
                );
                $this->ajaxReturn($data);
            }else{
                logger('提现申请失败！'."\n");
                $data = array(
                    'status' => 0,
                    'content' => '提现申请失败,请重试！'
                );
                $this->ajaxReturn($data);
            }
        }else{
            logger('参数不全，请重试！'."\n");
            $data = array(
                'status' => 2,
                'content' => '参数不全，请重试！'
            );
            $this->ajaxReturn($data);
        }
    }
    //查询已申请提现的金额
    public function sply_take_back(){
        logger('查询已申请提现的金额');
        $withdraw = D('withdraw');
        $where = array(
            'uid' => session('uid'),
            'status' => 0
        );
        //查询出所有提现申请（尚未给予提现的）
        $result = $withdraw->where($where)->field('sum')->select();
        if($result){
            $sum = 0;
            foreach($result as $k => $v){
                $sum += $v['sum'];
            }
            logger('用户已申请提现金额：'.$sum."\n");
            return $sum;
        }else{
            logger('用户尚未申请提现'."\n");
            return 0;
        }
    }

}