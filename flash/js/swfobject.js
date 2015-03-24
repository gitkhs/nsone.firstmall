/**
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
if(typeof deconcept == "undefined") var deconcept = new Object();
if(typeof deconcept.util == "undefined") deconcept.util = new Object();
if(typeof deconcept.SWFObjectUtil == "undefined") deconcept.SWFObjectUtil = new Object();
deconcept.SWFObject = function(swf, id, w, h, ver, c, quality, xiRedirectUrl, redirectUrl, detectKey) {
	if (!document.getElementById) { return; }
	this.DETECT_KEY = detectKey ? detectKey : 'detectflash';
	//this.skipDetect = deconcept.util.getRequestParameter(this.DETECT_KEY);
	this.skipDetect = true;
	this.params = new Object();
	this.variables = new Object();
	this.attributes = new Array();
	if(swf) { this.setAttribute('swf', swf); }
	if(id) { this.setAttribute('id', id); }
	if(w) { this.setAttribute('width', w); }
	if(h) { this.setAttribute('height', h); }
	if(ver) { this.setAttribute('version', new deconcept.PlayerVersion(ver.toString().split("."))); }
	this.installedVer = deconcept.SWFObjectUtil.getPlayerVersion();
	if (!window.opera && document.all && this.installedVer.major > 7) {
		// only add the onunload cleanup if the Flash Player version supports External Interface and we are in IE
		deconcept.SWFObject.doPrepUnload = true;
	}
	if(c) { this.addParam('bgcolor', c); }
	var q = quality ? quality : 'high';
	this.addParam('quality', q);
	this.setAttribute('useExpressInstall', false);
	this.setAttribute('doExpressInstall', false);
	var xir = (xiRedirectUrl) ? xiRedirectUrl : window.location;
	this.setAttribute('xiRedirectUrl', xir);
	this.setAttribute('redirectUrl', '');
	if(redirectUrl) { this.setAttribute('redirectUrl', redirectUrl); }
}
deconcept.SWFObject.prototype = {

	useExpressInstall: function(path) {
		this.xiSWFPath = !path ? "expressinstall.swf" : path;
		this.setAttribute('useExpressInstall', true);
	},
	setAttribute: function(name, value){
		this.attributes[name] = value;
	},
	getAttribute: function(name){
		return this.attributes[name];
	},
	addParam: function(name, value){
		this.params[name] = value;
	},
	getParams: function(){
		return this.params;
	},
	addVariable: function(name, value){
		this.variables[name] = value;
	},
	getVariable: function(name){
		return this.variables[name];
	},
	getVariables: function(){
		return this.variables;
	},
	getVariablePairs: function(){
		var variablePairs = new Array();
		var key;
		var variables = this.getVariables();
		for(key in variables){
			variablePairs[variablePairs.length] = key +"="+ variables[key];
		}
		return variablePairs;
	},
	getSWFHTML: function() {
		var swfNode = "";
		if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) { // netscape plugin architecture
			if (this.getAttribute("doExpressInstall")) {
				this.addVariable("MMplayerType", "PlugIn");
				this.setAttribute('swf', this.xiSWFPath);
			}
			swfNode = '<object id="'+ this.getAttribute('id') + 'name="'+ this.getAttribute('id') + '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'">';
			swfNode += '<param name="movie" value="'+ this.getAttribute('swf') +'" />';
			var params = this.getParams();
			for(var key in params) {
			 swfNode += '<param name="'+ key +'" value="'+ params[key] +'" />';
			}
			//swfNode += '<param name="wmode" value="transparent" />';
			swfNode += '<param name="menu" value="false"/>';
			swfNode += '<param name="salign" value="LT"/>';
			swfNode += '<param name="scale" value="noscale"/>';			
			swfNode += '<param name="allowScriptAccess" value="sameDomain"/>';
			
			var pairs = this.getVariablePairs().join("&");
			if(pairs.length > 0) {swfNode += '<param name="flashvars" value="'+ pairs +'" />';}
			
			//swfNode = '<embed type="application/x-shockwave-flash" wmode="transparent" src="'+ this.getAttribute('swf') +'" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'" style="'+ this.getAttribute('style') +'"';
			swfNode += '<embed type="application/x-shockwave-flash" src="'+ this.getAttribute('swf') +'" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'"';
			swfNode += ' id="'+ this.getAttribute('id') +'" name="'+ this.getAttribute('id') +'" ';
			var params = this.getParams();
			 for(var key in params){ 
				if( key !== "wmode" ) {
					swfNode += [key] +'="'+ params[key] +'" '; 
				}
			}
			var pairs = this.getVariablePairs().join("&");
			 if (pairs.length > 0){ swfNode += 'flashvars="'+ pairs +'"'; }
			swfNode += '></embed>';
			
			swfNode += "</object>";
		} else { // PC IE
			if (this.getAttribute("doExpressInstall")) {
				this.addVariable("MMplayerType", "ActiveX");
				this.setAttribute('swf', this.xiSWFPath);
			}
			//swfNode = '<object id="'+ this.getAttribute('id') +'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'" style="'+ this.getAttribute('style') +'">';
			swfNode = '<object id="'+ this.getAttribute('id') + 'name="'+ this.getAttribute('id') + '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'">';
			swfNode += '<param name="movie" value="'+ this.getAttribute('swf') +'" />';
			var params = this.getParams();
			for(var key in params) {
			 swfNode += '<param name="'+ key +'" value="'+ params[key] +'" />';
			}
			//swfNode += '<param name="wmode" value="transparent" />';
			swfNode += '<param name="menu" value="false"/>';
			swfNode += '<param name="salign" value="LT"/>';
			swfNode += '<param name="scale" value="noscale"/>';
			swfNode += '<param name="wmode" value="transparent"/>';
			swfNode += '<param name="allowScriptAccess" value="sameDomain"/>';
			
			var pairs = this.getVariablePairs().join("&");
			if(pairs.length > 0) {swfNode += '<param name="flashvars" value="'+ pairs +'" />';}
			//swfNode += "</object>";
			
			swfNode += '<embed type="application/x-shockwave-flash" src="'+ this.getAttribute('swf') +'" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'"';
			swfNode += ' id="'+ this.getAttribute('id') +'" name="'+ this.getAttribute('id') +'" ';
			var params = this.getParams();
			 for(var key in params){ 
				if( key !== "wmode" ) {
					swfNode += [key] +'="'+ params[key] +'" '; 
				}
			}
			var pairs = this.getVariablePairs().join("&");
			 if (pairs.length > 0){ swfNode += 'flashvars="'+ pairs +'"'; }
			swfNode += '></embed>';
			
			swfNode += "</object>";
			
			/*swfNode += '<noscript>';
			swfNode += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,16,0" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'">';
			swfNode += '<param name="movie" value="'+ this.getAttribute('swf') +'"/>';
			swfNode += '<param name="allowScriptAccess" value="always" />';
			swfNode += '<param name="quality" value="high" />';
			swfNode += '<param name="wmode"  value="transparent" />';
			swfNode += '<embed src="'+ this.getAttribute('swf') +'"></embed>';
			swfNode += '</object>';
			swfNode += '</noscript>';*/
		}
		
		//alert(swfNode);
		return swfNode;
	},
	write: function(elementId){
		document.write(this.getSWFHTML());
		//document.getElementById(elementId).innerHTML = this.getSWFHTML();
		
		/*if(this.getAttribute('useExpressInstall')) {
			// check to see if we need to do an express install
			var expressInstallReqVer = new deconcept.PlayerVersion([6,0,65]);
			if (this.installedVer.versionIsValid(expressInstallReqVer) && !this.installedVer.versionIsValid(this.getAttribute('version'))) {
				this.setAttribute('doExpressInstall', true);
				this.addVariable("MMredirectURL", escape(this.getAttribute('xiRedirectUrl')));
				document.title = document.title.slice(0, 47) + " - Flash Player Installation";
				this.addVariable("MMdoctitle", document.title);
			}
		}
		if(this.skipDetect || this.getAttribute('doExpressInstall') || this.installedVer.versionIsValid(this.getAttribute('version'))){
			try {
				var n = (typeof elementId == 'string') ? document.getElementById(elementId) : elementId;
				n.innerHTML = this.getSWFHTML();
				return true;
			} catch (e) { }
		}else{
			if(this.getAttribute('redirectUrl') != "") {
				document.location.replace(this.getAttribute('redirectUrl'));
			}
		}*/
		return false;
	}
}

/* ---- detection functions ---- */
deconcept.SWFObjectUtil.getPlayerVersion = function(){
	var PlayerVersion = new deconcept.PlayerVersion([0,0,0]);
	if(navigator.plugins && navigator.mimeTypes.length){
		var x = navigator.plugins["Shockwave Flash"];
		if(x && x.description) {
			PlayerVersion = new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/, "").replace(/(\s+r|\s+b[0-9]+)/, ".").split("."));
		}
	}else if (navigator.userAgent && navigator.userAgent.indexOf("Windows CE") >= 0){ // if Windows CE
		var axo = 1;
		var counter = 3;
		while(axo) {
			try {
				counter++;
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+ counter);
//				document.write("player v: "+ counter);
				PlayerVersion = new deconcept.PlayerVersion([counter,0,0]);
			} catch (e) {
				axo = null;
			}
		}
	} else { // Win IE (non mobile)
		// do minor version lookup in IE, but avoid fp6 crashing issues
		// see http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
		try{
			 var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.9");
		}catch(e){
			try{
				var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
			}catch(e){
				try {
					var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
					PlayerVersion = new deconcept.PlayerVersion([6,0,21]);
					axo.AllowScriptAccess = "always"; // error if player version < 6.0.47 (thanks to Michael Williams @ Adobe for this code)
				} catch(e) {
					if (PlayerVersion.major == 6) {
						return PlayerVersion;
					}
				}
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
				} catch(e) {}
			}
		}
		if (axo != null) {
			PlayerVersion = new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));
		}
	}
	return PlayerVersion;
}
deconcept.PlayerVersion = function(arrVersion){
	this.major = arrVersion[0] != null ? parseInt(arrVersion[0]) : 0;
	this.minor = arrVersion[1] != null ? parseInt(arrVersion[1]) : 0;
	this.rev = arrVersion[2] != null ? parseInt(arrVersion[2]) : 0;
}
deconcept.PlayerVersion.prototype.versionIsValid = function(fv){
	if(this.major < fv.major) return false;
	if(this.major > fv.major) return true;
	if(this.minor < fv.minor) return false;
	if(this.minor > fv.minor) return true;
	if(this.rev < fv.rev) return false;
	return true;
}
/* ---- get value of query string param ---- */
deconcept.util = {
	getRequestParameter: function(param) {
		var q = document.location.search || document.location.hash;
		if (param == null) { return q; }
		if(q) {
			var pairs = q.substring(1).split("&");
			for (var i=0; i < pairs.length; i++) {
				if (pairs[i].substring(0, pairs[i].indexOf("=")) == param) {
					return pairs[i].substring((pairs[i].indexOf("=")+1));
				}
			}
		}
		return "";
	}
}
/* fix for video streaming bug */
/*
deconcept.SWFObjectUtil.cleanupSWFs = function() {
	var objects = document.getElementsByTagName("OBJECT");
	for (var i = objects.length - 1; i >= 0; i--) {
		objects[i].style.display = 'none';
		for (var x in objects[i]) {
			if (typeof objects[i][x] == 'function') {
				objects[i][x] = function(){};
			}
		}
	}
}
*/

deconcept.SWFObjectUtil.cleanupSWFs = function() {
	try{
			if (window.opera || !document.all) return;
			//var objects = document.getElementsByTagName("OBJECT");
			//for (var i=0; i < objects.length; i++) {
				//objects[i].style.display = 'none';
//				for (var x in objects[i]) {
	//				if (typeof objects[i][x] == 'function') {
		//				objects[i][x] = function(){};
			//		}
				//}
			//}
			for (var i=0,swfLen=deconcept.hasFuncSwfs.length; i < swfLen; i++) {
				for (var x in deconcept.hasFuncSwfs[i]) {
					if (typeof deconcept.hasFuncSwfs[i][x] == 'function') {
						deconcept.hasFuncSwfs[i][x] = function(){};
					}
				}
			}
		}catch(e){
		}
	}


// fixes bug in some fp9 versions see http://blog.deconcept.com/2006/07/28/swfobject-143-released/
if (deconcept.SWFObject.doPrepUnload) {
	if (!deconcept.unloadSet) {
		deconcept.SWFObjectUtil.prepUnload = function() {
			__flash_unloadHandler = function(){};
			__flash_savedUnloadHandler = function(){};
			window.attachEvent("onunload", deconcept.SWFObjectUtil.cleanupSWFs);
		}
		window.attachEvent("onbeforeunload", deconcept.SWFObjectUtil.prepUnload);
		deconcept.unloadSet = true;
	}
}
/* add document.getElementById if needed (mobile IE < 5) */
if (!document.getElementById && document.all) { document.getElementById = function(id) { return document.all[id]; }}

/* add some aliases for ease of use/backwards compatibility */
var getQueryParamValue = deconcept.util.getRequestParameter;
var FlashObject = deconcept.SWFObject; // for legacy support
var SWFObject = deconcept.SWFObject;

/* flashcommon.js ****************************************************************/
function FF_changeFlashSize(movieid, w, h)
{
	setDivSize(movieid, w,h);

    var swfmovie = document.getElementById(movieid);

	if(swfmovie)
	{
		if(w != null)
			swfmovie.setAttribute("width", w);

		if(h != null)
			swfmovie.setAttribute("height", h);
	}
}

function FF_gotoPage(pageUrl, target)
{
	// 해당 pageUrl 경로로 target 이동합니다.
	if(target == "_blank"){
		window.open(pageUrl);
	}else if(target == "_top") {
		top.location.href = pageUrl;
	}else{
		location.href = pageUrl;
	}
}

function FF_setCookie(key, value)
{
	if(value == null || value == "null")
		value = "";
	setCookie(key, value);
}

function FF_getCookie(key)
{
	return getCookie(key);
}

function FF_clearCookies()
{
	setCookie("f_movieCode", "");
	setCookie("f_cinemaCode", "");
	setCookie("f_cinemaTypeCode", "");
	setCookie("f_screenCode", "");
	setCookie("f_playDate", "");
	setCookie("f_playNo", "");
}