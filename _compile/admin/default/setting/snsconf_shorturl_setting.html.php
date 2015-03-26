<?php /* Template_ 2.2.6 2014/12/11 11:28:09 /www/nsone_firstmall_kr/admin/skin/default/setting/snsconf_shorturl_setting.html 000003623 */ ?>
<script type="text/javascript">

$(document).ready(function() {
	// shorturl guide
	$("#shorturl_guide").on("click",function(){
		openDialog("짧은 URL 변환 설정 안내", "shorturl_help_guide",{"width":"650","height":"200","show" : "fade","hide" : "fade"});
	});
	

	$("#shorturl").click(function(){

		if( !$("#shorturl_app_id").val() ){
			alert('API ID 설정값을 정확히 입력해 주세요.');
			return false;
		}

		if( !$("#shorturl_app_key").val() ){
			alert('API Key 설정값을 정확히 입력해 주세요.');
			return false;
		}

		var data = $("#snsShortUrlRegist").serialize();
		$.ajax({
			'url' : '../setting_process/snsconf_shorturl',
			'type' : 'post',
			'data': data,
			'dataType': 'json',
			'success': function(res) {
				var shorturl = $.trim(res.shorturl);
				if(shorturl == "INVALID_LOGIN" || shorturl == "INVALID_APIKEY"){
					openDialogAlert("오류 : bit.ly 키값을 정확히 입력해 주세요.",'300','140');
				}else{
					$("#shorturlview").html(shorturl);
					$("#shorturl_help_lay").dialog('close');
					openDialogAlert("설정되었습니다.",'300','140');
				}
			}
			,'error': function(e){ debug(e); }
		});

	});

});
</script>

<div id="shorturl_help_lay" class="hide">
<form name="snsShortUrlRegist" id="snsShortUrlRegist" method="post" action="" target="actionFrame">
<input type="hidden" name="pagemode" id="pagemode" value="member">
<input type="hidden" name="shorturl_use2" value="Y">
	<div style="clear:both;height:28px;">
	<span class="btn small orange" style="float:right;margin-right:5px;"><button type="button" id="shorturl_guide">bit.ly 키 발급 안내</button></span>
	</div>
	<table class="joinform-user-table info-table-style" style="width:97%">
		<col width="100px" /><col width="" />
		<tbody >
			<tr >
			<th class="its-th">API ID</th>
			<td class="its-td"><input type='text'  name="shorturl_app_id"  id="shorturl_app_id" value="<?php if($TPL_VAR["sns"]["shorturl_app_id"]){?><?php echo $TPL_VAR["sns"]["shorturl_app_id"]?><?php }else{?><?php }?>" style="width:95%;"></td>
		</tr>
		<tr >
			<th class="its-th">API Key</th>
			<td class="its-td"><input type='text'  name="shorturl_app_key"  id="shorturl_app_key" value="<?php if($TPL_VAR["sns"]["shorturl_app_key"]){?><?php echo $TPL_VAR["sns"]["shorturl_app_key"]?><?php }else{?><?php }?>" style="width:95%;"></td>
		</tr>
	</tbody>
	</table>
	
	<div class="center" style="padding:15px;"><span class="btn large black"><button  type="button"  id="shorturl">설정하기</button></span></div>
</form>
</div>
<!-- 짧은 url 변환 설정 레이어 -->
<div id="shorturl_help_guide" class="hide">
<table width="99%" class="joinform-user-table info-table-style">
	<col width="150" /><col width="" />
	<tr>
		<td class="its-td-align">
		<ol style="padding:10px;">
			<li>1. <a href="http://bit.ly/a/account/" target="_blank" ><span class="cyanblue">http://bit.ly/a/account/ </span></a> 방문 (가입 필요)</li>
			<li>2.  <a href="https://bitly.com/a/settings/advanced" target="_blank" ><span class="cyanblue">https://bitly.com/a/settings/advanced</span></a> > Legacy API Key<br/>
			&nbsp;&nbsp;&nbsp;발급받은 API ID/Key 값을 위에 입력칸에 넣은 후 '저장하기' 해 주세요.</li>
		</ol>
		</td>
	</tr>
</tbody>
</table> <!-- <span   >짧은주소(bit.ly 셋팅)를 원하시면 ↑위의 정보를 설정해 주십시오.</span> -->
</div>