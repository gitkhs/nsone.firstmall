<?php
class Excelmembermodel extends CI_Model {
	var $downloadType		= "Excel5";
	var $saveurl			= "data/tmp";
	var $cell = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	var $itemList = array(
		"number"			=> "*번호",
		"referer_name"	=> "*유입",
		"status_nm"		=> "*승인",
		"group_name"	=> "*등급",
		"type"				=> "*유형",
		"join_sns"			=> "연동",
		"userid"				=> "*아이디",
		"user_name"		=> "이름",
		"nickname"		=> "닉네임",
		"email"				=> "이메일",
		"mailing"			=> "이메일 수신",
		"cellphone"		=> "핸드폰",
		"sms"				=> "SMS 수신",
		"phone"				=> "전화번호",
		"address"			=> "주소",
		"birthday"			=> "생일",
		"anniversary"		=> "기념일",
		"sex_name"		=> "성별",
		"regist_date"		=> "가입일",
		"lastlogin_date"	=> "최종방문일",
		"coupon"			=> "보유쿠폰",
		"emoney"			=> "적립금",
		"point"				=> "포인트",
		"cash"				=> "이머니",
		"member_order_price"	=> "주문금액",
		"member_order_cnt"		=> "주문",
		"review_cnt"		=> "리뷰",
		"login_cnt"			=> "방문",
		"member_recommend_cnt"	=> "추천",
		"member_invite_cnt"			=> "초대",
		"recommend"		=> "추천인",
		"bceo"				=> "대표자명",
		"bno"					=> "사업자등록번호",
		"bitem"				=> "업태",
		"bstatus"			=> "종목",
		"bperson"			=> "담당자명",
		"bpart"				=> "담당자 부서명"
	);

	var $requireds = array(
		"number",
		"referer_name",
		"status_nm",
		"group_name",
		"type",
		"userid"
	);

	public function excel_cell($count){
		$cell =$count;
		$char = 26;
		for($i=0;$i<$cell;$i++) {
			if($i<$char) $alpha[] = $this->cell[$i];
			else {
				$idx1 = (int)($i-$char)/$char;
				$idx2 = ($i-$char)%$char;
				$alpha[] = $this->cell[$idx1].$this->cell[$idx2];
			}
		}
		return $alpha;
	}
	public function excel_num($column){
		$cell =100;
		$char = 26;
		for($i=0; $i<$cell; $i++) {
			if($i < $char){
				$alpha[] = $this->cell[$i];
				if($column==$this->cell[$i]) return $i;
			}else {
				$idx1 = (int)($i-$char)/$char;
				$idx2 = ($i-$char)%$char;
				$alpha[] = $this->cell[$idx1].$this->cell[$idx2];
				if($column==$this->cell[$idx1].$this->cell[$idx2]) return $i;
			}

		}
	}

	public function create_excel_list($gets){
		###
		$title_items = array();

		$datas = get_data("fm_exceldownload",array("gb"=>"MEMBER"));
		if (!$datas) {
			$callback = "";
			openDialogAlert("항목설정을 해주세요",400,140,'parent',$callback);
			exit;
		}
		$title_items = explode("|",$datas[0]['item']);

		//회원 정보 다운로드 비밀번호 검증
		$check_down_passwd = $this->check_down_passwd($gets['member_download_passwd']);

		if (!$check_down_passwd) {
			$callback = "parent.openDialog('회원정보 다운로드','admin_member_download', {'width':550,'height':402});parent.$('input[name=member_download_passwd]').val('');parent.$('input[name=member_download_passwd]').focus();";
			openDialogAlert("다운로드 비밀번호가 일치하지 않습니다.",400,140,'parent',$callback);
			exit;
		}

		$this->load->model('snsmember');
		$this->load->model('membermodel');
		$this->load->model('couponmodel');

		if($gets['excel_type']=='search'){
			$_GET = $gets;

			###
			if($_GET['header_search_keyword']) $_GET['keyword'] = $_GET['header_search_keyword'];

			### SEARCH
			if ($_GET['keyword']=="이름, 아이디, 이메일, 전화번호, 핸드폰(뒷자리4), 주소, 닉네임") {
				unset($_GET['keyword']);
			}

			$sc = $_GET;
			$sc['orderby']			= (isset($_GET['orderby'])) ?	$_GET['orderby']:'A.member_seq';
			$sc['sort']				= (isset($_GET['sort'])) ?		$_GET['sort']:'desc';
			$sc['nolimit'] = "y";

			// 판매환경
			if( $_GET['sitetype'] ){
				$sc['sitetype'] = implode('\',\'',$_GET['sitetype']);
			}

			// 가입방법
			if( $_GET['snsrute'] ) {
				foreach($_GET['snsrute'] as $key=>$val){$sc[$val] = 1;}
			}

			### MEMBER
			$data = $this->membermodel->admin_member_list($sc);

			$idx = 0;
			$this->load->model('Goodsreview','Boardmodel');//리뷰건
			$datas = array();

			ini_set("memory_limit",-1);
			set_time_limit(0);
			//foreach($data['result'] as $k=>$datarow){
			for($k=0;$k<count($data['result']);$k++){

				unset($this->db->queries);
				unset($this->db->query_times);

				$idx++;
				$data['result'][$k]['number']	= $idx;
				//$data['result'][$k]['referer_name'] = getstrcut($data['result'][$k]['referer_name'], 2, '')." ".$data['result'][$k]['referer'];
				$data['result'][$k]['referer_name'] = $data['result'][$k]['referer_name'];

				if($data['result'][$k]['business_seq']){
					$data['result'][$k]['user_name'] = $data['result'][$k]['bname'];
					$data['result'][$k]['cellphone'] = $data['result'][$k]['bcellphone'];
					$data['result'][$k]['phone'] = $data['result'][$k]['bphone'];
					$data['result'][$k]['zipcode'] = $data['result'][$k]['bzipcode'];
					$data['result'][$k]['address'] = $data['result'][$k]['baddress'];
					$data['result'][$k]['address_detail'] = $data['result'][$k]['baddress_detail'];
				}

				if (!$data['result'][$k]['zipcode'] || $data['result'][$k]['zipcode'] == "-") {
					$tmp_address = "";
				} else {
					$tmp_address = sprintf("(%s)", $data['result'][$k]['zipcode']);
				}

				if ($data['result'][$k]['address']) {
					$tmp_address .= sprintf("<br/>(지번) %s", $data['result'][$k]['address']);
				}

				if ($data['result'][$k]['address_street']) {
					$tmp_address .= sprintf("<br/>(도로명) %s", $data['result'][$k]['address_street']);
				}

				if ($data['result'][$k]['address_detail']) {
					$tmp_address .= sprintf("<br/>(공통상세) %s", $data['result'][$k]['address_detail']);
				}

				$data['result'][$k]['address'] = $tmp_address;
				unset($tmp_address);

				$data['result'][$k]['type']	= $data['result'][$k]['business_seq'] ? '기업' : '개인';

				if ($data['result'][$k]['sex']=="female") {
					$data['result'][$k]['sex_name'] = "여";
				} else if ($data['result'][$k]['sex']=="male") {
					$data['result'][$k]['sex_name'] = "남";
				} else {
					$data['result'][$k]['sex_name'] = "";
				}

				// 보유쿠폰
				$dsc['whereis'] = " and use_status='unused' and member_seq='".$data['result'][$k]['member_seq']."'";
				$data['result'][$k]['coupon']	= $this->couponmodel->get_download_total_count($dsc);

				if(in_array('join_sns',$title_items)){
					$snsmbsc = array();
					$snsmbsc['select'] = " * ";
					$snsmbsc['whereis'] = " and member_seq ='".$data['result'][$k]['member_seq']."' ";
					$snslist = $this->snsmember->snsmb_list($snsmbsc);
					if($snslist['result'][0]) {
						$info_sns = array();
						//debug_var($snslist['result']);
						foreach ($snslist['result'] as $key=>$key2) {
							if ($key2['rute']=="naver") $info_sns[]="N";
							if ($key2['rute']=="facebook") $info_sns[]="F";
							if ($key2['rute']=="cyworld") $info_sns[]="C";
							if ($key2['rute']=="twitter") $info_sns[]="T";
							if ($key2['rute']=="daum") $info_sns[]="D";
							if ($key2['rute']=="kakao") $info_sns[]="K";
						}
						$data['result'][$k]['join_sns'] = join("/",$info_sns);
					}
				}

			}
		}
		$this->excel_write($data['result'], $title_items);
	}

	public function excel_write($data, $title_items) {
		$this->load->library('pxl');
		$arrSystem	= ($this->config_system)?$this->config_system:config_load('system');
		$arr_sub_domain = explode(".",$arrSystem['subDomain']);
		$name_sub_domain = sprintf("%s","{$arr_sub_domain['0']}");
		$date_info1 = date("Y-m-d");
		$date_info2 = date("H:i:s");
		$date_info = str_replace("-","",$date_info1).str_replace(":","",$date_info2);
		$filenames = $name_sub_domain."_member_list_".$date_info.".xls";

		$item_arr = $this->itemList;
		$fields = array();
		$item = array();
		foreach($title_items as $k){
			$item[] = $k;
			$fields[$k] = $item_arr[$k];
		}
		$cell_arr = $this->excel_cell(count($item));
		$cnt = count($fields);
		$t=2;

		foreach ($data as $k)
		{
			$items = array();
			for($i=0;$i<$cnt;$i++){
				$tmp = $item[$i];
				$items[$t][$i] = $k[$tmp];
			}
			$t++;

			$datas[] = $items;
		}

		// 회원정보 다운로드 로그기록
		$manager_id = $this->managerInfo['manager_id'];
		$insert_data = array();
		$insert_data['manager_seq'] = $this->managerInfo['manager_seq'];
		$insert_data['manager_id'] = $manager_id;
		$down_count = count($data);
		$str_down_count = number_format($down_count)."명";
		$insert_data['manager_log'] = sprintf("%s %s %s (%s) %s", $date_info1, $date_info2 ,"관리자가($manager_id)가 회원정보($str_down_count)를 다운로드 하였습니다.", $_SERVER['REMOTE_ADDR'], $filenames);
		$insert_data['ip'] = $_SERVER['REMOTE_ADDR'];
		$insert_data['down_count'] = $down_count;
		$insert_data['file_name'] = $filenames;
		$insert_data['reg_date'] = sprintf("%s %s", $date_info1, $date_info2);
		$result = $this->db->insert('fm_log_member_download', $insert_data);

		$this->pxl->excel_download($datas, $fields, $filenames,'회원정보다운로드');
	}

	//회원 정보 다운로드 비밀번호 검증
	public function check_down_passwd($passwd){
		### 회원 정보 다운로드 비밀번호 검증
		$str_md5 = md5($passwd);
		$str_sha256_md5 = hash('sha256',$str_md5);
		$query = "SELECT * FROM fm_manager WHERE manager_id=? AND (member_download_passwd=? OR member_download_passwd=?)";
		$query = $this->db->query($query,array($this->managerInfo['manager_id'],$str_md5,$str_sha256_md5));
		$data = $query->row_array();
		if(!$data){
			return false;
		} else {
			return true;
		}
	}

}
/* End of file excelmembermodel.php */
/* Location: .app/models/excelmember */