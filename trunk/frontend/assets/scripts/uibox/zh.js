/**
 * @author chenzx
 * @version 0.1
 * 日志从这里开始，上线前，该日志暂时保留。日志格式：日期+空格+修订者+空格+修改内容
 * 08.6.6 KCZ 添加注释
 * 08.6.16 KCZ path改为url
 * 08.6.19 KCZ 增加obj保留字。{Object} obj 返回的对象
 * 08.6.20 chenzx 动态更改domain，动态添加跨域的iframe
 * 08.6.20 KCZ 将json2Obj方法在异常情况下的返回值由null改为{}
 * 08.6.20 KCZ 增加siblingNode方法，将组件调用autoRun方法时的传入结点由<code>的父结点改为<code>的上一个兄弟结点
 * 08.7.08 chenzx 增加sendRequest方法
 * 08.7.09 KCZ 将loadJsonP、loadJsonPCall和sendRequest三个方法统一为loadJsonP
 * 08.7.24 chenzx 修改runCode和autoRun，修正js加载完成后callback的问题。
 */
if (typeof ZHJS == "undefined") {
	document.write("<style>.uibox{display:none;}</style>");
	var ZHJS = {
		xBugLog:[],
		sVersion: "v=20080606",
		dStartTime: new Date(),
		sUiboxDir: "/assets/scripts/uibox/",
		oJsUrl: {},
		oFiles: {},
		oFrames: {},
		iID: 0,
		bDomReady: false,
		/**
		 * @param {String} as
		 * @return {Object} DOM元素
		 */
		$: function(as){
			return document.getElementById(as);
		},
		isIE: (function(){
			var lsv = navigator.userAgent.toLowerCase();
			if (lsv.indexOf("msie") != -1){
				return parseInt(lsv.substring(lsv.indexOf("msie") + 5, lsv.indexOf("; w")));
			} 
			else{
				return 0;
			}
		})(),
		/**
		 * 加载一个远程js/css文件
		 * @param {String} asUrl
		 * @param {Function} afCallback
		 * @param {String} asType
		 * @return {Boolean} 是否从远程加载
		 */
		includeUrl: function(asUrl, afCallback, asType){
			if (asUrl == null || ZHJS.oJsUrl[asUrl]) {
				if (afCallback != null) {
					afCallback(asUrl);
				}
				return false;
			}
			ZHJS.oJsUrl[asUrl] = true;
			var leHead = document.getElementsByTagName('head')[0];
			if (asType == null){
				asType = "js";
			}				
			var leTag = null;
			if (asType == "js") {
				leTag = document.createElement('script');
				leTag.setAttribute('type', 'text/javascript');
				leTag.setAttribute('src', asUrl);
			}
			else {
				leTag = document.createElement('link');
				leTag.setAttribute('rel', 'stylesheet');
				leTag.setAttribute('type', 'text/css');
				leTag.setAttribute('href', asUrl);
			}
			if (afCallback != null) {
				leTag.onload = leTag.onreadystatechange = function(){
					if (leTag.ready) {
						return false;
					}
					if (!leTag.readyState || leTag.readyState == "loaded" || leTag.readyState == 'complete') {
						leTag.ready = true;
						afCallback(asUrl);
					}
				};
			}
			leHead.appendChild(leTag);
			return true;
		},
		loadJsonP: function(asUrl, afCallback){
			if (!asUrl) {
				return false;
			}
			var lsUrl = asUrl.split("{W:random}").join(Math.random());
			if (lsUrl.indexOf("callback=") != -1) {
				ZHJS.includeUrl(lsUrl);
				return true;
			}
			if (afCallback) {
				ZHJS.iID++;
				window["_autoCallBack_" + ZHJS.iID] = afCallback;
				lsUrl = lsUrl + (lsUrl.indexOf("?") != -1 ? "&" : "?") + "callback=_autoCallBack_" + ZHJS.iID;
				ZHJS.includeUrl(lsUrl);
			}
			else {
				var loImg = new Image();
				loImg.src = lsUrl;
			}
			return true;
		},
		/**
		 * 运行一个存在于uibox目录中的js组件
		 * @param {String} asUrl 组件文件的Url
		 * @param {String,Object} aeP 元素或元素ID
		 * @param {Array} axConf 组件配置
		 * @param {Function} afCallback 回调函数 (Optional)
		 */
		runCode: function(asUrl, aeP, axConf, afCallback){
			if (asUrl == null || asUrl == "") {
				return false;
			}
			var lsFun = asUrl.substring(asUrl.lastIndexOf("/") + 1, asUrl.lastIndexOf(".js")).split("_v")[0];
			var lsPath = asUrl.substring(0, asUrl.indexOf("?") != -1 ? asUrl.indexOf("?") : asUrl.length);
			var loQuery = ZHJS.query2Obj(asUrl);
			lsFun = (loQuery.init == null ? lsFun : loQuery.init);
			if (lsFun == "") {
				return false;
			}
			var lsObjName = loQuery.obj;
			if (ZHJS.oFiles[lsPath]) {
				if (window[lsFun] != null) {
					var loP = window[lsFun](aeP, axConf);
					if (lsObjName != null) {
						window[lsObjName] = loP;
					}
					if (afCallback != null) {
						afCallback(loP);
					}
				}
				else {
					ZHJS.oFiles[lsPath].push([asUrl, aeP, axConf, lsFun, afCallback, lsObjName]);
				}
				return true;
			}
			var lsSign = asUrl.indexOf("?") != -1 ? "&" : "?";
			var lsUrl = ZHJS.sUiboxDir + asUrl + lsSign + ZHJS.sVersion;
			function lfCallback(){
				for (var i = 0; i < ZHJS.oFiles[lsPath].length; i++) {
					if (window[ZHJS.oFiles[lsPath][i][3]] != null) {
						var loP = window[ZHJS.oFiles[lsPath][i][3]](ZHJS.oFiles[lsPath][i][1], ZHJS.oFiles[lsPath][i][2]);
						if (ZHJS.oFiles[lsPath][i][5] != null) {
							window[ZHJS.oFiles[lsPath][i][5]] = loP;
						}
						if (ZHJS.oFiles[lsPath][i][4] != null) {
							var lfun = ZHJS.oFiles[lsPath][i][4];
							if (typeof(lfun)=="string"){
								window[lfun](loP);
							}else{
								lfun(loP);
							}			
						}
					}
				}
			}
			ZHJS.oFiles[lsPath] = [[asUrl, aeP, axConf, lsFun, afCallback, lsObjName]];
			ZHJS.includeUrl(lsUrl, lfCallback);
		},
		/**
		 * 发送一个ajax同步请求
		 * @param {Object} aoOBJ
		 * @return {String} 应答文本
		 */
		loadServices: function(aoOBJ){
			var loXML = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
			try {
				loXML.open(aoOBJ.method || "POST", aoOBJ.webservices, false);
				if (aoOBJ.contentType){
					loXML.setRequestHeader("Content-Type", aoOBJ.contentType);
				}
				loXML.send(aoOBJ.values);
			} 
			catch (ex) {
				return "";
			}
			if (loXML != null && loXML.readyState == 4) {
				return loXML.responseText;
			}
			return "";
		},
		/**
		 * 根据传入的参数查找兄弟结点
		 * @param {Element} aeTag 原结点
		 * @param {Number} aiWay 兄弟结点的下标，正值表示向下的兄弟结点，负值表示向上的兄弟结点，默认值为-1
		 * @return {Element} 兄弟结点
		 */
		siblingNode: function(aeTag, aiWay){
			if (aeTag == null) {
				return;
			}
			aiWay = aiWay || -1;
			if (aiWay == 0) {
				return aeTag;
			}
			var asProp = aiWay > 0 ? "nextSibling" : "previousSibling";
			aiWay = Math.abs(aiWay);
			do {
				if (aeTag==null) break;
				aeTag = aeTag[asProp];
				if (aeTag && aeTag.nodeType == 1) {
					--aiWay;
				}
			}
			while (aiWay > 0);
			return aeTag;
		},
		
		/**
		 * 把对象转换成键值对形式的字符串
		 * @param {Object} aoOBJ
		 * @return {String} 键值对形式的字符串
		 */
		obj2Query: function(aoOBJ){
			if (aoOBJ == null){
				return null;
			} 
			var lxS = [];
			for (var o in aoOBJ) {
				if (typeof(aoOBJ[o]) == "string" || typeof(aoOBJ[o]) == "number" || typeof(aoOBJ[o]) == "boolean"){
					lxS[lxS.length] = o.toLowerCase() + "=" + encodeURIComponent(aoOBJ[o]);
				} 	
			}
			return lxS.join("&");
		},
		/**
		 * 把键值对形式的字符串转换成对象
		 * @param {String} 键值对形式的字符串
		 * @return {Object}
		 */
		query2Obj: function(asQuery){
			if (asQuery == null || asQuery == "") {
				return {};
			}
			var liIndex = asQuery.indexOf("?");
			var lsQuery = liIndex > -1 ? asQuery.substring(liIndex + 1) : asQuery;
			var lxQuery = lsQuery.split("&");
			var loQuery = {};
			for (var i = 0; i < lxQuery.length; i++) {
				var lxKeyValue = lxQuery[i].split("=");
				loQuery[lxKeyValue[0]] = lxKeyValue[1];
			}
			return loQuery;
		},
		/**
		 * 把一个对象变量转换成Json形式的字符串
		 * @param {Object} aobj
		 * @return {String} Json形式的字符串
		 */
		obj2Json: function(aobj){
			switch (typeof(aobj)) {
				case 'string':
					return '"' + aobj.replace(/(["\\])/g, '\\$1') + '"';
				case 'array':
					return '[' + aobj.map(ZHJS.obj2Json).join(',') + ']';
				case 'object':
					var lxStr = [];
					for (var property in aobj) 
						if (typeof(aobj[property]) != "function") {
							lxStr.push(ZHJS.obj2Json(property) + ':' + ZHJS.obj2Json(aobj[property]));
						}
					return '{' + lxStr.join(',') + '}';
				case 'number':
					if (isFinite(aobj)) {
						break;
					}
				case 'function':
					return '""';
				case 'boolean':
					return aobj;
			}
			return String(aobj);
		},
		/**
		 * 把Json形式的字符串转换成对象。注：Json的键值对必须用双引号包括
		 * @param {String} astr
		 * @return {Object} Json对象
		 */
		json2Obj: function(astr){
			var ljson;
			if(typeof astr == "object"){
				return astr;
			}
			astr = (astr != null) ? astr.split("\n").join("").split("\r").join("") : "";
			if (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/.test(astr.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) {
				if (astr != "") {
					ljson = eval('(' + astr + ')');
					return ljson;
				}
			}
			return {};
		},
		resize: function(axImg, axWidth, aiHeight){
			if (axImg == null || axWidth == null){
				return;
			}
			var lxImg = axImg;
			if (axWidth.length == 1) {
				var liWidth = axWidth[0];
				for (var i = 0; i < lxImg.length; i++) {
					if (lxImg[i].width > liWidth) {
						lxImg[i].style.height = Math.ceil(lxImg[i].height * liWidth / lxImg[i].width) + "px";
						lxImg[i].style.width = liWidth + "px";
					}
				}
			}
			else 
				if (axWidth.length == 2) {
					var liWidth;
					var liWidth0 = axWidth[0];
					var liWidth1 = axWidth[1];
					for (var i = 0; i < lxImg.length; i++) {
						if (lxImg[i].width > liWidth1) {
							liWidth = liWidth1;
						}
						else 
							if (lxImg[i].width < lxImg[i].height && lxImg[i].width > liWidth0) {
								liWidth = liWidth1;
							}
						lxImg[i].style.height = Math.ceil(lxImg[i].height * liWidth / lxImg[i].width) + "px";
						lxImg[i].style.width = liWidth + "px";
					}
				}
			if (aiHeight != null) {
				for (var i = 0; i < lxImg.length; i++) {
					if (lxImg[i].height > aiHeight) {
						lxImg[i].style.width = Math.ceil(lxImg[i].width * aiHeight / lxImg[i].height) + "px";
						lxImg[i].style.height = aiHeight + "px";
					}
				}
			}
		},
		loadAjax: function(aoObj, afCallback, asDomain){
			if(ZHJS.isIE){
				action(delay);
			}else{
				delay();
			}

			function delay(){
				if (document.domain != "myspace.cn"){
					document.domain = "myspace.cn";
				} 
				if (aoObj == null){
					return false;
				}
				if (asDomain == null){
					asDomain = "ajaxv2";
				}
				var lsFrameUrl = "http://" + asDomain + ".myspace.cn/_common/static/post.html";
				var lsFrameName = asDomain + "_frame";
				var lsFrameWrapperId = asDomain+"_frameWrapper";
				var leFrameWrapper = document.getElementById(lsFrameWrapperId);
				if (window.frames[lsFrameName] == null){
					leFrameWrapper = document.createElement("span");
					leFrameWrapper.id = lsFrameWrapperId;
					leFrameWrapper.style.display = "none";
					document.body.appendChild(leFrameWrapper);
					leFrameWrapper.innerHTML = '<iframe name='+lsFrameName+'></iframe>';
					addLoadListener();
				}else if(window.frames[lsFrameName].loadServices == null){
					addLoadListener();
				}else{
					lfCallback();
				}
				function addLoadListener(){
					var leIframe =  leFrameWrapper.getElementsByTagName("iframe")[0];
				    if (navigator.userAgent.indexOf("IE")!=-1) {
				       leIframe.attachEvent("onload", lfCallback);
				    } else {
				        leIframe.addEventListener("load", lfCallback, false);
				    }
					leIframe.src = lsFrameUrl;				
				}
				
				function lfCallback(){
					afCallback(ZHJS.json2Obj(window.frames[lsFrameName].loadServices(aoObj)));
				}				
			}
			function action(afDelay){
				var timer = function(){
					try{
						document.documentElement.doScroll("left");
						afDelay();
					}catch(error) {
						var loErr=new Image();
						loErr.src="/images/error.gif?url="+location.href+"&service="+aoObj.webservices;
						setTimeout(timer, 200);
					}					
				}
				timer();
			}
		},
		/**
		 * 自动识别组件，对后置渲染标识并进行处理
		 */
		autoRun: function(){
			var lxHTag = document.getElementsByTagName("CODE");
			var lxQueueHTML = [];
			for (var i = 0; i < lxHTag.length; i++) {
				if ((" "+lxHTag[i].className+" ").indexOf("uibox") != -1) {
					var lxComm = lxHTag[i].childNodes;
					var lxVals = [];
					for (var j = 0; j < lxComm.length; j++) {
						if (lxComm[j].nodeValue != null && lxComm[j].nodeValue != "") {
							var lsvalue = lxComm[j].nodeValue.replace(/^\s+|\s+$/g, "");
							if (lsvalue != ""){
								lxVals[lxVals.length] = lsvalue;
							}
						}
					}
					if (lxVals.length > 0) {
						if (lxVals[0].indexOf("url:") != -1) {
							var lsUrl = lxVals[0].substring(4);
							if (lsUrl.indexOf("http://") != -1) {
								ZHJS.loadJsonP(lsUrl);
							}
							else {
								if (lsUrl.indexOf("html.js") != -1) {
									var loPEL = lxHTag[i].parentNode;
									var loHTML = {};
									loHTML.node = loPEL;
									loHTML.html = lxVals[1] || "";
									lxQueueHTML.push(loHTML);
								}
								else {
									var loQuery = ZHJS.query2Obj(lsUrl);
									var lfCallback = window[loQuery.callback||null];
									var lsbox = loQuery.box||null;
									if (lsbox == "parent") {
										ZHJS.runCode(lsUrl, lxHTag[i].parentNode, lxVals, lfCallback);
									}else if(lsbox == "this"){
										ZHJS.runCode(lsUrl, lxHTag[i], lxVals, lfCallback);
									}else {
										ZHJS.runCode(lsUrl, ZHJS.siblingNode(lxHTag[i]), lxVals, lfCallback);
									}
								}
							}
						}
					}
				}
			}
			for(var i=0; i<lxQueueHTML.length; i++){
				lxQueueHTML[i].node.innerHTML = lxQueueHTML[i].html; 
			}
		},
		/**
		 * DOM树构建完毕后，执行回调函数
		 * @param {Object} afCallback
		 */
		domReady: function(afCallback){
			if (!afCallback){
				return;
			}
			if (ZHJS.bDomReady) {
				afCallback();
				return;
			}
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", function(){
					afCallback()
				}, false);
			}
		/*@cc_on @*/
		/*@if (@_win32)
		 document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
		 document.getElementById("__ie_onload").onreadystatechange = function() {
		 if (this.readyState == "complete")  afCallback();
		 };
		 /*@end @*/
		}
	};
	ZHJS.domReady(function(){
		ZHJS.bDomReady = true;
		var lxJs = document.getElementsByTagName("script");
		for (var i = 0; i < lxJs.length; i++) {
			if (lxJs[i].src != null && lxJs[i].src.indexOf("myspace.js") != -1) {
				ZHJS.sUiboxDir = lxJs[i].src.substring(0, lxJs[i].src.indexOf("myspace.js"));
				ZHJS.sVersion = lxJs[i].src.substring(lxJs[i].src.indexOf("myspace.js") + 11);
				break;
			}
		}
		if(ZHJS.isIE){
			if (ZHJS.isIE ==6){
				setTimeout(ZHJS.autoRun,1500);
			}else{
				setTimeout(ZHJS.autoRun,100);				
			}
		}else{
			ZHJS.autoRun();
		}
	});
}