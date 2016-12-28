<?php
namespace Home\Controller;
use Think\Controller;
class AlipayController extends Controller{
	public function _initialize(){
		ini_set('date.timezone','Asia/Shanghai');
		header("content-type:text/html; charset=utf-8;");
		vendor('Alipay.Core');
		vendor('Alipay.Md5');
		vendor('Alipay.Notify');
		vendor('Alipay.Submit');
	}
	//预留
	public function index(){

	}
	//处理同步返回
	public function return_url(){

	}
	//异步通知处理函数
	public function notify_url(){

	}
	//预支付处理函数
	public function pay1(){
		logger('支付宝支付预处理(展示订单信息)开始-->');
		$post = I();
		logger('访问参数：'.var_export($post,TRUE));
		$alipay_config = $post['config'];
		logger('支付配置参数：'.var_export($alipay_config,TRUE)); //debug
		$trade = $post['trade'];
		logger('订单参数：'.var_export($trade,TRUE)); //debug
		$parameter = array($alipay_config,$trade);
		logger('组合参数：'.var_export($parameter,TRUE)); //debug
		$parameter['_input_charset'] = trim(strtolower($alipay_config['input_charset']));
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		logger('生成支付宝网址：'.$html_text);

		$this->assign('html_text',$html_text);
		$this->display();
	}
	//预支付处理函数
	public function pay(){
		logger('支付宝支付预处理(展示订单信息)开始-->');
		$parameter = I();
		$parameter = json_decode($parmeter,TRUE);
		logger('访问参数：'.var_export($parameter,TRUE));
		$parameter['_input_charset'] = trim(strtolower($parameter['input_charset']));
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		logger('生成支付宝网址：'.$html_text);
		$this->assign('html_text',$html_text);
		$this->display();
	}
}