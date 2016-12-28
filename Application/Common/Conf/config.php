<?php
///////定义银行简写///////////////
$bank = array(
	'ICBC' => "中国工商银行",
	'CCB' => "中国建设银行",
	'ABC' => "中国农业银行",
	'BOC' => "中国银行",
	'CMBC' => "中国民生银行",
	'CMB' => "招商银行",
	'CIB' => "兴业银行",
	'BCM' => "交通银行",
	'CEB' => "中国光大银行",
	'GDB' => "广东发展银行",
	'BOB' => "北京市商业银行",
	'SDB' => "深圳发展银行",
	'CITIC' => "中信银行",
);
//↓↓↓↓↓↓↓↓↓↓支付宝支付配置信息↓↓↓↓↓↓↓↓↓↓
/* *
 * 配置文件
 * 版本：3.4
 * 修改日期：2016-03-08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['partner']		= '2088021265703813';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];

// MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['key']			= 'k9sd3t7mw8ugfk968564q8okc057j0nk';
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = "http://qd.zcsmkj.com/index.php/Home/alipay/notify_url.php";

// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://qd.zcsmkj.com/index.php/Home/alipay/return_url.php";

//签名方式
$alipay_config['sign_type']    = strtoupper('MD5');

//字符编码格式 目前支持utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

// 支付类型 ，无需修改
$alipay_config['payment_type'] = "1";
		
// 产品类型，无需修改
$alipay_config['service'] = "alipay.wap.create.direct.pay.by.user";

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑支付宝配置END↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//↓↓↓↓↓↓↓↓↓↓微信支付配置信息↓↓↓↓↓↓↓↓↓↓
$wxpay_config['appid'] = 'wx426b3015555a46be';
$wxpay_config['mchid'] = '1225312702';
$wxpay_config['key'] = 'e10adc3949ba59abbe56e057f20f883e';
$wxpay_config['appsecret'] = '01c6d59a3f9024db6336662ac95c8e74';
$wxpay_config['sslcret_path'] = '../cert/apiclient_cert.pem';
$wxpay_config['sslkey_path'] = '../cert/apiclient_key.pem';
$wxpay_config['curl_proxy_host'] = '0.0.0.0';
$wxpay_config['curl_proxy_port'] = '0';
$wxpay_config['report_levenl'] = '1';
//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑微信配置END↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//智诚软件->
//↓↓↓↓↓↓↓↓↓↓微信支付配置信息↓↓↓↓↓↓↓↓↓↓
$wpay_config['appid'] = 'wx1334b973fcb18fd8';
$wpay_config['mchid'] = '1250267501';
$wpay_config['key'] = 'xrre68qEkLlkq0Z0s6zwQ0rQm6rbzraZ';
$wpay_config['appsecret'] = '0a0da6fadbd48765a5ade0b0bb69dba2'; 
$wpay_config['sslcret_path'] = '../cert/apiclient_cert.pem';
$wpay_config['sslkey_path'] = '../cert/apiclient_key.pem';
$wpay_config['curl_proxy_host'] = '0.0.0.0';
$wpay_config['curl_proxy_port'] = '0';
$wpay_config['report_levenl'] = '1';
//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑微信配置END↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
return array(
	//'配置项'=>'配置值'
	"DB_TYPE"=>"mysql",
	"DB_HOST"=>"localhost",
	"DB_NAME"=>"qiangdaili",
	"DB_USER"=>"root",
	"DB_PWD"=>"root",
	"DB_PORT"=>"3306",
	"DB_PREFIX"=>"",
	'DB_CHARSET'=>'utf8',
	// 支付宝支付接口，以二维数组的形式返回
	'ALIPAY' => $alipay_config,
	'WXPAY' => $wxpay_config,
	'WPAY' => $wpay_config,
	'BANK' => $bank
	
);