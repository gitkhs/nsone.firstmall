<?php
function designAreaDivision($source, &$tpl){

	$on_ms   =$tpl->on_ms;
	$tpl_path =$tpl->tpl_path;
	if ($on_ms) $tpl_path=preg_replace('@\\\\+@', '/', $tpl_path);

	$skinTplFile = str_replace("{$_SERVER['DOCUMENT_ROOT']}/application/views/front/","",$tpl_path);

	$dom = new DOMDocument('1.0','utf-8'); 
	@$dom->loadHTML($source); 
	$x = new DOMXPath($dom); 
 
	foreach($x->query("//img") as $node) 
	{    
		$skinImgPath = $node->getAttribute('src');

		$node->setAttribute("skinTplFile",base64_encode($skinTplFile));
		$node->setAttribute("skinImgPath",base64_encode($skinImgPath));
	}

	foreach($x->query("//input") as $node) 
	{   
		if($node->getAttribute('type') == 'image')
		{

			$skinImgPath = $node->getAttribute('src');

			$node->setAttribute("skinTplFile",base64_encode($skinTplFile));
			$node->setAttribute("skinImgPath",base64_encode($skinImgPath));
		}
	}

	$newSource = $dom->saveHtml();

	return $newSource;	
}