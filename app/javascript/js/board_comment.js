if(!$.isFunction("getAuthLogin")){
    function getAuthLogin(){
		openDialogAlert('접근권한이 없습니다.!','400','150','');
	}
}

if(!$.isFunction("getLogin")){
    function getLogin(){
		openDialogAlert('이용하시려면 로그인이 필요합니다!','400','155','');
	}
}
if(!$.isFunction("getMbLogin")){
	function getMbLogin(){
		openDialogAlert('이용하시려면 로그인이 필요합니다!','400','155','');
	}
}

$(document).ready(function() { 

	$(".viewerlay_close_btn").live("click",function(){
		var board_seq = $(this).attr('board_seq');
		$("#viewer"+board_seq).hide();
		$("#viewer"+board_seq).html('');
		$("#tdviewer"+board_seq).hide();
	});

	
	// 비밀글 비밀번호입력 > 게시글 보기
	$('.boad_cmt_view_btn_no').live('click', function() {
		$('#BoardPwcheckForm')[0].reset();//초기화
		var seq = $(this).attr('board_seq'); 
		var viewlink = $(this).attr('viewlink');
		$("#pwck_seq").val(seq);
		$("#pwck_returnurl").val(viewlink);
		openDialog("비밀글  <span class='desc'>비밀번호를 입력해 주세요.</span>", "BoardPwCk", {"width":"370","height":"230"});
	});
	
	//비밀글 > 비번체크
	$("#BoardCmtPwcheckForm").validate({
		submitHandler: function(form) {
			var seq = $("#pwck_seq").val();
			var pw = $("#pwck_pw").val();
			var returnurl = $("#pwck_returnurl").val();
			if(!pw || pw == $("#pwck_pw").attr('title') ){
				alert('비밀번호를 입력해 주세요.');
				setDefaultText();
				$("#pwck_pw").focus();
				return false;
			}else{
				$.ajax({
					'url' : '../board_process',
					'data' : {'mode':'board_hidden_cmt_pwcheck', 'seq':seq, 'pw':pw, 'board_id':board_id},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res) {
						if(res.result == true) {
							if(res.msg){
								openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
							}else{
								document.location.href=returnurl;
							}
						}else{
							if(res.msg){
								openDialogAlert(res.msg,'400','150',function(){});
							}else{
								openDialogAlert('잘못된 접근입니다.','400','150',function(){});
							}
						}
					}
				});
			}//endif
			return false;
		}
	});

	if($.cookie("cmtlistlay")) {$("#cmtlistlay").show();}

	$("#commentlayshow").click( function() {
		var comment = $("#comment_num").text();  
		var comment_arrow = $("#comment_arrow").attr("src");// "▲";
		$.cookie( "cmtlistlay", '1' );
		if ( comment > 0 ) {
			$("#cmtlistlay").toggle();
		} 

		if($("#cmtlistlay").css("display") == "none" ){
			comment_arrow = comment_arrow.replace("cmt_btn_open","cmt_btn_close");//"▼";//cmt_btn_close.gif
		}else{
			comment_arrow = comment_arrow.replace("cmt_btn_close","cmt_btn_open");//"▲";//cmt_btn_open.gifelse{
		}
		$("#comment_arrow").attr("src", comment_arrow);//.text(comment_arrow);

		if ( commentlay != 'N' && isperm_write != "_no" ) {
			$('#cmtform1')[0].reset();//초기화
		}
		setDefaultText();
	});

	//댓글 작성권한없음
	$("#cwrite_no").live("click",function() {
		getboardLogin();
	});

	//댓글등록및 수정
	$('#cmtform1').validate({
		onkeyup: false,
		rules: {
			name: { required:true},
			content: { required:true}
		},
		messages: {
			name: { required:''}, 
			captcha_code: { required:''},
			pw: { required:''},
			content: { required:''}
		},
		errorPlacement: function(error, element) {
				setDefaultText();
			error.appendTo(element.parent());
		},
		submitHandler: function(f) {

			if(!$("#cmtname").val() || $("#cmtname").val() == $("#cmtname").attr('title') ) {
				setDefaultText();
				alert('이름을 입력해 주세요.');
				$("#cmtname").focus();
				return false;
			} 
			if(!$("#cmtcontent").val() || $("#cmtcontent").val() == "<p>&nbsp;</p>"  || $("#cmtcontent").val() == $("#cmtcontent").attr('title') ){
				setDefaultText();
				alert('내용을 입력해 주세요.');
				$("#cmtcontent").focus();
				return false;
			}
			f.submit();
		}
	});


	//비회원 댓글 수정
	$("[name=boad_cmt_modify_btn_no]").live("click", function() {
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");

		$("#cmtmodetype_new").val('modify');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		openDialog("댓글 수정 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"}); 
	});

	//회원 댓글 수정
	$("[name=boad_cmt_modify_btn]").live("click", function() {
		var cmtseq = $(this).attr("board_cmt_seq");
		getModifyCmt(cmtseq);
	});

	//댓글 수정 : 회원글인 경우 로그인
	$("[name=boad_cmt_modify_btn_mbno], [name=boad_cmt_modify_btn_hidden_mbno]").live("click", function() {
		getcmtMbLogin();
	});

	// 댓글 삭제
	$("[name=boad_cmt_delete_btn]").live("click",function(){
		var cmtseq = $(this).attr("board_cmt_seq");
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();
		if(confirm("정말로 댓글을 삭제하시겠습니까? ")) {
			if(gl_isuser) {
				$.ajax({
					'url' : '../board_comment_process',
					'data' : {'mode':'board_comment_delete', 'delcmtseq':cmtseq, 'seq':seq, 'board_id':board_id},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res){
						if(res) {
							if(res.result == true){
								if(res.callback){
									openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
								}else{
									openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
								}
							}else{
								openDialogAlert(res.msg,'400','150',function(){});
							}
						}else{
							openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
						}
					}
				});
			}else{
				$.ajax({
					'url' : '../board_comment_process',
					'data' : {'mode':'board_comment_delete_pwcheck', 'delcmtseq':cmtseq, 'seq':seq,  'board_id':board_id, 'returnurl':returnurl, 'view':'comment_delete'},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res){
						if(res) {
							if(res.result == true){
								if(res.callback){
									openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
								}else{
									openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
								}
							}else{
								openDialogAlert(res.msg,'400','150',function(){});
							}
						}else{
							openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
						}
					}
				}); 
			}
		}
	});

	//답글삭제
	$("[name=boad_cmt_delete_reply_btn]").live("click",function(){
		var cmtseq = $(this).attr("board_cmt_reply_seq");//$(this).attr("board_cmt_seq");
		var seq = $('#board_seq').val();
		var returnurl = $('#cmtreturnurl').val();
		if(confirm("정말로 답글을 삭제하시겠습니까? ")) {
			if(gl_isuser) {
				$.ajax({
					'url' : '../board_comment_process',
					'data' : {'mode':'board_comment_delete', 'delcmtseq':cmtseq, 'seq':seq, 'board_id':board_id},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res){
						if(res) {
							if(res.result == true){
								if(res.callback){
									openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
								}else{
									openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
								}
							}else{
								openDialogAlert(res.msg,'400','150',function(){});
							}
						}else{
							openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
						}
					}
				});
			}else{
				$.ajax({
					'url' : '../board_comment_process',
					'data' : {'mode':'board_comment_delete_pwcheck', 'delcmtseq':cmtseq, 'seq':seq,  'board_id':board_id, 'returnurl':returnurl, 'view':'comment_delete'},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res){
						if(res) {
							if(res.result == true){
								if(res.callback){
									openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
								}else{
									openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
								}
							}else{
								openDialogAlert(res.msg,'400','150',function(){});
							}
						}else{
							openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
						}
					}
				}); 
			}
		}
	});

	//댓글취소
	$("#board_comment_cancel").live("click",function(){
		$('#cmtseq').val('');
		if(!gl_isuser){
			$('#cmtname').val('');
			$('#cmtpw').val('');
		}
		$('#cmtcontent').val('');
		$('#cmtmode').val('board_comment_write');
		setDefaultText();
	});

	//댓글 > 답글 등록폼 작성권한없음
	$("[name=boad_cmt_reply_btn_no]").live("click", function() {
		getboardLogin();
	});

	//댓글 > 답글 등록가능함
	$("[name=boad_cmt_reply_btn]").live("click", function() {
		var idx = $(this).attr("board_cmt_idx");
		var cmtseq = $(this).attr("board_cmt_seq");
		//if($('#cmtname'+cmtseq).val() != $('#cmtname'+cmtseq).attr('title')) $('#cmtname'+cmtseq).val($('#cmtname'+cmtseq).attr('title'));
		//if($('#cmtpw'+cmtseq).val() != $('#cmtpw'+cmtseq).attr('title')) $('#cmtpw'+cmtseq).val('');
		//if($('#cmtsubject'+cmtseq).val() != $('#cmtsubject'+cmtseq).attr('title')) $('#cmtsubject'+cmtseq).val($('#cmtsubject'+cmtseq).attr('title'));
		//if($('#cmtcontent'+cmtseq).val() != $('#cmtcontent'+cmtseq).attr('title')) $('#cmtcontent'+cmtseq).val($('#cmtcontent'+cmtseq).attr('title'));
		if($('#board_commentsend_reply'+cmtseq).attr('board_cmt_reply_seq')){
			$("tr.cmtreplyform"+idx).show();
		}else{
			$("tr.cmtreplyform"+idx).toggle();
		}
		$("tr.cmtreplyform"+idx).find('.captcha_code').val('');
		$('#board_commentsend_reply'+cmtseq).text('답글등록');
		$('#board_commentsend_reply'+cmtseq).attr('board_cmt_reply_seq','');
		setDefaultText();
	});

	//회원/비회원 댓글 > 답글 수정
	$("[name=boad_cmt_modify_reply_btn]").live("click", function() {
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var cmtreplyidx = $(this).attr("board_cmt_idx");
		getModifyReplyCmt(cmtseq, cmtreplyseq, cmtreplyidx);
	});
	
	
	//비회원 > 답글 수정 비밀번호 입력창
	$("[name=boad_cmt_modify_reply_btn_no]").live("click",function(){		
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var cmtidx = $(this).attr("board_cmt_idx");
		$("#cmtmodetype_new").val('reply_modify');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);//답글의 부모고유번호
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);//답글본래고유번호
		$("#cmt_pwck_cmtreplyidx_new").val(cmtidx);
		openDialog("비밀답글 수정  <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"}); 
	});

	//댓글 > 답글 등록/수정
	$("button[name=board_commentsend_reply]").live("click", function() {
		var idx					= $(this).attr("board_cmt_idx");
		var cmtseq			= $(this).attr("board_cmt_seq");//parent seq
		var cmtreplyseq	= $(this).attr("board_cmt_reply_seq");//reply seq
		var isperm_moddel	= $(this).attr("isperm_moddel");
		var board_id		= $('#board_id').val();
		var seq					= $('#board_seq').val();
		var returnurl			= $('#cmtreturnurl').val();
		var cmtpage			= $('#cmtpage').val();
		returnurl += "&cmtpage="+cmtpage;

		var cmtcontent = $("#cmtcontent"+cmtseq).val();
		var cmthidden = ($("#cmthidden"+cmtseq).attr("checked"))?1:0; 

		var user_name = $("#cmtname"+cmtseq).val();
		var password = $("#cmtpw"+cmtseq).val();
		var captcha_code = $("tr.cmtreplyform"+idx).find('.captcha_code').val();

		if(!gl_isuser){
			if(!user_name || user_name == $("#cmtname"+cmtseq).attr('title') ) {
				setDefaultText();
				alert('이름을 입력해 주세요.');
				$("#cmtname"+cmtseq).focus();
				return false;
			}

			if( !isperm_moddel ) {
				if(!password || password == $("#cmtpw"+cmtseq).attr('title') ) {
				setDefaultText();
					alert('비밀번호를 입력해 주세요.');
					$("#pw"+cmtseq).focus();
					return false;
				}
			}
		}

		if(!cmtcontent || cmtcontent == $("#cmtcontent"+cmtseq).attr('title') ) {
				setDefaultText();
			alert('답글을 입력해 주세요.');
			$("#cmtcontent"+cmtseq).focus();
			return false;
		}

		if(cmtreplyseq) {//답글수정시
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_reply_modify_pwcheck', 'cmtseq':cmtreplyseq, 'cmtreplyseq':cmtreplyseq, 'seq':seq, 'board_id':board_id, 'name':user_name, 'pw':password, 'content':cmtcontent, 'captcha_code':captcha_code, 'returnurl':returnurl, 'hidden':cmthidden},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							if(res.callback){
								openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
							}else{
								openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
							}
						}else{
							openDialogAlert(res.msg,'400','150',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
					}
				}
			});
		}else{//답글등록시
			$.ajax({
				'url' : '../board_comment_process',
				'data' : {'mode':'board_comment_reply', 'cmtseq':cmtseq, 'seq':seq, 'board_id':board_id, 'name':user_name, 'pw':password, 'content':cmtcontent, 'captcha_code':captcha_code, 'returnurl':returnurl, 'hidden':cmthidden},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					if(res) {
						if(res.result == true){
							if(res.callback){
								openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
							}else{
								openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
							}
						}else{
							openDialogAlert(res.msg,'400','150',function(){});
						}
					}else{
						openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
					}
				}
			});
		}
	});

	//답글취소
	$("button[name=board_comment_reply_cancel]").live("click",function(){
		var cmtseq = $(this).attr("board_cmt_seq");
		$("#cmtname"+cmtseq).val('');
		$("#cmtpw"+cmtseq).val('');
		$("#cmtcontent"+cmtseq).val('');
		$('#board_comment_reply_cancel'+cmtseq).attr('board_cmt_reply_seq','');
		$(".cmtreplylay").hide();//답글폼숨김
		setDefaultText();
	});


	//비회원 > 댓글, 답글 비밀번호입력창
	$("img[name=boad_cmt_delete_btn_no]").live("click",function(){
		$('#CmtBoardPwcheckForm')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		$("#cmt_pwck_seq").val(seq);
		$("#cmt_pwck_cmtseq").val(cmtseq);
		openDialog("댓글삭제 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCk", {"width":"370","height":"230"});
	});

	//댓글 > 덧글 : 회원글 로그인
	$("[name=boad_cmt_modify_reply_btn_mbno], [name=boad_cmt_modify_reply_btn_hidden_mbno], [name=boad_cmt_delete_btn_mbno], [name=boad_cmt_delete_btn_hidden_mbno], [name=boad_cmt_delete_reply_btn_hidden_mbno], [name=boad_cmt_delete_reply_btn_mbno], span.boad_cmt_content_hidden_mbno").live("click",function(){
		getcmtMbLogin();
	});

	//비회원 비밀댓글 수정
	$("[name=boad_cmt_modify_btn_hidden_no]").live("click", function() {
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");

		$("#cmtmodetype_new").val('modify');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		openDialog("비밀댓글 수정 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"}); 
	}); 

	//비회원 비밀답글 수정
	$("[name=boad_cmt_modify_reply_btn_hidden_no]").live("click", function() {
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var cmtidx = $(this).attr("board_cmt_idx");

		$("#cmtmodetype_new").val('reply_modify');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);//답글의 부모고유번호
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);//답글본래고유번호
		$("#cmt_pwck_cmtreplyidx_new").val(cmtidx);
		openDialog("비밀답글 수정  <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"}); 
	}); 

	//비회원 > 비밀댓글 비밀번호입력창
	$("[name=boad_cmt_delete_btn_hidden_no]").live("click",function(){
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");

		$("#cmtmodetype_new").val('delete');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		openDialog("비밀댓글 삭제 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"});
	});

	//비회원 > 답글 비밀번호입력창
	$("[name=boad_cmt_delete_reply_btn_no]").live("click",function(){
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");

		$("#cmtmodetype_new").val('reply_delete');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);
		openDialog("답글 삭제 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"});
	}); 

	//비회원 > 비밀답글 비밀번호입력창
	$("[name=boad_cmt_delete_reply_btn_hidden_no]").live("click",function(){
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");

		$("#cmtmodetype_new").val('reply_delete');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);
		openDialog("비밀답글 삭제 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"});
	}); 


	//비회원 > 비밀댓글 비밀번호입력창
	$("span.boad_cmt_content_hidden_no").live("click",function(){
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");

		$("#cmtmodetype_new").val('view');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);
		openDialog("비밀댓글 보기 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"});
	});

	//비회원 > 비밀답글 비밀번호입력창
	$("span.boad_cmt_reply_content_hidden_no, span.boad_cmt_reply_content_hidden_mbno").live("click",function(){
		$('#CmtBoardPwcheckFormNew')[0].reset();//초기화
		var seq = $('#board_seq').val();
		var cmtseq = $(this).attr("board_cmt_seq");
		var cmtreplyseq = $(this).attr("board_cmt_reply_seq");
		var cmtidx = $(this).attr("board_cmt_idx");
		
		$("#cmtmodetype_new").val('reply_view');
		$("#cmt_pwck_seq_new").val(seq);
		$("#cmt_pwck_cmtseq_new").val(cmtseq);
		$("#cmt_pwck_cmtreplyseq_new").val(cmtreplyseq);
		$("#cmt_pwck_cmtreplyidx_new").val(cmtidx);
		openDialog("비밀답글 보기 <span class='desc'>비밀번호를 입력해 주세요.</span>", "CmtBoardPwCkNew", {"width":"370","height":"230"});
	});

	//비회원 > 비밀댓글 비밀번호입력창
	$("#CmtBoardPwcheckBtnNew").live("click",function(){ 
		var modetype = $(this).parents("form").find("#cmtmodetype_new").val();
		var seq = $(this).parents("form").find("#cmt_pwck_seq_new").val();
		var cmtseq = $(this).parents("form").find("#cmt_pwck_cmtseq_new").val();//본래글
		var cmtreplyseq = $(this).parents("form").find("#cmt_pwck_cmtreplyseq_new").val();//부모글
		var cmtreplyidx = $(this).parents("form").find("#cmt_pwck_cmtreplyidx_new").val();
		var pw = $(this).parents("form").find("#cmt_pwck_pw_new").val();  
		var returnurl = $('#cmtreturnurl').val();
		if(!pw || pw == $(this).parents("form").find("#cmt_pwck_pw_new").attr('title') ) {
				setDefaultText();
			alert('비밀번호를 입력해 주세요.');
			$(this).parents("form").find("#cmt_pwck_pw_new").focus();
			return false;
		}else{
			cmtboardcheckform(modetype, seq, cmtseq, cmtreplyseq, pw, board_id, cmtreplyidx, returnurl);
		}//endif 
	});

	//비밀글추가 관련 댓글
	$("#CmtBoardPwcheckFormNewold").validate({
		submitHandler: function(form) {
			var modetype = $("#cmtmodetype_new").val();
			var seq = $("#cmt_pwck_seq_new").val();
			var cmtseq = $("#cmt_pwck_cmtseq_new").val();//본래글
			var cmtreplyseq = $("#cmt_pwck_cmtreplyseq_new").val();//부모글
			var cmtreplyidx = $("#cmt_pwck_cmtreplyidx_new").val();
			var pw = $("#cmt_pwck_pw_new").val();
			if(!pw || pw == $("#cmt_pwck_pw_new").attr('title') ){
				setDefaultText();
				alert('비밀번호를 입력해 주세요.');
				$("#cmt_pwck_pw_new").focus();
				return false;
			}else{
				cmtboardcheckform(modetype, seq, cmtseq, cmtreplyseq, pw, board_id, cmtreplyidx, returnurl);
			}//endif
		}
	});
		
	//기존 비회원 > 댓글, 답글 삭제
	$("#CmtBoardPwcheckForm").validate({
		submitHandler: function(form) {
			var seq = $("#cmt_pwck_seq").val();
			var cmtseq = $("#cmt_pwck_cmtseq").val();
			var pw = $("#cmt_pwck_pw").val();
			if(!pw || pw == $("#cmt_pwck_pw").attr('title') ){
				setDefaultText();
				alert('비밀번호를 입력해 주세요.');
				$("#cmt_pwck_pw").focus();
				return false;
			}else{
				var returnurl = $('#cmtreturnurl').val();
				$.ajax({
					'url' : '../board_comment_process',
					'data' : {'mode':'board_comment_delete_pwcheck', 'delcmtseq':cmtseq, 'seq':seq,  'pw':pw, 'board_id':board_id, 'returnurl':returnurl},
					'type' : 'post',
					'dataType': 'json',
					'success' : function(res){
						if(res) {
							if(res.result == true){
								if(res.callback){
									openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
								}else{
									openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
								}
							}else{
								openDialogAlert(res.msg,'400','150',function(){});
							}
						}else{
							openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
						}
					}
				});
			}//endif
		}
	});

}); 

function getboardLogin(){
	if(gl_isuser){
		openDialogAlert('해당 서비스를 이용하시려면 관리자에게 문의하여 주시길 바랍니다.','450','140');
	}else{
		openDialogConfirm('이용하시려면 로그인이 필요합니다!<br/>로그인하시겠습니까?','400','150',function(){location.href="/member/login?return_url="+return_url;},function(){});
	}
}

function getcmtMbLogin(){
	if(gl_isuser){
		openDialogAlert('글작성자만 이용가능합니다.','400','140');
	}else{
		openDialogConfirm('이용하시려면 로그인이 필요합니다!<br/>로그인하시겠습니까?','400','150',function(){location.href="/member/login?return_url="+return_url;},function(){});
	}
}

function cmtboardcheckform(modetype, seq, cmtseq, cmtreplyseq, pw, board_id, cmtreplyidx, returnurl) { 
	if( modetype == 'reply_modify' ) {//답글수정
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_hidden_reply_cmt_pwcheck', 'seq':seq, 'cmtseq':cmtseq, 'cmtreplyseq':cmtreplyseq, 'pw':pw, 'board_id':board_id,  'view':'comment_modify'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res) { 
				if(res.result == true) { 
					$('#CmtBoardPwCkNew').dialog('close');
					getModifyReplyCmt(cmtseq, cmtreplyseq, cmtreplyidx);//폼수정 , res.cmtpw 
				}else{
					if(res.msg){
						openDialogAlert(res.msg,'400','150',function(){});
					}else{
						openDialogAlert('잘못된 접근입니다.','400','150',function(){});
					}
				}
			}
		});
	}else if( modetype == 'reply_view' ) {//보기
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_hidden_reply_cmt_pwcheck', 'seq':seq, 'cmtseq':cmtseq, 'cmtreplyseq':cmtreplyseq,  'pw':pw, 'board_id':board_id,  'view':'comment_view'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res) { 
				if(res.result == true) {
					$('#CmtBoardPwCkNew').dialog('close'); 
					$(".boad_cmt_reply_content_"+cmtreplyseq).text(res.content);
					$(".boad_cmt_reply_content_"+cmtreplyseq).removeClass("gray");
				}else{
					if(res.msg){
						openDialogAlert(res.msg,'400','150',function(){});
					}else{
						openDialogAlert('잘못된 접근입니다.','400','150',function(){});
					}
				}
			}
		});
	}else if( modetype == 'reply_delete' ){//답글삭제시
		var returnurl = $('#cmtreturnurl').val();
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_comment_reply_delete_pwcheck', 'delcmtseq':cmtreplyseq, 'cmtseq':cmtseq, 'seq':seq,  'pw':pw, 'board_id':board_id, 'returnurl':returnurl, 'view':'comment_delete'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res){
				if(res) {
					if(res.result == true){
						$('#CmtBoardPwCkNew').dialog('close');
						if(res.callback){
							openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
						}else{
							openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
						}
					}else{
						openDialogAlert(res.msg,'400','150',function(){});
					}
				}else{
					openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
				}
			}
		});
	}else if( modetype == 'delete' ){//댓글삭제시
		var returnurl = $('#cmtreturnurl').val();
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_comment_delete_pwcheck', 'delcmtseq':cmtseq, 'seq':seq,  'pw':pw, 'board_id':board_id, 'returnurl':returnurl, 'view':'comment_delete'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res){
				if(res) {
					if(res.result == true){
						$('#CmtBoardPwCkNew').dialog('close');
						if(res.callback){
							openDialogAlert(res.msg,'400','150',function(){boardviewtypeshow(returnurl,seq,'layer');});
						}else{
							openDialogAlert(res.msg,'400','150',function(){document.location.href=returnurl;});
						}
					}else{
						openDialogAlert(res.msg,'400','150',function(){});
					}
				}else{
					openDialogAlert("잘못된 접근입니다.",'400','150',function(){});
				}
			}
		}); 
	}else if( modetype == 'modify' ){//댓글수정시
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_hidden_cmt_pwcheck', 'seq':seq,  'cmtseq':cmtseq, 'pw':pw, 'board_id':board_id, 'view':'comment_modify'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res) { 
				if(res.result == true) {
					$('#CmtBoardPwCkNew').dialog('close');
					getModifyCmt(cmtseq );
				}else{
					if(res.msg){
						openDialogAlert(res.msg,'400','150',function(){});
					}else{
						openDialogAlert('잘못된 접근입니다.','400','150',function(){});
					}
				}
			}
		});
	
	}else if( modetype == 'view' ){//보기
		$.ajax({
			'url' : '../board_comment_process',
			'data' : {'mode':'board_hidden_cmt_pwcheck', 'seq':seq,  'cmtseq':cmtseq, 'pw':pw, 'board_id':board_id, 'view':'comment_view'},
			'type' : 'post',
			'dataType': 'json',
			'success' : function(res) { 
				if(res.result == true) {
					$('#CmtBoardPwCkNew').dialog('close'); 
					$(".boad_cmt_content_"+res.cmtseq).text(res.content);
					$(".boad_cmt_content_"+res.cmtseq).removeClass("gray");
				}else{
					if(res.msg){
						openDialogAlert(res.msg,'400','150',function(){});
					}else{
						openDialogAlert('잘못된 접근입니다.','400','150',function(){});
					}
				}
			}
		});
	}
}

//비밀댓글 수정폼보여주기
function getModifyCmt(cmtseq ){ 
	var board_id = $('#board_id').val();
	var seq = $('#board_seq').val();
	var returnurl = $('#cmtreturnurl').val(); 
	$.ajax({
		'url' : '../board_comment_process',
		'data' : {'mode':'board_comment_item', 'cmtseq':cmtseq, 'seq':seq, 'board_id':board_id},
		'type' : 'post',
		'dataType': 'json',
		'success': function(data) {
			//alert(data.isperm_display);
			if(data.isperm_display != 1){//삭제되지않은 글 
				$('#board_commentsend').attr('isperm_moddel',false);
				$('#cwrite').show();
				if(gl_isuser){
					$('#cmtmode').val('board_comment_modify');
					if( data.mseq > 0 ) {
						$('#cwrite').find('.pwchecklay').hide();
					}else{
						if( data.isperm_moddel ) {//비회원 권한있으면
							$('#cwrite').find('.pwchecklay').hide();
						}else{
							$('#cwrite').find('.pwchecklay').show();
						}
					}
				}else{
					$('#cmtmode').val('board_comment_modify_pwcheck');
					if( data.mseq > 0 ) {
						getcmtMbLogin();
					}else{
						if( data.isperm_moddel ) {//비회원 권한있으면
							$('#cwrite').find('.pwchecklay').hide();
						}else{
							$('#cwrite').find('.pwchecklay').show();
						}
					}
				}
				
				$('#board_commentsend').attr('isperm_moddel',data.isperm_moddel);
				$('#cmtname').val(data.name);
				$('#cmtsubject').val(data.subject);
				$('#cmtcontent').val(data.content); 
				$('#cmtseq').val(data.seq);
				if(data.hidden == 1 ) {
					$('#cmthidden').attr("checked",true);
				}else{
					$('#cmthidden').attr("checked",false);
				}
				$('#board_commentsend').text('댓글수정');
				document.location.href="#cwriteform";
				$('.cmtreplylay').hide();
				setDefaultText();
			}
		}
	});
}

//비밀답글 수정폼보여주기
function getModifyReplyCmt(cmtseq, cmtreplyseq, idx ){  
	var board_id = $('#board_id').val();
	var seq = $('#board_seq').val();
	var returnurl = $('#cmtreturnurl').val();
	$(".cmtreplyform"+idx).find('.captcha_code').val('');

	$.ajax({
		'url' : '../board_comment_process',
		'data' : {'mode':'board_comment_reply_item', 'cmtseq':cmtseq, 'cmtreplyseq':cmtreplyseq, 'seq':seq, 'board_id':board_id},
		'type' : 'post',
		'dataType': 'json',
		'success': function(data) { 
			//alert(data.isperm_display);
			if(data.isperm_display != 1){//삭제되지않은 글 
				$('#board_commentsend_reply'+cmtseq).attr('isperm_moddel',false);
				$(".cmtreplyform"+idx).show();
				if(gl_isuser){
					if( data.mseq > 0 ) {
						$(".cmtreplyform"+idx).find('.pwchecklay').hide();
					}else{
						if( data.isperm_moddel ) {//비회원 권한있으면
							$(".cmtreplyform"+idx).find('.pwchecklay').hide();
						}else{
							$(".cmtreplyform"+idx).find('.pwchecklay').show();
						}
					}
				}else{
					if( data.mseq > 0 ) {
						getcmtMbLogin();
					}else{ 
						if( data.isperm_moddel ) {//비회원 권한있으면
							$(".cmtreplyform"+idx).find('.pwchecklay').hide();  
						}else{
							$(".cmtreplyform"+idx).find('.pwchecklay').show();
						}
					}
				}
				$('#board_commentsend_reply'+cmtseq).attr('isperm_moddel',data.isperm_moddel);
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
				setDefaultText();
			}
		}
	});
}