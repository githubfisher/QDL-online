<?php
namespace Home\Controller;
use Think\Controller;
class OtherController extends Controller {
    public function _initialize(){
        ini_set('date.timezone','Asia/Shanghai');
        header("content-type:text/html; charset=utf-8;");
        vendor('Alipay.Submit');
        vendor('Alipay.Core');
        vendor('Alipay.Md5');
    }
	//非微信浏览器处理函数
    public function other(){
    	logger('普通浏览器处理访问===》');
        $post = $_GET;
        logger('携带参数：'.var_export($post,TRUE)); //debug
        $index = A('index');
        $pid = $post['id']; //项目id
        $program = $index->get_program($pid);
        $uid = $program['uid'];
        $store = $index->get_store($uid);
        // 若店铺查找不到，说明项目记录中的店铺id非法
        if(!$store){
            $this->error('非法店铺ID！');
        } 
        $sply = D('sply');
        $where = array('pro_id'=>$pid,'is_qd'=>1);
        $sply_user = $sply->where($where)->select();
        // $s = $sply->getLastsql(); //debug
        // logger($s); //debug
        // logger('抢订名单'.var_export($sply_user,TRUE)); //debug
        $happy = '';
        foreach($sply_user as $k => $v){
            if($k == 9){
                break;
            }else{
                $happy .= '恭喜'.$v['sply_store_name'].$v['sply_name'].'抢订'.$v['sply_area'].'的代理权！  ';
            }
        }
        $btn_value = '提交申请';
        $action = "/index.php/Home/Other/sply.html')}"; //表单提交action 默认只提交申请没有支付
        if($program['is_pay'] == 1){ //是否支付
            logger('项目需要支付');
            $this->assign('payid',$program['otherpay_id']);   //支付宝支付id
            $btn_value = '去支付'; //提交按钮 显示文字
            //支付信息
            $trade_info = array(
                'tradeno' => $this->set_trade_num($pid,$program['otherpay_id'],$uid),
                'subject' => '地区代理费用',
                'boby' => '抢定地区代理权支付费用',
                'fee' => $program['pro_price']
            ); 
            $action = "/index.php/Home/Other/prepay.html"; //表单提交action,预支付处理
        }
        $this->assign('trade',$trade_info);
        $this->assign('btn',$btn_value);
        $this->assign('action',$action);
		// $templet = 'Index/other/templet'.$pro['templet_id'];
        if($post['tip'] == 1){ //提示
            $this->assign('tip','yes');
        }
        $templet = 'Index/index';
		$this->assign('title',$program['pro_name']); //项目标题
        $this->assign('happy',$happy); //已成功抢定的地区信息
        $this->assign('store',$store['store_name']);
        $this->assign('hotline',$store['phone']);
        $this->assign('qq',$store['qq']);
        $this->assign('erweicode',$store['ercode']);
        $this->assign('describe',chansfer_to_html($program['pro_content']));
        $this->assign('deadline',date('Y-m-d H:i:s',$program['finish_time']));
        $this->assign('pid',$pid);
        $this->assign('uid',$uid);
        $this->assign('cpu','other'); //标识处理浏览器类型
        $this->assign('url','http://qd.zcsmkj.com/');
 		$this->display($templet); 
    }
    //支付宝支付和申请提交处理函数
    public function alipay(){
    	logger('提交申请&支付宝支付');
    	$post = I();
    	$payid = $post['payid'];
    	$data = array(
    		'uid' => '111',
    		'pro_id' => '1111',
    		'sply_time' => time(),
    		'sply_phone' => $post['phone'],
    		'sply_name' => $post['name'],
    		'sply_qq' => $post['qq'],
    		'sply_area' => $post['area'],
    		'sply_store_name' => $post['store'],
    		'sply_email' => $post['email'],
    		'sply_ip' => get_client_ip(),
    		'payment_id' => $payid,
    		'payment' => '支付宝',
    		'is_pay' => 1,
    		'price' => $post['WIDtotal_fee']
    	);
    	$sply = D('sply');
    	$result = $sply->add($data);
    	if($result){
    		logger('申请数据提交成功！');
    		logger('支付开始->');
    	}else{
    		logger('申请数据提交失败');
    		$this->redirect('data');
    	}
    }
    //订单号生成函数 ，订单号格式 AD+年月日时分秒+P+项目ID+P支付ID+U+用户ID+R随机2位数 D20160622110956P1P3U190RA1
    public function set_trade_num($proid,$payid,$uid){
        $tradeno = 'AD';
        $tradeno .= date('YmdHis',time());
        $tradeno .= 'P'.$proid.'P'.$payid.'U'.$uid.'R';
        $tradeno .= randnum(2);
        return $tradeno;
    }
    //处理申请（不支付）
    public function sply(){
        logger('Other申请处理，非支付类型');
        $post = I();
        $data = array(
            'uid' => $post['uid'],
            'pro_id' => $post['pid'],
            'is_pay' => 0,
            'price' => 0,
            'payment' => 'nopay',
            'pay_is' => 2,
            'pay_time' => 0,
            'pay_money' => 0,
            'sply_time' => time(),
            'sply_ip' => get_client_ip(),
            'sply_area' => $post['area'],
            'sply_store_name' => $post['store'],
            'sply_name' => $post['name'],
            'sply_phone' => $post['phone'],
            'sply_qq' => $post['qq'],
            'sply_email' => $post['email'],
            'payment_id' => $post['payid'],
            'tradeno' => 0
        );
        // $data = array_filter($data); //清空键值为空的键
        $sply = D('sply');
        //先查证是否重复申请
        $where = array(
            'sply_area' => $post['area'],
            'sply_phone' => $post['phone'],
            'pro_id' => $post['pid'],
            'uid' => $post['uid']
        );
        $repeat = $sply->where($where)->find();
        if($repeat){
            logger('已存在相同项目相同用户相同地区相同手机号申请,申请拒绝!'."\n");
            $data = array(
                'status' => 2,
                'content' => '请勿重复提交申请!'
            );
            $this->ajaxReturn($data);
        }
        $result = $sply->add($data);
        if($result){
            logger('申请数据写入成功'."\n");
            // $this->redirect('Index/sply_success');
            $data = array(
                'status' => 1,
                'content' => '申请成功!去看咨询详情',
                'sply' => $result
            );
        }else{
            logger('申请数据写入失败');
            logger('申请数组：'.var_export($data,TRUE));
            // $this->redirect('Index/sply_field');
            $data = array(
                'status' => 0,
                'content' => '申请失败,请重试!'
            );
        }
        $this->ajaxReturn($data);
    }
    //预支付函数
    public function prepay(){
        logger('Other申请处理，预支付处理，读取支付配置');
        $post = I();
        $data = array(
            'uid' => $post['uid'],
            'pro_id' => $post['pid'],
            'is_pay' => 1,
            'price' => $post['WIDtotal_fee'],
            'payment' => 'alipay',
            'pay_is' => 0,
            'pay_time' => 0,
            'pay_money' => 0,
            'sply_time' => time(),
            'sply_ip' => get_client_ip(),
            'sply_area' => $post['area'],
            'sply_store_name' => $post['store'],
            'sply_name' => $post['name'],
            'sply_phone' => $post['phone'],
            'sply_qq' => $post['qq'],
            'sply_email' => $post['email'],
            'payment_id' => $post['payid'],
            'tradeno' => $post['WIDout_trade_no']
        );
        $data = array_filter($data); //清空键值为空的键
        $sply = D('sply');
        $result = $sply->add($data);
        if($result){
            logger('申请数据写入成功,之后去支付');
            //默认支付宝支付方式 配置
            $alipay_config = C('ALIPAY');
            if($post['payid'] != 1){ 
            //如果用户使用自有支付方式，则读取出来替换默认的支付方式
                $payment = D('alipayment');
                $where = array(
                    'id' => $post['payid']
                );
                $pay_config = $payment->where($where)->find();
                $alipay_config['partner'] = $pay_config['partner'];
                $alipay_config['seller_id'] = $pay_config['partner'];
                $alipay_config['key'] = $pay_config['key'];
            }
            $trade = array(
                "out_trade_no"  => $post['WIDout_trade_no'],
                "subject"   => $post['WIDsubject'],
                "total_fee" => $post['WIDtotal_fee'],
                "body"  => $post['WIDbody'],
                "show_url"  => $post['WIDshowurl'],
            );
            $parameter = array_merge($alipay_config,$trade);
            $parameter['_input_charset'] = trim(strtolower($parameter['input_charset']));
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            logger('生成支付宝网址：'.$html_text);
            $this->assign('html_text',$html_text);
            $this->display('Alipay/pay');

        }else{
            logger('申请数据写入失败，提示用户重新申请');
            logger('申请数组：'.var_export($data,TRUE));
            $this->redirect('Index/sply_field');
        } 
    }
}
?>