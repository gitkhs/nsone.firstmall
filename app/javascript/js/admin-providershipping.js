$(document).ready(function() {	
	
	$(".modifyDeliveryButton").live("click",function(){
		var provider_seq = '';
		if( $("input[name='shipping_provider_seq']").val() ){
			provider_seq = $("input[name='shipping_provider_seq']").val();
		}
		
		if(provider_seq == ''){
			alert('입점사를 등록 후 설정 가능합니다!');
			return false;
		}
		var url = '../setting/shipping_modify?code='+$(this).attr('name')+'&provider_seq='+provider_seq;		
		$.get(url, function(data) {			
		  	$('#shippingModifyPopup').html(data);		  
		});
		
		if( $(this).attr('name') == 'delivery'){
			openDialog($(this).attr('title')+" 설정", "shippingModifyPopup", {"width":"1000","height":700});
		}else{
			openDialog($(this).attr('title')+" 설정", "shippingModifyPopup", {"width":"1000","height":350});
		}
	});	  	

	$("#addInternationalShipping").live("click",function(){
		
		$.get('../setting/international_shipping?code=regist', function(data) {						
		  	$('#internationalShippingPopup').html(data);		  
		});
		openDialog("해외 배송 추가", "internationalShippingPopup", {"width":"95%","height":500});
		setDefaultText();
	});

	$(".modifyInternational").bind("click",function(){		
		var code = $(this).attr("name");
		$.get('../setting/international_shipping?code='+code, function(data) {			
		  	$('#internationalShippingPopup').html(data);		  
		});
		openDialog("해외 배송 수정", "internationalShippingPopup", {"width":"95%","height":500});
		setDefaultText();
	});
	
	 // 우편번호 검색
    $("#senddingZipcodeButton").live("click",function(){
        openDialogZipcode('sendding');
    });
 	// 우편번호 검색
    $("#returnZipcodeButton").live("click",function(){
        openDialogZipcode('return');
    });
 	
 	 
    $("#addDeliveryCompany").live("click",function(e){
	    var obj = $("select[name='deliveryCompany'] option:selected");
	    var targetObj = $(this).parent().parent().parent();
	    var result = true;       
	    targetObj.find("li").each(function(){            
	        var clone = $(this).clone();
		if( clone.find("span").html() == obj.html()){
			result = false;
		}
	    });        
	    if(obj.val() != "" && result ){
	    	var tag = "<li code='"+obj.val()+"'><input type='hidden' name='deliveryCompanyCode[]' value='"+obj.val()+"'><span style='display:inline-block; width:225px;'>"+obj.html()+"</span><span class=\"btn small gray\"><button type=\"button\" class=\"removeDeliveryCompany\">-</button></span></li>";
	    }
	    targetObj.find("ul").append(tag);
	    e.preventDefault();
		return false;		
	});
	
	$(".removeDeliveryCompany").live("click",function(e){
	    $(this).parent().parent().remove();
	    e.preventDefault();
		return false;
	});
		
	$("#addDeliveryCost").live("click",function(e){		
		var trObj = $("table#addDeliveryCostTable tbody tr").eq(0).clone();
		trObj.find(".sigungu").html('');		
		trObj.find("input").val('');		
		$("table#addDeliveryCostTable tbody").append(trObj);
		e.preventDefault();
		return false;		
	});

	$(".delDeliveryCost").live("click",function(e){		
		var trObj = $(this).parent().parent().parent();		
		if(trObj.parent().find("tr").length > 1) trObj.remove();
		e.preventDefault();
		return false;
	});	

	$(".searchArea").live("click",function(e){
		var idx = $(this).parent().parent().parent().index();
		openDialogSido('sigungu',idx);
		e.preventDefault();
		return false;
	});
	
	$("button.delCategory").live("click",function(){
		$(this).parent().parent().remove();
	});
	
	$("button.delBrand").live("click",function(){
		$(this).parent().parent().remove();
	});
});

function get_provider_shipping(){
	var sel = $("select[name='deli_group'] option:selected").val();	
	var provider_seq = 1;
	if(sel == 'provider'){
		provider_seq = '';
		if( $("input[name='provider_seq']").length ){			
			provider_seq = $("input[name='provider_seq']").val();			
		}		
	}
	
	var url = '../setting/shipping?provider_seq='+provider_seq;	
	$.get(url, function(data) {
	  	$('#providershipping').html(data);		  
	});
	
}