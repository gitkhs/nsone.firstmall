<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//엑셀다운/저장하기
class pxl
{
	function __construct() {
		$CI =& get_instance();
		//date_default_timezone_set("Asia/Seoul");                                    /** timezone_setting    **/
		require_once APPPATH.'/libraries/PHPExcel.php';
		require_once APPPATH.'/libraries/PHPExcel/IOFactory.php';
	}

	/**
	* 데이타, 타이틀, 파일명, html name
	**/
	public function excel_download($datas, $fields, $filenames, $kindname, $directdownload=true, $couponusedate = NULL,$couponusetotal = NULL)
	{
		@ini_set('memory_limit', '5120M');
		@set_time_limit(0);
		if($directdownload){
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename={$filenames}");
			header("Content-Description: PHP4 Generated Data");
			header("Content-charset=utf-8");
		}
$r_tag[] = "<html xmlns:v='urn:schemas-microsoft-com:vml'
xmlns:o='urn:schemas-microsoft-com:office:office'
xmlns:x='urn:schemas-microsoft-com:office:excel'
xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<!--[if gte mso 9]>
<xml>
	<x:ExcelWorkbook>
	<x:ExcelWorksheets>
	<x:ExcelWorksheet>
	<x:Name>{$kindname}</x:Name>
	<x:WorksheetOptions>
	<x:DefaultRowHeight>270</x:DefaultRowHeight>
	<x:Selected/>
	<x:DoNotDisplayGridlines/>
	<x:ProtectContents>False</x:ProtectContents>
	<x:ProtectObjects>False</x:ProtectObjects>
	<x:ProtectScenarios>False</x:ProtectScenarios>
	</x:WorksheetOptions>
	</x:ExcelWorksheet>
	</x:ExcelWorksheets>
	<x:WindowHeight>12825</x:WindowHeight>
	<x:WindowWidth>18945</x:WindowWidth>
	<x:WindowTopX>120</x:WindowTopX>
	<x:WindowTopY>30</x:WindowTopY>
	<x:ProtectStructure>False</x:ProtectStructure>
	<x:ProtectWindows>False</x:ProtectWindows>
	</x:ExcelWorkbook>
</xml>
<![endif]-->
<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel;charset=utf-8\">
<style>
<!--table
	{mso-displayed-decimal-separator:\"\.\";
	mso-displayed-thousand-separator:\"\,\";}
@page
	{margin:1.0in .75in 1.0in .75in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;}
.number {mso-number-format:\"@\"}
 *{
	color:black;
	font-family:돋움, monospace;
	font-size:10.0pt;
 }
/**td  {mso-number-format:\@;}**/
	-->
</style>
</head>
<table width='100%' border='1' cellpadding='0' cellspacing='0'>
";

if($couponusedate){
	$r_tag[] = "<tr>\r\n";
	$r_tag[] = "<td height='30' style='text-align:center;size:25pt;background:#efefef' colspan='".count($fields)."' >쿠폰사용일 : ".$couponusedate."</td>\r\n"; //count($fields);
	$r_tag[] = "</tr>\r\n";
}

$r_tag[] = "<tr>\r\n";
$i = 0;
foreach($fields as $k=>$data_field){
	$fieldstitle[$i] = $data_field;
	$i++;
	if(empty($data_field)) continue;
	$r_tag[] = "<td style='background:#efefef;text-align:center;'>".$data_field."</td>\r\n";
}
$r_tag[] = "</tr>\r\n";

foreach($datas as $r_data)
{
	foreach($r_data as $k => $sub_data)
	{
	$r_tag[] = "<tr>\r\n";
		if(is_array($sub_data)){
			for($j=0;$j<count($fields);$j++) {
				if(empty($fieldstitle[$j])) continue;
				if( strstr($fieldstitle[$j], '개별배송비') || strstr($fieldstitle[$j], '배송비쿠폰') || strstr($fieldstitle[$j], '배송비코드') ||  strstr($fieldstitle[$j], '정가') || strstr($fieldstitle[$j], '할인가') || strstr($fieldstitle[$j], '적립금사용') || strstr($fieldstitle[$j], '이머니사용') || strstr($fieldstitle[$j], '에누리') || strstr($fieldstitle[$j], '결제금액')  ) {
					if( strstr($fieldstitle[$j], '복수구매할인가') ) {
						$number = " style='mso-number-format:\"@\"' ";
					}else{
						$number = " style='mso-number-format:\"0_ \"' ";
					}
				}else{
					$number = (is_numeric($sub_data[$j]) || strstr($fieldstitle[$j], '*카테고리') || strstr($fieldstitle[$j], '브랜드') || strstr($fieldstitle[$j], '상세주소') || strstr($fieldstitle[$j], '추가입력옵션')  )?" class='number' style='mso-number-format:\"@\"' ":"";
				}

				if( isset($sub_data[$j]) ) {
					$sub_data[$j] = str_replace("<", "&lt;", $sub_data[$j]);
					$sub_data[$j] = str_replace(">", "&gt;", $sub_data[$j]);
					$r_tag[] = "<td ".$number."> ".$sub_data[$j]."</td>\r\n";
				}else{
					$r_tag[] = "<td >".$sub_data[$j]."</td>\r\n";
				}
			}
		}else{
			if(empty($fieldstitle[$j])) continue;
			if( strstr($fieldstitle[$j], '개별배송비') || strstr($fieldstitle[$j], '배송비쿠폰') || strstr($fieldstitle[$j], '배송비코드') ||  strstr($fieldstitle[$j], '정가') || strstr($fieldstitle[$j], '할인가') || strstr($fieldstitle[$j], '적립금사용') || strstr($fieldstitle[$j], '이머니사용') || strstr($fieldstitle[$j], '에누리') || strstr($fieldstitle[$j], '결제금액')  ) {
				if( strstr($fieldstitle[$j], '복수구매할인가') ) {
					$number = " style='mso-number-format:\"@\"' ";
				}else{
					$number = " style='mso-number-format:\"0_ \"' ";
				}
			}else{
				$number = (is_numeric($sub_data) || strstr($fieldstitle[$j], '*카테고리') || strstr($fieldstitle[$j], '브랜드') || strstr($fieldstitle[$j], '상세주소')  )?" class='number' style='mso-number-format:\"@\"' ":"";
			}
			$r_tag[] = "<td ".$number."> ".$sub_data."</td>\r\n";
		}
		$r_tag[] = "</tr>\r\n";
		if($couponusetotal){
			foreach($couponusetotal as $ck => $couponuse_data)
			{
				if ( $couponusetotal[$ck]['count'] == $k ) {//%출고에 있는 그룹명% : 수수료 3,500원 / 정산금액 26,500원
					$r_tag[] = "<tr>\r\n";
					$r_tag[] = "<td height='30' style='text-align:right;size:25pt;background:yellow;font-weight:bold;' colspan='24' > ".$couponusetotal[$ck]['social_goods_group_name']." : 수수료 ".number_format($couponusetotal[$ck]['address_commission_price'])."원 / 정산금액 ".number_format($couponusetotal[$ck]['address_commission_account'])."원 </td>\r\n";
					$r_tag[] = "</tr>\r\n";
					break;
				}
			}
		}
	}
}


$r_tag[] = "</table>\r\n";

		if($directdownload){
			foreach($r_tag as $tag){
				echo $tag;
			}
			exit;
		}else{
			return implode("",$r_tag);
		}
	}

	public function pxl_excel_down($datas, $fields, $filenames,$kindname, $serverfilenames){
		$CI =& get_instance();

		$CI->load->library('pxl');
		$filename = ROOTPATH.$CI->excelgoodsmodel->saveurl.'/excel_'.$serverfilenames.'_down.xls';
		ini_set('memory_limit', '5120M');
		set_time_limit(0);
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '5120MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->setActiveSheetIndex(0);
		$sheet = $this->objPHPExcel->getActiveSheet();

		$cell_arr = $CI->excelgoodsmodel->excel_cell(count($fields));
		$cnt = 0;
		foreach($fields as $k=>$data_field){
			$sheet->setCellValue($cell_arr[$cnt]."1", $data_field);//title
			$cnt++;
		}
		$sheet->setTitle($kindname);//sheet title
		$t=2;
		$celltype_plain_text = PHPExcel_Cell_DataType::TYPE_STRING;

		foreach($datas as $r_data)
		{
			$i = 0;
			foreach($r_data as $k => $sub_data)
			{
				if(is_array($sub_data)){
					for($j=0;$j<count($fields);$j++) {
						if( $sub_data[$j] ) {
							$sheet->setCellValueExplicit($cell_arr[$j].$t, $sub_data[$j], $celltype_plain_text);
						}else{
							$sheet->setCellValueExplicit($cell_arr[$j].$t, '', $celltype_plain_text);
						}
					}
				}else{
					$sheet->setCellValueExplicit($cell_arr[$i].$t, $sub_data, $celltype_plain_text);
					$i++;
				}
				$t++;
			}
		}
		$objWriter = IOFactory::createWriter($this->objPHPExcel, $CI->excelgoodsmodel->downloadType);
		$objWriter->save($filename);
		$result = array("realfiledir"=>$filename,"filenames"=>$filenames);
		echo json_encode($result);
		exit;
	}

}
?>