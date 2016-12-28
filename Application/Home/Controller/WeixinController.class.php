<?php
namespace Home\Controller;
use Think\Controller;
class WeixinController extends Controller {
    //GET传参，可能会存在超出长度限制的情况！！！ -->此时需要借助memcache或重新查询数据库来解决参数问题。
    public function _initialize(){
        ini_set('date.timezone','Asia/Shanghai');
        header("content-type:text/html; charset=utf-8;");
        // vendor('Weipay.Config');
        // vendor('Weipay.Api');
        // vendor('Weipay.Data');
        // vendor('Weipay.Exception');
        // vendor('Weipay.Notify');
        vendor('Weipay.Js');
    }
    //微信浏览器处理函数
    public function weixin(){
    	logger('微信浏览器处理访问===》');
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
        $action = "/index.php/Home/Weixin/sply.html"; //表单提交action 默认只提交申请没有支付
        if($program['is_pay'] == 1){ //是否支付
            logger('项目需要支付');
            $this->assign('payid',$program['weipay_id']);   //支付宝支付id
            $btn_value = '去支付'; //提交按钮 显示文字
            //支付信息
            $trade_info = array(
                'tradeno' => $this->set_trade_num($pid,$program['weipay_id'],$uid),
                'subject' => '地区代理费用',
                'boby' => '抢定地区代理权支付费用',
                'fee' => $program['pro_price']
            ); 
            $action = "/index.php/Home/Weixin/prepay.html"; //表单提交action,预支付处理
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
        $this->assign('cpu','weixin'); //标识处理浏览器类型
        $this->assign('url','http://qd.zcsmkj.com/');
        logger('下面显示申请首页'."\n"); //debug
        $this->display($templet); 
    }
    // 申请函数，不支付的情形调用
    public function sply(){
        logger('WeiXin申请处理，非支付类型');
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
            logger('申请数据写入成功');
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
    // 微信支付和提交信息处理函数
    public function prepay(){
    	logger('WeiXin申请处理，预支付处理，读取支付配置');
        $post = I();
        $data = array(
            'uid' => $post['uid'],
            'pro_id' => $post['pid'],
            'is_pay' => 1,
            'price' => $post['WIDtotal_fee'],
            'payment' => 'weipay',
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
        // $data = array_filter($data); //清空键值为空的键
        logger('传入参数:'.var_export($data,TRUE)); //debug
        if(empty($post['area']) || empty($post['phone']) || empty($post['store']) || empty($post['name'])){
            logger('申请资料不全'); //debug
            // $this->error('申请资料不全,请重新提交!');
        }
        $sply = D('sply');
        //先查询是否已有申请
        $where = array(
            'sply_area' => $post['area'],
            'sply_phone' => $post['phone']
        );
        $check = $sply->where($where)->find();
        if($check){
            logger('重复申请'); //debug
            // $this->error('已申请过了!');
        }
        $result = $sply->add($data);
        $data = array(
            'status' => 1,
            'content' => 'good'
        );
        $this->ajaxReturn($data);
        
        if($result){
            logger('申请数据写入成功,之后去微信支付');
            // //默认支付宝支付方式 配置
            // $weipay_config = C('WXPAY');
            // if($post['payid'] != 1){ 
            // //如果用户使用自有支付方式，则读取出来替换默认的支付方式
            //     $payment = D('weipayment');
            //     $where = array(
            //         'id' => $post['payid']
            //     );
            //     $pay_config = $payment->where($where)->find();
            //     $weipay_config['appid'] = $pay_config['appid'];
            //     $weipay_config['appsecret'] = $pay_config['appsecret'];
            //     $weipay_config['key'] = $pay_config['key'];
            //     $weipay_config['mchid'] = $pay_config['mchid'];
            // }
            $trade = array(
                "out_trade_no"  => $post['WIDout_trade_no'],
                "subject"   => $post['WIDsubject'],
                "total_fee" => $post['WIDtotal_fee'],
                "body"  => $post['WIDbody'],
                "show_url"  => $post['WIDshowurl'],
            );
            logger('订单信息:'.var_export($trade,TRUE)); //debug
            //①、获取用户openid 从微信例子jsapi.php文件copy
            $tools = new \JsApiPay();
            $openId = $tools->GetOpenid();
            logger('获取的用户openID:'.$openId); //debug
            //②、统一下单
            $input = new \WxPayUnifiedOrder();
            // $input->SetAppid($weipay_config['appid']); //* 设置微信分配的公众账号ID
            // $input->SetMch_id($weipay_config['mchid']); //* 获取微信支付分配的商户号的值
            // $input->SetNonce_str($weipay_config['key']); //* 设置随机字符串，不长于32位。推荐随机数生成算法

            $input->SetBody($trade['body']); //* 设置商品或支付单简要描述
            $input->SetAttach("测试"); //* 设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetOut_trade_no($trade['out_trade_no']); //* 设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
            $input->SetTotal_fee($trade['total_fee']); //* 设置订单总金额，只能为整数，详见支付金额
            $input->SetTime_start(date("YmdHis")); //* 设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            $input->SetTime_expire(date("YmdHis", time() + 600)); //* 设置订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
            $input->SetGoods_tag('test'); //获取商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠的值
            $input->SetNotify_url("http://qdl.bjletu.com/index.php/Home/Weipay/notify.html"); //* 设置接收微信支付异步通知回调地址
            $input->SetTrade_type("JSAPI"); //* 设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
            $input->SetOpenid($openId);
            $order = \WxPayApi::unifiedOrder($input);
            logger('微信需要的参数数组:'.var_export($order,TRUE)); //debug
            $jsApiParameters = $tools->GetJsApiParameters($order);
            logger('微信生产网址:'.$jsApiParameters); //debug
            //获取共享收货地址js函数参数
            $editAddress = $tools->GetEditAddressParameters();

            //③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
            /**
             * 注意：
             * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
             * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
             * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
             */
            // logger('生成微信参数：'.var_export($order,TRUE)); //debug
            $this->assign('data',$jsApiParameters);
            logger('去显示支付界面'); //debug
            $this->display('Weipay/pay');
        }else{
            logger('申请数据写入失败，提示用户重新申请');
            logger('申请数组：'.var_export($data,TRUE));
            $this->redirect('Index/sply_field');
        } 
    }
    //订单号生成函数 ，订单号格式 WD+年月日时分秒+P+项目ID+P支付ID+U+用户ID+R随机2位数 D20160622110956P1P3U190RA1
    public function set_trade_num($proid,$payid,$uid){
        // $tradeno = 'WD';
        // $tradeno .= date('YmdHis',time());
        // $tradeno .= 'P'.$proid.'P'.$payid.'U'.$uid.'R';
        $tradeno =  date('YmdHis',time());
        // $tradeno .= randnum(2);
        return $tradeno;
    }
    //回调异步处理函数
    public function notify(){
        logger('调用微信支付回调接口');
    }
    public function ceshi(){
        logger('传入参数:'.var_export($_POST,TRUE));//debug
        $data = array(
            'status' => 1,
            'content' => 'good'
        );
        $this->ajaxReturn($data);
    }
}
?>