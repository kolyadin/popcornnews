function swf(FlashVar){
	var realEstate = new SWFObject("http://www.popcornnews.ru/widget/swf/popcorn_lj.swf", "vidjet-star", "585", "480", "9.0.0");
	realEstate.addParam("allowScriptAccess","always");
	realEstate.addParam("allowFullScreen","true");
	realEstate.addParam("movie","http://www.popcornnews.ru/widget/swf/popcorn_lj.swf");
	realEstate.addParam("quality","high");
	realEstate.addParam("bgcolor","#ffffff");
	realEstate.addParam("FlashVars","xmldata=http://www.popcornnews.ru/widget/xml/"+FlashVar+".xml");
	realEstate.write("vidjet-star");
}

function vidget(){}
vidget.prototype = {
	init: function(){
		this.selectCont = document.getElementById('select');
		this.vidjetStar = document.getElementById('vidjet-star');
		this.text = document.getElementById('text');
		this.value = '76065';
		
		this.openXHR();
		
		swf('76065'); // lady gaga
		this.text.innerHTML = '&lt;object width="585" height="480">&lt;param name="movie" value="http://www.popcornnews.ru/widget/swf/popcorn_lj.swf">&lt;/param>&lt;param name="allowFullScreen" value="true">&lt;/param>&lt;param name="allowscriptaccess" value="always">&lt;param name="FlashVars" value="xmldata=http://www.popcornnews.ru/widget/xml/'+this.value+'.xml">&lt;/param>&lt;embed src="http://www.popcornnews.ru/widget/swf/popcorn_lj.swf" type="application/x-shockwave-flash" FlashVars="xmldata=http://www.popcornnews.ru/widget/xml/'+this.value+'.xml" allowscriptaccess="always" allowfullscreen="true" width="585" height="480">&lt;/embed>&lt;/object>'
	},
	ajaxResult: function(){
		var self= this;
		this.selectCont.innerHTML = '<select id="star-select">'+this.getResult+'</select>';/*swf()*/
		this.starSelect = document.getElementById('star-select');
		this.starSelect.onchange = function(){
			self.value = this.value;
			swf(this.value);
			//self.text.innerHTML = self.vidjetStar.innerHTML.replace(/</gi,'&lt;').replace(/>/gi, '&gt;');
			self.text.innerHTML = '&lt;object width="585" height="480">&lt;param name="movie" value="http://www.popcornnews.ru/widget/swf/popcorn_lj.swf">&lt;/param>&lt;param name="allowFullScreen" value="true">&lt;/param>&lt;param name="allowscriptaccess" value="always">&lt;param name="FlashVars" value="xmldata=http://www.popcornnews.ru/widget/xml/'+self.value+'.xml">&lt;/param>&lt;embed src="http://www.popcornnews.ru/widget/swf/popcorn_lj.swf" type="application/x-shockwave-flash" FlashVars="xmldata=http://www.popcornnews.ru/widget/xml/'+self.value+'.xml" allowscriptaccess="always" allowfullscreen="true" width="585" height="480">&lt;/embed>&lt;/object>';
		}
	},
	openXHR: function(){
		var self = this;
		var XHRObj = this.getXHR();
		XHRObj.open('GET','/ajax/persons_list',true);
		XHRObj.onreadystatechange = function() {
			if (XHRObj.readyState == 4) {
				if(XHRObj.status == 200) {
					self.getResult = XHRObj.responseText;
					self.ajaxResult();
				}
				if(XHRObj.status != 200) {
					self.getResult = [];
					self.ajaxResult();
				}
			}
		}
		XHRObj.send(null);
	},
	getXHR: function(){
		var xmlhttp;
		try {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (E) {
				xmlhttp = false;
			}
		}
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
			xmlhttp = new XMLHttpRequest();
		}
		return xmlhttp;
	}
}
new vidget().init();