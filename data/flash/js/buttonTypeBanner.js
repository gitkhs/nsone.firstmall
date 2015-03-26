var btnTypeBanner = {};
	
	btnTypeBanner.banner = function(id, instance, items, width, height, menuPosH, menuMarH, menuPosV, menuMarV, autoPlay, titleURL, titleH, menuType, menuAlign, menuGap)
	{
		
		this.id 			= id;
		this.instance 		= instance;
		this.items 			= items;		
		this.width 			= width;
		this.height 		= height;
		this.totalNum		= this.items.length;		
		this.menuPosH 		= menuPosH;
		this.menuMarH		= menuMarH;
		this.menuPosV		= menuPosV;
		this.menuMarV		= menuMarV;
		this.menuAlign		= menuAlign;
		this.autoPlayTime 	= 4000;
		this.motionTime 	= 700;
		this.currentNum		= 0;
		this.tmpNum			= this.items.length;
		this.autoPlay		= autoPlay;
		this.menuGap		= menuGap;
		this.titleURL		= titleURL;
		this.titleH		    = titleH;
		this.visualX		= 0;
		this.visualY		= 0;
		this.menuType		= menuType;
		
		//alert(this.id)
	}
	
	btnTypeBanner.banner.prototype = 
	{
		make : function()
		{
			var str = '';
			str += '<div style = "width : ' + this.width + 'px; height : ' + this.height + 'px; overflow:hidden; position:relative;">';
			str += '<div style = "width : ' + this.width + 'px; height : ' + this.height + 'px; left:0x;">';
			
			str += this.menuHTML();	
			
			if(this.titleURL != "")
			{			
				str += '<img src = "' + this.titleURL + '" style = "position:absolute;" border = "0">'		
				str += '<div style="top:' + this.titleH + 'px; position:relative; ">';
			}
			
			str += this.visualHTML();
			
			if(this.titleURL != "") str += '</div>'
			
			str += '</div>'
			str += '</div>'
			
			$("#"+this.id).html(str);
			
			this.setManuVisual(this.currentNum);
			this.startTimer();
		},
		
		//메뉴 화면에 세팅
		menuHTML : function()
		{
			var i;
			var menuX;
			var menuY;
			var menuBoxW = 0;
			var menuBoxH = 0;	
			
			for(i = 0; i < this.totalNum; i++)
			{
				var obj = this.items[i];
				menuBoxW += obj.menuW + this.menuGap;
				menuBoxH += obj.menuH + this.menuGap;					
			}
			
			if(this.menuAlign=="H")
			{			
				menuBoxH = this.items[0].menuH;
				menuBoxW -= this.menuGap;
			}else{
				menuBoxW = this.items[0].menuW;	
				menuBoxH -= this.menuGap;
			}				
			
			switch(this.menuPosH)
			{				
				case "L":
					menuX = 0 + this.menuMarH;
					
					if(this.menuType == "IMG") 
					{
						if (this.menuAlign == "V") this.visualX = menuX + this.items[0].menuW;						
					}				
				break;
				
				case "C":
					menuX = (this.width - menuBoxW) / 2;
				break;
				
				case "R":
					menuX = this.width - menuBoxW - this.menuMarH;
				break;				
			}
			

			switch(this.menuPosV)
			{
				case "T":
					if(this.titleURL != "")					
						menuY = this.titleH + this.menuMarV;
					else 
						menuY = this.menuMarV;					
					
					if(this.menuType == "IMG")
					{
						if (this.menuAlign == "H") this.visualY = 0					
					}
					else 
					{
						if(this.titleURL != "") this.visualY = 0;				
					}
					
				break;
				
				case "B":
					menuY = this.height - menuBoxH - this.menuMarV;					
					if(this.titleURL != "") this.visualY = 0;	
					
				break;							
			}				
			
			var str = '<div style="position:absolute;z-index:10000; top:'+menuY+'px; left:'+menuX+'px">';
			var pos = 0;
			
			for(var i = 0; i<this.totalNum; i++)
			{
				var obj = this.items[i];				
				var menuId = this.id + "menu" + i;
				var menuOffId = this.id + "menuOff" + i;	
				var menuOnId = this.id + "menuOn" + i;					
				
				if(this.menuAlign == "H")
				{
					str += '<div id= "'+ menuId +'" style = "position:absolute; left:' + pos +'px; cursor:hand; float:left;"';
					str += 'onclick = "' + this.instance + '.menuClick(' + i + ');" ';
					str += 'onmouseover = "' + this.instance + '.menuOver();" ';
					str += 'onmouseout = "' + this.instance + '.menuOut();">'					
					pos += obj.menuW + this.menuGap;
				}
				else
				{
					str += '<div id= "'+ menuId +'" style = "position:absolute; top:' + pos +'px; cursor:hand; float:top;"';
					str += 'onclick = "' + this.instance + '.menuClick(' + i + ');" ';
					str += 'onmouseover = "' + this.instance + '.menuOver();" ';
					str += 'onmouseout = "' + this.instance + '.menuOut();">'
					pos += obj.menuH + this.menuGap;
				}
				str += '<div id= "' + menuOnId + '" style = "position:absolute; z-index:99;">';
				str += '<img src = "' + obj.menuOn + '" style = "position:absolute;" border = "0">'	
				str += '</div>';
				str += '<div id= "' + menuOffId + '" style = "position:absolute; z-index:100;">';
				str += '<img src = "' + obj.menuOff + '" border = "0">'				
				str += '</div>';				
				str += '</div>';				
			}
			
			str += '</div>';
			
			return str;			
		},
		
		//비주얼 화면 세팅
		visualHTML : function()
		{
			var str = '';
			var obj;
			var visualId;
			
			for(var i = 0; i < this.totalNum; i++)
			{
				obj 		= this.items[i];
				visualId 	= this.id + "visual" + i;
			
				str += '<div id = "' + visualId + '" style = "position:absolute; z-index:'+ (300) + '; cursor:hand; top:-2000" >';
				str += '<img src = "' + obj.visual + '" ';
				str += 'onclick = "' + this.instance + '.visualClick(' + i + ');" ';
				str += 'onmouseover = "' + this.instance + '.visualOver();" ';
				str += 'onmouseout = "' + this.instance + '.visualOut();" border = "0"> ';
				str += '</div>';
			}
			
			return str;
		},
		
		//메뉴 마우스 이멘트
		menuOver : function()
		{	
			this.stopTimer();
		},
		
		menuOut : function()
		{
			this.startTimer();			
		},
		
		menuClick : function(n)
		{
			this.setManuVisual(n)	
		},
		
		//비주얼 마우스 이벤트
		visualOver : function()
		{	
			this.stopTimer();
		},
		
		visualOut : function()
		{
			this.startTimer();
		},
		
		//페이지 이동
		visualClick  : function(n)
		{	
			var obj = this.items[n];
			
			if(obj.target == "_blank")			
				window.open(obj.link);
			else
				document.location = obj.link;		
		},	
		
		//자동플레이 설정
		startTimer : function()
		{
			if(this.autoPlay)
			{
				var _this = this;
				
				if(this.autoPlayTime > 0) this.intervalId = setInterval( function(){_this.motionPlay.call(_this)}, this.autoPlayTime);				
			}			
		},
		
		//자동플레이 설정 해지
		stopTimer : function()
		{
			if(this.autoPlay)
			{
				if(this.autoPlayTime > 0) clearInterval(this.intervalId);
			}			
		},
		
		motionPlay : function()
		{
			if(++this.currentNum > this.totalNum - 1) this.currentNum = 0;
			
			this.setManuVisual(this.currentNum);
		},
		
		//페이드 인, 메이드아웃 모션 
		setManuVisual : function(n)
		{
			this.currentNum = n;
			
			id 			= this.id;
			currentNum 	= this.currentNum;
			tmpNum 		= this.tmpNum;
			totalNum 	= this.totalNum;		
			
			if(this.currentNum != this.tmpNum)
			{				
				$("#" + this.id + "menuOff" + this.currentNum).fadeOut(300, 0);
				//$("#" + this.id + "menuOn" + this.currentNum).fadeIn(300, 0);				
				
				$("#" + this.id + "visual" + this.currentNum).css({'top':this.visualY, 'left':this.visualX, 'opacity':0}).animate({"opacity":1}, {deurantion:this.motionTime, complete:function()
				{
					if(tmpNum != totalNum)
					{						
						$(this).css({'position':'absolute', 'z-index' : 200});						
						$("#" + id + "visual" + tmpNum).css({'top': -2000, 'position':'absolute', 'z-index':300});						
						tmpNum = currentNum;											
					}					
				}});
				
				if(this.temp != this.totalNum)
				{					
					$("#" + this.id + "menuOff" + this.tmpNum).fadeIn(300, 0);
					//$("#" + this.id + "menuOn" + this.tmpNum).fadeOut(300, 0);
					
					this.tmpNum = currentNum ;										
				}				
			}					
		}		
	}