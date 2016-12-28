<?php
namespace Home\Controller;
use Think\Controller;
class WeipayController extends Controller {
    public function _initialize(){
        ini_set('date.timezone','Asia/Shanghai');
        header("content-type:text/html; charset=utf-8;");
        vendor('Weipay.Config');
        vendor('Weipay.Api');
        vendor('Weipay.Data');
        vendor('Weipay.Exception');
        vendor('Weipay.Notify');
        vendor('Weipay.Js');
        vendor('Weipay.Notip');
    }
    // 异步回调处理函数
    public function notify(){
        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }