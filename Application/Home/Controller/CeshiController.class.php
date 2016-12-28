<?php
namespace Home\Controller;
use Think\Controller;
class CeshiController extends Controller {
	public function _initialize(){
        ini_set('date.timezone','Asia/Shanghai');
        header("content-type:text/html; charset=utf-8;");
        // vendor('Weipay.Config');
        vendor('Weipay.Api');
        // vendor('Weipay.Data');
        // vendor('Weipay.Exception');
        // vendor('Weipay.Notify');
        vendor('Weipay.Js');
    }
	public function show(){
		//支付信息
        $trade_info = array(
            'tradeno' => $this->set_trade_num($pid,$program['weipay_id'],$uid),
            'subject' => '地区代理费用',
            'boby' => '抢定地区代理权支付费用',
            'fee' => $program['pro_price']
        ); 


		//打印输出数组信息
		function printf_info($data)
		{
		    foreach($data as $key=>$value){
		        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
		    }
		}
		$appid = 'wx426b3015555a46be';
		$appsecret= '01c6d59a3f9024db6336662ac95c8e74';
		$mchid = '1225312702';
		$key = 'e10adc3949ba59abbe56e057f20f883e';
		// $wxpay_config = new WxPayConfig($appid,$appsecret,$mchid,$key);
		//①、获取用户openid
		$tools = new \JsApiPay();
		$openId = $tools->GetOpenid();
		logger('openid:'.$openId); //debug
		//②、统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
		$input->SetTotal_fee("1");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://qdl.bjletu.com/index.php/home/ceshi/notify.php");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input);
		logger('order:'.var_export($order,TRUE)); //debug
		echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters($order);

		//获取共享收货地址js函数参数
		$editAddress = $tools->GetEditAddressParameters();
		$this->assign('para',$jsApiParameters);
		$this->display('Weipay/pay');
	}
	public function notify(){

	}
	//订单号生成函数 ，订单号格式 WD+年月日时分秒+P+项目ID+P支付ID+U+用户ID+R随机2位数 D20160622110956P1P3U190RA1
    public function set_trade_num($proid,$payid,$uid){
        $tradeno = 'WD';
        $tradeno .= date('YmdHis',time());
        $tradeno .= 'P'.$proid.'P'.$payid.'U'.$uid.'R';
        // $tradeno =  date('YmdHis',time());
        $tradeno .= randnum(2);
        return $tradeno;
    }
}