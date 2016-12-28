<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$ua = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($ua,'MicroMessenger')){ //判断浏览器类型
    		$weixin = FALSE;
    		logger('非微信浏览器访问！');
    	}else{
    		$preg = "/MicroMessenger\/(.+)/";
    		preg_match_all($preg,$ua,$new_cnt);
    		$weixin = TRUE;
    		$weixin_vision = "".$new_cnt[1][0]."\n";
    		logger('微信浏览器访问，版本'.$weixin_vision);
    	}
    	if(strpos($ua,'Android')){
    		$mobile_phone = 'Android';
    		logger('安卓系统');
    	}elseif(strpos($ua,'iphone OS')){
    		$mobile_phone = 'iOS';
    		logger('苹果iOS');
    	}else{
    		$mobile_phone = 'other_mobile_phone';
    		logger('其他系统');
    	}
    	$post = I();
    	$pid = $post['id']; //项目id
    	logger('用户访问项目（项目ID: '.!empty($pid) ? $pro_id : '空'.' ）申请页面');
    	$result = $this->get_program($pid);
    	if($result){
    		if($result['is_start'] == 0){
                logger('访问的项目尚未启动！'."\n");
    			$this->error('您要申请的项目尚未开启，请随时关注！');
    		}
    		//是否是在微信端打开了非微信的URL地址
	    	if(!$post['weixin'] && $weixin){ //非微信url，是微信浏览器。跳转到微信URL
                logger('微信中打开了非微信url');
                //判断项目是否需要调用支付
                if($result['is_pay'] == 1){
                    logger('该项目需要支付');
                    //判断项目是否开通的微信支付
                    if(!empty($result['weipay'])){
                        //在数据库记录找到微信的url，并跳转
                        redirect($result['weixin_url'],1,'成功打开，正在为您跳转......'); //跳转到微信的url
                    }else{
                        logger('该项目未开通微信支付，提示用户改用其他浏览器，以便调用支付宝');
                        $_GET['tip'] = 1; //提示用户更换其他浏览器
                        $this->redirect('Weixin/weixin',$_GET);
                    }
                }else{
                    logger('该项目不需要支付，请用户继续浏览');
                    $this->redirect('Weixin/weixin',$_GET);
                    //$this->display('Index/other/templet1'); //可以在数据库中存储模板地址，在这里以变量显示
                }
	    	}elseif($post['weixin'] && !$weixin){ // 微信url， 非微信浏览器
                logger('非微信浏览器打开微信的url');
                //判断项目是否需要调用支付
                if($result['is_pay'] == 1){
                    logger('该项目需要支付');
                    //判断项目是否开通的微信支付
                    if(!empty($result['weipay'])){
                        logger('该项目开通了微信支付，提示用户改用微信打开链接！');
                        $_GET['tip'] = 1; //提示用户更换为微信浏览器
                        $this->redirect('Other/other',$_GET);
                    }
                    // 其实也可以提示一下，虽然没有支付问题，但如果有获取openID问题，也会少获取一些信息
                    $this->redirect('Other/other',$_GET); //没有设置微信支付,把微信url当做普通url来用
                }else{
                    // 其实也可以提示一下，虽然没有支付问题，但如果有获取openID问题，也会少获取一些信息
                    $this->redirect('Other/other',$_GET); //项目不需要支付,把微信url当做普通url来用
                }
	    	}else{
                //在正确的浏览器中打开了正确的URL
		    	if($weixin){
                    logger('使用微信浏览器打开微信url');
		    		$this->redirect('Weixin/weixin',$_GET);
		    	}else{
                    logger('使用非微信浏览器打开非微信url');
                    // logger('传送参数：'.var_export($info,TRUE)); //debug
		    		$this->redirect('Other/other',$_GET);
		    	}
	    	}  
    	}else{
            // $this->redirect('Index/test',$_GET); //test
           // redirect('http://www.baidu.com',1,'跳转'); //test
    		$this->error('您要申请的项目不存在，请查证！');
    	}
    }
    //测试函数
    public function test(){
        logger('测试跳转'."\n");
        logger('携带参数：'.var_export($_GET,TRUE)."\n");
    }
    //获取店铺信息
    public function get_store($uid){
        $where = array(
            'id' => $uid
        );
        $admin = D('admin');
        $store = $admin->where($where)->field('realname,phone,store_name,ercode,qq')->find();
        return $store;
    }
    //获取项目信息
    public function get_program($pid){
        $program = D('program');
        $where = array(
            'id' => $pid
        );
        $result = $program->where($where)->find();
        return $result;
    }
    // 获取全部申请信息
    public function get_all_sply(){

    }
    // 获取已抢定的申请
    public function get_yes_sply(){

    }
    public function set_trade_num(){

    }
    //显示有多少家已经咨询该地区的代理
    public function show_hot(){
        logger('显示地区热点咨询');
        $post = I();
        logger('携带参数:'.var_export($post,TRUE)); //debug
        $id = $post['id']; //申请记录ID
        $sply = D('sply');
        $where = array(
            'id' => $id
        );
        $result = $sply->where($where)->field()->find();
        if($result){
            logger('查询到用户申请记录'); 
            //查询客服电话
            $pro_where = array(
                'id' => $result['uid']
            );
            $admin = D('admin');
            $phone = $admin->where($pro_where)->find();
            //查询是否已被买断
            $where = array();
            $where = array(
                'sply_area' => $result['sply_area'],
                'pro_id' => $result['pro_id'],
                'is_qd' => 1,
            );
            $payis = $sply->where($where)->find();
            if($payis){
                logger('用户咨询申请的地区已有其他用户取得代理权!'."\n");
                $where = array();
                $where = array(
                    'sply_area' => $result['sply_area'],
                    'pro_id' => $result['pro_id']
                );
                $allsply = $sply->where($where)->select();
                $num = count($allsply);
                $this->assign('splys',$allsply);
                $this->assign('hotline',$phone['phone']);
                $this->assign('pid',$result['pro_id']);

                 $data=array(
                'num'=>$num, //申请数量
                'area'=>$payis['sply_area'],
                'name'=>$payis['sply_name']
                    );
                $this->assign('title',"申请详情");
                $this->assign('data',$data);
                $this->assign('res','no');
                $this->assign('results','来晚了一步！代理权已被抢走！');
                $this->assign('img','/Public/xin_ceshi/img/sorry.png');
                $this->display('result');
            }else{
                logger('该地区代理权还未被买断!-->去显示热点');
                $where = array();
                $where = array(
                    'sply_area' => $result['sply_area'],
                    'pro_id' => $result['pro_id']
                );
                $allsply = $sply->where($where)->select();
                $num = count($allsply);
                $this->assign('splys',$allsply);
                $this->assign('hotline',$phone['phone']);

                $data=array(
                'num'=>$num, //申请数量
                'area'=>$result['sply_area'],
                'name'=>""
                    );
                $this->assign('title',"申请详情");
                $this->assign('data',$data);
                $this->assign('res','yes');
                $this->assign('results','恭喜您，该地区的代理权还未被抢订！');
                $this->assign('img','/Public/xin_ceshi/img/happy.png');
                $this->display('result');
            }
        }else{
            logger('未未未查询到用户申请记录'."\n"); 
            $this->assign('title',"申请详情-不明");
            $this->assign('hotline',$phone['phone']);
            $this->assign('res','no');
            $this->assign('results','很抱歉,该地区申请情况不明,请继续关注！');
            $this->assign('img','/Public/xin_ceshi/img/unknown.png');
            $this->display('result');
        }
    }
}