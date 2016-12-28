<?php
namespace Admin\Controller;
use Think\Controller;
class ManageController extends Controller {
	//显示用户
	public function index(){
		logger('超级管理员:用户管理');
		$admin = D('admin');
		$admins = $admin->select();
		$this->assign('user',session('user'));
		$this->assign('admin',$admins);
		$this->assign('title','地区代理管理系统');
		$this->display();
	}
	//添加管理员用户
	public function add_admin(){
		logger('超级管理员:add用户');
		$post = I();
		logger('传入参数:'.var_export($post,TRUE)); die;//debug
		$admin = D('admin');
		$data = array(
			'user' => $post['user'],
			'pwd' => $post['pwd'],
			'register_time' => time(),
			'store_name' => $post['store_name'],
			'deadline' => time()+$post['deadline']*86400
		);
		$result = $admin->add($data);
		if($result){
			logger('超级管理员:add用户Success!');
			$ajax_data = array(
				'status' => 1,
				'content' => '添加用户成功!'
			);
		}else{
			logger('超级管理员:add用户Faild!');
			$ajax_data = array(
				'status' => 0,
				'content' => '添加用户失败,请重试!'
			);
		} 
		$this->ajaxReturn($ajax_data);
	}
	//编辑用户信息
	public function update_admin(){
		logger('超级管理员:update用户');
		$post = I();
		logger('传入参数:'.var_export($post,TRUE)); die;//debug
		$admin = D('admin');
		$data = array(
			'user' => $post['user'],
			'pwd' => $post['pwd'],
			'store_name' => $post['store_name'],
			'deadline_time' => strtotime($post['deadline_time'])
		);
		$where = array(
			'id' => $post['id']
		);
		$result = $admin->where($where)->save($data);
		if($result){
			logger('超级管理员:update用户Success!');
			$ajax_data = array(
				'status' => 1,
				'content' => '更新成功!'
			);
		}else{
			logger('超级管理员:add用户Faild!');
			$ajax_data = array(
				'status' => 0,
				'content' => '更新失败,请重试!'
			);
		} 
		$this->ajaxReturn($ajax_data);
	}
	//导出所有用户数据
	public function export_admin(){
		logger('超级管理员:export用户');
		$admin = D('admin');
		$admins = $admin->select();
		if($admins){
			$str = iconv('utf-8','gb2312',"序号,用户名,店铺名,手机号,QQ号,开通时间,服务截止日期,登录次数,最新登录,登录IP\n");
			foreach($admins as $k => $v){
				if($v['deadline_time'] == '' || $v['deadline_time'] == NULL || $v['deadline_time'] == 0){
					$deadline = iconv('utf-8','gb2312','无限期');
				}else{
					$deadline = date('Y-m-d',$v['deadline_time']);
				}
				$str .= $v['id'].','.iconv('utf-8','gb2312',$v['user']).','.iconv('utf-8','gb2312',$v['store_name']).','.$v['phone'].','.$v['qq'].','.date('Y-m-d',$v['register_time']).','.$deadline.','.$v['login_times'].','.date('Y-m-d',$v['login_time']).','.$v['login_ip']."\n";
			}
			$filename = '考勤汇总表-'.date('Ymdhis').'.csv'; //设置文件名
	        logger("导出成功\n");
	        $export = $this->export_csv($filename,$str); //导出 
		}else{
			logger('超级管理员:无用户!');
			$this->error('无用户!');
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
	//显示所有申请
	public function show_sply(){
		logger('超级管理员:显示申请数据');
        $post = I();
        // logger('携带参数：'.var_export($post,TRUE)); //debug
        $program = D('program');
        if(empty($post['id'])){
            logger('超级管理员:显示全部申请');
            $sply = D('sply');
            //联合查询
            $my_sply = $sply->table('sply a,program b')->where('a.pro_id = b.id')->field('a.*,b.pro_name')->select();
            // logger('查询结果：'.var_export($my_sply,TRUE)); //debug
            $this->assign('pro_id','all');
        }else{
        	logger('超级管理员:显示项目'.$post['id'].'的申请');
            // $where = array(
            //     'id' => $post['id']
            // );
            // $my_program = $program->field('id,pro_name')->where($where)->find();
            // $where = array(
            //     'pro_id' => $post['id'],
            //     'is_show' => 1
            // );
            $sply = D('sply');
            //联合查询
            $my_sply = $sply->table('sply a,program b')->where('a.pro_id = b.id AND b.id = '.$post['id'])->field('a.*,b.pro_name')->select();
            // $my_sply = $sply->where($where)->select();
            // foreach($my_sply as $k => $v){
            //     $my_sply[$k]['pro_name'] = $my_program['pro_name'];
            // }
            $this->assign('pro_id',$post['id']);
        }
        $programs = $program->select();
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
        $sply = D('sply');
        // logger('参数：'.var_export($post,TRUE));//debug
        //判断导出类型是否为空
        if($proid){
            $sply = D('sply');
            if($proid == 'all'){
                logger('导出全部申请用户信息-->');
                $my_sply = $sply->table('sply a,program b')->where('a.pro_id = b.id')->field('a.*,b.pro_name')->select();
                // logger('全部申请数据查询结果：'.var_export($my_sply,TRUE)); //debug
            }else{
                logger('导出项目'.$proid.'的申请用户信息-->');
                $my_sply = $sply->table('sply a,program b')->where('a.pro_id = b.id AND b.id = '.$proid)->field('a.*,b.pro_name')->select();
            }  
            $str = iconv('utf-8','gb2312',"序号,项目ID,项目名称,申请地区,申请人,联系方式,QQ号码,影楼名称,是否支付,申请状态,申请时间\n"); 
            foreach($my_sply as $k => $v){
                $id = $v['id'];
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
	// 显示所有项目
	public function show_program(){
		logger('项目列表->');
        $program = D('program');
        $my_program = $program->select();
        $this->assign('program',$my_program); 
        $this->assign('title','地区代理管理系统');
    	$this->assign('user',session('user'));
    	$this->display();
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
    //显示所有取现申请
	public function show_withdraw(){
		logger('超级管理员:显示所有取现申请');
		$withdraw = D('withdraw');
		$result = $withdraw->join('admin ON admin.id = withdraw.uid')->join('account ON account.id = withdraw.account_id')->field('admin.store_name,admin.phone,admin.qq,withdraw.*,account.account,account.name,account.branch,account.bank')->select();
		if($result){
			foreach($result as $k => $v){
				$result[$k]['account'] = trim($result[$k]['account'],' ');
				$result[$k]['account'] = str_apart($result[$k]['account'],4);
				$pass_time = time() - $v['sply_time'];
				// logger($pass_time); //debug
				$day = $pass_time%86400;
				switch($day){
					case 0:
						$result[$k]['background'] = '#39e6e6';
						$result[$k]['color'] = '#fff';
						$result[$k]['pass_time'] = second2dhis($pass_time);
						break;
					case 1:
						$result[$k]['background'] = '#e4ef3d';
						$result[$k]['color'] = '#fff';
						$result[$k]['pass_time'] = second2dhis($pass_time);
						break;
					case 2:
						$result[$k]['background'] = '#f37f40';
						$result[$k]['color'] = '#fff';
						$result[$k]['pass_time'] = second2dhis($pass_time);
						break;
					default:
						$result[$k]['background'] = '#f66180';
						$result[$k]['color'] = '#fff';
						$result[$k]['pass_time'] = second2dhis($pass_time);
						break;
				}
			}
		}else{
			logger('超级管理员:无提现申请!');
		}
		$this->assign('title','提现-地区代理管理系统');
		$this->assign('user',session('user'));
		$this->assign('withdraw',$result);
		$this->display();
	}
	//更新提现申请的状态, 注意:实际转款需要通过其他方式,这里只记录信息
	public function update_withdraw(){
		logger('超级管理员:更新提现申请的状态');
		$post = I();
		logger('超级管理员:传入参数:'.var_export($post,TRUE)); //debug
		if($post['wid'] && $post['type']){
			$withdraw = D('withdraw');
			if($post['type'] == 1){
				logger('超级管理员:更新为已转账');
				$data = array(
					'status' => 1,
					'modify_admin' => session('user'),
					'modify_time' => time()
				);
				$result = $withdraw->where(array('id'=>$post['wid']))->save($data);
				if($result){
					logger('超级管理员:更新成功!');
					$ajax_data = array(
						'status' => 1,
						'content' => '更新成功!'
					);
				}else{
					logger('超级管理员:更新失败!');
					$ajax_data = array(
						'status' => 0,
						'content' => '更新失败!'
					);
				}
			}else{
				logger('超级管理员:撤销已转账状态');
				$data = array(
					'status' => 0,
					'modify_admin' => session('user'),
					'modify_time' => time()
				);
				$result = $withdraw->where(array('id'=>$post['wid']))->save($data);
				if($result){
					logger('超级管理员:撤销成功!');
					$ajax_data = array(
						'status' => 1,
						'content' => '撤销成功!'
					);
				}else{
					logger('超级管理员:撤销失败!');
					$ajax_data = array(
						'status' => 0,
						'content' => '撤销失败!'
					);
				}
			}
		}else{
			logger('超级管理员:参数不全!');
			$ajax_data = array(
				'status' => 2,
				'content' => '参数不全!'
			);
		}
		$this->ajaxReturn($ajax_data);
	}
}