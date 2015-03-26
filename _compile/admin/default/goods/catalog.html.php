<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/goods/catalog.html 000032229 */ 
$TPL_loop_1=empty($TPL_VAR["loop"])||!is_array($TPL_VAR["loop"])?0:count($TPL_VAR["loop"]);
$TPL_model_1=empty($TPL_VAR["model"])||!is_array($TPL_VAR["model"])?0:count($TPL_VAR["model"]);
$TPL_brand_1=empty($TPL_VAR["brand"])||!is_array($TPL_VAR["brand"])?0:count($TPL_VAR["brand"]);
$TPL_manufacture_1=empty($TPL_VAR["manufacture"])||!is_array($TPL_VAR["manufacture"])?0:count($TPL_VAR["manufacture"]);
$TPL_orign_1=empty($TPL_VAR["orign"])||!is_array($TPL_VAR["orign"])?0:count($TPL_VAR["orign"]);?>
<?php $this->print_("layout_header",$TPL_SCP,1);?>


<script type="text/javascript">
// SEARCH FOLDER
function showSearch(){
	if($("#goods_search_form").css('display')=='none'){
		$("#goods_search_form").show();
		$.cookie("goods_list_folder", "folded");
	}else{
		$("#goods_search_form").hide();
		$.cookie("goods_list_folder", "unfolded");
	}
}


$(document).ready(function() {
 

	$("#delete_btn").click(function(){
<?php if(!$TPL_VAR["auth"]){?>
		alert("권한이 없습니다.");
		return;
<?php }?>

		var cnt = $("input:checkbox[name='goods_seq[]']:checked").length;
		if(cnt<1){
			alert("삭제할 상품을 선택해 주세요.");
			return;
		}else{
			var queryString = $("#goodsForm").serialize();
			if(!confirm("선택한 상품을 삭제 시키겠습니까? ")) return;
			$.ajax({
				type: "get",
				url: "../goods_process/goods_delete",
				data: queryString,
				success: function(result){
					//alert(result);
					location.reload();
				}
			});
		}
	});

	$("#chkAll").click(function(){
		if($(this).attr("checked")){
			$(".chk").attr("checked",true).change();
		}else{
			$(".chk").attr("checked",false).change();
		}
	});

	$(".manager_copy_btn").click(function(){

<?php if(!$TPL_VAR["auth"]){?>
		alert("권한이 없습니다.");
		return;
<?php }?>

		if(!confirm("이 상품을 복사해서 상품을 등록하시겠습니까?")) return;

		$.ajax({
			type: "get",
			url: "../goods_process/goods_copy",
			data: "goods_seq="+$(this).attr("goods_seq"),
			success: function(result){
				//alert(result);
				location.reload();
			}
		});
	});

	// 체크박스 색상
	$("input[type='checkbox'][name='goods_seq[]']").live('change',function(){
		if($(this).is(':checked')){
			$(this).closest('tr').addClass('checked-tr-background');
		}else{
			$(this).closest('tr').removeClass('checked-tr-background');
		}
	}).change();


	$("button[name='down_list']").click(function(){
		//window.open("/admin/order/download_list","","");
		location.href = "/admin/goods/download_write";
	});


	$("button[name='excel_down']").click(function(){
		if( $("input[name='keyword']").val() == $("input[name='keyword']").attr("title") ){
			$("input[name='keyword']").focus();
		}

		if(!$("#excel_type").val()){
			alert("양식을 선택 해 주세요.");
			return;
		}
		if($("#excel_type").val()=='select'){
			var cnt = $("input:checkbox[name='goods_seq[]']:checked").length;
			if(cnt<1){
				alert("다운로드 할 상품을 선택해 주세요.");
				return;
			}
		}

		var queryString = $("#goodsForm").serializeArray();
		ajaxexceldown('/admin/goods_process/excel_down', queryString);
	});


	// export_upload
	$("button[name='upload_excel']").live("click",function(){
		openDialog("상품일괄등록/수정 <span class='desc'></span>", "export_upload", {"width":"800","height":"500","show" : "fade","hide" : "fade"});
	});

<?php if($TPL_VAR["service_code"]=='P_FREE'){?>
	$(".waterMarkImageSetting").bind("click",function(){
		openDialog('쇼핑몰 업그레이드 안내<span class=\'desc\'></span>', 'nofreeService', {'width':600,'height':200});
	});
<?php }else{?>
	$(".waterMarkImageSetting").bind("click",function(){
		$.ajax({
			type: "get",
			url: "../setting/watermark_setting?layerid=watermark_setting_popup",
			success: function(result){
				$("div#watermark_setting_popup").html(result);
			}
		});
		openDialog("워터마크 설정", "watermark_setting_popup", {"width":"700","height":"510","show" : "fade","hide" : "fade"});
	});
<?php }?>


	$(".goodsOptionBtn").bind("click",function(){
		var btnObj = $(this);
		var goodsOptionTableObj = $(this).closest("td").find(".goodsOptionTable");

		if(goodsOptionTableObj.html()==''){
			goodsOptionTableObj.load('get_goods_option',{'goods_seq':$(this).attr('goods_seq')});
		}

		$(".goodsOptionTable:visible").not($(this).closest("td").find(".goodsOptionTable")).closest("td").find(".goodsOptionBtn").click();
		$(this).closest("td").find(".goodsOptionTable").toggle();
		if($(this).html() == '닫기'){
			$(this).html('옵션');
		}else{
			$(this).html('닫기');
		}
		return false;
	});

	$('#order_star').toggle(function() {
	  $(this).addClass("checked");
	  $("span.icon-star-gray.checked").each(function(i){
		if(i>0){
			$(this).closest('tr').find("input[type='checkbox']").attr('checked',true);
		}
	  });

	}, function() {
	   $("span.icon-star-gray.checked").each(function(i){
		   if(i>0){
			$(this).closest('tr').find("input[type='checkbox']").attr('checked',false);
		   }
	   });
	   $(this).removeClass("checked");
	});

	$("#set_option_view").bind("click", function(){
		openDialog("옵션보기 설정", "set_option_view_lay", {'width':600,'height':270});
	});

	$(".btnSort").bind("click", function(){
		var sort = $("input[name='sort']").val();
		if($(this).attr("orderby") != "<?php echo $TPL_VAR["sorderby"]?>") sort = "";

		if(sort == "asc"){
			sort = "desc";
		}else if(sort == "desc" || sort == ""){
			sort = "asc";
		}
		var orderby = sort+"_"+$(this).attr("orderby");

		$(this).attr("sort",sort);
		$("select[name='orderby'] option[value='"+orderby+"']").attr("selected",true);
		$("input[name='keyword']").focus();
		$("form[name='goodsForm']").submit();
	});

});

//
function ajaxexceldown(url, queryString){
	var inputs = "";
	 jQuery.each(queryString, function(i, field){
		 inputs +='<input type="hidden" name="'+field.name+'" value="'+ field.value +'" />';
	 });
	jQuery('<form action="'+ url +'" method="post" target="actionFrame" >'+inputs+'</form>')
	.appendTo('body').submit().remove();
}

function goodsView(seq){
	$("input[name='keyword']").focus();
	$("input[name='no']").val(seq);
	var search = location.search;
	search = search.substring(1,search.length);
	$("input[name='query_string']").val(search);
	$("form[name='goodsForm']").attr('action','regist');
	$("form[name='goodsForm']").submit();
}

function openAdvancedStatistic(goods_seq){
	$.ajax({
		type: "get",
		url: "../statistic/advanced_statistics",
		data: "ispop=pop&goods_seq="+goods_seq,
		success: function(result){
			$(document).find('body').append('<div id="Advanced_Statistics"></div>');
			$("#Advanced_Statistics").html(result);
			openDialog("<span style='margin-left:410px;'>이 상품의 고급 통계</span>", "Advanced_Statistics", {"width":"1000","height":"700","show" : "fade","hide" : "fade"});
		}
	});
}

function searchformchange(){
	$("input[name='keyword']").focus();
	$("form[name='goodsForm']").submit();
}

// 옵션보기 설정 저장 완료처리
function optionViewSave(){
	loadingStop();
	closeDialog("set_option_view_lay");
}

// 빅데이터 미리보기 페이지 오픈
function openBigdataPreview(goods_seq){
	window.open('../bigdata/preview?no='+goods_seq);
}
</script>
<style>
.goodsOptionTable {display:none; position:absolute; border-collapse:collapse; top:-10px; left:60px; border:1px solid #f5f5f5;}
.goodsOptionTable table {width:220px;}
.goodsOptionTable th {padding:5px; border:1px solid #ddd; background-color:#f5f5f5}
.goodsOptionTable td {height:25px !important; border:1px solid #ddd; background-color:#ffffff; text-align:center;}
</style>

<!-- 페이지 타이틀 바 : 시작 -->
<div id="page-title-bar-area">
	<div id="page-title-bar">

		<!-- 타이틀 -->
		<div class="page-title">
			<h2><span class="icon-goods-kind-goods"></span>상품 리스트</h2>
		</div>

		<!-- 좌측 버튼 -->
		<ul class="page-buttons-left">
			<li><span class="btn large orange"><button type="button" id="set_option_view">옵션보기 설정</button></span></li>
		</ul>


		<!-- 우측 버튼 -->
		<ul class="page-buttons-right">
			<li><span class="btn large"><button name="upload_excel"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 상품일괄등록/수정</button></span></li>
			<li><span class="btn large black"><button onclick="location.href='regist';">상품등록하기<span class="arrowright"></span></button></span></li>
			<!--
			<li><span class="btn large black"><button type="submit">저장하기<span class="arrowright"></span></button></span></li>
			-->
		</ul>
<?php if($TPL_VAR["service_code"]!='P_FREE'){?>
		<ul class="page-buttons-right">
			<li>
				<span class="btn large orange"><button type="button" class="waterMarkImageSetting">워터마크 설정</button></span>
			</li>
		</ul>
<?php }?>
	</div>
</div>
<!-- 페이지 타이틀 바 : 끝 -->

<form name="goodsForm" id="goodsForm">
<input type="hidden" name="query_string"/>
<input type="hidden" name="no" />
<input type="hidden" name="sort" value="<?php echo $TPL_VAR["sort"]?>"/>

<!-- 상품 검색폼 : 시작 -->
<?php $this->print_("goods_search_form",$TPL_SCP,1);?>

<!-- 상품 검색폼 : 끝 -->
<div class="clearbox">
	<ul class="right-btns clearbox">
		<li><select class="custom-select-box-multi" name="orderby" onchange="searchformchange();">
			<option value="asc_goods_name" <?php if($TPL_VAR["orderby"]=='asc_goods_name'){?>selected<?php }?>>상품명순↑</option>
			<option value="desc_goods_name" <?php if($TPL_VAR["orderby"]=='desc_goods_name'){?>selected<?php }?>>상품명순↓</option>
			<option value="asc_consumer_price" <?php if($TPL_VAR["orderby"]=='asc_consumer_price'){?>selected<?php }?>>정가↑</option>
			<option value="desc_consumer_price" <?php if($TPL_VAR["orderby"]=='desc_consumer_price'){?>selected<?php }?>>정가↓</option>
			<option value="asc_price" <?php if($TPL_VAR["orderby"]=='asc_price'){?>selected<?php }?>>할인가↑</option>
			<option value="desc_price" <?php if($TPL_VAR["orderby"]=='desc_price'){?>selected<?php }?>>할인가↓</option>
			<option value="asc_tot_stock"  <?php if($TPL_VAR["orderby"]=='asc_tot_stock'){?>selected<?php }?>>재고↑</option>
			<option value="desc_tot_stock" <?php if($TPL_VAR["orderby"]=='desc_tot_stock'){?>selected<?php }?>>재고↓</option>
			<option value="asc_page_view" <?php if($TPL_VAR["orderby"]=='asc_page_view'){?>selected<?php }?>>페이지뷰순↑</option>
			<option value="desc_page_view" <?php if($TPL_VAR["orderby"]=='desc_page_view'){?>selected<?php }?>>페이지뷰순↓</option>
			<option value="asc_goods_seq" <?php if($TPL_VAR["orderby"]=='asc_goods_seq'){?>selected<?php }?>>등록일순↑</option>
			<option value="desc_goods_seq" <?php if($TPL_VAR["orderby"]=='desc_goods_seq'){?>selected<?php }?>>등록일순↓</option>
			<option value="asc_update_date" <?php if($TPL_VAR["orderby"]=='asc_update_date'){?>selected<?php }?>>수정일순↑</option>
			<option value="desc_update_date" <?php if($TPL_VAR["orderby"]=='desc_update_date'){?>selected<?php }?>>수정일순↓</option>
		</select></li>
		<li><select  class="custom-select-box-multi" name="perpage" onchange="searchformchange();">
			<option id="dp_qty10" value="10" <?php if($TPL_VAR["perpage"]== 10){?> selected<?php }?> >10개씩</option>
			<option id="dp_qty50" value="50" <?php if($TPL_VAR["perpage"]== 50){?> selected<?php }?> >50개씩</option>
			<option id="dp_qty100" value="100" <?php if($TPL_VAR["perpage"]== 100){?> selected<?php }?> >100개씩</option>
			<option id="dp_qty200" value="200" <?php if($TPL_VAR["perpage"]== 200){?> selected<?php }?> >200개씩</option>
		</select></li>
	</ul>
</div>
<ul class="left-btns clearbox">
	<li><div style="margin-top:rpx;">
<?php if($TPL_VAR["search_yn"]=='y'){?>
	검색 <b><?php echo number_format($TPL_VAR["page"]["totalcount"])?></b> 개
<?php }else{?>
	총 <b><?php echo number_format($TPL_VAR["page"]["totalcount"])?></b> 개</div>
<?php }?>
	</li>
</ul>

<div class="fr">
	<div class="clearbox">
		<ul class="right-btns clearbox">
			<li>
				<select class="custom-select-box-multi" name="excel_type" id="excel_type">
					<option value="">양식선택</option>
					<option value="select">선택 다운로드</option>
					<option value="search">검색 다운로드</option>
				</select>
				<span class="btn small"><button type="button" name="excel_down"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 다운로드</button></span>
				<span class="btn small"><button type="button" name="down_list"><img src="/admin/skin/default/images/common/btn_img_ex.gif" align="absmiddle" /> 다운로드 양식</button></span>
			</li>

		</ul>
	</div>
</div>

<!-- 주문리스트 테이블 : 시작 -->
<table class="list-table-style" cellspacing="0">
	<!-- 테이블 헤더 : 시작 -->
	<colgroup>
		<col width="30" />
		<col width="30" />
		<col width="40" />
		<col width="40"/>
		<col />

		<col width="90" />
		<col width="90" />
		<col width="70" />
		<col width="70" />
		<col width="40" />

		<col width="60" />
		<col width="150" />
		<col width="60" />
		<col width="40" />

		<col width="70" />
		<col width="100" />
	</colgroup>
	<thead class="lth">
	<tr>
		<th><input type="checkbox" id="chkAll" /></th>
		<th><span class="icon-star-gray hand <?php if($TPL_VAR["sc"]["orderby"]=='favorite_chk'&&$TPL_VAR["sc"]["sort"]=='desc'){?>checked<?php }?>" id="order_star"></span></th>
		<th>번호</th>
		
		<th colspan="2">
			<span class="btnSort hand" orderby="goods_name" title="[상품명]으로 정렬">상품명<?php if($TPL_VAR["orderby"]=='asc_goods_name'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_goods_name'){?>▼<?php }?></span>
		</th>
		<th>
			<span class="btnSort hand" orderby="consumer_price" title="[정가]로 정렬">정가<?php if($TPL_VAR["orderby"]=='asc_consumer_price'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_consumer_price'){?>▼<?php }?></span>
		</th>
		<th>
			<span class="btnSort hand" orderby="price" title="[할인가]로 정렬">할인가<?php if($TPL_VAR["orderby"]=='asc_price'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_price'){?>▼<?php }?></span>
		</th>
		<th>
			<span class="btnSort hand" orderby="tot_stock" title="[재고] 정렬">재고<?php if($TPL_VAR["orderby"]=='asc_tot_stock'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_tot_stock'){?>▼<?php }?></span>/가용
		</th>
		<th>개별 배송비</th>
		<th>구매</th>

		<th>
			<span class="btnSort hand" orderby="page_view" title="[페이지뷰]로 정렬">페이지뷰<?php if($TPL_VAR["orderby"]=='asc_page_view'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_page_view'){?>▼<?php }?></span>
		</th>
		<th><span class="btnSort hand" orderby="goods_seq" title="[등록일순] 정렬">등록일<?php if($TPL_VAR["orderby"]=='asc_goods_seq'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_goods_seq'){?>▼<?php }?></span>
		/<span class="btnSort hand" orderby="update_date" title="[수정일순] 정렬">수정일<?php if($TPL_VAR["orderby"]=='asc_update_date'){?>▲<?php }elseif($TPL_VAR["orderby"]=='desc_update_date'){?>▼<?php }?></span></th>

		<th>상태</th>
		<th>노출</th>

		<th>통계</th>
		<th>관리</th>
	</tr>
	</thead>
	<!-- 테이블 헤더 : 끝 -->

	<!-- 리스트 : 시작 -->
	<tbody class="ltb">
<?php if($TPL_VAR["loop"]){?>
<?php if($TPL_loop_1){foreach($TPL_VAR["loop"] as $TPL_V1){?>
		<tr class="list-row" style="height:70px;">
			<td align="center"><input type="checkbox" class="chk" name="goods_seq[]" value="<?php echo $TPL_V1["goods_seq"]?>" /></td>
			<td align="center"><span class="icon-star-gray star_select <?php echo $TPL_V1["favorite_chk"]?>" goods_seq="<?php echo $TPL_V1["goods_seq"]?>"></span></td>
			<td align="center"><?php echo $TPL_VAR["page"]["totalcount"]-$TPL_V1["_no"]+ 1?></td>
			<td align="center"><a href="/goods/view?no=<?php echo $TPL_V1["goods_seq"]?>" target="_blank"><img class="small_goods_image" src="<?php echo viewImg($TPL_V1["goods_seq"],'thumbView')?>" width="50"></a></td>
			<td align="left" style="padding-left:10px;">

<?php if($TPL_V1["tax"]=='exempt'&&$TPL_V1["cancel_type"]=='1'){?>
					<div>
					<span style="color:red;" class="left" >[비과세]</span>
					<span class="order-item-cancel-type left" >[청약철회불가]</span>
					</div>
<?php }elseif($TPL_V1["tax"]=='exempt'){?>
					<div>
					<span style="color:red;" class="left" >[비과세]</span>
					</div>
<?php }elseif($TPL_V1["cancel_type"]=='1'){?>
					<div>
					<span class="order-item-cancel-type left" >[청약철회불가]</span>
					</div>
<?php }?>

<?php if($TPL_V1["goods_code"]){?><div ><a href="../goods/regist?no=<?php echo $TPL_V1["goods_seq"]?>" target="_blank" class="fx11">[상품코드: <?php echo $TPL_V1["goods_code"]?>]</a></div><?php }?>
			<a href="../goods/regist?no=<?php echo $TPL_V1["goods_seq"]?>" target="_blank"><?php echo getstrcut(strip_tags($TPL_V1["goods_name"]), 80)?></a> <div style="padding-top:5px;"><?php echo $TPL_V1["catename"]?></div>
			</td>
			<td align="right"><?php echo number_format($TPL_V1["consumer_price"])?>&nbsp;</td>
			<td align="right"><?php echo number_format($TPL_V1["price"])?>&nbsp;</td>
			<td align="right">
<?php if($TPL_V1["tot_stock"]< 0){?>
				<span style='color:red'><?php echo number_format($TPL_V1["tot_stock"])?></span>
<?php }else{?>
				<?php echo number_format($TPL_V1["tot_stock"])?>

<?php }?>
<?php if($TPL_V1["rstock"]< 0){?>
				<br/> <span style='color:red'><?php echo number_format($TPL_V1["rstock"])?></span>
<?php }else{?>
				<br/> <?php echo number_format($TPL_V1["rstock"])?>

<?php }?>
<?php if($TPL_V1["options"][ 0]["option_title"]){?>
				<br/>
				<span class="btn small <?php if($TPL_V1["stocknothing"]> 0||$TPL_V1["rstocknothing"]> 0){?>red<?php }else{?>blue<?php }?>"><button class="goodsOptionBtn" type="button" goods_seq="<?php echo $TPL_V1["goods_seq"]?>">옵션</button></span>
				<div class="relative" style="z-index:100;">
					<div class="goodsOptionTable hide" style="max-height:300px;overflow:auto;"></div>
				</div>
<?php }?>
			</td>
			<td align="<?php if($TPL_V1["unlimit_shipping_price"]){?>right<?php }else{?>center<?php }?>"><?php if($TPL_V1["unlimit_shipping_price"]){?><?php echo number_format($TPL_V1["unlimit_shipping_price"])?><?php }else{?>-<?php }?>&nbsp;</td>
			<td align="center"><a href="/admin/order/catalog?goods_seq=<?php echo $TPL_V1["goods_seq"]?>">조회</a></td>
			<td align="center"><?php echo number_format($TPL_V1["page_view"])?></td>
			<td align="center"><?php echo $TPL_V1["regist_date"]?><br/><?php echo $TPL_V1["update_date"]?></td>
			<td align="center"><?php echo $TPL_V1["goods_status_text"]?></td>
			<td align="center"><?php echo $TPL_V1["goods_view_text"]?></td>
			<td align="center">
				<div><img src="/admin/skin/default/images/design/btn_stats.gif" style="cursor:pointer;" onclick="openAdvancedStatistic('<?php echo $TPL_V1["goods_seq"]?>');"  /></div>
				<div style="margin-top:2px;"><img src="/admin/skin/default/images/design/btn_bigdata.gif" style="cursor:pointer;" onclick="openBigdataPreview('<?php echo $TPL_V1["goods_seq"]?>');"  /></div>
			</td>
			<td align="center">
				<span class="btn small valign-middle"><input type="button" name="manager_modify_btn" value="수정" goods_seq="<?php echo $TPL_V1["goods_seq"]?>" onclick="goodsView('<?php echo $TPL_V1["goods_seq"]?>');"/></span>
				<span class="btn small valign-middle"><input type="button" class="manager_copy_btn" value="복사" goods_seq="<?php echo $TPL_V1["goods_seq"]?>"/></span>
			</td>
		</tr>
<?php }}?>
<?php }else{?>
	<tr class="list-row">
		<td align="center" colspan="16">
<?php if($TPL_VAR["search_text"]){?>
				'<?php echo $TPL_VAR["search_text"]?>' 검색된 상품이 없습니다.
<?php }else{?>
				등록된 상품이 없습니다.
<?php }?>
		</td>
	</tr>
<?php }?>
	</tbody>
	<!-- 리스트 : 끝 -->

</table>
<!-- 주문리스트 테이블 : 끝 -->
<div class="clearbox">
	<ul class="left-btns">
		<li>
			<span class="btn small gray"><button type="button" id="delete_btn">삭제</button></span>
		</li>
	</ul>
</div>


<br style="line-height:10px;" />

</form>

<!-- 페이징 -->
<div class="paging_navigation" style="margin:auto;"><?php echo $TPL_VAR["page"]["html"]?></div>

<div class="hide" id="search_detail_dialog">
<form name="set_search_detail" method="post" action="set_search_default" target="actionFrame">
<div id="contents">
	<table class="search-form-table" id="serch_tab">
	<tr id="goods_search_form" style="display:block;">
	<tr>
		<td>
			<table class="sf-option-table">
			<tr>
				<th>카테고리</th>
				<td colspan="7">
					<select class="line" name="s_category1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="s_category2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="s_category3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="s_category4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>
				</td>
			</tr>
			<tr>
				<th>브랜드</th>
				<td colspan="7">
					<select class="line" name="s_brands1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="s_brands2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="s_brands3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="s_brands4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>
				</td>
			</tr>
			<tr>
				<th>지역</th>
				<td colspan="7">
					<select class="line" name="s_locations1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="s_locations2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="s_locations3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="s_locations4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>
				</td>
			</tr>
			<tr>
				<th><select name="date_gb" class="search_select_pop">
						<option value="regist_date" <?php if($TPL_VAR["sc"]["date_gb"]=='regist_date'){?>selected<?php }?>>등록일</option>
						<option value="update_date" <?php if($TPL_VAR["sc"]["date_gb"]=='update_date'){?>selected<?php }?>>수정일</option>
					</select></th>
				<td colspan="7">
					<label class="search_label"><input type="radio" name="regist_date" value="today" <?php if(!$_GET["regist_date_type"]||$_GET["regist_date_type"]=='today'){?> checked="checked" <?php }?>/> 오늘</label>
					<label class="search_label"><input type="radio" name="regist_date" value="3day" <?php if($_GET["regist_date_type"]=='3day'){?> checked="checked" <?php }?>/> 3일간</label>
					<label class="search_label"><input type="radio" name="regist_date" value="7day" <?php if($_GET["regist_date_type"]=='7day'){?> checked="checked" <?php }?>/> 일주일</label>
					<label class="search_label"><input type="radio" name="regist_date" value="1mon" <?php if($_GET["regist_date_type"]=='1mon'){?> checked="checked" <?php }?>/> 1개월</label>
					<label class="search_label"><input type="radio" name="regist_date" value="3mon" <?php if($_GET["regist_date_type"]=='3mon'){?> checked="checked" <?php }?>/> 3개월</label>
					<label class="search_label"><input type="radio" name="regist_date" value="all" <?php if($_GET["regist_date_type"]=='all'){?> checked="checked" <?php }?>/> 전체</label>
				</td>
			</tr>
			<tr>
				<th><select name="price_gb" class="search_select_pop">
						<option value="consumer_price" <?php if($TPL_VAR["sc"]["price_gb"]=='consumer_price'){?>selected<?php }?>>정상가</option>
						<option value="price" <?php if($TPL_VAR["sc"]["price_gb"]=='price'){?>selected<?php }?>>할인가</option>
					</select></th>
				<td>
					<input type="text" name="sprice" value="<?php echo $_GET["sprice"]?>" size="5" class="line" /> - <input type="text" name="eprice" value="<?php echo $_GET["eprice"]?>" size="5" class="line"/>
				</td>
				<th>재고수량</th>
				<td>
					<input type="text" name="sstock" value="<?php echo $_GET["sstock"]?>" size="5" class="line"/> - <input type="text" name="estock" value="<?php echo $_GET["estock"]?>" size="5" class="line"/>
				</td>
				<th>페이지뷰</th>
				<td>
					<input type="text" name="spage_view" value="<?php echo $_GET["spage_view"]?>" class="line" size="5"/> - <input type="text" name="epage_view" value="<?php echo $_GET["epage_view"]?>" class="line" size="5"/>
				</td>
				<th></th>
				<td></td>
			</tr>
			<tr>
				<!--
				<th>매입처</th>
				<td>
					<select name=""></select>
				</td>
				<th>판매처</th>
				<td>
					<select name=""></select>
				</td>
				-->
				<th>모델명</th>
				<td>
					<select name="model" class="line">
						<option value="">= 선택하세요 =</option>
<?php if($TPL_model_1){foreach($TPL_VAR["model"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["contents"]?>" <?php if($TPL_VAR["sc"]["model"]==$TPL_V1["contents"]){?>selected<?php }?>><?php echo $TPL_V1["contents"]?></option>
<?php }}?>
					</select>
				</td>
				<th>브랜드</th>
				<td>
					<select name="brand" class="line">
						<option value="">= 선택하세요 =</option>
<?php if($TPL_brand_1){foreach($TPL_VAR["brand"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["contents"]?>" <?php if($TPL_VAR["sc"]["brand"]==$TPL_V1["contents"]){?>selected<?php }?>><?php echo $TPL_V1["contents"]?></option>
<?php }}?>
					</select>
				</td>
				<th>상태</th>
				<td colspan="3">
					<label><input type="checkbox" name="goodsStatus[]" value="normal" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('normal',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>정상<label>
					<label><input type="checkbox" name="goodsStatus[]" value="runout" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('runout',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>품절<label>
					<label><input type="checkbox" name="goodsStatus[]" value="purchasing" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('purchasing',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>재고확보중<label>
					<label><input type="checkbox" name="goodsStatus[]" value="unsold" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('unsold',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>판매중지<label>
				</td>
			</tr>
			<tr>
				<th>제조사</th>
				<td>
					<select name="manufacture" class="line">
						<option value="">= 선택하세요 =</option>
<?php if($TPL_manufacture_1){foreach($TPL_VAR["manufacture"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["contents"]?>" <?php if($TPL_VAR["sc"]["manufacture"]==$TPL_V1["contents"]){?>selected<?php }?>><?php echo $TPL_V1["contents"]?></option>
<?php }}?>
					</select>
				</td>
				<th>원산지</th>
				<td>
					<select name="orign" class="line">
						<option value="">= 선택하세요 =</option>
<?php if($TPL_orign_1){foreach($TPL_VAR["orign"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["contents"]?>" <?php if($TPL_VAR["sc"]["orign"]==$TPL_V1["contents"]){?>selected<?php }?>><?php echo $TPL_V1["contents"]?></option>
<?php }}?>
					</select>
				</td>
				<th>노출</th>
				<td>
					<label><input type="checkbox" name="goodsView[]" value="look" <?php if($TPL_VAR["sc"]["goodsView"]&&in_array('look',$TPL_VAR["sc"]["goodsView"])){?>checked<?php }?>/>보임<label>
					<label><input type="checkbox" name="goodsView[]" value="notLook" <?php if($TPL_VAR["sc"]["goodsView"]&&in_array('notLook',$TPL_VAR["sc"]["goodsView"])){?>checked<?php }?>/>안보임<label>
				</td>
				<th>과세/비과세</th>
				<td>
					<label><input type="checkbox" name="taxView[]" value="tax" <?php if($TPL_VAR["sc"]["taxView"]&&in_array('tax',$TPL_VAR["sc"]["taxView"])){?>checked<?php }?>/>과세<label>
					<label><input type="checkbox" name="taxView[]" value="exempt" <?php if($TPL_VAR["sc"]["taxView"]&&in_array('exempt',$TPL_VAR["sc"]["taxView"])){?>checked<?php }?>/>비과세<label>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>
<div align="center" style="padding-top:10px;">
	<span class="btn large black">
		<button type="submit">저장하기<span class="arrowright"></span></button>
	</span>
</div>
</form>
</div>



<div id="export_upload" class="hide">
<form name="excelRegist" id="excelRegist" method="post" action="../goods_process/excel_upload" enctype="multipart/form-data"  target="actionFrame">

	<div class="clearbox"></div>
	<div class="item-title">상품 일괄 등록 및 수정</div>
	<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="20%" />
		<col width="80%" />
	</colgroup>
	<tr>
		<th class="its-th-align center">일괄수정</th>
		<td class="its-td">
			<input type="file" name="excel_file" id="excel_file" style="height:20px;"/>
		</td>
	</tr>
	</table>

	<div style="width:100%;text-align:center;padding-top:10px;">
	<span class="btn large cyanblue"><button id="upload_submit">확인</button></span>
	</div>

	<div style="padding:15px;"></div>

	<div style="padding-left:10px;font-size:12px;">
		* 상품을 일괄 등록하거나 수정할 때 엑셀 양식을  먼저 다운로드 받은 후에 이용하면 됩니다.<br/>
		&nbsp;&nbsp; ( <span style="color:red;">필독! 엑셀파일 저장시 확장자가 XLS 인 엑셀 97~2003 양식으로 저장해 주세요</span> ) <br/>
		<div style="padding:3px;"></div>
		* 일괄 등록과 수정의 구분은 고유값 필드에 있는 값의 유무로 판단합니다.(고유값 필드에 값이 있으면 수정, 없으면 등록입니다.)<br/>
		<div style="padding:3px;"></div>
		* 상품 옵션은 옵션마다 1개의 행을 차지합니다.(옵션을 등록한 이후에 엑셀을 다운로드 받아서 보면 이해하기 편합니다.)<br/>
		<div style="padding:3px;"></div>
		* 옵션 항목에는 옵션값만 입력해야 하며 상품 공통 정보를 입력하면 안됩니다. 상품 공통 정보 항목도 옵션값을 입력하면 안됩니다. <br/>
		<div style="padding:3px;"></div>
		* 대표카테고리와 추가카테고리가 병합되었습니다. 맨마지막 카테고리번호가 대표카테고리로 등록됩니다.<br/>
		<div style="padding:3px;"></div>
		* 대표브랜드와 추가브랜드가 병합되었습니다. 맨마지막 브랜드번호가 대표브랜드로 등록됩니다.<br/>
	</div>

	<div style="padding:15px;"></div>


</form>
</div>

<!--### 워터마크세팅 -->
<div id="watermark_setting_popup"></div>


<div id="set_option_view_lay" class="hide">
	<form name="ovFrm" method="post" action="../goods_process/set_option_view_count" target="actionFrame" onsubmit="return chkOptionViewCount(this);">
	<div style="line-height:30px;">상품 등록/수정 화면에서</div>
	<div style="line-height:30px;">
		필수옵션은 기본으로 <input type="text" size="3" name="option_view_count" value="<?php echo $TPL_VAR["config_goods"]["option_view_count"]?>" />개가 보이며 
		나머지는 <span style="color:red;">모두열기▼</span>를 클릭하여 봅니다. 
	</div>
	<div style="line-height:30px;">
		추가구성옵션은 기본으로 <input type="text" size="3" name="suboption_view_count" value="<?php echo $TPL_VAR["config_goods"]["suboption_view_count"]?>" />개가 보이며 
		나머지는 <span style="color:red;">모두열기▼</span>를 클릭하여 봅니다. 
	</div>
	<div style="line-height:30px;">
		<span class="desc">※ <span style="color:red;">모두열기▼</span>는 기본 개수를 초과한 옵션 개수가
		있을 경우에만 나타납니다.
	</div>
	<div style="line-height:30px;width:100%;text-align:center;">
		<span class="btn medium cyanblue"><button type="submit">저장</button></span>
	</div>
	</form>
</div>


<?php $this->print_("layout_footer",$TPL_SCP,1);?>