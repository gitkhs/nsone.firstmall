<?php

function showLocationDepth($location_code)
{
	$CI =& get_instance();
	$CI->load->model('locationmodel');
	
	$location['category_code'] = $CI->locationmodel->split_location($location_code);

	if($location['category_code'])foreach($location['category_code'] as $code){
		$location['category'][] = $CI->locationmodel->one_location_name($code);
	}
	
	echo '<div class="category_depth clearbox">';
	echo '	<ul class="list">';
	echo '		<li class="item"><a href="/main">홈</a></li>';
	foreach($location['category'] as $k=>$v){
		echo "		<li class='item' id='{$location['category_code'][$k]}'>&gt; <a href='location?code={$location['category_code'][$k]}'>{$v}</a>";
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
			$.getJSON('child_location?code='+$(this).attr('id'), function(data) {
				if(data && data.length){
					tag += \"<ul class='sub_menu_list'>\";
					for(var i=0;i<data.length;i++){
						tag += \"<li class='sub_item'><a href='location?code=\"+data[i].category_code+\"'>\"+data[i].title+\"</a></li>\";
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