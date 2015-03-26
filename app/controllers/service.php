<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class service extends front_base {

	public function main_index()
	{
		redirect("/service/index");
	}

	public function index()
	{

	}

	/* 고객센터 */
	public function cs()
	{
		$arr = config_load('bank');
		if($arr) foreach(config_load('bank') as $k => $v){
			list($tmp) = code_load('bankCode',$v['bank']);
			$v['bank'] = $tmp['value'];
			$bank[] = $v;
		}
		$this->template->assign(array('bank'=>$bank));
		$this->print_layout($this->template_path());
	}

	/* 회사소개 */
	public function company(){
		$this->print_layout($this->template_path());
	}

	/* 이용약관 */
	public function agreement(){
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign('shopName',$arrBasic['shopName']);

		$data = config_load('member');
		$data['agreement'] = str_replace("{shopName}",$arrBasic['shopName'],$data['agreement']);

		$this->template->assign($data);
		$this->print_layout($this->template_path());
	}

	/* 개인정보취급방침 */
	public function privacy(){
		$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$this->template->assign('shopName',$arrBasic['shopName']);

		$data = config_load('member');
		$data['privacy'] = str_replace("{shopName}",$arrBasic['shopName'],$data['privacy']);
		$data['privacy'] = str_replace("{domain}",$arrBasic['domain'],$data['privacy']);

		$this->template->assign($data);
		$this->print_layout($this->template_path());
	}

	/* 이용안내 */
	public function guide(){
		$data = config_load("shippingdelivery");

		$deliveryCompany = array();
		if(isset($data['deliveryCompanyCode'])){
			foreach( $data['deliveryCompanyCode'] as $deliveryCompanyCode ){
				$tmp = config_load('delivery_url',$deliveryCompanyCode);
				$deliveryCompany[] = $tmp[$deliveryCompanyCode]['company'];
			}
		}

		$this->template->assign('deliveryCompanyName',$deliveryCompany?$deliveryCompany[0]:'');
		$this->print_layout($this->template_path());
	}

	/* 제휴안내 */
	public function partnership(){
		$this->print_layout($this->template_path());
	}


	public function partnership_send(){

		$file_path	= "../../data/email/partnership.html";
		$_POST["zipcode"] = $_POST["recipient_zipcode"][0]."-".$_POST["recipient_zipcode"][1];
		$this->template->assign(array('order'=>$_POST));
		$this->template->compile_dir = ROOTPATH."data/email/";
		$this->template->define(array('tpl'=>$file_path));
		$bodyTpl = $this->template->fetch('tpl');
		$body	= trim($bodyTpl);
		$body	= preg_replace("/\/data\/mail/", $domain."/data/mail", $body);
		$body	= str_replace("http://http://", "http://", $body);

		$email = config_load('email');

		$email['partnership_skin'] = $out;

		$adminEmail = $basic['partnershipEmail']?$basic['partnershipEmail']:$basic['companyEmail'];

		require_once $_SERVER['DOCUMENT_ROOT']."/app/libraries/Email_send.class.php";
		$mail		= new Mail(isset($params));
		$basic = ($this->config_basic)?$this->config_basic:config_load('basic');
		$headers['From']		= $_POST["email"];
		$headers['Name']	= $_POST["writer"];
		$headers['Subject'] = "[".$_POST["company"]."]".$_POST["qtype"]." 문의입니다.";
		$headers['To']			= $basic['partnershipEmail'];//"kbm@gabia.com; ".
		$resSend = $mail->send($headers, $body);

		if($resSend){
			$callback = "parent.document.location.reload();";
			openDialogAlert("문의가 접수되었습니다.",400,140,'parent',$callback);
		}else{
			openDialogAlert("문의가 접수중 에러가 발생되었습니다<br>잠시 후 다시 시도하여 주십시오.",400,140,'parent',$callback);
		}


	}

	public function policy(){
		if( defined('__ISUSER__') != true ) {
			$arrBasic = ($this->config_basic)?$this->config_basic:config_load('basic');
			$member = config_load('member');

			//비회원 개인정보 수집-이용 약관동의 추가
			$privacy['policy'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$member['policy']));
		}
		$this->template->assign($privacy);
		$this->print_layout($this->template_path());
	}

	public function cancellation(){
		$arrOrder = config_load('order');
		$privacy['cancellation'] = str_replace("{domain}",$arrBasic['domain'],str_replace("{shopName}",$arrBasic['shopName'],$arrOrder['cancellation']));
		$this->template->assign($privacy);
		$this->print_layout($this->template_path());
	}

}

