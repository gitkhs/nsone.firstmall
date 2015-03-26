<?
function addImageLazyAttributes($source, $tpl){
	preg_match_all("/<img[^>][^this.src]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i",$source,$temp);
	$temp[1] = array_unique($temp[1]);
	foreach($temp[1] as $a){
		$source = str_replace("src=\"".$a."\"","data-echo=\"".$a."\" src=\"\"",$source);
	}
	return $source;
}
?>