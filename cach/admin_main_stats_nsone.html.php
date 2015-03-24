<?php /* Template_ 2.2.6 2015/03/24 13:37:19 /www/nsone_firstmall_kr/data/cach/admin_main_stats_nsone.html 000015572 */ ?>
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
		data: "pageType="+pageType+"&goods_seq="+addParams,
		success: function(result){
			$(".statistics_area").html(result);
			$(".statistics_area").slideDown();
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
			seriesColors:null,
			series: series,
			legend: legend,
			axes: axes,
			grid:grid
		});
	}
}
</script>
<div class="advanced-statistic-main P_EXPA" >
	<div class="sub-wrap" style="padding:0 7px; position:relative;">
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
		<tr class="first-tr">
<td class="nleftline edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">1위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">1위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">1위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">1위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr class="second-tr">
<td class="nleftline edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">2위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">2위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">2위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="rank-td" rowspan="2" width="35px">2위</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr class="third-tr">
<td class="nleftline edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="today-rank-td" rowspan="2" width="35px">
						1위<br/>
						<img src="/admin/skin/default/images/main/bt_today.gif" />
					</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="today-rank-td" rowspan="2" width="35px">
						1위<br/>
						<img src="/admin/skin/default/images/main/bt_today.gif" />
					</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="today-rank-td" rowspan="2" width="35px">
						1위<br/>
						<img src="/admin/skin/default/images/main/bt_today.gif" />
					</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
<td class="edge">				<table cellspacing="0" cellpadding="0" border="0" class="lank-table category main">
				<tr>
					<td class="today-rank-td" rowspan="2" width="35px">
						1위<br/>
						<img src="/admin/skin/default/images/main/bt_today.gif" />
					</td>
					<td class="name-td">
<a href="../goods/regist?no="></a>					</td>
					<td class="image-td" rowspan="2" width="50px">
					</td>
				</tr>
				<tr>
					<td class="count-td">
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</tbody>
		</table>

		<!-- 그래프 영역 : START -->
		<div class="stistic-data-div">	
			<!-- 표그래프 영역 : START -->
			<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="33%">
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title">매출</div>
						<div class="sub-chart-main-area">
							<div id="chart1" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td width="33%">
					<div class="sub-chart-main-box">
						<div class="sub-chart-main-title">회원</div>
						<div class="sub-chart-main-area">
							<div id="chart2" class="sub-chart-main"></div>
						</div>
					</div>
				</td>
				<td width="33%">
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
							<tr>
								<td colspan="5">데이터가 없습니다.</td>
							</tr>
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
	data	= [[["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c",0]]];
	label	= [{'label':'구매금액'}];
	createChart('line', 'chart1', '', data, label, false);

	// 회원
	data	= [[["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c",0]]];
	label	= [{'label':'가입수'}];
	createChart('line', 'chart2', '', data, label, false);

	// 방문
	data	= [[["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c","2"]]];
	label	= [{'label':'방문수'}];
	createChart('line', 'chart3', '2', data, label, false);

	// 매출유입경로
	data	= [null];
	label	= [null];
	createChart('round', 'chart4', '0', data, label, false);
	

	/*
	// 매출유입경로
	data	= [];
	label	= [];
	data.push([["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c",0]]);
	label.push({'label':'no_data'});
	createChart('line', 'chart4', '', data, label, false);

	// 회원유입경로
	data	= [];
	label	= [];
	data.push([["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c",0]]);
	label.push({'label':'no_data'});
	createChart('line', 'chart5', '', data, label, false);

	// 방문유입경로
	data	= [];
	label	= [];
	data.push([["15\uc77c",0],["16\uc77c",0],["17\uc77c",0],["18\uc77c",0],["19\uc77c",0],["20\uc77c",0],["21\uc77c",0],["22\uc77c",0],["23\uc77c",0],["24\uc77c",45]]);
	label.push({'label':'직접입력'});
	createChart('line', 'chart6', '45', data, label, false);

	*/

}
</script><!-- End Cach Page 2015-03-24 13:37:19 -->