as._$ = as.$;
as.$ = function(selector,pn,fc) {
	var result = as._$(selector,pn,fc);
	return result.length ? result : (document.getElementById(selector) ? document.getElementById(selector) : []);
}
as.getBTN = function(tn,pn) {
	return as.$(tn,pn) || [];
}
as.getBCN = function(cn,tn,pn) {
	return as.$(tn+cn?("."+cn):"",pn) || [];
}
as.addEvent = function(element,evt,fn) {
	as.e[evt](element,fn);
}
as.cancelEvent = function(e) {
	e.preventDefault ? e.preventDefault() : e.returnValue = false;
}
as.prependElement = function(element,container,ih) {
	return as.prepend(element,container,ih);
}
as.appendElement = function(element,container,ih) {
	return as.append(element,container,ih);
}
as.insertBefore = function(element,container,before,ih) {
	return as.before(element,before,ih);
}
as.removeChild = as.remove;
as.getVScrollDirection = function(data) {
	var ie = false;
	if (/MSIE/.test(navigator.userAgent)) ie = true;
	if ((ie && (data < 0)) || (!ie && (data > 0))) {
		return "down";
	}
	else if ((ie && (data > 0)) || (!ie && (data < 0))) {
		return "up";
	}
}
as.fadeOnce = function(element,opacity) {
	if (/MSIE/.test(navigator.userAgent)) { element.style.filter = "alpha(opacity="+(opacity)+")"; }
	else { element.style.opacity = opacity; }
}
as.fade = function(element,start,end,speed) {
	if (/MSIE/.test(navigator.userAgent)) {
		start = start*100;
		end = end*100;
		speed = speed*200;
	}
	var cfv = start;									//current fading value
	as.fadeOnce(element,cfv);
	var fi = window.setInterval(
		function() {
			if (Math.abs(cfv-start) > Math.abs(end-start)) {
				window.clearInterval(fi);
			}
			cfv+=speed;
			as.fadeOnce(element,cfv);
		},1
	);
}
as.fadeIn = function(element,speed) {
	as.fade(element,1,0,-speed);		
}
as.fadeOut = function(element,speed) {
	as.fade(element,0,1,speed);		
}
as.overlay = function(mw, mh, cn, fn, na) {
	var body = as.getBTN("body")[0];
	var overlay = as.appendElement("div",body);
	if (cn) {overlay.className += cn}
	var st = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var noa = function() {
		var bt = Math.floor(document.documentElement.clientHeight/2);
		var bl = Math.floor(body.clientWidth/2)
		var t = Math.floor(bt-mh/2);
		var l = Math.floor(bl-mw/2);				
		overlay.style.width = mw+"px";
		overlay.style.height = mh+"px";
		overlay.style.top = t+st+"px";
		overlay.style.left = l+"px";
		setTimeout(
			function() {
				fn();	
			},100
		);				
		return overlay;
	}
	/*@cc_on
		@if (@_jscript_version < 5.7)				
			var iframe = as.create("iframe");
			overlay.appendChild(iframe);
			iframe.setAttribute("frameborder",0);
			iframe.setAttribute("allowtransparency",true);
			noa();
			return overlay;
		@end
	@*/
	if (na) {
		noa();
		return overlay;
	}
	as.style(
		overlay,
		{
			top: body.clientHeight/2+st+"px",
			left: body.clientWidth/2+"px"
		}
	);
	var h = 0;
	var w = 0;
	var bt = Math.floor(document.documentElement.clientHeight/2);
	var bl = Math.floor(body.clientWidth/2);
	var hp = 7;
	var wp = 14;
	var oi = window.setInterval(
		function() {
			if (h+hp > mh) {
				hp = 0;	
				h = mh;
			}
			if (w+wp > mw) {
				wp = 0;
				w = mw;
			}
			if ((h >= mh) && (w >= mw)) {
				clearInterval(oi);
				fn();
				return;
			}
			h += hp;
			w += wp;
			t = Math.floor(bt-h/2);
			l = Math.floor(bl-w/2);
			as.style(
				overlay,
				{
					width: w+"px",
					height: h+"px",
					left: l+"px",
					top: t+st+"px"
				}
			);
		},
		5
	);
	return overlay;
}
as.ready.add = as.ready;
as.ready.init = function() {}

as._ajax = as.ajax;
as.ajax = function() {	
	if (this == as) {
		as._ajax.apply(this,arguments);
	}
	else {
		this.responseText='';
		this.loadStatus;
		this.getter=null;
		this.func=null; // func for backlink action (this function will be exec when XML data is loaded)
		
		this.makeRequest=function (url,func,getter) {
			this.getter = getter;
			this.http_request = false;
			this.func=func;
			if (window.XMLHttpRequest) { // Mozilla, Safari,...
				this.http_request = new XMLHttpRequest();
				if (this.http_request.overrideMimeType) {
					this.http_request.overrideMimeType('text/xml');
					// See note below about this line
				}
			} else if (window.ActiveXObject) { // IE
				try {
					this.http_request = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						this.http_request = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e) {}
				}
			}
			if (!this.http_request) {
				throw new Error('Cannot create an XMLHTTP instance. You shutdown ActiveX ?\n Compatibility: IE 5.0, Mozilla 1.7, Firefox 1.0, Opera 8.0');
				return false;
			}
			this.http_request.onreadystatechange = this.alertContents;
			this.http_request.open('GET', url, true);
			this.http_request.send(null);
		}
		
		this.makeRequestPost=function (url,func,data) {
			this.http_request = false;
			this.func=func;
			if (window.XMLHttpRequest) { // Mozilla, Safari,...
				this.http_request = new XMLHttpRequest();
				if (this.http_request.overrideMimeType) {
					this.http_request.overrideMimeType('text/xml');
					// See note below about this line
				}
			} else if (window.ActiveXObject) { // IE
				try {
					this.http_request = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						this.http_request = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e) {}
				}
			}
	
			if (!this.http_request) {
				alert('Cannot create an XMLHTTP instance. You shutdown ActiveX ?\n Compatibility: IE 5.0, Mozilla 1.7, Firefox 1.0, Opera 8.0');
				return false;
			}
			this.http_request.onreadystatechange = this.alertContents;
			this.http_request.open('POST', url, true);
			this.http_request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			this.http_request.send(data);
		}
	
		this.alertContents=as.bind(function (e) {
			var ajax=this;//window['storage'].ajax;
			var http_request=this.http_request;
			this.send_status(http_request.readyState);
			if (http_request.readyState == 4) {
				if (http_request.status == 200) {
					this.responseText=http_request.responseText;
					if(http_request.responseXML && http_request.responseXML.documentElement == null)
					{
						try
						{
							http_request.responseXML.loadXML(http_request.responseText)
						} catch (e) {}
					}
					this.func(http_request.responseText,http_request.responseXML);
				} else {
					this.loadStatus=http_request.status;
					this.func('',null);
				}
			}
		},this);
		
		this.send_status=function(status)
		{
			//window.status=status;
		}
	}
}