// x:위도, y:경도, width:맵가로사이즈, height:맵세로사이즈, title_txt:라벨
function callMap(id_name,x,y,width,height,title_txt){
	if(width.search(/[^0-9]/) != -1)	width	= 700;
	if(height.search(/[^0-9]/) != -1)	height	= 300;
	var oPoint = new nhn.api.map.LatLng(x, y);
	var defaultLevel = 11;
	nhn.api.map.setDefaultPoint('LatLng');

	var oMap = new nhn.api.map.Map(id_name, {
						point : oPoint,
						zoom : defaultLevel,
						enableWheelZoom : true,
						enableDragPan : true,
						enableDblClickZoom : false,
						mapMode : 0,
						activateTrafficMap : false,
						activateBicycleMap : false,
						minMaxLevel : [ 1, 14 ],
						size : new nhn.api.map.Size(width, height)
	});
	
	var oSlider = new nhn.api.map.ZoomControl();
	oMap.addControl(oSlider);
	oSlider.setPosition({
			top : 10,
			left : 10
	});

	var oMapTypeBtn = new nhn.api.map.MapTypeBtn();
	oMap.addControl(oMapTypeBtn);
	oMapTypeBtn.setPosition({
		bottom : 10,
		right : 80
	});
	
	var oThemeMapBtn = new nhn.api.map.ThemeMapBtn();
	oThemeMapBtn.setPosition({
		bottom : 10,
		right : 10
	});
	oMap.addControl(oThemeMapBtn);
	
	var oSize = new nhn.api.map.Size(28, 37);
	var oOffset = new nhn.api.map.Size(14, 37);
	var oIcon = new nhn.api.map.Icon('http://static.naver.com/maps2/icons/pin_spot2.png', oSize, oOffset);
	var oMarker = new nhn.api.map.Marker(oIcon,{
		title : title_txt
	});
	oMarker.setPoint(oPoint);
	oMap.addOverlay(oMarker);
		
	

	var oInfoWnd = new nhn.api.map.InfoWindow();
	oInfoWnd.setVisible(false);
	oMap.addOverlay(oInfoWnd);

	oInfoWnd.setPosition({
		top : 20,
		left :20
	});
	
	if (title_txt != "") {
		var oLabel = new nhn.api.map.MarkerLabel({detectCoveredMarker : true});
		oMap.addOverlay(oLabel);
		oLabel.setVisible(true, oMarker);
	}		
}