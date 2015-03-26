<?
// 상세 설명 이미지 테그 조정
// view 에서 {=dataGoodsContents(goods.contents,layout_config.body_width-20)} 치환 코드 사용
function dataGoodsContents($contents,$width='500')
{	
	$cnt = preg_match_all("/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$contents, $matches);
	foreach($matches[1] as $img_key => $ori_img){
		$img = $ori_img;
		if( preg_match('/http:\/\//',$img) ){
			$arr_img = explode('/',$img);
			unset($arr_img[0],$arr_img[1],$arr_img[2]);
			$img = implode('/',$arr_img);
		}else{
			if(substr($img,0,1) == '/') $img = substr($img,1);
		}
		
		$size = @getimagesize('.'.$ori_img);

		$img_tag = '<img src="'.$ori_img.'" border="0" />';
		if($size[0] > $width) $img_tag = '<img src="'.$ori_img.'" width="'.$width.'" border="0" />';				
		$replace[$img_key] = $img_tag;			
	}
	$mobile_contents = str_replace($matches[0],$replace,$contents);
	return $mobile_contents;
}
?>