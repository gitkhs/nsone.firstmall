<?php /* Template_ 2.2.6 2015/03/11 14:46:14 /www/nsone_firstmall_kr/admin/skin/default/statistic/statistic_main.html 000015675 */ 
$TPL_refer_loop_1=empty($TPL_VAR["refer_loop"])||!is_array($TPL_VAR["refer_loop"])?0:count($TPL_VAR["refer_loop"]);?>
<!--[if IE]><script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/jquery.jqplot.min.js"></script>
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/plugins/jqplot.barRenderer.min.js"></script>
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/plugins/jqplot.pointLabels.min.js"></script>
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
<script language="javascript" type="text/javascript" src="/app/javascript/plugin/jqplot/plugins/jqplot.donutRenderer.min.js"></script>
<link class="include" rel="stylesheet" type="text/css" href="/app/javascript/plugin/jqplot/jquery.jqplot.min.css" />
<script type="text/javascript">
function getAdvancedStatistic(addParams){
	var pageType = $("select[name='advanced_statistic'] option:selected").val();

	$.ajax({
		type: "get",
		url: "../statistic/advanced_statistic_sub",
		data: "pageType="+pageType+"&goods_seq=<?php echo $_GET["goods_seq"]?>"+addParams,
		success: function(result){
			$(".statistics_area").html(result);
<?php if($_GET["ispop"]!='pop'){?>
			$(".statistics_area").slideDown();
<?php }?>
		}
	});
}

// Chart 생성 함수
function createChart(chart_type, chart_id, maxValue, data, labelData, show_status)
{
	$("#"+chart_id).html('');

	if	(chart_type == 'round'){
		var animate		= {};
		var stackSeries	= false;
		var defaults	= {renderer: jQuery.jqplot.PieRenderer,
							rendererOptions: {showDataLabels: true,dataLabels: 'percent'}};
		var legend		= {show: show_status,location: 'e',placement: 'outside'};
		var grid		= {background: 'transparent',borderWidth: 0,shadow: false}
		var series		= {};
		var axes		= {};
	}else{
		var maxValue = maxValue;
		var gap = parseInt(maxValue.toString().substring(0,1)) < 2 ? Math.pow(10,maxValue.toString().length-2) : Math.pow(10,maxValue.toString().length-1);
		var yaxisMax = parseInt(maxValue.toString().substring(0,1)) < 2 ? gap * (parseInt(maxValue.toString().substring(0,2))+2) : gap * (parseInt(maxValue.toString().substring(0,1))+2);
		yaxisMax = yaxisMax > 100 ? yaxisMax : 100;

		if	(chart_type == 'stick'){
			var animate		= !$.jqplot.use_excanvas;
			var stackSeries	= false;
			var defaults	= { renderer:$.jqplot.BarRenderer,
								rendererOptions: {barMargin: 15,highlightMouseDown: true},
								pointLabels: {show: true},showMarker:true};
			var legend		= {show: show_status,location: 'e',placement: 'outside'};
			var axes		= {xaxis: {renderer: $.jqplot.CategoryAxisRenderer},
								yaxis: {adMin: 0}};
			var series		= labelData;
			var grid		= {drawGridLines: true,gridLineColor: '#dddddd',background: '#fffdf6',
								borderWidth: 0,shadow: false};
		}else{
			var animate		= {};
			var stackSeries	= false;
			var defaults	= { showMarker:true, pointLabels: { show:true }};
			var legend		= {show:show_status, location: 'e',xoffset: 15,yoffset: 15,placement: 'outside'};
			var axes		= {xaxis: {renderer: $.jqplot.CategoryAxisRenderer},
								yaxis: {min: 0,max: yaxisMax,numberTicks: 11}};
			var series		= labelData;
			var grid		= {drawGridLines: true,gridLineColor: '#dddddd',background: '#fffdf6',
								borderWidth: 0,shadow: false};
		}
	}
	
	if(chart_id != '' && data != ''){
		var plot = $.jqplot(chart_id, data, {
			animate: animate,
			stackSeries: stackSeries,
			seriesDefaults: defaults,
			seriesColors:<?php echo json_encode($TPL_VAR["seriesColors"])?>,
			series: series,
			legend: legend,
			axes: axes,
			grid:grid
		});
	}
}
</script>
<div class="advanced-statistic-main <?php echo $TPL_VAR["service_code"]?>" >
	<div class="sub-wrap" style="padding:0 7px; position:relative;">
<?php if(strstr($TPL_VAR["managerInfo"]["manager_auth"],"statistic_goods=N")){?>
		<div class="auth-for-graph">
			<div class="auth-for-graph-text">권한없음</div>
		</div>
<?php }?>
<?php if($TPL_VAR["advanced_statistic_limit"]=='y'){?>
		<div class="upgrade-for-free-popular"></div>
		<div class="upgrade-for-free-popular_btn">
			<img src="/admin/skin/default/images/common/btn_upgrade_free.png" style="cursor:pointer;" onclick="window.open('http://firstmall.kr/ec_hosting/addservice/frelocate.php?p=upgrade&s=P_FREE');" />
		</div>
<?php }?>
		<table cellspacing="0" cellpadding="0" border="0" class="stistic-data-table">
		<colgroup>
			<col width="25%" />
			<col width="25%" />
			<col width="25%" />
			<col width="25%" />
		</colgroup>
		<thead>
		<tr>
			<th>
				판매상품
				<div class="tcount"><a href="../statistic_sales/sales_goods"><img src="/admin/skin/default/images/main/btn_s_more.gif" /></a></div>
			</th>
			<th>
				장바구니
				<div class="tcount"><a href="../statistic_goods/goods_cart"><img src="/admin/skin/default/images/main/btn_s_more.gif" /></a></div>
			</th>
			<th>
				위시리스트
				<div class="tcount"><a href="../statistic_goods/goods_wish"><img src="/admin/skin/default/images/main/btn_s_more.gif" /></a></div>
			</th>
			<th>
				검색어
				<div class="tcount"><a href="../statistic_goods/goods_search"><img src="/admin/skin/default/images/main/btn_s_more.gif" /></a></div>
			</th>
		</tr>
		</thead>
		<tbody >
<?php if(is_array($TPL_R1=range( 0,count($TPL_VAR["rank_array"])- 1))&&!empty($TPL_R1)){$TPL_I1=-1;foreach($TPL_R1 as $TPL_V1){$TPL_I1++;?>
		<tr class="<?php echo $TPL_VAR["rank_array"][$TPL_I1]?>-tr">
<?php if(is_array($TPL_R2=$TPL_VAR["stat"]["rank"][$TPL_I1])&&!empty($TPL_R2)){$TPL_I2=-1;foreach($TPL_R2 as $TPL_V2){$TPL_I2++;?>
<?php if($TPL_I2> 0){?><td class="edge"><?php }else{?><td class="nleftline edge"><?php }?>
				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
<?php if($TPL_VAR["rank_array"][$TPL_I1]=='third'){?>
					<td class="today-rank-td" rowspan="2" width="35px">
						1위<br/>
						<img src="/admin/skin/default/images/main/bt_today.gif" />
					</td>
<?php }else{?>
					<td class="rank-td" rowspan="2" width="35px"><?php echo ($TPL_I1+ 1)?>위</td>
<?php }?>
					<td class="name-td">
<?php if($TPL_V2["keyword"]){?><?php echo htmlspecialchars($TPL_V2["keyword"])?><?php }else{?><a href="../goods/regist?no=<?php echo $TPL_V2["goods_seq"]?>"><?php echo getstrcut($TPL_V2["goods_name"], 15)?></a><?php }?>
					</td>
					<td class="image-td" rowspan="2" width="50px">
<?php if($TPL_V2["goods_seq"]){?>
						&nbsp;
						<a href="../goods/regist?no=<?php echo $TPL_V2["goods_seq"]?>"><img class="small_goods_image" src="<?php echo viewImg($TPL_V2["goods_seq"],'thumbView')?>" onerror="this.src='/data/icon/error/noimage_list.gif';" width="35px" height="35px" /></a>
<?php }?>
					</td>
				</tr>
				<tr>
					<td class="count-td">
<?php if(!empty($TPL_V2["price"])){?><?php echo number_format($TPL_V2["price"])?>원
<?php }elseif(!empty($TPL_V2["cnt"])){?><?php echo number_format($TPL_V2["cnt"])?>명<?php }?>
					</td>
				</tr>
				</table>
			</td>
<?php }}?>
		</tr>
<?php }}?>
		</tbody>
		</table>

		<!-- 그래프 영역 : START -->
		<div class="stistic-data-div">	
			<!-- 표그래프 영역 : START -->
			<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="33%">
<?php if(strstr($TPL_VAR["managerInfo"]["manager_auth"],"statistic_sales=N")){?>
					<div class="auth-for-graph1">
						<div class="auth-for-graph-text">권한없음</div>
					</div>
<?php }?>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title">매출</div>
						<div class="sub-chart-main-area">
							<div id="chart1" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td width="33%">
<?php if(strstr($TPL_VAR["managerInfo"]["manager_auth"],"statistic_member=N")){?>
					<div class="auth-for-graph2">
						<div class="auth-for-graph-text">권한없음</div>
					</div>
<?php }?>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title">회원</div>
						<div class="sub-chart-main-area">
							<div id="chart2" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td width="33%">
<?php if(strstr($TPL_VAR["managerInfo"]["manager_auth"],"statistic_visitor=N")){?>
					<div class="auth-for-graph3">
						<div class="auth-for-graph-text">권한없음</div>
					</div>
<?php }?>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title">방문</div>
						<div class="sub-chart-main-area">
							<div id="chart3" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
			</tr>
			<!--tr>
				<td style="position:relative;">
<?php if($TPL_VAR["advanced_statistic_limit"]=='y'){?>
					<div class="upgrade-for-free-graph_btn">
						<img src="/admin/skin/default/images/common/btn_upgrade_free.png" style="cursor:pointer;" onclick="window.open('http://firstmall.kr/ec_hosting/addservice/frelocate.php?p=upgrade&s=P_FREE');" />
					</div>
<?php }?>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title2">매출 유입</div>
						<div class="sub-chart-main-area">
							<div id="chart4" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title2">회원 유입</div>
						<div class="sub-chart-main-area">
							<div id="chart5" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td>
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title2">방문 유입</div>
						<div class="sub-chart-main-area">
							<div id="chart6" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
			</tr-->
			</table>
			<!-- 표그래프 영역 : END -->

			<!-- 그래프하단 추가 영역 : START -->
			<div style="width:810px; height:174px; margin:0 auto; border-top:1px solid #dedede;">
<?php if(strstr($TPL_VAR["managerInfo"]["manager_auth"],"statistic_goods=N")){?>
				<div class="auth-for-graph4">
					<div class="auth-for-graph-text">권한없음</div>
				</div>
<?php }?>
<?php if($TPL_VAR["advanced_statistic_limit"]=='y'){?>
				<div class="upgrade-for-free-graph"></div>				
				<div class="upgrade-for-free-graph_btn">
					<img src="/admin/skin/default/images/common/btn_upgrade_free.png" style="cursor:pointer;" onclick="window.open('http://firstmall.kr/ec_hosting/addservice/frelocate.php?p=upgrade&s=P_FREE');" />
				</div>
<?php }?>
				<table cellpadding="0" cellspacing="0" width="100%" >
				<tr>
					<td width="289px">
						<!-- 원그래프 영역 -->
						<div id="chart4" class="sub-chart-main" style="height:190px;"></div>
					</td>
					<td>
						<!-- 표 차트 영역 -->
						<div class="sub-chart-main">
							<table cellpadding="0" cellspacing="0" width="100%" border="0" class="tb_referer">
							<colgroup>
								<col width="7%" />
								<col width="13%" />
								<col width="13%" />
								<col width="3%" />
								<col />
							</colgroup>
							<tr>
								<th colspan="5">매출 상위 유입경로 (최근 10일 기준)</th>
							</tr>
<?php if($TPL_VAR["refer_loop"]){?>
<?php if($TPL_refer_loop_1){foreach($TPL_VAR["refer_loop"] as $TPL_V1){?>
							<tr>
								<td><b><?php echo $TPL_V1["rank"]?>위.</b></td>
								<td><?php echo $TPL_V1["referer_name"]?></td>
								<td align="right"><b><?php echo round((($TPL_V1["cnt"]/$TPL_VAR["refer_data"]["total"])* 100), 1)?>%</b></td>
								<td>&nbsp;</td>
								<td align="left"><?php if($TPL_V1["referer_url"]!='0'){?><a href="<?php echo $TPL_V1["referer_url"]?>" target="_blank"><?php echo getstrcut($TPL_V1["referer_url"], 50,'...')?></a><?php }?></td>
							</tr>
<?php }}?>
<?php }else{?>
							<tr>
								<td colspan="5">데이터가 없습니다.</td>
							</tr>
<?php }?>
							</table>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<!-- 그래프하단 추가 영역 : END -->
		</div>
		<!-- 그래프 영역 : END -->

	</div>
</div>

<script class="code" type="text/javascript">
$(document).ready(function(){
	var sleepTime	= 10;
	if	($.browser.msie){
		if	($.browser.version < 9)
			sleepTime	= 1000;
	}

	setTimeout('loadMainGraph()', sleepTime);
});

function loadMainGraph(){

	var data	= [];
	var label	= [];

	// 매출
<?php if(count($TPL_VAR["dataForChart"]['매출'])> 0){?>
	data	= [<?php echo json_encode($TPL_VAR["dataForChart"]['매출'])?>];
	label	= [{'label':'구매금액'}];
	createChart('line', 'chart1', '<?php echo $TPL_VAR["maxValue"]['매출']?>', data, label, false);
<?php }?>

	// 회원
<?php if(count($TPL_VAR["dataForChart"]['회원'])> 0){?>
	data	= [<?php echo json_encode($TPL_VAR["dataForChart"]['회원'])?>];
	label	= [{'label':'가입수'}];
	createChart('line', 'chart2', '<?php echo $TPL_VAR["maxValue"]['회원']?>', data, label, false);
<?php }?>

	// 방문
<?php if(count($TPL_VAR["dataForChart"]['방문'])> 0){?>
	data	= [<?php echo json_encode($TPL_VAR["dataForChart"]['방문'])?>];
	label	= [{'label':'방문수'}];
	createChart('line', 'chart3', '<?php echo $TPL_VAR["maxValue"]['방문']?>', data, label, false);
<?php }?>

	// 매출유입경로
<?php if(count($TPL_VAR["refer_data"])> 0){?>
	data	= [<?php echo json_encode($TPL_VAR["refer_data"]["data"])?>];
	label	= [<?php echo json_encode($TPL_VAR["refer_data"]["label"])?>];
	createChart('round', 'chart4', '<?php echo $TPL_VAR["refer_data"]["total"]?>', data, label, false);
<?php }?>
	

	/*
	// 매출유입경로
	data	= [];
	label	= [];
<?php if(count($TPL_VAR["dataForChart"]['매출유입경로'])> 0){?>
<?php if(is_array($TPL_R1=$TPL_VAR["dataForChart"]['매출유입경로'])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
	data.push(<?php echo json_encode($TPL_V1)?>);
	label.push({'label':'<?php echo $TPL_K1?>'});
<?php }}?>
	createChart('line', 'chart4', '<?php echo $TPL_VAR["maxValue"]['매출유입경로']?>', data, label, false);
<?php }?>

	// 회원유입경로
	data	= [];
	label	= [];
<?php if(count($TPL_VAR["dataForChart"]['회원유입경로'])> 0){?>
<?php if(is_array($TPL_R1=$TPL_VAR["dataForChart"]['회원유입경로'])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
	data.push(<?php echo json_encode($TPL_V1)?>);
	label.push({'label':'<?php echo $TPL_K1?>'});
<?php }}?>
	createChart('line', 'chart5', '<?php echo $TPL_VAR["maxValue"]['회원유입경로']?>', data, label, false);
<?php }?>

	// 방문유입경로
	data	= [];
	label	= [];
<?php if(count($TPL_VAR["dataForChart"]['방문유입경로'])> 0){?>
<?php if(is_array($TPL_R1=$TPL_VAR["dataForChart"]['방문유입경로'])&&!empty($TPL_R1)){foreach($TPL_R1 as $TPL_K1=>$TPL_V1){?>
	data.push(<?php echo json_encode($TPL_V1)?>);
	label.push({'label':'<?php echo $TPL_K1?>'});
<?php }}?>
	createChart('line', 'chart6', '<?php echo $TPL_VAR["maxValue"]['방문유입경로']?>', data, label, false);
<?php }?>

	*/

}
</script>