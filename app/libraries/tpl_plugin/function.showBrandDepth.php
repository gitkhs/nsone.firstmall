<?php

function showBrandDepth($brand_code)
{
	$CI =& get_instance();
	$CI->load->model('brandmodel');
	
	$brand['category_code'] = $CI->brandmodel->split_brand($brand_code);

	if($brand['category_code'])foreach($brand['category_code'] as $code){
		$brand['category'][] = $CI->brandmodel->one_brand_name($code);
	}
	
	echo '<div class="category_depth clearbox">';
	echo '	<ul class="list">';
	echo '		<li class="item"><a href="/main">홈</a></li>';
	foreach($brand['category'] as $k=>$v){
		echo "		<li class='item' id='{$brand['category_code'][$k]}'>&gt; <a href='brand?code={$brand['category_code'][$k]}'>{$v}</a>";
		echo "			<div class='sub_menu'></div>";
		echo "		</li>";
	}
	echo '	</ul>';
	echo '</div>';
	echo "
		<script>
		$('div.category_depth ul li').mouseenter(function(){
			var tag = '';
			var obj = $(this);
			$.getJSON('child_brand?code='+$(this).attr('id'), function(data) {
				if(data && data.length){
					tag += \"<ul class='sub_menu_list'>\";
					for(var i=0;i<data.length;i++){
						tag += \"<li class='sub_item'><a href='brand?code=\"+data[i].category_code+\"'>\"+data[i].title+\"</a></li>\";
					}
					tag += \"</ul>\";
					obj.find('div.sub_menu').html(tag);
					obj.addClass('selected');
				}
			});
	
		}).mouseleave(function(){
			$(this).removeClass('selected');
		});
		</script>
	";
}
?>