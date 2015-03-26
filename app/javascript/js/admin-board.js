
//게시글 상세 이미지 리사이즈
function imageResize(image,default_width){
	var width = image.width;
	var height = image.height;
	var maxWidth = (default_width) ? default_width : 750;
	var widthPercent = maxWidth / width;

	if (width > maxWidth) {
		width = width * widthPercent;
		height = height * widthPercent; 
	}
	image.width = width;
	image.height = height;
}


/*
 * 게시판 관련 자바스크립트
 */ 
$(document).ready(function() {  
	// 게시글 등록
	$('#boad_write_btn').live('click', function() {
		boardaddFormDialog(boardwriteurl, '90%', '800', '게시글 등록','false');
	});
	
	
	$("span.realfilelist").live("click",function(){
		var filedown = $(this).attr("filedown");
		if(confirm("파일을 다운받으시겠습니까?") ) {
			document.location.href=filedown;
		}
	});

	$(".userinfo").live("click",function(){
		var mseq = $(this).attr("mseq");
		var href = "/admin/member/detail?member_seq="+mseq;
		var a = window.open(href, 'mbdetail', '');
		if ( a ) {
			a.focus();
		}		
	});
	
	$("a[viewlinkurl]").live("click",function(){
		var viewlink = $(this).attr('href');
		boardaddFormDialog(viewlink, '90%', '800', '게시글 보기','false');
	});

	// 게시글 수정
	$(":input[name=boad_modify_btn]").live("click", function() {
		var seq = $(this).attr("board_seq");
		boardaddFormDialog(boardmodifyurl+seq, '90%', '800', '게시글 수정','false');
	});

	// 게시글 답변
	$(":input[name=boad_reply_btn]").live("click", function() {
		var seq = $(this).attr("board_seq"); 
		boardaddFormDialog(boardreplyurl+seq, '90%', '840', '게시글 답변','false');
	});


	// 게시글 삭제
	$(":input[name=boad_delete_btn]").live("click", function() {
		var board_id = $(this).attr('board_id');
		var delseq = $(this).attr('board_seq'); 
		if( board_id == 'goods_review' ) {
			$.ajax({
				'url' : '../board_goods_process',
				'data' : {'mode':'goods_review_less_view', 'delseq':delseq, 'board_id':board_id},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res.result == 'delete') { 
						var msg = "삭제된 게시글은 복구할 수 없습니다.\n정말로 삭제하시겠습니까?  ";
						openDialogConfirmtitle(res.name+' 삭제',msg,'450','140',function(){
						loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5}); 
							$.ajax({
							'url' : '../board_process',
							'data' : {'mode':'board_delete', 'delseq':delseq, 'board_id':board_id},
							'type' : 'post',
							'success' : function(res){
								loadingStop("body",true);
								openDialogAlert('정상적으로 삭제되었습니다.','400','140',function(){document.location.reload(); }); 
							}
						});},function(){});
					}else if(res.result == "lees") { 
						openDialogConfirmtitle(res.name+' 삭제',res.msg,'480','340',function() {
						loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5}); 
						var board_less_emoney = $("#board_less_emoney").val();
						var board_less_point = $("#board_less_point").val(); 
							$.ajax({
								'url' : '../board_process',
								'data' : {'mode':'board_delete', 'delseq':delseq, 'board_id':board_id, 'board_less_emoney':board_less_emoney, 'board_less_point':board_less_point},
								'type' : 'post',
								'success' : function(res){
								loadingStop("body",true);
								openDialogAlert('정상적으로 삭제되었습니다.','400','140',function(){document.location.reload(); }); 
								}
							});
						},function(){}); 
					}else{
						openDialogAlert(res.msg,'400','140'); 
					}
				}
			});
		}else{
			$.ajax({
				'url' : '../board_goods_process',
				'data' : {'mode':'board_less_view', 'delseq':delseq, 'board_id':board_id},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res.result == 'delete') { 
						var msg = "삭제된 게시글은 복구할 수 없습니다.\n정말로 삭제하시겠습니까?  ";
						openDialogConfirmtitle(res.name+' 삭제',msg,'450','140',function(){
						loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5}); 
							$.ajax({
							'url' : '../board_process',
							'data' : {'mode':'board_delete', 'delseq':delseq, 'board_id':board_id},
							'type' : 'post',
							'success' : function(res){
								loadingStop("body",true);
								openDialogAlert('정상적으로 삭제되었습니다.','400','140',function(){document.location.reload(); });
							}
						});},function(){});
					}else if(res.result == "lees") { 
						openDialogConfirmtitle(res.name+' 삭제',res.msg,'480','285',function() {
						loadingStart("body",{segments: 12, width: 15.5, space: 6, length: 13, color: '#000000', speed: 1.5}); 
							var board_less_emoney = $("#board_less_emoney").val();
							$.ajax({
								'url' : '../board_process',
								'data' : {'mode':'board_delete', 'delseq':delseq, 'board_id':board_id, 'board_less_emoney':board_less_emoney},
								'type' : 'post',
								'success' : function(res){
								loadingStop("body",true);
								openDialogAlert('정상적으로 삭제되었습니다.','400','140',function(){document.location.reload(); });
								}
							});
						},function(){}); 
					}else{
						openDialogAlert(res.msg,'400','140'); 
					}
				}
			});
		}
	}); 

	// 게시글 보기
	$('span.boad_view_btn').live('click', function() {
		var viewlink = $(this).attr('viewlink');
		boardaddFormDialog(viewlink, '90%', '800', '게시글 보기','false');
	});
	
	//검색시
	$('button.boardsearchsubmit').live('click', function() {
		$("#search_text").focus();//검색
		/**if( !$("#search_text").val() ) {
			alert('검색어를 입력해 주세요!');
			return;
		}
		**/
		$("#page").val('');
		$('#boardsearch').submit();
	});

	//삭제글//비밀글
	$('input#searchhidden, input#searchhiddennone,  input#searchhiddenall, input#searchdisplay').live('click', function() {
		$("#search_text").focus();//검색
		$("#page").val('');
		$('#boardsearch').submit();
	});
	
	// FAQ 게시글 보기
	$('span.boad_faqview_btn').live('click', function() {
		var board_seq = $(this).attr('board_seq');
		$("#faqcontent_"+board_seq).toggle();
	}); 

	//FAQ 노출여부 설정
	$('input.listhidden').live('click', function() {
		var board_id = $(this).attr('board_id');
		var board_seq = $(this).attr('board_seq');
		var hidden = $(this).attr("checked"); 
		if(hidden != 'checked' ){
			$(this).parent().parent().css("background","#FFF");
		}else{
			$(this).parent().parent().css("background","#CECECE");
		}
		$.ajax({
				'url' : '../board_process',
				'data' : {'mode':'board_faq_hidden', 'seq':board_seq, 'board_id':board_id, 'hidden':hidden},
				'type' : 'post',
				'target' : 'actionFrame',
				'success' : function(res) { 
				}
			});
	});

	$("#commentlayshow").live("click",function() {
		$("#cmtlistlay").toggle();
	});

	//댓글등록및 수정
	$('#cmtform1').validate({
		onkeyup: false,
		rules: {
			name: { required:true},
			content: { required:true}
		},
		messages: {
			name: { required:'입력해 주세요.'},
			captcha_code: { required:'입력해 주세요.'},
			pw: { required:''},
			content: { required:'입력해 주세요.'}
		},
		errorPlacement: function(error, element) {
			error.appendTo(element.parent());
		},
		submitHandler: function(f) {

			if(!$("#cmtname").val() || $("#cmtname").val() == "이름을 입력해 주세요." ) {
				openDialogAlert('이름을 입력해 주세요.','400','140');
				//alert('이름을 입력해 주세요.');
				$("#cmtname").focus();
				return false;
			}

			if(!$("#cmtcontent").val() || $("#cmtcontent").val() == "내용을 입력해 주세요.") {
				openDialogAlert('내용을 입력해 주세요.','400','140');
				//alert('내용을 입력해 주세요.');
				$("#cmtcontent").focus();
				return false;
			}
			f.submit();
		}
	});
 

	//회원 댓글 수정
	$("img[name=boad_cmt_modify_btn]").live("click", function() {
		var cmtseq = $(this).attr("board_cmt_seq");
		var board_id = $(this).attr('board_id');
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_comment_item', 'cmtseq':cmtseq, 'seq':seq, 'board_id':board_id},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(data){
				if(data) {
					if(data.result == true) {
						$('#cwrite').show(); 
						$('#cmtname').val(data.name);
						$('#cmtsubject').val(data.subject);
						$('#cmtcontent').val(data.content);
						$('#cmtseq').val(data.seq);
						if(data.hidden == 1 ) {
							$('#cmthidden').attr("checked",true);
						}else{
							$('#cmthidden').attr("checked",false);
						}
						$('#cmtmode').val('board_comment_modify');
						$('#board_commentsend').text('댓글수정');
						document.location.href="#cwriteform";
						$('.cmtreplylay').hide(); 
					}else{
						openDialogAlert(data.msg,'400','140',function(){});
					}
				}else{
					openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
				} 
			}
		});
	});


	// 댓글 삭제
	$("img[name=boad_cmt_delete_btn]").live("click",function(){
		var cmtseq = $(this).attr("board_cmt_seq");
		var board_id = $(this).attr('board_id');
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();
		if(confirm("정말로 댓글을 삭제하시겠습니까? ")) {
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_delete', 'delcmtseq':cmtseq, 'seq':seq, 'board_id':board_id},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							openDialogAlert(res.msg,'400','140',function(){boardaddFormDialog(returnurl+'#cwriteform', '90%', '800', '게시글 보기','false');});
						}else{
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
					}
				}
			});
		}
	});

	//댓글취소
	$("#board_comment_cancel").live("click",function(){
		$('#cmtseq').val('');
		//$('#cmtname').val('');
		//$('#cmtpw').val('');
		$('#cmtcontent').val('');
		$('#cmtmode').val('board_comment_write');
		setDefaultText();
	});
 

	//댓글 > 답글 등록가능함
	$("img[name=boad_cmt_reply_btn]").live("click", function() {
		var idx = $(this).attr("board_cmt_idx");
		var cmtseq = $(this).attr("board_cmt_seq");
		if($('#cmtname'+cmtseq).val() != $('#cmtname'+cmtseq).attr('title')) $('#cmtname'+cmtseq).val($('#cmtname'+cmtseq).attr('title'));
		if($('#cmtpw'+cmtseq).val() != $('#cmtpw'+cmtseq).attr('title')) $('#cmtpw'+cmtseq).val('');
		if($('#cmtsubject'+cmtseq).val() != $('#cmtsubject'+cmtseq).attr('title')) $('#cmtsubject'+cmtseq).val($('#cmtsubject'+cmtseq).attr('title'));
		if($('#cmtcontent'+cmtseq).val() != $('#cmtcontent'+cmtseq).attr('title')) $('#cmtcontent'+cmtseq).val($('#cmtcontent'+cmtseq).attr('title'));
		if($('#board_commentsend_reply'+cmtseq).attr('board_cmt_reply_seq')){
			$("tr.cmtreplyform"+idx).show();
		}else{
			$("tr.cmtreplyform"+idx).toggle();
		}
		$("tr.cmtreplyform"+idx).find('.captcha_code').val('');
		$('#board_commentsend_reply'+cmtseq).text('답글등록');
		$('#board_commentsend_reply'+cmtseq).attr('board_cmt_reply_seq','');

	});

	//회원/비회원 댓글 > 답글 수정
	$("img[name=boad_cmt_modify_reply_btn], img[name=boad_cmt_modify_reply_btn_no]").live("click", function() {
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var idx = $(this).attr("board_cmt_idx");
		var board_id = $(this).attr('board_id');
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();
		$("tr.cmtreplyform"+idx).find('.captcha_code').val('');

		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_comment_item', 'cmtseq':cmtreplyseq, 'seq':seq, 'board_id':board_id},
			'type' : 'post',
			'dataType': 'json',
			'success': function(data) {
				$("tr.cmtreplyform"+idx).show(); 
				$('#cmtname'+cmtseq).val(data.name);
				$('#cmtsubject'+cmtseq).val(data.subject);
				$('#cmtcontent'+cmtseq).val(data.content);
				if(data.hidden == 1 ) {
					$('#cmthidden'+cmtseq).attr("checked",true);
				}else{
					$('#cmthidden'+cmtseq).attr("checked",false);
				}
				$('#board_commentsend_reply'+cmtseq).text('답글수정');
				$('#board_commentsend_reply'+cmtseq).attr('board_cmt_reply_seq',cmtreplyseq);
			}
		});
	});
 
	//댓글 > 답글 등록/수정
	$("button[name=board_commentsend_reply]").live("click", function() {
		var idx = $(this).attr("board_cmt_idx");
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var board_id = $(this).attr('board_id');
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();

		var cmtcontent = $("#cmtcontent"+cmtseq).val();

		var user_name = $("#cmtname"+cmtseq).val();
		var password = $("#cmtpw"+cmtseq).val();
		var cmthidden = ($("#cmthidden"+cmtseq).attr("checked"))?1:0;
		var captcha_code = $("tr.cmtreplyform"+idx).find('.captcha_code').val(); 

		if(!cmtcontent || cmtcontent == '입력해 주세요.') {
			
			openDialogAlert('답글을 입력해 주세요.','400','140');
			//alert('답글을 입력해 주세요.');
			$("#cmtcontent"+cmtseq).focus();
			return false;
		}
		if(cmtreplyseq) {//답글수정시
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_reply_modify_pwcheck', 'cmtseq':cmtreplyseq, 'seq':seq, 'board_id':board_id, 'name':user_name, 'hidden':cmthidden, 'pw':password, 'content':cmtcontent, 'captcha_code':captcha_code},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							openDialogAlert(res.msg,'400','140',function(){boardaddFormDialog(returnurl+'#cwriteform', '90%', '800', '게시글 보기','false');});//
						}else{
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
					}
				}
			});
		}else{//답글등록시
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_reply', 'cmtseq':cmtseq, 'seq':seq, 'board_id':board_id, 'name':user_name, 'hidden':cmthidden, 'pw':password, 'content':cmtcontent},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							openDialogAlert(res.msg,'400','140',function(){boardaddFormDialog(returnurl+'#cwriteform', '90%', '800', '게시글 보기','false');});
						}else{
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
					}
				}
			});
		}
	});

	//답글취소
	$("button[name=board_comment_reply_cancel]").live("click",function(){
		var cmtseq = $(this).attr("board_cmt_seq");
		//$("#cmtname"+cmtseq).val('');
		//$("#cmtpw"+cmtseq).val('');
		$("#cmtcontent"+cmtseq).val('');
		$('#board_comment_reply_cancel'+cmtseq).attr('board_cmt_reply_seq','');
		$(".cmtreplylay").hide();//답글폼숨김
		setDefaultText();
	});

	
	// 게시글 > 댓글 전체삭제
	$(":input[name=board_cmt_alldelete_btn]").live("click", function() {
		var board_id = $(this).attr('board_id');
		var parentseq = $(this).attr('board_seq'); 
		var returnurl = $('#cmtreturnurl').val();
		if(confirm("정말로 댓글을 일괄삭제하시겠습니까? ")) {
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_alldelete', 'seq':parentseq, 'board_id':board_id},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							openDialogAlert(res.msg,'400','140',function(){boardaddFormDialog(returnurl+'#cwriteform', '90%', '800', '게시글 보기','false');});
						}else{
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
					}
				}
			});
		}
	});

	// 게시글 > 댓글 선택삭제
	$(":input[name=board_cmt_seldelete_btn]").live("click", function() {
		var board_id = $(this).attr('board_id');
		var parentseq = $(this).attr('board_seq'); 
		var returnurl = $('#cmtreturnurl').val();

		var delcmtseq = '';
		$('.cmtcheckeds').each(function(e, el) { 
			if( $(el).attr('checked') == 'checked' ){
				delcmtseq += $(el).val() + ",";
			}
		});
		if(!delcmtseq){
			openDialogAlert('삭제할 댓글을 선택해 주세요.','400','140');
			//alert('삭제할 댓글을 선택해 주세요.');
			return false;
		}

		if(confirm("정말로 선택된 댓글을 삭제하시겠습니까? ")) {
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_seldelete', 'delcmtseq':delcmtseq, 'seq':parentseq, 'board_id':board_id},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							openDialogAlert(res.msg,'400','140',function(){boardaddFormDialog(returnurl+'#cwriteform', '90%', '800', '게시글 보기','false');});
						}else{
							openDialogAlert(res.msg,'400','140',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','140',function(){});
					}
				}
			});
		}
	});

	$(":input[name=boardviewclose]").live("click", function() {
		$('#dlg').dialog('close');//layer 창닫기
	});

	$("#boardhidden").live("click", function() { 
		if( $(this).attr('checked') == 'checked' ){
			$("#boardpw").attr("disabled",false); 
		}else{ 
			$("#boardpw").attr("disabled",true); 
		}
	});

	$('select#multichkec').change(function() {  
		if($(this).val() == 'false'){
			$('#checkboxAll').attr("checked",'');
			$('#checkboxAll').attr('rel', 'no');
			$('#checkboxAll').removeAttr("checked");
			$('.checkeds').each(function(e, el) {
				if( $(el).attr('disabled') != 'disabled' ){//제외
					$(el).removeAttr("checked");
					$(this).closest('tr').removeClass('checked-tr-background');
				}
			});
		}else{
			$('#checkboxAll').attr("checked","checked");			
			$('#checkboxAll').attr('rel', 'yes');
			$('.checkeds').each(function(e, el) {
				if( $(el).attr('disabled') != 'disabled' ){//제외
					$(el).attr('checked', true);
					$(this).closest('tr').addClass('checked-tr-background');
				}
			});
		}
	});

	
	$('#typecheckedall').live('click', function() { 
		$('.skin').each(function(e, el) { 
			$(el).attr('checked', true); 
		});
	});
	
	//게시글 선택/일괄 
	$('#checkboxAll').live('click', function() { 
		checkAll(this, '.checkeds');
	});
	
	//댓글 선택/일괄
	$('#checkboxcmtAll').live('click', function() { 
		checkAll(this, '.cmtcheckeds');
	});

	
	/**
	 * 체크박스 전체 선택
	 * @param string el 전체 선택 체크박스
	 * @param string targetEl 적용될 체크박스 클래스명
	 */
	function checkAll(el, targetEl) { 
		if( $(el).attr('rel') == 'yes' ) {
			var do_check = false;
			$(el).attr('rel', 'no');
		} else {
			var do_check = true;
			$(el).attr('rel', 'yes');
		}
		$(targetEl).each(function(e, el) {
			if( $(el).attr('disabled') != 'disabled' ){//제외
				$(el).attr('checked', do_check).change();
			}
		});
	}

	$('select#multicmode').change(function() { 
		var mode = $(this).val();
		var delseq = '';
		$('.checkeds').each(function(e, el) { 
			if( $(el).attr('checked') == 'checked' ){
				delseq += $(el).val() + ",";
			}
		});
		if(!delseq){
			openDialogAlert('게시물을 선택해 주세요.','400','140');
			return false;
		}
		$("#board_mode").val(mode);//mode
		$("#delseq").val(delseq);//mode 
		if(mode == 'board_multi_delete' ){//다중삭제시
			var msg = "삭제된 게시글은 복구할 수 없습니다.\n정말로 삭제하시겠습니까? ";
			openDialogConfirmtitle('상품후기 삭제',msg,'450','140',function(){$("#BoardCopy").submit();return true;},function(){return false;});
			/**
			if(confirm("삭제된 게시글은 복구할 수 없습니다.\n정말로 삭제하시겠습니까? ")) {
				$("#BoardCopy").submit();
				return true;
			}
			**/
			return false;
		}else if(mode == 'board_multi_data_cmt_delete' ){//다중삭제시
			
			var msg = "삭제된 (원본글+덧글)은 복구할 수 없습니다.\n정말로 삭제하시겠습니까? ";
			openDialogConfirmtitle('상품후기 삭제',msg,'450','140',function(){$("#BoardCopy").submit();return true;},function(){return false;});
			/**if(confirm("삭제된 (원본글+덧글)은 복구할 수 없습니다.\n정말로 삭제하시겠습니까? ")) {
				$("#BoardCopy").submit();
				return true;
			}
			**/
			return false;
		}else{
			if(mode == 'board_multi_move' ){//다중이동시
				var modetitle = "이동할 게시판을 선택해 주세요.";
				var modebtntitle = "게시물이동";
			}else{
				var modetitle = "복사할 게시판을 선택해 주세요.";
				var modebtntitle = "게시물복사";
			}
		}
		$("#boardcopybtn").val(modebtntitle);
		openDialog("게시판 선택  <span class='desc'>"+modetitle+"</span>", "boardmovecopyPopup", {"width":"400","height":"150","show" : "fade","hide" : "fade"});
	});

	$("#boardcopybtn").click(function(){
		if($("#board_mode").val() == 'board_multi_move' ){//다중이동시
			var modebtntitle = "이동";
		}else{
			var modebtntitle = "복사";
		}
		if(!$("#copyid").val()){
			alert(modebtntitle+'할 게시판을 선택해 주세요.');
			return false;
		}
		var copyidar = $("#copyid option:selected").text().split("(");
		if(confirm("정말로 게시물을 [ "+copyidar[0] +"] (으)로 "+modebtntitle+"하시겠습니까?")){
			$("#BoardCopy").submit();
		}
	});

	// 날짜 선택시 해당 일자로 셋팅한다.
	$("input.select_date").click(function() {
		switch(this.id) {
		case 'today' :  $('#rdate_s').val(getDate(0));
			$('#rdate_f').val(getDate(0));
			break;
		case '3day' :   $('#rdate_s').val(getDate(3));
			$('#rdate_f').val(getDate(0));
			break;
		case '1week' :  $('#rdate_s').val(getDate(7));
			$('#rdate_f').val(getDate(0));
			break;
		case '1month' : $('#rdate_s').val(getDate(30));
			$('#rdate_f').val(getDate(0));
			break;

		case '3month' : $('#rdate_s').val(getDate(90));
			$('#rdate_f').val(getDate(0));
			break;
		case 'select_date_all' :$('#rdate_s').val('');
			$('#rdate_f').val('');
			break;
		default:
			$('#rdate_s').val('');
			$('#rdate_f').val('');
		}
	});

	try{ 
	if(eval("board_id")) 
	{ 
		$('#display_quantity').bind('change', function() {
			$.cookie( "itemlist_qty"+board_id, $(this).val() );
			$("#perpage").val($(this).val());
			$("#boardsearch").submit();
		});

		if($.cookie("itemlist_qty"+board_id)) {
			$('#display_quantity').val($.cookie("itemlist_qty"+board_id) );
		}
	} 
	}catch(e){ 
		
		$('#display_quantity').bind('change', function() {
			$.cookie( "itemlist_qty_manager", $(this).val() );
			$("#perpage").val($(this).val());
			$("#boardsearch").submit();
		});

		if($.cookie("itemlist_qty_manager")) {
			$('#display_quantity').val($.cookie("itemlist_qty_manager") );
		}
	}
	
	$("#searchcategory").bind("change",function(){ 
		$("#category").val($(this).val());
		$("#page").val('');
		$("#boardsearch").submit(); 
	}); 

	$("#selreply").bind("change",function(){ 
		$("#searchreply").val($(this).val());
		$("#page").val('');
		$("#boardsearch").submit(); 
	});
	
	$("#searchscore").bind("change",function(){ 
		$("#score").val($(this).val());
		$("#page").val('');
		$("#boardsearch").submit(); 
	});

	$("#searchscore_star").bind("change",function(){ 
		$("#score_avg").val($(this).val());
		$("#page").val('');
		$("#boardsearch").submit(); 
	});
	

	//동영상등록폼
	$(".batchVideoRegist").live("click",function(){
		var seq = $(this).attr("board_seq");
		window.open('popup_video?seq='+seq+'&id='+board_id,'','width=550,height=350');
	});

	//SMS보내기
	$(".hidden_sms_send").live("click",function(event){
		var board_id = $(this).attr(".board_id");
		var board_seq = $(this).attr(".board_seq");
		$.get('../member/sms_pop?board_id='+board_id+'&board_seq='+board_seq, function(data) {
			$('#sendPopup').html(data);
			setDefaultText();
			openDialog("SMS 발송 <span class='desc'></span>", "sendPopup", {"width":"600","height":"280"});
		});
	});

	/**
	* 게시글 평가하기
	**/
	$("span.icon_recommend_lay_y, span.icon_none_rec_lay_y, span.icon_recommend1_lay_y,  span.icon_recommend2_lay_y,  span.icon_recommend3_lay_y,  span.icon_recommend4_lay_y,  span.icon_recommend5_lay_y").live("click", function() {
		//openDialogAlert('이미 평가하신 게시글입니다.','400','150'); 
	});

	$("span.icon_recommend_lay, span.icon_none_rec_lay, span.icon_recommend1_lay, span.icon_recommend2_lay,  span.icon_recommend3_lay,  span.icon_recommend4_lay,  span.icon_recommend5_lay").live("click", function() {// 
		if(!eval('board_seq')) { 
			var board_seq_new = $(this).attr("board_seq");
		}else{
			var board_seq_new = board_seq;
		}
		if(!eval('board_id')) { 
			var board_id_new = $(this).attr("board_id");
		}else{
			var board_id_new = board_id;
		}
		var board_recommend = $(this).attr("board_recommend");
		boardscoresave(board_recommend, board_seq_new, board_id_new);
	});

	
	/**
	* 댓글 평가하기
	**/
	$("span.icon_cmt_recommend_lay_y, span.icon_cmt_none_rec_lay_y").live("click", function() {
		//openDialogAlert('이미 평가하신 댓글입니다.','400','150'); 
	});
	
	$("span.icon_cmt_recommend_lay, span.icon_cmt_none_rec_lay").live("click", function() { 
		if(!eval('board_seq')) { 
			var board_seq_new = $(this).attr("board_seq");
		}else{
			var board_seq_new = board_seq;
		}
		if(!eval('board_id')) { 
			var board_id_new = $(this).attr("board_id");
		}else{
			var board_id_new = board_id;
		}
		var cparent = $(this).attr("board_cmt_seq");
		var board_recommend = $(this).attr("board_recommend");
		boardcmtscoresave(board_recommend, board_seq_new, cparent, board_id_new);
	});

}); 

/**
* 게시글 평가하기 
	if( eval('gl_isuser') ) {//회원전용
	}else{
		getLogin();
	}

**/
function boardscoresave(scoreid, boardseq, board_id) { 
		//openDialogConfirmtitle('게시글 평가하기','정말로 평가하시겠습니까?','240','150',function() {
			$.ajax({
				'url' : '../board_process/board_score_save',
				'data' : {'parent':boardseq, 'board_id':board_id, 'scoreid':scoreid},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res.result) { 
						$("span.idx-"+scoreid+"-"+boardseq).text(res.scoreid); 
						$("span.icon_recommend_"+boardseq+"_lay").addClass("icon_recommend_lay_y").removeClass("icon_recommend_lay");
						$("span.icon_none_rec_"+boardseq+"_lay").addClass("icon_none_rec_lay_y").removeClass("icon_none_rec_lay");
						$("span.icon_recommend1_"+boardseq+"_lay").addClass("icon_recommend1_lay_y").removeClass("icon_recommend1_lay"); 
						$("span.icon_recommend2_"+boardseq+"_lay").addClass("icon_recommend2_lay_y").removeClass("icon_recommend2_lay"); 
						$("span.icon_recommend3_"+boardseq+"_lay").addClass("icon_recommend3_lay_y").removeClass("icon_recommend3_lay"); 
						$("span.icon_recommend4_"+boardseq+"_lay").addClass("icon_recommend4_lay_y").removeClass("icon_recommend4_lay"); 
						$("span.icon_recommend5_"+boardseq+"_lay").addClass("icon_recommend5_lay_y").removeClass("icon_recommend5_lay");
						$("input[name='input-recommend["+boardseq+"]']").addClass("hide");
						openDialogAlert(res.msg,'400','150'); 
					}else{
						openDialogAlert(res.msg,'400','150'); 
					}
				}
			});
		//},function(){$("input[name='input-recommend["+boardseq+"]']").attr('checked', false);});
}

/**
* 댓글 평가하기 
	
	if( eval('gl_isuser') ) {//회원전용
	}else{
		getLogin();
	}
**/
function boardcmtscoresave( scoreid, boardseq, cseq, board_id) {
		//openDialogConfirmtitle('댓글 평가하기','정말로 평가하시겠습니까?','240','150',function() {
			$.ajax({
				'url' : '../board_comment_process/board_score_save',
				'data' : {'parent':boardseq,  'cparent':cseq, 'board_id':board_id, 'scoreid':scoreid},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res.result) { 
						$("span.idx-cmt-"+scoreid+"-"+boardseq+"-"+cseq).text(res.scoreid); 
						$("span.icon_cmt_recommend_"+boardseq+"_"+cseq+"_lay").addClass("icon_cmt_recommend_lay_y").removeClass("icon_cmt_recommend_lay");
						$("span.icon_cmt_none_rec_"+boardseq+"_"+cseq+"_lay").addClass("icon_cmt_none_rec_lay_y").removeClass("icon_cmt_none_rec_lay");
						openDialogAlert(res.msg,'400','150'); 
					}else{
						openDialogAlert(res.msg,'400','150'); 
					}
				}
			});
		//},function(){});
}

//게시판글쓰기공통
function boardwrite(){
	EditorJSLoader.ready(function(Editor) {
		DaumEditorLoader.init(".daumeditor");
	});

	$("input[name='onlynotice_sdate']").addClass('datepicker');
	$("input[name='onlynotice_edate']").addClass('datepicker');
	setDatepicker($("input[name='onlynotice_sdate']"));
	setDatepicker($("input[name='onlynotice_edate']"));
	setDefaultText();
	noticecheck();
	if ( board_id == 'gs_seller_notice' ) {
		$("input[name='onlypopup_sdate']").addClass('datepicker');
		$("input[name='onlypopup_edate']").addClass('datepicker');
		setDatepicker($("input[name='onlypopup_sdate']"));
		setDatepicker($("input[name='onlypopup_edate']"));
		popupcheck();
	}
}

function noticecheck(){
	var boardnotice = $("input[name='notice']:checked").val(); 
	if( boardnotice ){  
		if( !$("#seq").val() ) $("#onlynotice0").attr("checked","checked");
		$("input[name='onlynotice']").attr("disabled",false); 
		$("input[name='onlynotice_sdate']").attr("disabled",false); 
		$("input[name='onlynotice_edate']").attr("disabled",false); 
	}else{
		$("input[name='onlynotice']").attr("checked",false);
		$("input[name='onlynotice']").attr("disabled",true); 
		$("input[name='onlynotice_sdate']").attr("disabled",true); 
		$("input[name='onlynotice_edate']").attr("disabled",true); 
	}
}


function popupcheck(){
	var boardpopup = $("input[name='popup']:checked").val(); 
	if( boardpopup ){  
		if( !$("#seq").val() ) $("#onlypopup0").attr("checked","checked");
		$("input[name='onlypopup']").attr("disabled",false); 
		$("input[name='onlypopup_sdate']").attr("disabled",false); 
		$("input[name='onlypopup_edate']").attr("disabled",false); 
	}else{
		$("input[name='onlypopup']").attr("checked",false);
		$("input[name='onlypopup']").attr("disabled",true); 
		$("input[name='onlypopup_sdate']").attr("disabled",true); 
		$("input[name='onlypopup_edate']").attr("disabled",true); 
	}
}


//sns 연동 관련 함수
function goTwitter(msg,url) {
	var href = "http://twitter.com/home?status=" + encodeURIComponent(msg) + " " + encodeURIComponent(url);
	var a = window.open(href, 'twitter', '');
	if ( a ) {
		a.focus();
	}
}
function goMe2Day(msg,url,tag) {
	var href = "http://me2day.net/posts/new?new_post[body]=" + encodeURIComponent(msg) + " " + encodeURIComponent(url) + "&new_post[tags]=" + encodeURIComponent(tag);
	var a = window.open(href, 'me2Day', '');
	if ( a ) {
		a.focus();
	}
}
function goFaceBook(msg,url) {
	var href = "http://www.facebook.com/sharer.php?u=" + url + "&t=" + encodeURIComponent(msg);
	var a = window.open(href, 'facebook', '');
	if ( a ) {
		a.focus();
	}
}
function goCyWorld(no) {
	var href = "http://api.cyworld.com/openscrap/post/v1/?xu=http://ticketmonster.co.kr/html/cyworldConnectToXml.php?no=" + no +"&sid=suGPZc14uNs4a4oaJbVPWkDSZCwgY8Xe";
	var a = window.open(href, 'facebook', 'width=450,height=410');
	if ( a ) {
		a.focus();
	}
}
function goYozmDaum(link,prefix,parameter) {
	var href = "http://yozm.daum.net/api/popup/post?sourceid=&link=" + encodeURIComponent(link) + "&prefix=" + encodeURIComponent(prefix) + "&parameter=" + encodeURIComponent(parameter);
	var a = window.open(href, 'yozmSend', 'width=466, height=356');
	if ( a ) {
		a.focus();
	}
}

/**
 * 현재 시각을 Time 형식으로 리턴
 */
function getCurrentTime(date) {
	return toTimeString(new Date(date));
}

/**
 * 자바스크립트 Date 객체를 Time 스트링으로 변환
 * parameter date: JavaScript Date Object
 */
function toTimeString(date) {
	var year  = date.getFullYear();
	var month = date.getMonth() + 1; // 1월=0,12월=11이므로 1 더함
	var day   = date.getDate();

	if (("" + month).length == 1) {month = "0" + month;}
	if (("" + day).length   == 1) {day   = "0" + day;}

	return ("" + year + month + day)
}

/**
 * 현재 年을 YYYY형식으로 리턴
 */
function getYear(date) {
	return getCurrentTime(date).substr(0,4);
}

/**
 * 현재 月을 MM형식으로 리턴
 */
function getMonth(date) {
	return getCurrentTime(date).substr(4,2);
}

/**
 * 현재 日을 DD형식으로 리턴
 */
function getDay(date) {
	return getCurrentTime(date).substr(6,2);
}

function getDate(day) {
	var d = new Date();
	var dt = d - day*24*60*60*1000;
	return getYear(dt) + '-' + getMonth(dt) + '-' + getDay(dt);
}


/**
 * 신규생성 다이얼로그 창을 띄운다.
 * <pre>
 * 1. createElementContainer 함수를 이용하여 매번 div 태그를 입력하지 않고 다이얼로그 생성시 자동으로 생성한다.
 * 2. refreshTable 함수를 이용하여 다이얼로그 내용 부분을 불러온다.
 * </pre>
 * @param string url 폼화면 주소
 * @param int width 가로 사이즈
 * @param int height 세로 사이즈
 * @param string title 제목
 * @param string btn_yn 'false'이면 닫기버튼만 나타낸다.
 */
function boardaddFormDialog(url, width, height, title, btn_yn) {
	createElementContainer(title);
	refreshTable(url);
 
	$('#dlg').dialog({
		bgiframe: true,
		autoOpen: false,
		width: width,
		//height: height,
		resizable: false,
		draggable: true,
		modal: true,
		overlay: {
			backgroundColor: '#000000',
			opacity: 0.8
		},
		position: {my:'center',at:'top',of:window}
	}).dialog('open');
	return false;
}

function createElementContainer(title) {
	var dlg_title = title ? title : '등록 폼';
	var el = '<div id="dlg" title="' + dlg_title + '"   ><div id="dlg_content" ></div></div>';
	$('#dlg').remove();
	$(el).appendTo('body');
}

function refreshTable(url) {
	$.get(url, {}, function(data, textStatus) {
		$('#dlg_content').html(data);
	});
}


function BoardchkByte(str){
	var cnt = 0;
	for(i=0;i<str.length;i++) {
		cnt += str.charCodeAt(i) > 128 ? 2 : 1;
		if(str.charCodeAt(i)==10) cnt++;
	}
	return cnt;
}

function set_goods_list(displayId,inputGoods) {
	var displayIdNew = displayId+parseInt(new Date().getTime() / 1000, 10);
	$(displayIdNew).dialog("close");
	$("<div id='"+displayIdNew+"'></div>").appendTo('body'); 
	$.ajax({
		type: "get",
		url: "../goods/select",
		data: "page=1&goods_review=1&isadmin=1&inputGoods="+inputGoods+"&displayId="+displayIdNew,
		success: function(result){
			$("div#"+displayIdNew).html(result);
		}
	});
	openDialog("상품 검색", displayIdNew, {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
}


function goodslistclose(displayId, goods_seq) {
	$("div#"+displayId).dialog('close');
}
