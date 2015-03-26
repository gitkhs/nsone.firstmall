<?php
class Joincheckmodel extends CI_Model {
	function __construct() {
		parent::__construct();

	}

	//로그인시 체크이벤트 참여
	function login_joincheck($member_seq){
		$today = date('Y-m-d');

		//진행되는 이벤트 있는 확인
		$sql=$this->db->query("SELECT *
			FROM fm_joincheck as evt
		 	where check_state != 'stop' and check_type = 'login' and current_date() between start_date and end_date");
		$state = $sql->row_array();

		if( $state['joincheck_seq'] ){
			//출첵 이벤트
			$result = $this->joincheck($state['joincheck_seq'],$member_seq);
			if($result['code']=='success' || $result['code']=='emoney_pay'){
				 $lgn_check =$result;
			}else{
			 	$lgn_check = '';
			}
		} else{
			$lgn_check='';
		}
		return $lgn_check;
	}


	//출석체크 프로세서
	function joincheck($joincheck_seq,$member_seq,$comment=''){

		$params['member_seq']		=  $member_seq;		//회원
		$params['check_comment']	=  $comment;			//출석맨트
		$params['joincheck_seq']	=  $joincheck_seq;			//출석맨트

		$today = date('Y-m-d');
		$prev = date('Y-m-d',strtotime('-1 day',strtotime($today)));


		//진행중인지 확인
		$sql = $this->db->query("select
			if(check_state = 'stop','stop',if(current_date() between start_date and end_date,'ing',if(end_date < current_date(),'end','before')))
			 as status
		from fm_joincheck where joincheck_seq=".$joincheck_seq);
		$state = $sql->row_array();

		//진행중일때
		if($state['status']=='ing'){
			$query = $this->db->get_where('fm_joincheck',array('joincheck_seq'=>$joincheck_seq));
			$joincheck = $query -> row_array();
			$clear_type = $joincheck['check_clear_type'];

			//당일날 중복등록인지 확인하기
			$where=array('joincheck_seq'=>$joincheck_seq,'member_seq'=>$params['member_seq'],'check_date'=>$today);
			$qcheck = $this -> db -> get_where('fm_joincheck_list',$where);
			$recheck = $qcheck -> row_array();

			//중복이 아닐경우
			if(!$recheck){
				//결과값에 저장하기 위해서 데이터 가져오기
				$where = array('joincheck_seq'=>$joincheck_seq,'member_seq'=>$params['member_seq']);
				$query = $this->db->get_where('fm_joincheck_result',$where);
				$jccheck = $query -> row_array();

				$aldata = array(
				'member_seq' 		=> $params['member_seq'],
				'joincheck_seq'		=> $params['joincheck_seq']
				);

				//기존 결과값 수정
				if($jccheck){

					// 달성타입이 연속 출석인지 확인
					if($clear_type=='straight'){

						$where=array('joincheck_seq'=>$joincheck_seq,'member_seq'=>$params['member_seq'],'check_date'=>$prev);
						$query = $this -> db -> get_where('fm_joincheck_list',$where);
						$jrcst = $query -> row_array();
						//연속 타입이고 전날 체크했을 경우
						if($jrcst){
							$aldata['straight_cnt'] = $jccheck['straight_cnt'] + 1;
						}else{
							$aldata['straight_cnt'] = 1;
						}
						$aldata['count_cnt'] = $jccheck['count_cnt'] + 1;

						//목표달성여부체크
						if($joincheck['check_clear_count'] == $aldata['straight_cnt']){
							$aldata['clear_success'] = 'Y';
							$aldata['clear_suc_date'] = date('Y-m-d H:i:s');
							//목표 달성시 적립 지급
							if($jccheck['emoney_pay'] == 'N'){
								$upsql = "update fm_member set emoney = emoney+".$joincheck['emoney']." where member_seq = ".$params['member_seq'];
								$upqry = $this->db->query($upsql);
								if($upqry){
									$aldata['emoney_pay']='Y';
									$aldata['emoney_pay_date']=date('Y-m-d H:i:s');
									$aldata['emoney']=$joincheck['emoney'];

									$limit_date = "";
									if($joincheck['reserve_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['reserve_year']));//$joincheck['reserve_year']."-12-31";
									}else if($joincheck['reserve_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['reserve_direct'], date("d"), date("Y")));
									}
									//emoney history
									$this->load->model('Emoneymodel');
									$hparam['member_seq']	= $params['member_seq'];
									$hparam['type']			= 'joincheck';
									$hparam['emoney']		= $joincheck['emoney'];
									$hparam['remain']		= $joincheck['emoney'];
									$hparam['memo']			= '출석체크 이벤트';
									$hparam['regist_date']	= date("Y-m-d H:i:s");
									$hparam['limit_date']	= $limit_date;
									$this->Emoneymodel->emoney_write($hparam);


									### POINT
									$this->load->model('membermodel');
									$limit_date = "";
									if($joincheck['point_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['point_year']));//$joincheck['point_year']."-12-31";
									}else if($joincheck['point_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['point_direct'], date("d"), date("Y")));
									}
									$iparam['gb']			= "plus";
									$iparam['type']			= 'joincheck';
									$iparam['point']		= $joincheck['point'];
									$iparam['memo']			= '출석체크 이벤트';
									$iparam['regist_date']	= date("Y-m-d H:i:s");
									$iparam['limit_date']	= $limit_date;
									$this->membermodel->point_insert($iparam, $params['member_seq']);

								}else{
									$aldata['emoney_pay']='N';
								}
							}
						}elseif($joincheck['check_clear_count'] <= $aldata['straight_cnt']){
							$aldata['clear_success'] = $jccheck['clear_success'];
							$aldata['emoney_pay'] = $jccheck['emoney_pay'];
						}else{
							$aldata['clear_success'] = 'N';
						}

					//달성 타입이 일반일때
					}else{
						$aldata['count_cnt'] = $jccheck['count_cnt'] + 1;
						//목표달성여부체크
						if($joincheck['check_clear_count'] == $aldata['count_cnt']){
							$aldata['clear_success'] = 'Y';
							$aldata['clear_suc_date'] = date('Y-m-d H:i:s');
							//목표 달성시 적립 지급
							if($jccheck['emoney_pay'] == 'N'){
								$upsql = "update fm_member set emoney = emoney+".$joincheck['emoney']." where member_seq = ".$params['member_seq'];
								$upqry = $this->db->query($upsql);
								if($upqry){
									$aldata['emoney_pay']='Y';
									$aldata['emoney_pay_date']=date('Y-m-d H:i:s');
									$aldata['emoney']=$joincheck['emoney'];

									$limit_date = "";
									if($joincheck['reserve_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['reserve_year']));//$joincheck['reserve_year']."-12-31";
									}else if($joincheck['reserve_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['reserve_direct'], date("d"), date("Y")));
									}
									//emoney history
									$this->load->model('Emoneymodel');
									$hparam['member_seq']	= $params['member_seq'];
									$hparam['type']			= 'joincheck';
									$hparam['emoney']		= $joincheck['emoney'];
									$hparam['remain']		= $joincheck['emoney'];
									$hparam['memo']			= '출석체크 이벤트';
									$hparam['regist_date']	= date("Y-m-d H:i:s");
									$hparam['limit_date']	= $limit_date;
									$this->Emoneymodel->emoney_write($hparam);


									### POINT
									$this->load->model('membermodel');
									$limit_date = "";
									if($joincheck['point_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['point_year']));//$joincheck['point_year']."-12-31";
									}else if($joincheck['point_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['point_direct'], date("d"), date("Y")));
									}
									$iparam['gb']			= "plus";
									$iparam['type']			= 'joincheck';
									$iparam['point']		= $joincheck['point'];
									$iparam['memo']			= '출석체크 이벤트';
									$iparam['regist_date']	= date("Y-m-d H:i:s");
									$iparam['limit_date']	= $limit_date;
									$this->membermodel->point_insert($iparam, $params['member_seq']);

								}else{
									$aldata['emoney_pay']='N';
								}
							}
						}elseif($joincheck['check_clear_count'] <= $aldata['count_cnt']){
							$aldata['clear_success'] = $jccheck['clear_success'];
							$aldata['emoney_pay'] = $jccheck['emoney_pay'];
						}else{
							$aldata['clear_success'] = 'N';
						}

					}

					$this->db->where('jcresult_seq',$jccheck['jcresult_seq']);
					$rcslt = $this->db->update('fm_joincheck_result', $aldata);

				//신규 결과값 생성
				}else{

					//연속출석에 따라서 결과값 다르게 저장
					if($clear_type=='straight'){

						$aldata['straight_cnt']=1;

						//목표달성여부체크
						if($joincheck['check_clear_count'] == $aldata['straight_cnt']){
							$aldata['clear_success'] = 'Y';
							$aldata['clear_suc_date'] = date('Y-m-d H:i:s');

							//목표 달성시 적립 지급

								$upsql = "update fm_member set emoney = emoney+".$joincheck['emoney']." where member_seq = ".$params['member_seq'];
								$upqry = $this->db->query($upsql);

								if($upqry){
									$aldata['emoney_pay']='Y';
									$aldata['emoney_pay_date']=date('Y-m-d H:i:s');
									$aldata['emoney']=$joincheck['emoney'];

									$limit_date = "";
									if($joincheck['reserve_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['reserve_year']));//$joincheck['reserve_year']."-12-31";
									}else if($joincheck['reserve_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['reserve_direct'], date("d"), date("Y")));
									}
									//emoney history
									$this->load->model('Emoneymodel');
									$hparam['member_seq']	= $params['member_seq'];
									$hparam['type']			= 'joincheck';
									$hparam['emoney']		= $joincheck['emoney'];
									$hparam['remain']		= $joincheck['emoney'];
									$hparam['memo']			= '출석체크 이벤트';
									$hparam['regist_date']	= date("Y-m-d H:i:s");
									$hparam['limit_date']	= $limit_date;
									$this->Emoneymodel->emoney_write($hparam);


									### POINT
									$this->load->model('membermodel');
									$limit_date = "";
									if($joincheck['point_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['point_year']));//$joincheck['point_year']."-12-31";
									}else if($joincheck['point_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['point_direct'], date("d"), date("Y")));
									}
									$iparam['gb']			= "plus";
									$iparam['type']			= 'joincheck';
									$iparam['point']		= $joincheck['point'];
									$iparam['memo']			= '출석체크 이벤트';
									$iparam['regist_date']	= date("Y-m-d H:i:s");
									$iparam['limit_date']	= $limit_date;
									$this->membermodel->point_insert($iparam, $params['member_seq']);

							}

						}else{
							$aldata['clear_success'] = 'N';
						}

					}else{
						$aldata['count_cnt']=1;
						//목표달성여부체크
						if($joincheck['check_clear_count'] == $aldata['count_cnt']){
							$aldata['clear_success'] = 'Y';
							$aldata['clear_suc_date'] = date('Y-m-d H:i:s');

							//목표 달성시 적립 지급

								$upsql = "update fm_member set emoney = emoney+".$joincheck['emoney']." where member_seq = ".$params['member_seq'];
								$upqry = $this->db->query($upsql);
								if($upqry){
									$aldata['emoney_pay']='Y';
									$aldata['emoney_pay_date']=date('Y-m-d H:i:s');
									$aldata['emoney']=$joincheck['emoney'];

									$limit_date = "";
									if($joincheck['reserve_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['reserve_year']));//$joincheck['reserve_year']."-12-31";
									}else if($joincheck['reserve_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['reserve_direct'], date("d"), date("Y")));
									}
									//emoney history
									$this->load->model('Emoneymodel');
									$hparam['member_seq']	= $params['member_seq'];
									$hparam['type']			= 'joincheck';
									$hparam['emoney']		= $joincheck['emoney'];
									$hparam['remain']		= $joincheck['emoney'];
									$hparam['memo']			= '출석체크 이벤트';
									$hparam['regist_date']	= date("Y-m-d H:i:s");
									$hparam['limit_date']	= $limit_date;
									$this->Emoneymodel->emoney_write($hparam);


									### POINT
									$this->load->model('membermodel');
									$limit_date = "";
									if($joincheck['point_select']=='year'){
										$limit_date = date("Y-m-d", mktime(0,0,0,12, 31, date("Y")+$joincheck['point_year']));//$joincheck['point_year']."-12-31";
									}else if($joincheck['point_select']=='direct'){
										$limit_date = date("Y-m-d", mktime(0,0,0,date("m")+$joincheck['point_direct'], date("d"), date("Y")));
									}
									$iparam['gb']			= "plus";
									$iparam['type']			= 'joincheck';
									$iparam['point']		= $joincheck['point'];
									$iparam['memo']			= '출석체크 이벤트';
									$iparam['regist_date']	= date("Y-m-d H:i:s");
									$iparam['limit_date']	= $limit_date;
									$this->membermodel->point_insert($iparam, $params['member_seq']);

							}

						}else{
							$aldata['clear_success'] = 'N';
						}
					}
					$rcslt = $this->db->insert('fm_joincheck_result', $aldata);
				}

				//등록한 댓글/스템프 저장
				$data = array(
				'member_seq' 		=> $params['member_seq'],
				'joincheck_seq'		=> $params['joincheck_seq'],
				'check_date'		=> $today,
				'check_comment'		=> $params['check_comment'],
				'regist_date'		=> date('Y-m-d H:i:s')
				);

				$result = $this->db->insert('fm_joincheck_list', $data);


				if($result){
					//달성전이고 참여 성공했을때
					if($aldata['clear_success'] == 'N'){
						return array(
							'code' => 'success',
							'msg' => $joincheck['check_it']
							);
					//달성 했을때
					}elseif($aldata['clear_success'] == 'Y' && $jccheck['emoney_pay']!='Y' && $aldata['emoney_pay'] == 'Y'){
						
						$c_msg = str_replace("{emoney}",number_format($joincheck['emoney']),$joincheck['check_complete']);
						$c_msg = str_replace("{point}",number_format($joincheck['point']),$c_msg);

						return array(
							'code' => 'emoney_pay',
							'msg' => $c_msg
							);
					//달성치를 넘고 참여했을때
					}elseif($aldata['clear_success'] == 'Y' && $jccheck['emoney_pay']=='Y'){
						//로그인형일 때는 안보임
						if($joincheck['check_type'] != 'login'){
							return array(
							'code' => 'success',
							'msg' => $joincheck['check_it']
							);
						}
					}
				}else{
					return array(
						'code' => 'fail',
						'msg' => "작성이 실패하였습니다."
					);
				}

			//중복일 경우
			}else{
				return array(
					'code' => 'duplicate',
					'msg' => $joincheck['check_already']
				);
			}

		//진행 완료인 경우
		}elseif($state['status']=='end'){
			return array(
					'code' => 'end',
					'msg' => "종료된 이벤트입니다."
				);

		//진행 전인 경우
		}elseif ( $state['status']=='before'){
			return array(
					'code' => 'before',
					'msg' => "진행 전 이벤트 입니다."
				);

		//중지일 경우
		}elseif ($state['status']=='stop'){
			return array(
					'code' => 'stop',
					'msg' => "중지된 이벤트 입니다."
				);

		}
	}//joincheck

}

/* End of file joincheckmodel.php */
/* Location: ./app/models/joincheckmodel.php */