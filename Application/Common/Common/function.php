<?php
// 调用CURL函数，组合访问参数和URL，得到返回值
function getXML($url,$xml){
		// $header = 'Content-type:text/xml;';
		$this_header = array(
			"content-type: application/x-www-form-urlencoded; 
			charset=GB2312"
		);

		
		$url .= $xml;
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		// curl_setopt($ch, CURLOPT_ENCODING, "");
		$output = curl_exec($ch);
		curl_close($ch);
	    return $output;
    }
// 日志函数
function logger($log_content){ 
	//$folder = "./Log/";
	$folder = './Log/'.date("Y").'/'.date("m").'/'.date("d").'D/';
	if(!file_exists($folder)){
		mkdir($folder,0777,TRUE);
	}
	for($i=5;$i--;$i>0){
		$max_size = 100000;   
    	$log_filename = $folder.'/'.date('Ymd',time()).'-'.$i.'.txt';
		if(file_exists($log_filename) && (abs(filesize($log_filename)) >= $max_size)){
			$next = $i + 1;
			$log_filename = $folder.'/'.date('Ymd',time()).'-'.$next.'.txt';
			//新内容写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
    		file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content." \r\n", FILE_APPEND);  
		}elseif(file_exists($log_filename) && (abs(filesize($log_filename)) < $max_size)){
			//写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
			file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content." \r\n", FILE_APPEND); 
		}else{
			if($i == 1){
				//写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
				file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content." \r\n", FILE_APPEND); 
			}
		}
	} 
}
// 将数组转换成XML字符串
function transXML($arr){
	$xmlTpl = "<aa>%s</aa><yy>%s</yy><uu>%s</uu>";
	$result = sprintf($xmlTpl,$arr['operation'],$arr['dogid'],$arr['username']);
	return $result;
}
//二维数组按指定项指定顺序排序
function array_sort($array,$key,$sort='ASC'){
	$array_temp = array();
	//循环数组，将需要排序的键值取出组成新数组
	foreach($array as $k => $v){
		$array_temp[$k] = $v[$key];
	}
	// 判断排序类型
	if($sort === 'ASC'){
		asort($array_temp);
	}else{
		arsort($array_temp);
	}
	$result_array = array();
	// 按照新顺序重新组装数组
	foreach($array_temp as $k => $v){
		$result_array[] = $array[$k];
	}
	return $result_array;
}
//Jpush 函数 Start //
//极光推送
//初始化Jpush
function jpush_init(){
	$app_key = C('DevKey');
	$master_secret = C('ApiDevSecret');
	// 初始化
	$client = new \JPush($app_key, $master_secret);
	return $client;
}
// Jpush简单推送函数
function simple_push($platform = 'all' ,$content){
	// 初始化
	$client = jpush_init();
	// 简单推送示例
	$result = $client->push()
    ->setPlatform($platform) //'all'
    ->addAllAudience()
    ->setNotificationAlert($content) //'Hi, JPush'
    ->send();

	echo 'Result=' . json_encode($result) . '</br>';
	logger('JPush---简单推送----结果：'.json_encode($result).'------完毕------');
}
// 完整的推送示例,包含指定Platform,指定Alias,Tag,指定iOS,Android notification,指定Message等
function push($platform = 'all',$alias,$tags,$content,$android,$ios,$msg,$option){
	// 初始化
	$client = jpush_init();
	$result = $client->push()
				    ->setPlatform($platform) //array('ios', 'android')
				    ->addAlias($alias) //'alias1'
				    ->addTag($tags) //array('tag1', 'tag2')
				    ->setNotificationAlert($content) //'Hi, JPush'
				    ->addAndroidNotification($android['alert'],$android['title'],1,$android['extras']) //'Hi, android notification', 'notification title', 1, array("key1"=>"value1", "key2"=>"value2")
				    ->addIosNotification($ios['alert'],$ios['title'],JPush::DISABLE_BADGE,true,$ios['category'],$ios['extras']) //"Hi, iOS notification", 'iOS sound', JPush::DISABLE_BADGE, true, 'iOS category', array("key1"=>"value1", "key2"=>"value2")
				    ->setMessage($msg['content'],$msg['title'],$msg['type'],$msg['extras']) //"msg content", 'msg title', 'type', array("key1"=>"value1", "key2"=>"value2")
				    ->setOptions($option['tips'],$option['time'],$option['order'],$option['toggle']) //100000, 3600, null, false
				    ->send();
	echo 'Result=' . json_encode($result) . '</br>';
	logger('JPush---完整推送----结果：'.json_encode($result).'------完毕------');
}
//定时推送消息
function set_time_push($platform = 'all',$alert,$schedule){
	// 初始化
	$client = jpush_init();

	$payload = $client->push()
				    ->setPlatform($platform)  //"all"
				    ->addAllAudience()
				    ->setNotificationAlert($alert) //"Hi, 这是一条定时发送的消息"
				    ->build();

	// 创建一个2016-12-22 13:45:00触发的定时任务
	$response = $client->schedule()->createSingleSchedule($schedule['message'], $payload, $schedule['time']); //"每天14点发送的定时任务", $payload, array("time"=>"2016-12-22 13:45:00")
	echo 'Result=' . json_encode($response) . '</br>';
	logger('JPush---定时推送----结果：'.json_encode($result).'------完毕------');
}
//Jpush,发送短信推送消息
function sms_jpush($platform = 'all',$tags,$alert,$sms){
	// 初始化
	$client = jpush_init();

	$result = $client->push()
				    ->setPlatform($platform)  //'all'
				    ->addTag($tags)  //'tag1'
				    ->setNotificationAlert($alert) //"Hi, JPush SMS"
				    ->setSmsMessage($sms['message'], $sms['time'])  //'Hi, JPush SMS', 60
				    ->send();

	echo 'Result=' . json_encode($result) . '</br>';
	logger('JPush---短信推送----结果：'.json_encode($result).'------完毕------');
}
//Jpush ,自定义简单发送
function jpush($array){
	// 初始化
	$client = jpush_init();
	// 简单推送示例
	$result = $client->push()
    ->setPlatform($array['platform']) //'all'
    ->addAlias($array['alias'])
    ->addAndroidNotification($array['msg']['content'],$array['msg']['title'],2,$array['msg']['message'])
    ->addIosNotification($array['msg']['content'],$array['msg']['title'],JPush::DISABLE_BADGE,true,$array['msg']['category'],$array['msg']['message'])
    ->setOptions(100000, 3600, null, false) //100000, 3600, null, false
    ->send();

	return json_encode($result);
}
//Jpush 自定义广播函数__全平台全用户广播
function Jboardcast($array){
	// 初始化
	$client = jpush_init();
	// 简单推送示例
	$result = $client->push()
    ->setPlatform($array['platform']) //'all'
    ->addAllAudience()
    ->addAndroidNotification($array['msg']['content'],$array['msg']['title'],2,$array['msg']['message'])
    ->addIosNotification($array['msg']['content'],$array['msg']['title'],JPush::DISABLE_BADGE,true,$array['msg']['category'],$array['msg']['message'])
    ->setOptions(100000, 3600, null, false) //100000, 3600, null, false
    ->send();

	return json_encode($result);
}
//Jpush 自定义广播函数_全平台按标签tag广播 2016-5-24
function Jboardcast_Tag($array){
	// 初始化
	$client = jpush_init();
	// 简单推送示例
	$result = $client->push()
    ->setPlatform($array['platform']) //'all'
    ->addTag($array['tag'])
    ->addAndroidNotification($array['msg']['content'],$array['msg']['title'],2,$array['msg']['message'])
    ->addIosNotification($array['msg']['content'],$array['msg']['title'],JPush::DISABLE_BADGE,true,$array['msg']['category'],$array['msg']['message'])
    ->setOptions(100000, 3600, null, false) //100000, 3600, null, false
    ->send();

	return json_encode($result);
}
function getDevices(){
	// 初始化
	$client = jpush_init();

	//从现在不如写一个类(控制器),各方法写进去.随时调用.

	// 获取指定设备的Mobile,Alias,Tags等信息
	$result = $client->device()->getDevices($REGISTRATION_ID1);
	return $result;
}
function getTags(){
	// 初始化
	$client = jpush_init();
	// 获取Tag列表
	$result = $client->device()->getTags();
	return $result;
}
function isDeviceInTag(){
	// 初始化
	$client = jpush_init();
	// 判断指定RegistrationId是否在指定Tag中
	$result = $client->device()->isDeviceInTag($REGISTRATION_ID1, $TAG1);
	return $result;
}
function getAliasDevices(){
	// 初始化
	$client = jpush_init();
	// 获取指定Alias下的设备
	$result = $client->device()->getAliasDevices($ALIAS1);
	return $result;
}
function updateDevice(){
	// 初始化
	$client = jpush_init();
	// 更新指定的设备的Alias(亦可以增加/删除Tags)
	$result = $client->device()->updateDevice($REGISTRATION_ID1, $ALIAS1);
	return $result;
}
function updateTag(){
	// 初始化
	$client = jpush_init();
	// 增加指定Tag下的设备(亦可以删除设备)
	$result = $client->device()->updateTag($TAG1, array($REGISTRATION_ID1, $REGISTRATION_ID2));
	return $result;
}
function deleteAlias(){
	// 初始化
	$client = jpush_init();
	// 删除指定Alias
	$result = $client->device()->deleteAlias($ALIAS1);
	return $result;
}
//Jpush 函数 END //
//中文日期时间处理函数,返回格式 " 2016-06-03 00:00:00 "
function chtimetostr($string){
	$array = array(
		0 => '年',
		1 => '月',
		2 => '日',
		3 => '时',
		4 => '分',
		5 => '秒'
	);
	foreach($array as $k => $v){
		if($k == 0 || $k == 1){
			if (strstr($string,$v)) {
					$string = str_replace($v,'-',$string);
				}
		}elseif($k == 3 || $k == 4){
			if (strstr($string,$v)) {
					$string = str_replace($v,':',$string);
				}
		}else{
			if (strstr($string,$v)) {
					$string = str_replace($v,'',$string);
				}
		}
	}
	return $string;
}
// 环信处理函数，初始化
function easemob_init(){
	$option = C('Easemob');
	$client = new \Easemob($option);
	return $client;
}
function easemob_create_user($user,$pwd){
	//初始化
	$client = easemob_init();
	// 新建用户
	$result  = $client->createUser($user,$pwd);
	return $result;
}
function delete_easemob_user($user){
	//初始化
	$client = easemob_init();
	// 新建用户
	$result  = $client->deleteUser($user);
	return $result;
}
function modify_easemob_pwd($user,$pwd){
	//初始化
	$client = easemob_init();
	//修改密码
	$result = $client->resetPassword($user,$pwd);
	return $result;
}
//URL_encode 基本没有用处 原来摄控本 两个相同地址会有不同结果，后来发现是少了一个“<”
function urlcode($url){
	//$url = rawurlencode(mb_convert_encoding($url, 'GB2312','UTF-8'));
	$url = urlencode($url);
	$a = array('%3A','%2F','%40');
	$b = array(':','/','@');
	$url = str_replace($a, $b, $url); 
	return $url;
}
function randnum($sum){
	$num = '';
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	while(strlen($num) < $sum){
		$num .= substr($string,mt_rand()/36,1); 
	}
	return $num;
}
// 发送短信
function sendrawmessage($mobile,$message){
	$url = "http://www.lx198.com/sdk/send";
	$key['accId'] = '120214';
	$key['accName'] = '2459598020@qq.com';
	$key['accPwd'] = strtoupper(md5('Fang8707'));
	$key['aimcodes'] = $mobile;
	$key['dataType'] = "json";
	$key['content'] = $message;
	$key['bizId'] = date("YmdHis",time()+(6*3600));
	var_dump($key);
	$o="";  
	foreach ($key as $k=>$v)  
	{  
	    $o.= "$k=".urlencode($v)."&";  
	}  
	$post_data=substr($o,0,-1);  
	$ch = curl_init();  
	curl_setopt($ch, CURLOPT_POST, 1);  
	curl_setopt($ch, CURLOPT_HEADER, 0);  
	curl_setopt($ch, CURLOPT_URL,$url);  
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	//为了支持cookie  
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  
	$result = curl_exec($ch);
	curl_close ( $ch );
	$result = json_decode($result,true); 
	// var_dump($result);
	return $result;
}
//转译数据库中存储的HTML代码中的<>括号的转译符
function chansfer_to_html($str){
	$str = str_replace('&lt;','<', $str);
	$str = str_replace('&gt;', '>', $str);
	$str = str_replace('&quot;','"', $str);
	return $str;
}
/**
** 截取中文字符串
**/
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr")){
        $slice= mb_substr($str, $start, $length, $charset);
    }elseif(function_exists('iconv_substr')) {
        $slice= iconv_substr($str,$start,$length,$charset);
    }else{
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }    
        $fix='';
        if(strlen($slice) < strlen($str)){
            $fix='...';
        }
        return $suffix ? $slice.$fix : $slice;
}
//转换秒数成天时分秒
function second2dhis($second){
	if($second < 86400){ //如果不足一天
		// logger('小于一天'); //debug
		$format_time = gmstrftime('%H时%M分%S秒',$second);
	}else{
		// logger('大于一天'); //debug
		$time = explode(' ', gmstrftime('%j %H %M %S', $second));//Array ( [0] => 04 [1] => 14 [2] => 14 [3] => 35 ) 
		$format_time = ($time[0]-1).'天'.$time[1].'时'.$time[2].'分'.$time[3].'秒';
	}
	return $format_time;
}
function str_apart($str,$n=4,$order = true){
	$size = strlen($str);
	// logger($size." ".$size/$n);//debug
	$string = '';
	if($order){ //正序 从左向右
		for($i=0;$i<=$size/$n;$i++){
			$string .= substr($str,$i*$n,$n).' ';
		}
	}else{//反序
		for($i=0;$i<=$size/$n;$i++){
			if($i == 0){
				$left = $size%$n;
				$string .= substr($str,$i*$n,$left).' ';
			}else{
				$i--;
				$string .= substr($str,$left+$i*$n,$n).' ';
				$i++;
			}
		}
	}
	$string = trim($string,' ');
	return $string;
}
?>