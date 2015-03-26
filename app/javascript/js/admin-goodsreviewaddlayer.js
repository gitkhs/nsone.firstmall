// 평가정보 추가 함수

var label_item_seq = 0;
var label_item_radio = 0;
var goodsreviewicon = '/data/icon/goods_review/';
$(document).ready(function() {
	var layer_seq = 0;
	var label_maxid = $("input[name=Label_maxid]").val();
	if(label_maxid){
		layer_seq = label_maxid;
	}	
	
	$(".labelList").sortable({axis: 'y'});

	$(".sample").hide();

	$("#joinBtn").click(function(){
	
		resetLabel();
		joinDivShow();
	});

	if(!$('body').data('listJoinBtnEventBinded')){
		
		$("body").on("click",".listJoinBtn",function(){
			$('body').data('listJoinBtnEventBinded',true);	
			resetLabel();
			joinDivShow();
	
			var seq			= 	$(this).attr('value'); 
			var labelName	=	$('input[name="labelItem[user]['+seq+'][name]"]').val();
			var labelId	=	$('input[name="labelItem[user]['+seq+'][id]"]').val();
			var labelType	=	$('input[name="labelItem[user]['+seq+'][type]"]').val();
			var labelExp		=	$('input[name="labelItem[user]['+seq+'][exp]"]').val();
			var labelValue	=	$('input[name="labelItem[user]['+seq+'][value]"]').val().split("|");
			var labelIcon	=	$('input[name="labelItem[user]['+seq+'][icon]"]').val().split("|");

			var labelUse	=	$('input[name="labelItem[user]['+seq+'][use]"]').is(":checked");
			var labelRequire	=	$('input[name="labelItem[user]['+seq+'][required]"]').is(":checked");


			var vallength = labelValue.length - 1;
			label_item_radio = vallength;
			var i = 0;
			var labelsubValue = new Array();
			
			for(i=0; i< vallength; i++){
				labelsubValue[i] = labelValue[i].split(";");
			}
			
			$("input[name=windowLabelSeq]").val(seq);		
			$("input[name=windowLabelName]").val(labelName);
			$("input[name=windowLabelId]").val(labelId);
			$("input[name=windowLabelExp]").val(labelExp);
			$("input[name=windowLabelType]").val(labelType);
			$("input[name=windowLabelvalue]").val(labelValue);
			$("input[name=windowLabelIcon]").val(labelIcon);
			$("input[name=windowLabelsubvalue]").val(labelsubValue); 

			$("input[name=windowLabelUseCheck]").val(labelUse);
			$("input[name=windowLabelRequireCheck]").val(labelRequire);
			
			/* 이미지 */
			switch (labelType)
			{
				case "text"			:
					$("#text").attr("src", "../skin/default/images/common/btn_campaign_label_text_on.gif");
				break;
				case "radio"		:
					$("#radio").attr("src", "../skin/default/images/common/btn_campaign_label_multiple_on.gif");	
				break;
				case "textarea"		:
					$("#textarea").attr("src", "../skin/default/images/common/btn_campaign_label_textbox_on.gif");
				break;
				case "checkbox"		:
					$("#checkbox").attr("src", "../skin/default/images/common/btn_campaign_label_checkbox_on.gif");
				break;
				case "select"		:
					$("#select").attr("src", "../skin/default/images/common/btn_campaign_label_dropdown_on.gif");				
				break;			
				
			}
			
			html = labelAddItem(labelType, labelValue, labelsubValue, labelIcon);
				
			$("#labelTd").html(html);
			
		});
	}

	$("#closeBtn").click(function(){
		$("#joinDiv").hide();
	});

	$("#labelComment").click(function(){
		if ($("input[name=windowLabelComment]").val() == "N")
		{
			$("#labelCommentTr").show();
			$("input[name=windowLabelComment]").val("Y");
			$(this).attr("src", "../images/common/btn_campaign_label_comment_on.gif");
		}
		else {
			$("#labelCommentTr").hide();
			$("input[name=windowLabelComment]").val("N");
			$(this).attr("src", "../images/common/btn_campaign_label_comment.gif");
		}
	});

	$(".labelBtn").click(function(){
		var labelType = $(this).attr("id");
		var html = "";
		var msg = "";
		var labelImgSrc;

		$(".labelBtn").each(function(){
			labelImgSrc = $(this).attr("src").replace(/\_on/, "");
			$(this).attr("src", labelImgSrc);
		});

		$(".labelAdd").remove();
		html = labelAddItem(labelType);
		$("#labelTd").html(html);
		
		
		var imgSrc = $(this).attr("src").split(".gif");		
		imgSrc = imgSrc[0] + "\_on.gif";
		$(this).attr("src", imgSrc);
		var tt = $("input[name=windowLabelType]").val(labelType);		
	});

	$('#joinDiv').on("click", ".textAreaHeight",function(){
		
		var val = $(".textAreaHeight:checked").val();

		switch(val)
		{
			case "large" :
				$("#labelTextArea").css("height", "100px");
			break;

			case "medium" :
				$("#labelTextArea").css("height", "50px");
			break;

			case "small" :
				$("#labelTextArea").css("height", "35px");
			break;
		}
	});

	$("#labelWriteBtn").click(function() {
		
		var html = "";
		var checkTxt = "";
		var hiddenHtml = "";
		var labelValue = "";
		var labelIcon = "";
		var labelCheck = true;
		var labelRight = "";
		var labelComment			= $("input[name=windowLabelComment]").val();
		var windowLabelSeq		= $("input[name=windowLabelSeq]").val();
		var windowLabelName		= $("input[name=windowLabelName]").val();
		var windowLabelId			= $("input[name=windowLabelId]").val();

		var windowLabelExp = $("input[name=windowLabelExp]").val(); 
		var windowLabelType = $("input[name=windowLabelType]").val(); 
		
		if (!windowLabelName){
			alert("평가정보명을 입력하세요.");
			$("input[name=windowLabelName]").focus();
			return false;
		}
		if (!windowLabelType){
			alert("평가정보을 선택하세요.");
			return false;
		}

		var windowLabelUseChecked = ($("input[name=windowLabelUseCheck]").val() ==  'true')?" checked='checked' ":"";
		var windowLabelRequireChecked = ($("input[name=windowLabelRequireCheck]").val() ==  'true')?" checked='checked' ":"";

		switch(windowLabelType)
		{
			case "text" :
				labelValue = $("input[name='windowLabelValue[]']").size();
			break;

			case "radio" :
				$("input[name='windowLabelValue[]']").each(function(i){
					if(!$("input[name='windowLabelValue[]']").eq(i).val()){
						labelCheck = false;
						return false;
					}
					else {
						labelValue += $("input[name='windowLabelValue[]']").eq(i).val()+"|";
						labelIcon += $("input[name='labelIcon[]']").eq(i).val()+"|";
					}
				});
			break;

			case "textarea" :
				labelValue = $("input[name=windowLabelValue]:checked").val();
			break;

			case "checkbox" :
				$("input[name='windowLabelValue[]']").each(function(i){
					
					if(!$("input[name='windowLabelValue[]']").eq(i).val()){
						labelCheck = false;
						return false;
					}
					else {
						labelValue += $("input[name='windowLabelValue[]']").eq(i).val()+"|";
					}
				});
			break;

			case "select" :			

				$("#joinDiv input.windowLabelValue").each(function(i){
					
					if(!$("#joinDiv input.windowLabelValue").eq(i).val()){
						labelCheck = false;
						return false;
					}
					else {						
						var parent_label_item_seq = $("#joinDiv input.windowLabelValue").eq(i).attr('label_item_seq');
						
						var subLabelValues = new Array();
						$("#joinDiv input[name='windowSubLabelValue["+parent_label_item_seq+"][]']").each(function(){
							
							if($(this).val().length){
								subLabelValues.push($(this).val());
							}
						});
						
						labelValue += $("#joinDiv input.windowLabelValue").eq(i).val()+';'+subLabelValues.join(';')+"|"; 
						
					}
				});
			break;

		}

		if (labelCheck == false) {
			alert("평가정보값을 입력하세요.");
			return false;
		}
		
		var labelTypeText = getLabelTypeText(windowLabelType); 
		if (!windowLabelSeq){
			user_layer_seq = Number(layer_seq) + 1 ;		 
			layer_seq = user_layer_seq; 
			
			hiddenHtml = '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][name]" value="'+ windowLabelName +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][id]" value="'+ windowLabelName +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][type]" value="'+ windowLabelType +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][exp]" value="'+ windowLabelExp +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][icon]" value="'+ labelIcon +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][bulkorderform_seq]" value="'+ user_layer_seq +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ user_layer_seq +'][value]" value="'+ labelValue +'">';
			
			html = '<tr class="layer'+user_layer_seq+' hand">';		
			html += '<td class="its-td">'+ windowLabelName +'</td>';		
			html += '<td class="its-td"><img src="../skin/default/images/common/icon_move.gif" style="cursor:pointer"> ['+labelTypeText+'] '+ windowLabelExp+' <span class="btn small gray"><button type="button" class="listJoinBtn" id="listJoinBtn" value="'+ user_layer_seq  +'" >수정</button></span></td>';			
			html += '<td class="its-td"><input type="checkbox" name="labelItem[user]['+ user_layer_seq  +'][use]" class="bulkorder_chUse" bulkorder_ch="'+user_layer_seq+'" value="Y" /> 사용</td>';
			html += '<td class="its-td"><input type="checkbox" name="labelItem[user]['+ user_layer_seq  +'][required]" class="bulkorder_chRequired" value="Y" /> 필수</td>';		
			html += '<td class="its-td">'+hiddenHtml+'<span class="btn-minus" onclick="deleteRow(this)"><button type="button"></button></span></td>';
			html += '</tr>'; 
			$(".labelList_goodsreview").append(html); 
		}else{
			$(".layer"+ windowLabelSeq).remove();
			hiddenHtml = '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][name]" value="'+ windowLabelName +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][id]" value="'+ windowLabelId +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][type]" value="'+ windowLabelType +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][exp]" value="'+ windowLabelExp +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][icon]" value="'+ labelIcon +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][bulkorderform_seq]" value="'+ windowLabelSeq +'">';
			hiddenHtml += '<input type="hidden" name="labelItem[user]['+ windowLabelSeq +'][value]" value="'+ labelValue +'">';
			
			html = '<tr class="layer'+windowLabelSeq+'  hand">';		
			html += '<td class="its-td">'+ windowLabelName +'</td>';
			html += '<td class="its-td"><img src="../skin/default/images/common/icon_move.gif" style="cursor:pointer"> ['+labelTypeText+'] '+ windowLabelExp+' <span class="btn small gray"><button type="button" class="listJoinBtn" id="listJoinBtn" value="'+ windowLabelSeq  +'" join_type="user" >수정</button></span></td>';				
			html += '<td class="its-td"><input type="checkbox" name="labelItem[user]['+ windowLabelSeq  +'][use]" class="bulkorder_chUse" user_ch="'+windowLabelSeq+'" value="Y" '+windowLabelUseChecked+' /> 사용</td>';
			html += '<td class="its-td"><input type="checkbox" name="labelItem[user]['+ windowLabelSeq  +'][required]" class="bulkorder_chRequired" value="Y"  '+windowLabelRequireChecked+' /> 필수</td>';		
			html += '<td class="its-td">'+hiddenHtml+'<span class="btn-minus" onclick="deleteRow(this)"><button type="button"></button></span></td>';
			html += '</tr>';

			$(".labelList_goodsreview").append(html);
		}
		
		closeDialog("joinDiv");		
		//$("#joinDiv").hide()
		resetLabel();
		typeCheck();

	});

	$(".sampleBtn").click(function(){
		var position = $(window).scrollTop();
		var id = $(this).attr("id").replace(/Btn/, "");

		$(".sample").hide();
		$("#"+id).show();
	});

	$(".sampleCloseBtn").click(function(){
		$(".sample").hide();	
	});
});

function getLabelTypeText(labelType)
{
	var msg = "";
	switch (labelType)
	{
		case "text" :			msg = "텍스트박스";			break;
		case "radio" :			msg = "여러개 중 택1";		break;
		case "textarea" :		msg = "에디트박스";			break;
		case "checkbox" :		msg = "체크박스";			break;
		case "select" :			msg = "셀렉트박스";			break;

	}

	return msg;
}

function resetLabel()
{
	$("input[name=windowLabelSeq]").val("");
	$("input[name=windowLabelName]").val(""); 
	$("input[name=windowLabelExp]").val("");
	$("input[name=windowLabelType]").val("");
	$("#labelTd").html("우측에서 평가정보값을 선택하세요 ");
	$("input[name=windowLabelComment]").val("N");
	$("input[name=windowLabelCheck]").attr("checked", false);
	$("#labelCommentTr").hide();
	$(".labelAdd").remove();
	$("#labelComment").attr("src", "../images/btn_campaign_label_comment.gif");
	$(".sample").hide();

	$(".labelBtn").each(function(){
		labelImgSrc = $(this).attr("src").replace(/\_on/, "");
		$(this).attr("src", labelImgSrc);
	});
}

function joinDivShow()
{
	openDialog("평가정보 추가","joinDiv",{"width":"880","height":"500"});
	/*var position = $(window).scrollTop(); // 현재 스크롤바의 위치값을 반환합니다.
	$("#joinDiv").css("top", position+150);
	$("#joinDiv").show();*/
}

function rightChange(obj)
{
	if ($(obj).text() == "T") {
		$(obj).text("F");
	}
	else {
		$(obj).text("T");
	}
}

function labelAdd(type, right)
{
	var html = '<tr class="labelAdd"><td></td>';
	var category = $("input[name=category]").val();

	switch(type)
	{
		case "text" :
			html += '<td>';
			if (right) {
				html += '<tr class="labelAdd"><th align="left" style="width:65px;"></th>';
				html += '<td>';
				html += '<input type="text" name="windowLabelRight[]" value="" size="30" style="height:18px;" class="line"> <button type="button"  class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
				html += '</td>';
				html += '</tr>';
			}
			else {
				html += '<input type="text" name="windowLabelValue[]" size="30" style="height:18px;" class="line"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
			}
			html += '</td>';
			html += '</tr>';
		break;

		case "radio" :
			html += '<td>';
			if (right) {
				html += '<tr class="labelAdd"><th align="left" style="width:65px;"></th>';
				html += '<td>';
				html += '<input type="hidden" name="labelIcon[]" value="'+labelIcon[i]+'" /><img src="'+goodsreviewicon+'/emotion_happy.png" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="'+label_item_radio+'" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon"  iconindex="'+label_item_radio+'" /></span><input type="text" name="windowLabelRight[]" value=""  size="30" style="height:18px;" class="line"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
				html += '</td>';
				html += '</tr>';
			}
			else {
				html += '<input type="hidden" name="labelIcon[]" value="" /><img src="'+goodsreviewicon+'/emotion_happy.png" border="0" class="goodsReviewIcon hand" align="absmiddle"  iconindex="'+label_item_radio+'" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon"  iconindex="'+label_item_radio+'" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" size="30" style="height:18px;" class="line"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
			}
			html += '</td>';
			html += '</tr>';
			label_item_radio++;
		break;

		case "checkbox" :
			html += '<td>';
			if (right) {
				html += '<tr class="labelAdd"><th align="left" style="width:65px;"></th>';
				html += '<td>';
				html += '<input type="text" name="windowLabelRight[]" value=""  size="30" style="height:18px;" class="line"> <input type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
				html += '</td>';
				html += '</tr>';
			}
			else {
				html += '<input type="checkbox" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" size="30" style="height:18px;" class="line"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
			}
			html += '</td>';
			html += '</tr>';
		break;

		case "select" :
			html += '<td>';
			if (right) {
				if ($("#selectRight").size() > 0)
				{
					alert("정답은 하나 이상 등록할 수 없습니다.");
					return false;
				}
				html += '<tr class="labelAdd"><th align="left" style="width:65px;"></th>';
				html += '<td>';
				html += '<input type="text" name="windowLabelRight['+label_item_seq+']" id="selectRight" class="line" size="30" style="height:18px;"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
				html += '</td>';
				html += '</tr>';
			}
			else {
				html += '<input type="text" name="windowLabelValue['+label_item_seq+']" class="windowLabelValue line" label_item_seq="'+label_item_seq+'" size="30" style="height:18px;" > <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button> ';
			}
			html += '</td>';
			html += '</tr>';
			
			label_item_seq++;
		break;

	
	}

	if (right)
	{
		$("#labelRightTable").append(html);
	}
	else {
		$("#labelTable").append(html);
	}
}
 

function labelAddItem(labelType, labelValue, labelsubValue, labelIcon)
{
	var html = "";
	var labelTableHtml = "";
	var labelRightHtml = "";
	var category = $("input[name=category]").val();
	
	switch(labelType)
	{
		case "text" :

			html = '<input type="text" name="windowLabelValue[]" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="labelAdd(\'text\')" ><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button>';
			if (labelValue && labelValue > 1)
			{
				for (i=1; i<labelValue; i++)
				{
					labelTableHtml += '<tr class="labelAdd"><td></td>';
					labelTableHtml += '<td>';
					labelTableHtml += '<input type="text" name="windowLabelValue[]" size="30" style="height:18px;" class="line"> <button type="button" value="-" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
					labelTableHtml += '</td>';
					labelTableHtml += '</tr>';
				}
			}

			$("#labelTable").append(labelTableHtml);

					break;

		case "radio" :
			if (labelValue)
			{
				if(!labelIcon[0]) labelIcon[0] = 'emotion_happy.png';
				if(!labelIcon[1]) labelIcon[1] = 'emotion_happy.png';
				html = '<input type="hidden" name="labelIcon[]" value="'+labelIcon[0]+'" /><img src="'+goodsreviewicon+labelIcon[0]+'" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="0" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon" iconindex="0" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[0] +'" size="30" style="height:18px;" class="line"><br/><input type="hidden" name="labelIcon[]" value="'+labelIcon[1]+'" /><img src="'+goodsreviewicon+labelIcon[1]+'" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="1" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon" iconindex="1" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[1] +'" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="labelAdd(\'radio\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button>';
				label_item_radio=2;

				var count = labelValue.length-1;
				if (count > 2)
				{
					for (i=2; i<count; i++)
					{
						if(!labelIcon[i]) labelIcon[i] = 'emotion_happy.png';
						labelTableHtml += '<tr class="labelAdd"><td>&nbsp;</td>';
						labelTableHtml += '<td>';
						labelTableHtml += '<input type="hidden" name="labelIcon[]" value="'+labelIcon[i]+'" /><img src="'+goodsreviewicon+''+labelIcon[i]+'" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="'+i+'" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon"  iconindex="'+i+'" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[i] +'" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
						labelTableHtml += '</td>';
						labelTableHtml += '</tr>';
					}
				}
			}
			else {
				html = '<input type="hidden" name="labelIcon[]" value="" /><img src="'+goodsreviewicon+'/emotion_happy.png" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="0" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon" iconindex="0" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="" size="30" style="height:18px;" class="line"><br/><input type="hidden" name="labelIcon[]" value="" /><img src="'+goodsreviewicon+'/emotion_happy.png" border="0" class="goodsReviewIcon hand" align="absmiddle" iconindex="1" ><span class="btn small"><input type="button" value="선택" class="goodsReviewIcon" iconindex="1" /></span><input type="radio" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="labelAdd(\'radio\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button>';
				label_item_radio=2;
			}
			
			$("#labelTable").append(labelTableHtml);


		break;

		case "textarea" :
			
			var ckValueL = "";			
			var ckValueM = "";
			var ckValueS = "";
			
			if(labelValue == "large"){				
				 ckValueL = "checked";
			}
			if(labelValue == "medium"){				
				 ckValueM = "checked";
			}
			if(labelValue == "small"){				
				 ckValueS = "checked";
			}
			if(!labelValue){				
				 ckValueM = "checked";
			}
			
			html = '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
			html += '<tr>';
			html += '<td>';
			html += '<textarea id="labelTextArea" name="labelTextArea" style="width:98%; height:50px; padding:0;" class="line"></textarea></td></tr>';
			html += '<tr>';
			html += '<td>박스크기 : <input type="radio" name="windowLabelValue" value="large" class="textAreaHeight null" '+ ckValueL +'> large';
			html += '<input type="radio" name="windowLabelValue" value="medium" class="textAreaHeight null" '+ ckValueM +'> medium';
			html += '<input type="radio" name="windowLabelValue" value="small" class="textAreaHeight null" '+ ckValueS +'> small</td>';
			html += '</tr>';
			html += '</table>';
		break;

		case "checkbox" :
			if (labelValue)
			{
				html = '<input type="checkbox" name="labelContent[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[0] +'" size="30" style="height:18px;" class="line"> <br/> <input type="checkbox" name="labelContent[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[1] +'" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="labelAdd(\'checkbox\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button>';
				
				var count = labelValue.length-1;
				if (count > 2)
				{
					for (i=2; i<count; i++)
					{
						labelTableHtml += '<tr class="labelAdd"><td></td>';
						labelTableHtml += '<td>';
						labelTableHtml += '<input type="checkbox" name="labelKey[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="'+ labelValue[i] +'" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button>';
						labelTableHtml += '</td>';
						labelTableHtml += '</tr>';
					}
				}
			}
			else {
				html = '<input type="checkbox" name="labelContent[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="" size="30" style="height:18px;" class="line"> <br/> <input type="checkbox" name="labelContent[]" class="null" disabled> <input type="text" name="windowLabelValue[]" value="" size="30" style="height:18px;" class="line"> <button type="button" class="pointer" onclick="labelAdd(\'checkbox\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button>';

			}
			$("#labelTable").append(labelTableHtml);
		break;

		case "select" :

			if (labelValue)
			{							
				var count = labelValue.length-1;
				var subcount = 0;
				var j=0;
				
				html = '<input type="text" name="windowLabelvalue['+label_item_seq+']" label_item_seq="'+label_item_seq+'" value="'+ labelsubValue[0][0] +'" size="30" style="height:18px;" class="windowLabelValue line"> <button type="button" value="+" class="pointer" onclick="labelAdd(\'select\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button> ';
				
				if (count > 1)
				{
					for (i=1; i<count; i++)
					{
						label_item_seq++;
						
						labelTableHtml += '<tr class="labelAdd"><td></td>';
						labelTableHtml += '<td>';
						labelTableHtml += '<input type="text" name="windowLabelvalue['+label_item_seq+']" label_item_seq="'+label_item_seq+'" value="'+ labelsubValue[i][0] +'" size="30" style="height:18px;" class="windowLabelValue line"> <button type="button" class="pointer" onclick="deleteRow(this)"><img src="/admin/skin/default/images/design/icon_design_minus.gif" height="18px" align="middle"></button> ';
						labelTableHtml += '</td>';
						labelTableHtml += '</tr>'; 
						
					}
				}
				
			}
			else {
				html = '<input type="text" name="windowLabelValue['+label_item_seq+']" class="windowLabelValue line" label_item_seq="'+label_item_seq+'"  size="30" style="height:18px;"> <button type="button"  class="pointer" onclick="labelAdd(\'select\')"><img src="/admin/skin/default/images/design/icon_design_plus.gif" height="18px" align="middle"></button> ';
				label_item_seq++;
			}
			$("#labelTable").append(labelTableHtml);
		break;

		
	}
	
	return html;
}

function deleteRow(obj){

	if ($("input[name=campaignMemberCount]").val() > 0)
	{
		alert("참여자가 있을 경우 참여형식을 수정할 수 없습니다.");
		return false;
	}

	$(obj).parent().parent().remove();
	
	if( $(".bulkorder_chUse").size() < 1 ) {
		$("input[name='list_show[]'][value='[reviewinfo]']").attr("checked",false);
		$("input[name='list_show[]'][value='[reviewinfo]']").attr("disabled",true);
	}
} 



function deleteAddRow(seq){

	if ($("input[name=campaignMemberCount]").val() > 0)
	{
		alert("참여자가 있을 경우 참여형식을 수정할 수 없습니다.");
		return false;
	}

	$(".layer"+seq ).remove();
} 


function alertFocus(msg, target)
{
	if (msg) alert(msg);
	if (target) target.focus();
}