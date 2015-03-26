<?php
class Setting extends CI_Model {

	/* pg사 선택정보 저장 */
	public function set_pg_company(){
		config_save('system',array('pgCompany'=>$_POST['pgCompany']));
	}

	/* pg사 에스크로마크 업로드 */
	public function upload_escrow_mark(){
		$pgCompany = $_POST['pgCompany'];

		$escrowMarkPath = ROOTPATH."data/icon/escrow_mark/";

		if($_POST['newEscrowMark']){
			$filePath = ROOTPATH.$_POST['newEscrowMark'];

			if(file_exists($filePath)){
				$tmp = explode('.',$_POST['newEscrowMark']);
				$fileExt = $tmp[count($tmp)-1];
				$fileName = $pgCompany.".".$fileExt;
				if(copy($filePath,$escrowMarkPath.$fileName)){
					@chmod($escrowMarkPath.$fileName,0777);
					config_save($pgCompany,array('escrowMark'=>$fileName));
				}
			}
		}

		if($_POST['newEscrowMarkMobile']){
			$filePath = ROOTPATH.$_POST['newEscrowMarkMobile'];

			if(file_exists($filePath)){
				$tmp = explode('.',$_POST['newEscrowMarkMobile']);
				$fileExt = $tmp[count($tmp)-1];
				$fileName = $pgCompany."_mobile.".$fileExt;
				if(copy($filePath,$escrowMarkPath.$fileName)){
					@chmod($escrowMarkPath.$fileName,0777);
					config_save($pgCompany,array('escrowMarkMobile'=>$fileName));
				}
			}
		}

	}

	/* 파비콘 파일 저장 */
    public function upload_favicon()
    {
		/* 기존 설정정보 로드*/
		$data = config_load('system', 'favicon');
		$favicon = $data['favicon'];
		if($_FILES['faviconFile']['tmp_name']){
			$this->load->model('usedmodel');
			$data_used = $this->usedmodel->used_limit_check();
			if( $data_used['type'] ){
				if($favicon){
					@unlink($_SERVER['DOCUMENT_ROOT'].$favicon);
					$favicon = "";
				}
				$config['upload_path'] = './data/icon/favicon';
				$config['max_size']	= $this->config_system['uploadLimit'];
				$config['file_name'] = 'favicon'.time().".ico";
				$config['allowed_types'] = 'ico';
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('faviconFile'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,140,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$favicon = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].".ico";
			}else{
				openDialogAlert($data_used['msg'],400,140,'parent','');
			}
		}
		return $favicon;
    }

	/* snslogo 파일 저장 */
    public function upload_snslogo()
    {
		/* 기존 설정정보 로드*/
		$data = config_load('system', 'snslogo');
		$snslogo = $data['snslogo'];
		if($_FILES['snslogoFile']['tmp_name']){
			$this->load->model('usedmodel');
			$data_used = $this->usedmodel->used_limit_check();
			if( $data_used['type'] ){
				if($snslogo){
					unlink($_SERVER['DOCUMENT_ROOT'].$snslogo);
					$snslogo = "";
				}
				$config['upload_path'] = './data/icon/favicon';
				$config['max_size']	= $this->config_system['uploadLimit'];
				$tmp = @getimagesize($_FILES['snslogoFile']['tmp_name']);
				if( $tmp[0] < 200 && $tmp[1] < 200 ){ 
					$msg = '가로*세로 사이즈가 200 이상이어야 합니다.';
					openDialogAlert($msg,400,100,'parent');
					exit;
				}
				$_FILES['snslogoFile']['type'] = $tmp['mime'];

				$file_ext		= end(explode('.', $_FILES['snslogoFile']['name']));//확장자추출
				$file_name	= 'snslogo.'.$file_ext;//'.str_replace(" ", "", (substr(microtime(), 2, 6))).'
				$file_name	= str_replace("\'", "", $file_name); 	// ' " 제거
				$file_name	= str_replace("\"", "", $file_name); 	// ' " 제거
				$config['file_name'] = $file_name;
				$config['allowed_types'] = 'gif|jpg|png';
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('snslogoFile'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,100,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$snslogo = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
			}else{
				openDialogAlert($data_used['msg'],400,140,'parent','');
			}
		}
		return $snslogo;
    }

	/* kcp 결제창 로고 체크 */
	public function chk_kcp_paylog(){
		if($_POST['kcp_logo_val_img']){
			$filePath	= ROOTPATH.$_POST['kcp_logo_val_img'];
			$size		= getImageSize($filePath);
			if		($size[0] > 150){
				return 'width_over';
			}elseif	($size[1] > 50){
				return 'height_over';
			}
		}

		return null;
	}

	/* kcp 결제창 로고 업로드 */
	public function upload_kcp_logo(){
		$pgCompany = $_POST['pgCompany'];

		$kcp_logo_path = ROOTPATH."data/icon/manager/";

		if($_POST['kcp_logo_val_img']){
			$filePath	= ROOTPATH.$_POST['kcp_logo_val_img'];
			if(file_exists($filePath)){
				$tmp = explode('.',$_POST['kcp_logo_val_img']);
				$fileExt = $tmp[count($tmp)-1];
				$fileName = $pgCompany."_paylogo_".date('YmdHis').".".$fileExt;
				if(copy($filePath,$kcp_logo_path.$fileName)){
					@chmod($kcp_logo_path.$fileName,0777);
					config_save('kcp',array('kcp_logo_val_img'=>$kcp_logo_path.$fileName));
					config_save('kcp',array('kcp_logo_img_filename'=>$_POST['kcp_logo_img_filename']));
				}
			}
		}
	}

	/*기본설정 저장*/
	public function basic($favicon){
		foreach($_POST as $k => $data){
			if( ! is_array($data) ){
				$_POST[$k] = str_replace(array("'","\""),array("&apos;","&quot;"),$data);;
			}
		}
		$_POST['shopBranch'] = serialize($_POST['shopBranch']);
		$_POST['businessLicense'] = implode('-',$_POST['businessLicense']);

		if( isset($_POST['companyPhone']))$_POST['companyPhone'] = implode('-',$_POST['companyPhone']) != '--' ? implode('-',$_POST['companyPhone']) : '';
		if( isset($_POST['companyFax'])) $_POST['companyFax'] = implode('-',$_POST['companyFax']) != '--' ? implode('-',$_POST['companyFax']) : '';

		$_POST['companyZipcode'] = implode('-',$_POST['companyZipcode']);
		config_save('system',array('domain'=>$_POST['domain']));
		config_save('system',array('favicon'=>$favicon));
		config_save('basic',array('shopName'=>$_POST['shopName']));
		config_save('basic',array('shopBranch'=> $_POST['shopBranch'] ));
		config_save('basic',array('shopTitleTag'=>$_POST['shopTitleTag']));
		config_save('basic',array('shopGoodsTitleTag'=>$_POST['shopGoodsTitleTag']));
		config_save('basic',array('shopCategoryTitleTag'=>$_POST['shopCategoryTitleTag']));
		config_save('basic',array('metaTagUse'=>$_POST['metaTagUse']));
		config_save('basic',array('metaTagDescription'=>$_POST['metaTagDescription']));
		config_save('basic',array('metaTagKeyword'=>$_POST['metaTagKeyword']));
		config_save('basic',array('companyName'=>$_POST['companyName']));
		config_save('basic',array('businessConditions'=>$_POST['businessConditions']));
		config_save('basic',array('businessLine'=>$_POST['businessLine']));
		config_save('basic',array('businessLicense'=>$_POST['businessLicense']));
		config_save('basic',array('mailsellingLicense'=>$_POST['mailsellingLicense']));
		config_save('basic',array('ceo'=>$_POST['ceo']));
		config_save('basic',array('companyPhone'=>$_POST['companyPhone']));
		config_save('basic',array('companyFax'=>$_POST['companyFax']));
		config_save('basic',array('companyEmail'=>$_POST['companyEmail']));
		config_save('basic',array('partnershipEmail'=>$_POST['partnershipEmail']));
		config_save('basic',array('companyZipcode'=>$_POST['companyZipcode']));
		config_save('basic',array('companyAddress_type'=>$_POST['companyAddress_type']));
		config_save('basic',array('companyAddress'=>$_POST['companyAddress']));
		config_save('basic',array('companyAddress_street'=>$_POST['companyAddress_street']));
		config_save('basic',array('companyAddressDetail'=>$_POST['companyAddressDetail']));
		config_save('basic',array('member_info_manager'=>$_POST['member_info_manager']));
		config_save('basic',array('mapKey'=>$_POST['mapKey']));
	}

	/* SNS마케팅 저장*/
	public function snsconf($snslogo){
		config_save('system',array('snslogo'=>$snslogo));
	}

	/* kcp설정 저장 */
	public function kcp(){
		if( $_POST['not_use_pg'] == 'y' ) config_save('system',array('not_use_pg'=>'y'));
		else config_save('system',array('not_use_pg'=>'n'));

		config_save('kcp',array('mallCode'=>$_POST['mallCode']));
		config_save('kcp',array('merchantKey'=>$_POST['merchantKey']));
		config_save('kcp',array('payment'=>$_POST['payment']));
		config_save('kcp',array('interestTerms'=>$_POST['interestTerms']));
		config_save('kcp',array('nonInterestTerms'=>$_POST['nonInterestTerms']));
		config_save('kcp',array('pcCardCompanyCode'=>$_POST['pcCardCompanyCode']));
		config_save('kcp',array('pcCardCompanyTerms'=>$_POST['pcCardCompanyTerms']));
		config_save('kcp',array('escrow'=>$_POST['escrow']));
		config_save('kcp',array('escrowAccountLimit'=>$_POST['escrowAccountLimit']));
		config_save('kcp',array('escrowVirtualLimit'=>$_POST['escrowVirtualLimit']));
		config_save('kcp',array('cashReceipts'=>$_POST['cashReceipts']));
		config_save('kcp',array('mobilePayment'=>$_POST['mobilePayment']));
		config_save('kcp',array('mobileInterestTerms'=>$_POST['mobileInterestTerms']));
		config_save('kcp',array('mobileNonInterestTerms'=>$_POST['mobileNonInterestTerms']));
		config_save('kcp',array('mobileCardCompanyCode'=>$_POST['mobileCardCompanyCode']));
		config_save('kcp',array('mobileCardCompanyTerms'=>$_POST['mobileCardCompanyTerms']));
		config_save('kcp',array('mobileEscrow'=>$_POST['mobileEscrow']));
		config_save('kcp',array('mobileEscrowAccountLimit'=>$_POST['mobileEscrowAccountLimit']));
		config_save('kcp',array('mobileEscrowVirtualLimit'=>$_POST['mobileEscrowVirtualLimit']));
		config_save('kcp',array('mobileCashReceipts'=>$_POST['mobileCashReceipts']));
		config_save('kcp',array('kcp_skin_color'=>$_POST['kcp_skin_color']));
		config_save('kcp',array('kcp_logo_type'=>$_POST['kcp_logo_type']));
		config_save('kcp',array('kcp_logo_val_text'=>$_POST['kcp_logo_val_text']));
	}

	/* lg설정 저장 */
	public function lg(){
		if( $_POST['not_use_pg'] == 'y' ) config_save('system',array('not_use_pg'=>'y'));
		else config_save('system',array('not_use_pg'=>'n'));

		config_save('lg',array('mallCode'=>$_POST['mallCode']));
		config_save('lg',array('merchantKey'=>$_POST['merchantKey']));
		config_save('lg',array('payment'=>$_POST['payment']));
		config_save('lg',array('interestTerms'=>$_POST['interestTerms']));
		config_save('lg',array('nonInterestTerms'=>$_POST['nonInterestTerms']));
		config_save('lg',array('pcCardCompanyCode'=>$_POST['pcCardCompanyCode']));
		config_save('lg',array('pcCardCompanyTerms'=>$_POST['pcCardCompanyTerms']));
		config_save('lg',array('escrow'=>$_POST['escrow']));
		config_save('lg',array('escrowAccountLimit'=>$_POST['escrowAccountLimit']));
		config_save('lg',array('escrowVirtualLimit'=>$_POST['escrowVirtualLimit']));
		config_save('lg',array('cashReceipts'=>$_POST['cashReceipts']));
		config_save('lg',array('mobilePayment'=>$_POST['mobilePayment']));
		config_save('lg',array('mobileInterestTerms'=>$_POST['mobileInterestTerms']));
		config_save('lg',array('mobileNonInterestTerms'=>$_POST['mobileNonInterestTerms']));
		config_save('lg',array('mobileCardCompanyCode'=>$_POST['mobileCardCompanyCode']));
		config_save('lg',array('mobileCardCompanyTerms'=>$_POST['mobileCardCompanyTerms']));
		config_save('lg',array('mobileEscrow'=>$_POST['mobileEscrow']));
		config_save('lg',array('mobileEscrowAccountLimit'=>$_POST['mobileEscrowAccountLimit']));
		config_save('lg',array('mobileEscrowVirtualLimit'=>$_POST['mobileEscrowVirtualLimit']));
		config_save('lg',array('mobileCashReceipts'=>$_POST['mobileCashReceipts']));

		$this->load->helper('file');
		$mallConfPath = ROOTPATH."pg/lgdacom/conf/mall.conf";
		$mallConfContents = read_file($mallConfPath);
		$mallConfContents = preg_replace("/\n[\t\s]*[a-z0-9_\-]{2,}[\t\s]*=[\t\s]*[0-9a-z]{32}/i","",$mallConfContents);
		if($_POST['mallCode'] && $_POST['merchantKey']){
			$mallConfContents .= "\r\n{$_POST['mallCode']} = {$_POST['merchantKey']}";
		}
		write_file($mallConfPath,$mallConfContents);

	}

	/* 이니시스 키파일 저장 */
	public function upload_inicis_keyfile(){
		/* 키파일 업로드 */
		for($i=0;$i<=1;$i++){
			$arrFile = array('keypass.enc','mcert.pem','mpriv.pem');
			if($i == 0){
				$mollCodeName = "mallCode";
				$arrFileTag = array('keypass','mcert','mpriv');
			}else{
				$mollCodeName = "escrowMallCode";
				$arrFileTag = array('escrowKeypass','escrowMcert','escrowMpriv');
			}
			if(!$_POST[$mollCodeName]) continue;
			$keyUrl = './inicis/key/'.$_POST[$mollCodeName];
			@mkdir($keyUrl);
			@chmod($keyUrl,0707);
			foreach($arrFileTag as $key => $filename){
				if(!$_FILES[$filename]['tmp_name']) continue;
				$destination = $keyUrl .'/'. $arrFile[$key];
				move_uploaded_file($_FILES[$filename]['tmp_name'],$destination );
				config_save('inicis',array($filename=>$destination));
			}
		}
	}

	/* inicis 저장 */
	public function inicis(){
		if( $_POST['not_use_pg'] == 'y' ) config_save('system',array('not_use_pg'=>'y'));
		else config_save('system',array('not_use_pg'=>'n'));

		config_save('inicis',array('mallCode'=>$_POST['mallCode']));
		config_save('inicis',array('merchantKey'=>$_POST['merchantKey']));
		config_save('inicis',array('payment'=>$_POST['payment']));
		config_save('inicis',array('interestTerms'=>$_POST['interestTerms']));
		config_save('inicis',array('nonInterestTerms'=>$_POST['nonInterestTerms']));
		config_save('inicis',array('pcCardCompanyCode'=>$_POST['pcCardCompanyCode']));
		config_save('inicis',array('pcCardCompanyTerms'=>$_POST['pcCardCompanyTerms']));
		config_save('inicis',array('escrowMallCode'=>$_POST['escrowMallCode']));
		config_save('inicis',array('escrowMerchantKey'=>$_POST['escrowMerchantKey']));
		config_save('inicis',array('escrow'=>$_POST['escrow']));
		config_save('inicis',array('escrowAccountLimit'=>$_POST['escrowAccountLimit']));
		config_save('inicis',array('escrowVirtualLimit'=>$_POST['escrowVirtualLimit']));
		config_save('inicis',array('cashReceipts'=>$_POST['cashReceipts']));
		config_save('inicis',array('mobilePayment'=>$_POST['mobilePayment']));
		config_save('inicis',array('mobileInterestTerms'=>$_POST['mobileInterestTerms']));
		config_save('inicis',array('mobileNonInterestTerms'=>$_POST['mobileNonInterestTerms']));
		config_save('inicis',array('mobileCardCompanyCode'=>$_POST['mobileCardCompanyCode']));
		config_save('inicis',array('mobileCardCompanyTerms'=>$_POST['mobileCardCompanyTerms']));
		config_save('inicis',array('mobileEscrow'=>$_POST['mobileEscrow']));
		config_save('inicis',array('mobileEscrowAccountLimit'=>$_POST['mobileEscrowAccountLimit']));
		config_save('inicis',array('mobileEscrowVirtualLimit'=>$_POST['mobileEscrowVirtualLimit']));
		config_save('inicis',array('mobileCashReceipts'=>$_POST['mobileCashReceipts']));
	}

	/* allat 저장 */
	public function allat(){
		if( $_POST['not_use_pg'] == 'y' ) config_save('system',array('not_use_pg'=>'y'));
		else config_save('system',array('not_use_pg'=>'n'));

		config_save('allat',array('mallCode'=>$_POST['mallCode']));
		config_save('allat',array('merchantKey'=>$_POST['merchantKey']));
		config_save('allat',array('payment'=>$_POST['payment']));
		config_save('allat',array('nonInterestYn'=>$_POST['nonInterestYn']));
		config_save('allat',array('interestTerms'=>$_POST['interestTerms']));
		config_save('allat',array('escrow'=>$_POST['escrow']));
		config_save('allat',array('escrowAccountLimit'=>$_POST['escrowAccountLimit']));
		config_save('allat',array('escrowVirtualLimit'=>$_POST['escrowVirtualLimit']));
		config_save('allat',array('cashReceipts'=>$_POST['cashReceipts']));
		config_save('allat',array('mobilePayment'=>$_POST['mobilePayment']));
		config_save('allat',array('mobileNonInterestYn'=>$_POST['mobileNonInterestYn']));
		config_save('allat',array('mobileInterestTerms'=>$_POST['mobileInterestTerms']));
		config_save('allat',array('mobileEscrow'=>$_POST['mobileEscrow']));
		config_save('allat',array('mobileEscrowAccountLimit'=>$_POST['mobileEscrowAccountLimit']));
		config_save('allat',array('mobileEscrowVirtualLimit'=>$_POST['mobileEscrowVirtualLimit']));
		config_save('allat',array('mobileCashReceipts'=>$_POST['mobileCashReceipts']));
	}

	/* kspay설정 저장 */
	public function kspay(){
		if( $_POST['not_use_pg'] == 'y' ) config_save('system',array('not_use_pg'=>'y'));
		else config_save('system',array('not_use_pg'=>'n'));

		config_save('kspay',array('mallId'=>$_POST['mallId']));
		config_save('kspay',array('mallPass'=>$_POST['mallPass']));
		config_save('kspay',array('payment'=>$_POST['payment']));
		config_save('kspay',array('interestTerms'=>$_POST['interestTerms']));
		config_save('kspay',array('nonInterestTerms'=>$_POST['nonInterestTerms']));
		config_save('kspay',array('pcCardCompanyCode'=>$_POST['pcCardCompanyCode']));
		config_save('kspay',array('pcCardCompanyTerms'=>$_POST['pcCardCompanyTerms']));
		config_save('kspay',array('escrow'=>$_POST['escrow']));
		config_save('kspay',array('escrowAccountLimit'=>$_POST['escrowAccountLimit']));
		config_save('kspay',array('escrowVirtualLimit'=>$_POST['escrowVirtualLimit']));
		config_save('kspay',array('cashReceipts'=>$_POST['cashReceipts']));
		config_save('kspay',array('mobilePayment'=>$_POST['mobilePayment']));
		config_save('kspay',array('mobileInterestTerms'=>$_POST['mobileInterestTerms']));
		config_save('kspay',array('mobileNonInterestTerms'=>$_POST['mobileNonInterestTerms']));
		config_save('kspay',array('mobileCardCompanyCode'=>$_POST['mobileCardCompanyCode']));
		config_save('kspay',array('mobileCardCompanyTerms'=>$_POST['mobileCardCompanyTerms']));
		config_save('kspay',array('mobileEscrow'=>$_POST['mobileEscrow']));
		config_save('kspay',array('mobileEscrowAccountLimit'=>$_POST['mobileEscrowAccountLimit']));
		config_save('kspay',array('mobileEscrowVirtualLimit'=>$_POST['mobileEscrowVirtualLimit']));
		config_save('kspay',array('mobileCashReceipts'=>$_POST['mobileCashReceipts']));
	}

	/* 카카오페이 설정 저장 :: 2015-02-10 lwh */
	public function kakaopay(){

		if( $_POST['not_use_kakao'] == 'y' )
				config_save('system',array('not_use_kakao'=>'y'));
		else	config_save('system',array('not_use_kakao'=>'n'));

		config_save('payment',array('kakaopay'=>'카카오페이'));
		config_save('kakaopay',array('mid'=>$_POST['kakao_mid']));
		config_save('kakaopay',array('merchantEncKey'=>$_POST['kakao_merchantEncKey']));
		config_save('kakaopay',array('merchantHashKey'=>$_POST['kakao_merchantHashKey']));
		config_save('kakaopay',array('merchantKey'=>$_POST['kakao_merchantKey']));
		config_save('kakaopay',array('cancelPwd'=>$_POST['kakao_cancelPwd']));
		config_save('kakaopay',array('payment'=>$_POST['kakaopay_payment']));
		config_save('kakaopay',array('interestTerms'=>$_POST['kakaopay_interestTerms']));
		config_save('kakaopay',array('nonInterestTerms'=>$_POST['kakaopay_nonInterestTerms']));
		config_save('kakaopay',array('CardCompanyCode'=>$_POST['kakaoCardCompanyCode']));
		config_save('kakaopay',array('CardCompanyTerms'=>$_POST['kakaoCardCompanyTerms']));
	}

	/* bank 저장 */
	public function bank(){
		/* 설정 초기화 */
		config_delete("bank");
		config_save('order',array('bank' => 'n'));

		/* 설정저장 */
		foreach($_POST['bank'] as $key => $bank){
			if(!$_POST['bankUser'][$key]||!$_POST['account'][$key]) continue;
			$account = $_POST['account'][$key];
			$tmp = array(
					'bank'=>$bank,
					'bankUser'=>$_POST['bankUser'][$key],
					'account'=>$account,
					'accountUse'=>$_POST['accountUse'][$key]
			);
			if($key == 0) config_save('order',array('bank' => 'y'));
			config_save('bank',array($key => $tmp));
		}
	}

	/* bank2 저장 */
	public function bank2(){
		/* 설정 초기화 */
		config_delete("bank_return");

		/* 설정저장 */
		foreach($_POST['bankReturn'] as $key => $bank){
			if(!$_POST['bankUserReturn'][$key]||!$_POST['accountReturn'][$key]) continue;
			$account = $_POST['accountReturn'][$key];
			$tmp = array(
					'bankReturn'=>$bank,
					'bankUserReturn'=>$_POST['bankUserReturn'][$key],
					'accountReturn'=>$account,
					'accountUseReturn'=>$_POST['accountUseReturn'][$key]
			);
			config_save('bank_return',array($key => $tmp));
		}
	}

	/* 보안설정 저장 */
	public function protect(){
		$this->load->model('ssl');

		$setSystemConfig = array();

		switch($_POST['ssl']){
			case "pay":
				$setSystemConfig['ssl_use'] = 1;
				$setSystemConfig['ssl_pay'] = 1;
				$setSystemConfig['ssl_external'] = $_POST['ssl_external']?1:0;
				$setSystemConfig['ssl_ex_domain'] = trim($_POST['ssl_ex_domain']);
				$setSystemConfig['ssl_ex_port'] = trim($_POST['ssl_ex_port']);
			break;
			case "free":
				$setSystemConfig['ssl_use'] = 1;
				$setSystemConfig['ssl_pay'] = 0;
			break;
			default:
				$setSystemConfig['ssl_use'] = 0;
				$setSystemConfig['ssl_pay'] = 0;
			break;
		}
		/*
		테스트용 설정
		$setSystemConfig['ssl_kind'] = "Thawte SSL 128bit";
		$setSystemConfig['ssl_status'] = 1;
		$setSystemConfig['ssl_period_start'] = "2012-05-23";
		$setSystemConfig['ssl_period_expire'] = "2012-12-24";
		$setSystemConfig['ssl_port'] = "80";
		$setSystemConfig['ssl_domain'] = "www.lks.firstmall.kr";
		*/
		$setSystemConfig['protectIp'] = implode("\n",$_POST['protectIp']);
		$setSystemConfig['protectMouseRight'] = $_POST['protectMouseRight'];
		$setSystemConfig['protectMouseDragcopy'] = $_POST['protectMouseDragcopy'];

		config_save('system',$setSystemConfig);

	}

	/* 다음쇼핑하우 로고1,2 업로드 */
	public function upload_daumshopping_logo(){

		$data = config_load('system');
		$daumshopping_logo1 = $data['daumshopping_logo1'];
		$daumshopping_logo2 = $data['daumshopping_logo2'];

		$this->load->model('usedmodel');
		$data_used = $this->usedmodel->used_limit_check();
		if( $data_used['type'] ){

			$upload_path = './data/icon/daumshopping';
			if(!is_dir($upload_path)){
				@mkdir($upload_path);
				@chmod($upload_path,0707);
			}

			if($_FILES['logoimg1']['tmp_name']){
				if($daumshopping_logo1){
					@unlink($_SERVER['DOCUMENT_ROOT'].$daumshopping_logo1);
					$daumshopping_logo1 = "";
				}
				$file_ext = end(explode('.', $_FILES['logoimg1']['name']));//확장자추출
				$config['upload_path'] = $upload_path;
				$config['max_size']	= $this->config_system['uploadLimit'];
				$config['file_name'] = 'daumshopping_logo1_'.time().".".$file_ext;
				$config['allowed_types'] = 'gif|jpg|png';
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('logoimg1'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,100,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$daumshopping_logo1 = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
			}

			if($_FILES['logoimg2']['tmp_name']){
				if($daumshopping_logo2){
					@unlink($_SERVER['DOCUMENT_ROOT'].$daumshopping_logo2);
					$daumshopping_logo2 = "";
				}
				$file_ext = end(explode('.', $_FILES['logoimg2']['name']));//확장자추출
				$config['upload_path'] = $upload_path;
				$config['max_size']	= $this->config_system['uploadLimit'];
				$config['file_name'] = 'daumshopping_logo2_'.time().".".$file_ext;
				$config['allowed_types'] = 'gif|jpg|png';
				$this->load->library('upload', $config);
				if ( ! $this->upload->do_upload('logoimg2'))
				{
					$error = $this->upload->display_errors();
					openDialogAlert($error,400,100,'parent');
					exit;
				}
				$uploadData = $this->upload->data();
				$daumshopping_logo2 = str_replace($_SERVER['DOCUMENT_ROOT'],'',$uploadData['file_path']).$uploadData['raw_name'].$uploadData['file_ext'];
			}

		}else{
			openDialogAlert($data_used['msg'],400,140,'parent','');
		}

		return array($daumshopping_logo1,$daumshopping_logo2);

	}
}
?>