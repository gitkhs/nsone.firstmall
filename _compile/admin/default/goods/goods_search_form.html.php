<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/goods/goods_search_form.html 000028901 */ 
$TPL_sale_list_1=empty($TPL_VAR["sale_list"])||!is_array($TPL_VAR["sale_list"])?0:count($TPL_VAR["sale_list"]);
$TPL_model_1=empty($TPL_VAR["model"])||!is_array($TPL_VAR["model"])?0:count($TPL_VAR["model"]);
$TPL_brand_1=empty($TPL_VAR["brand"])||!is_array($TPL_VAR["brand"])?0:count($TPL_VAR["brand"]);
$TPL_manufacture_1=empty($TPL_VAR["manufacture"])||!is_array($TPL_VAR["manufacture"])?0:count($TPL_VAR["manufacture"]);
$TPL_orign_1=empty($TPL_VAR["orign"])||!is_array($TPL_VAR["orign"])?0:count($TPL_VAR["orign"]);
$TPL_r_goods_icon_1=empty($TPL_VAR["r_goods_icon"])||!is_array($TPL_VAR["r_goods_icon"])?0:count($TPL_VAR["r_goods_icon"]);
$TPL_mall_1=empty($TPL_VAR["mall"])||!is_array($TPL_VAR["mall"])?0:count($TPL_VAR["mall"]);?>
<script type="text/javascript">
$(document).ready(function() {
<?php if($TPL_VAR["socialcpuse"]||preg_match('/goods\/batch_modify/',$_SERVER["REQUEST_URI"])){?>
		/* 쿠폰상품그룹 착기버튼 */
		$("button#coupon_group_search").click(function() {
			var group_seq = ($("#social_goods_group").val())?$("#social_goods_group").val():'0';
			addFormDialog('./social_goods_group?type=list&sel_group_seq='+group_seq, '700', '450', '쿠폰상품그룹 찾기 ','false');
		});

		$("button#coupon_group_search_all").click(function(){
			$("#social_goods_group").val('');
			$(".social_goods_group_name").val('');
		});

		//쿠폰상품그룹 선택시
		$(".social_goods_group_sel").live("click",function(){
			var social_goods_group_seq = $(this).attr("social_goods_group_seq");
			var social_goods_group_name = $(this).attr("social_goods_group_name");
			$("#social_goods_group").val(social_goods_group_seq);
			$(".social_goods_group_name").val(social_goods_group_name);
			$('#dlg').dialog('close');
		});
<?php }?>

	/* 카테고리 불러오기 */
	category_admin_select_load('','category1','',function(){
<?php if($TPL_VAR["sc"]["category1"]){?>
		$("select[name='category1']").val('<?php echo $_GET["category1"]?>').change();
<?php }?>
	});
	$("select[name='category1']").live("change",function(){
		category_admin_select_load('category1','category2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category2"]){?>
			$("select[name='category2']").val('<?php echo $_GET["category2"]?>').change();
<?php }?>
		});
		category_admin_select_load('category2','category3',"");
		category_admin_select_load('category3','category4',"");
	});
	$("select[name='category2']").live("change",function(){
		category_admin_select_load('category2','category3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category3"]){?>
			$("select[name='category3']").val('<?php echo $_GET["category3"]?>').change();
<?php }?>
		});
		category_admin_select_load('category3','category4',"");
	});
	$("select[name='category3']").live("change",function(){
		category_admin_select_load('category3','category4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category4"]){?>
			$("select[name='category4']").val('<?php echo $_GET["category4"]?>').change();
<?php }?>
		});
	});

	$("select[name='s_category1']").live("change",function(){
		category_admin_select_load('s_category1','s_category2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category2"]){?>
			$("select[name='s_category2']").val('<?php echo $_GET["category2"]?>').change();
<?php }?>
		});
		category_admin_select_load('category2','category3',"");
		category_admin_select_load('category3','category4',"");
	});
	$("select[name='s_category2']").live("change",function(){
		category_admin_select_load('s_category2','s_category3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category3"]){?>
			$("select[name='s_category3']").val('<?php echo $_GET["category3"]?>').change();
<?php }?>
		});
		category_admin_select_load('s_category3','s_category4',"");
	});
	$("select[name='s_category3']").live("change",function(){
		category_admin_select_load('s_category3','s_category4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["category4"]){?>
			$("select[name='s_category4']").val('<?php echo $_GET["category4"]?>').change();
<?php }?>
		});
	});
	////////////////////////////

	/* 브랜드 불러오기 */
	brand_admin_select_load('','brands1','',function(){
<?php if($TPL_VAR["sc"]["brands1"]){?>
		$("select[name='brands1']").val('<?php echo $_GET["brands1"]?>').change();
<?php }?>
	});
	$("select[name='brands1']").live("change",function(){
		brand_admin_select_load('brands1','brands2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands2"]){?>
			$("select[name='brands2']").val('<?php echo $_GET["brands2"]?>').change();
<?php }?>
		});
		brand_admin_select_load('brands2','brands3',"");
		brand_admin_select_load('brands3','brands4',"");
	});
	$("select[name='brands2']").live("change",function(){
		brand_admin_select_load('brands2','brands3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands3"]){?>
			$("select[name='brands3']").val('<?php echo $_GET["brands3"]?>').change();
<?php }?>
		});
		brand_admin_select_load('brands3','brands4',"");
	});
	$("select[name='brands3']").live("change",function(){
		brand_admin_select_load('brands3','brands4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands4"]){?>
			$("select[name='brands4']").val('<?php echo $_GET["brands4"]?>').change();
<?php }?>
		});
	});
	$("select[name='s_brands1']").live("change",function(){
		brand_admin_select_load('s_brands1','s_brands2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands2"]){?>
			$("select[name='s_brands2']").val('<?php echo $_GET["brands2"]?>').change();
<?php }?>
		});
		brand_admin_select_load('s_brands2','s_brands3',"");
		brand_admin_select_load('s_brands3','s_brands4',"");
	});
	$("select[name='s_brands2']").live("change",function(){
		brand_admin_select_load('s_brands2','s_brands3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands3"]){?>
			$("select[name='s_brands3']").val('<?php echo $_GET["brands3"]?>').change();
<?php }?>
		});
		brand_admin_select_load('s_brands3','s_brands4',"");
	});
	$("select[name='s_brands3']").live("change",function(){
		brand_admin_select_load('s_brands3','s_brands4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["brands4"]){?>
			$("select[name='s_brands4']").val('<?php echo $_GET["brands4"]?>').change();
<?php }?>
		});
	});

	/* 지역 불러오기 */
	location_admin_select_load('','location1','',function(){
<?php if($TPL_VAR["sc"]["location1"]){?>
		$("select[name='location1']").val('<?php echo $_GET["location1"]?>').change();
<?php }?>
	});
	$("select[name='location1']").live("change",function(){
		location_admin_select_load('location1','location2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location2"]){?>
			$("select[name='location2']").val('<?php echo $_GET["location2"]?>').change();
<?php }?>
		});
		location_admin_select_load('location2','location3',"");
		location_admin_select_load('location3','location4',"");
	});
	$("select[name='location2']").live("change",function(){
		location_admin_select_load('location2','location3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location3"]){?>
			$("select[name='location3']").val('<?php echo $_GET["location3"]?>').change();
<?php }?>
		});
		location_admin_select_load('location3','location4',"");
	});
	$("select[name='location3']").live("change",function(){
		location_admin_select_load('location3','location4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location4"]){?>
			$("select[name='location4']").val('<?php echo $_GET["location4"]?>').change();
<?php }?>
		});
	});
	$("select[name='s_location1']").live("change",function(){
		location_admin_select_load('s_location1','s_location2',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location2"]){?>
			$("select[name='s_location2']").val('<?php echo $_GET["location2"]?>').change();
<?php }?>
		});
		location_admin_select_load('s_location2','s_location3',"");
		location_admin_select_load('s_location3','s_location4',"");
	});
	$("select[name='s_location2']").live("change",function(){
		location_admin_select_load('s_location2','s_location3',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location3"]){?>
			$("select[name='s_location3']").val('<?php echo $_GET["location3"]?>').change();
<?php }?>
		});
		location_admin_select_load('s_location3','s_location4',"");
	});
	$("select[name='s_location3']").live("change",function(){
		location_admin_select_load('s_location3','s_location4',$(this).val(),function(){
<?php if($TPL_VAR["sc"]["location4"]){?>
			$("select[name='s_location4']").val('<?php echo $_GET["location4"]?>').change();
<?php }?>
		});
	});

	$("[name='select_date']").click(function() {
		switch($(this).attr("id")) {
			case 'today' :
				$("input[name='sdate']").val(getDate(0));
				$("input[name='edate']").val(getDate(0));
				break;
			case '3day' :
				$("input[name='sdate']").val(getDate(3));
				$("input[name='edate']").val(getDate(0));
				break;
			case '1week' :
				$("input[name='sdate']").val(getDate(7));
				$("input[name='edate']").val(getDate(0));
				break;
			case '1month' :
				$("input[name='sdate']").val(getDate(30));
				$("input[name='edate']").val(getDate(0));
				break;
			case '3month' :
				$("input[name='sdate']").val(getDate(90));
				$("input[name='edate']").val(getDate(0));
				break;
			default :
				$("input[name='sdate']").val('');
				$("input[name='edate']").val('');
				break;
		}
	});

	$("#search_set").click(function(){
		category_admin_select_load('','s_category1','',function(){
<?php if($TPL_VAR["sc"]["category1"]){?>
			$("select[name='s_category1']").val('<?php echo $_GET["category1"]?>').change();
<?php }?>
		});

		brand_admin_select_load('','s_brands1','',function(){
<?php if($TPL_VAR["sc"]["brands1"]){?>
			$("select[name='s_brands1']").val('<?php echo $_GET["brands1"]?>').change();
<?php }?>
		});

		location_admin_select_load('','s_location1','',function(){
<?php if($TPL_VAR["sc"]["location1"]){?>
			$("select[name='s_location1']").val('<?php echo $_GET["location1"]?>').change();
<?php }?>
		});

		var title = '기본검색 설정<span style="font-size:11px; margin-left:26px;"> - 아래서 원하는 검색조건을 설정하여 편하게 쇼핑몰을 운영하세요</span>';
		openDialog(title, "search_detail_dialog", {"width":"800","height":"300"});
	});

	$("#get_default_button").click(function(){
		$.getJSON('get_search_default', function(result) {
			for(var i=0;i<result.length;i++){
				if(result[i][0].search(/goodsStatus\[[0-9]+\]/) != -1 || result[i][0].search(/goodsView\[[0-9]+\]/) != -1){
					//alert(result[i][0]+" : "+result[i][1]);
					if(result[i][1]=='normal') $("input[name='goodsStatus[]']").eq(0).attr("checked",true);
					else if(result[i][1]=='runout') $("input[name='goodsStatus[]']").eq(1).attr("checked",true);
					else if(result[i][1]=='unsold') $("input[name='goodsStatus[]']").eq(2).attr("checked",true);
					else if(result[i][1]=='look') $("input[name='goodsView[]']").eq(0).attr("checked",true);
					else if(result[i][1]=='notLook') $("input[name='goodsView[]']").eq(1).attr("checked",true);
				}else if(result[i][0]=='regist_date'){
					if(result[i][1] == 'today'){
						set_date('<?php echo date('Y-m-d')?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '3day'){
						set_date('<?php echo date('Y-m-d',strtotime("-3 day"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '7day'){
						set_date('<?php echo date('Y-m-d',strtotime("-7 day"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '1mon'){
						set_date('<?php echo date('Y-m-d',strtotime("-1 month"))?>','<?php echo date('Y-m-d')?>');
					}else if(result[i][1] == '3mon'){
						set_date('<?php echo date('Y-m-d',strtotime("-3 month"))?>','<?php echo date('Y-m-d')?>');
					}
				}
				$("*[name='"+result[i][0]+"']",document.memberForm).val(result[i][1]);
			}
		});
	});


	$(".star_select").click(function(){
		var status = "";
		if($(this).hasClass("checked")){
			$(this).removeClass("checked");
			status = "none";
		}else{
			$(this).addClass("checked");
			status = "checked";
		}

		$.ajax({
			type: "get",
			url: "../goods/set_favorite",
			data: "status="+status+"&goods_seq="+$(this).attr("goods_seq"),
			success: function(result){
				//alert(result);
			}
		});
	});

});

function set_date(start,end){
	$("input[name='sdate']").val(start);
	$("input[name='edate']").val(end);
}
</script>
<div class="search-form-container">
	<table class="search-form-table">
	<tr>
		<td>
			<table>
			<tr>
				<td width="500">
					<table class="sf-keyword-table">
					<tr>
						<td class="sfk-td-txt"><input type="text" name="keyword" value="<?php echo $_GET["keyword"]?>" title="상품명, 상품 고유값, 상품코드, 태그, 간략설명" /></td>
						<td class="sfk-td-btn"><button type="submit"><span>검색</span></button></td>
					</tr>
					</table>
				</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td>
					<span id="search_set" class="icon-arrow-down" style="cursor:pointer;">기본검색설정</span>
					<span class="btn small gray"><button type="button" id="get_default_button">적용 ▶</button></span>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<table class="search-form-table" id="serch_tab" align="center">
	<tr id="goods_search_form" style="display:block;">
	<tr>
		<td>
			<table class="sf-option-table">
			<colgroup>
				<col width="100" />
				<col width="150" />
				<col width="80" />
				<col width="150" />
				<col width="90" />
				<col width="230" />
				<col width="140" />
				<col width="150" />
			</colgroup>
			<tr>
				<th>카테고리</th>
				<td colspan="5">
					<select class="line" name="category1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="category2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="category3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="category4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>

					<label><input type="checkbox" name="goods_category" value="1" <?php if($TPL_VAR["sc"]["goods_category"]){?>checked<?php }?> />대표</label><span class="helpicon" title="체크 시 대표 카테고리를 기준으로 검색됩니다." options="{alignX: 'right'}"></span>

					<label><input type="checkbox" name="goods_category_no" value="1" <?php if($TPL_VAR["sc"]["goods_category_no"]){?>checked<?php }?> />카테고리 미연결</label><span class="helpicon" title="체크 시 카테고리가 없는 상품을 검색합니다." options="{alignX: 'right'}"></span>
				</td>
				<th class="left">이미지영역 동영상</th>
				<td>
					<label><input type="checkbox" name="file_key_w" value="1" <?php if($_GET["file_key_w"]){?>checked="checked"<?php }?> /> 있음</label>
					<select name="video_use" class="video_use">
						<option selected >전체</option>
						<option value="Y" <?php if($TPL_VAR["sc"]["video_use"]=='Y'){?>selected<?php }?>>노출</option>
						<option value="N" <?php if($TPL_VAR["sc"]["video_use"]=='N'){?>selected<?php }?>>미노출</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>브랜드</th>
				<td colspan="5">
					<select class="line" name="brands1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="brands2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="brands3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="brands4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>

					 <label><input type="checkbox" name="goods_brand" value="1" <?php if($TPL_VAR["sc"]["goods_brand"]){?>checked<?php }?> />대표</label><span class="helpicon" title="체크 시 대표 브랜드를 기준으로 검색됩니다." options="{alignX: 'right'}"></span>

					 <label><input type="checkbox" name="goods_brand_no" value="1" <?php if($TPL_VAR["sc"]["goods_brand_no"]){?>checked<?php }?> />브랜드 미연결</label><span class="helpicon" title="체크 시 브랜드가 없는 상품을 검색합니다." options="{alignX: 'right'}"></span>
				</td>
				<th class="left">설명영역 동영상</th>
				<td>
					<label><input type="checkbox" name="videototal" value="1" <?php if($_GET["videototal"]){?>checked="checked"<?php }?> /> 있음</label>
				</td>
			</tr>
			<tr>
				<th>지역</th>
				<td colspan="<?php if($_GET["mode"]=='imagehosting'&&preg_match('/goods\/batch_modify/',$_SERVER["REQUEST_URI"])){?>5<?php }else{?>6<?php }?>">
					<select class="line" name="location1" size="1" style="width:100px;"><option value="">= 1차 분류 =</option></select>
					<select class="line" name="location2" size="1" style="width:100px;"><option value="">= 2차 분류 =</option></select>
					<select class="line" name="location3" size="1" style="width:100px;"><option value="">= 3차 분류 =</option></select>
					<select class="line" name="location4" size="1" style="width:100px;"><option value="">= 4차 분류 =</option></select>

					 <label><input type="checkbox" name="goods_location" value="1" <?php if($TPL_VAR["sc"]["goods_location"]){?>checked<?php }?> />대표</label><span class="helpicon" title="체크 시 대표 지역을 기준으로 검색됩니다." options="{alignX: 'right'}"></span>

					 <label><input type="checkbox" name="goods_location_no" value="1" <?php if($TPL_VAR["sc"]["goods_location_no"]){?>checked<?php }?> />지역 미연결</label><span class="helpicon" title="체크 시 지역이 없는 상품을 검색합니다." options="{alignX: 'right'}"></span>
				</td>
<?php if($_GET["mode"]=='imagehosting'&&preg_match('/goods\/batch_modify/',$_SERVER["REQUEST_URI"])){?>
				<th class="left">이미지호스팅</th>
				<td>
					<label><input type="checkbox" name="gabiaimagehostign" value="1" <?php if($_GET["gabiaimagehostign"]){?>checked="checked"<?php }?> /> 미변환</label>
				</td>
<?php }?>
			</tr>
			<tr>
				<th><select name="date_gb" class="search_select">
						<option value="regist_date" <?php if($TPL_VAR["sc"]["date_gb"]=='regist_date'){?>selected<?php }?>>등록일</option>
						<option value="update_date" <?php if($TPL_VAR["sc"]["date_gb"]=='update_date'){?>selected<?php }?>>수정일</option>
					</select></th>
				<td colspan="3">
					<input type="text" name="sdate" value="<?php echo $_GET["sdate"]?>" class="datepicker line"  maxlength="10" size="10" />
					&nbsp;&nbsp;<span class="gray">-</span>&nbsp;&nbsp;
					<input type="text" name="edate" value="<?php echo $_GET["edate"]?>" class="datepicker line" maxlength="10" size="10" />
					&nbsp;&nbsp;
					<span class="btn small"><input type="button" value="오늘" id="today" name="select_date"/></span>
					<span class="btn small"><input type="button" value="3일간" id="3day" name="select_date"/></span>
					<span class="btn small"><input type="button" value="일주일" id="1week" name="select_date"/></span>
					<span class="btn small"><input type="button" value="1개월" id="1month" name="select_date"/></span>
					<span class="btn small"><input type="button" value="3개월" id="3month" name="select_date"/></span>
					<span class="btn small"><input type="button" value="전체" id="all" name="select_date"/></span>
				</td>
				<th>등급별 혜택</th>
				<td>
					<select name="sale_seq">
						<option value="">혜택 세트 선택</option>
<?php if($TPL_sale_list_1){foreach($TPL_VAR["sale_list"] as $TPL_V1){?>
						<option value="<?php echo $TPL_V1["sale_seq"]?>" <?php if($TPL_V1["sale_seq"]==$_GET["sale_seq"]){?>selected<?php }?>><?php echo $TPL_V1["sale_title"]?></option>
<?php }}?>
					</select>
				</td>

				<th>청약철회<span class="helpicon" title="청약철회 불가 상품은 구매자가 결제취소, 반품, 교환을 요청할 수 없습니다." options="{alignX: 'right'}"></span></th>
				<td>
				<label><input type="checkbox" name="cancel_type[0]" value="0" <?php if($TPL_VAR["sc"]["cancel_type"][ 0]=='0'){?>checked<?php }?> />가능</label>
				<label><input type="checkbox" name="cancel_type[1]" value="1" <?php if($TPL_VAR["sc"]["cancel_type"][ 1]){?>checked<?php }?> />불가능</label>
				</td>
			</tr>
			<tr>
				<th><select name="price_gb" class="search_select">
						<option value="consumer_price" <?php if($TPL_VAR["sc"]["price_gb"]=='consumer_price'){?>selected<?php }?>>정상가</option>
						<option value="price" <?php if($TPL_VAR["sc"]["price_gb"]=='price'){?>selected<?php }?>>할인가</option>
					</select></th>
				<td>
					<input type="text" name="sprice" value="<?php echo $_GET["sprice"]?>" size="7" class="line"/> - <input type="text" name="eprice" value="<?php echo $_GET["eprice"]?>" size="7" class="line"/>
				</td>
				<th>재고수량</th>
				<td>
					<label><input type="checkbox" name="optstock" value="1" <?php if($_GET["optstock"]){?>checked="checked"<?php }?>/>옵션별</label>
					<input type="text" name="sstock" value="<?php echo $_GET["sstock"]?>" size="3" class="line"/> - <input type="text" name="estock" value="<?php echo $_GET["estock"]?>" size="3" class="line"/>
				</td>
				<th>페이지뷰</th>
				<td>
					<input type="text" name="spage_view" value="<?php echo $_GET["spage_view"]?>" class="line" size="7"/> - <input type="text" name="epage_view" value="<?php echo $_GET["epage_view"]?>" class="line" size="7"/>
				</td>
				<th>적립금</th>
				<td>
				<label><input type="checkbox" name="search_reserve[0]" value="1" <?php if($TPL_VAR["sc"]["search_reserve"][ 0]){?>checked<?php }?> />기본</label>
				<label><input type="checkbox" name="search_reserve[1]" value="1" <?php if($TPL_VAR["sc"]["search_reserve"][ 1]){?>checked<?php }?> />개별</label>
				</td>
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
<?php if($TPL_V1["contents"]){?>
						<option value="<?php echo $TPL_V1["contents"]?>" <?php if($TPL_VAR["sc"]["model"]==$TPL_V1["contents"]){?>selected<?php }?>><?php echo $TPL_V1["contents"]?></option>
<?php }?>
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
				<td>
					<label><input type="checkbox" name="goodsStatus[]" value="normal" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('normal',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>정상</label>
					<label><input type="checkbox" name="goodsStatus[]" value="runout" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('runout',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>품절</label>
					<label><input type="checkbox" name="goodsStatus[]" value="purchasing" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('purchasing',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>재고확보중</label>
					<label><input type="checkbox" name="goodsStatus[]" value="unsold" <?php if($TPL_VAR["sc"]["goodsStatus"]&&in_array('unsold',$TPL_VAR["sc"]["goodsStatus"])){?>checked<?php }?>/>판매중지</label>
				</td>
				<th>배송</th>
				<td>
					<label><input type="checkbox" name="search_delivery[0]" value="1" <?php if($TPL_VAR["sc"]["search_delivery"][ 0]){?>checked<?php }?> />국내기본</label>
					<label><input type="checkbox" name="search_delivery[1]" value="1" <?php if($TPL_VAR["sc"]["search_delivery"][ 1]){?>checked<?php }?> />국내개별</label>
					<!--<label><input type="checkbox" name="search_delivery[2]" value="1" />해외</label>-->
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
<?php if(preg_match('/goods\/batch_modify/',$_SERVER["REQUEST_URI"])){?>
			<tr>
				<th>아이콘</th>
				<td colspan="7">
<?php if($TPL_r_goods_icon_1){foreach($TPL_VAR["r_goods_icon"] as $TPL_V1){?>
				<label style="display:inline-block;width:100px;overflow:hidden">
				<NOBR>
<?php if($TPL_VAR["sc"]["goodsIconCode"][$TPL_V1["codecd"]]){?>
					<input type="checkbox" name="goodsIconCode[<?php echo $TPL_V1["codecd"]?>]" value="1" checked="checked" />
<?php }else{?>
					<input type="checkbox" name="goodsIconCode[<?php echo $TPL_V1["codecd"]?>]" value="1">
<?php }?>
					<img src="/data/icon/goods/<?php echo $TPL_V1["codecd"]?>.gif" border="0" class="hand">
				</NOBR>
				</label>
<?php }}?>
				</td>
			</tr>
<?php }?>
<?php if($TPL_VAR["socialcpuse"]||preg_match('/goods\/batch_modify/',$_SERVER["REQUEST_URI"])){?>
			<tr>
				<th>쿠폰상품그룹</th>
				<td colspan="7">
					<input type="hidden" name="social_goods_group" id="social_goods_group" value="<?php echo $_GET["social_goods_group"]?>">
					<input type="text" name="social_goods_group_name"  class="social_goods_group_name" value="<?php echo $_GET["social_goods_group_name"]?>" readonly>
					<span class="btn small"><button type="button" id="coupon_group_search" >찾기</button></span>
					<span class="btn small"><button type="button" id="coupon_group_search_all" >전체</button></span>
				</td>
			</tr>
<?php }?>
<?php if($TPL_VAR["LINKAGE_SERVICE"]&&$TPL_VAR["linkage"]["linkage_id"]&&$TPL_VAR["mall"]){?>
			<tr>
				<th>판매마켓</th>
				<td colspan="7">
					<table cellpadding="0" cellspacing="0">
					<tr>
<?php if($TPL_mall_1){$TPL_I1=-1;foreach($TPL_VAR["mall"] as $TPL_K1=>$TPL_V1){$TPL_I1++;?>
<?php if($TPL_I1> 0&&($TPL_I1% 5)== 0){?>
						</tr><tr>
<?php }?>
						<td style="padding:2px 5px;">
							<label><input type="checkbox" name="openmarket[]" value="<?php echo $TPL_K1?>" <?php if(in_array($TPL_K1,$_GET['openmarket'])){?>checked<?php }?> /> <?php echo $TPL_V1["mall_name"]?></label>
							<input type="hidden" name="malllist[]" value="<?php echo $TPL_K1?>" />
						</td>
<?php }}?>
						<td style="padding:2px 5px;"><label><input type="checkbox" name="openmarket[]" value="etc" <?php if(in_array('etc',$_GET['openmarket'])){?>checked<?php }?> /> 그외 마켓</label></td>
					</tr>
					</table>
				</td>
			</tr>
<?php }?>
			</table>
		</td>
	</tr>
	</table>
</div>