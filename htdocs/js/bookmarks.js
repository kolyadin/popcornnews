function bookmarks(){}
bookmarks.prototype = {
	init: function(){
		this.paragraph = as.$('div.text p');
		this.bookMarks = as.$('a.bookMark');
		this.data = 'data';
		this.goToBookmark = document.getElementById('goToBookmark');
		if (!this.goToBookmark) return;
		this.text = document.getElementById('text');
		if (!this.text) return;
		this.textHidden = document.getElementById('text-hidden');
		if (!this.textHidden) return;
		
		this.text.style.display = 'block';
		
		for (var i=0;i<this.bookMarks.length;i++){
			this.bookMarks[i].innerHTML = 'Поставить<br/>закладку';
		}
		
		this.hiddenVisibleText();
		this.showMark();
		this.getActiveLink('','',this.getCookies(this.data));
	},
	visibleBookMark: function(v){
		var self = this;
		this.activeLink = [];
		for (var i=0;i<this.bookMarks.length;i++){
			if ((" "+this.bookMarks[i].className+" ").indexOf (' active ') != -1){self.activeLink.push(this.bookMarks[i])}
			this.bookMarks[i].style.display = 'none';
		}
		if (this.activeLink[0]){this.activeLink[0].style.display = 'block';}
		this.bookMarks[v].style.display = 'block';
		this.bookMarks[v].onclick = function(e){
			self.stopEvent(e);
			for (var i=0;i<self.bookMarks.length;i++){
				self.bookMarks[i].className = self.bookMarks[i].className.replace(/\bactive\b/gi,'');
				self.bookMarks[i].innerHTML = 'Поставить<br/>закладку';
				self.bookMarks[i].style.display = 'none';
			}
			self.getActiveLink(this,v);
			var date = self.createDate();
			self.rememberLink(v,date);
		};
		if (this.activeLink[0]){
			this.activeLink[0].onclick = function(){
				this.className = this.className.replace(/\bactive\b/gi,'');
				this.innerHTML = 'Поставить<br/>закладку';
				this.style.display = 'none';
				this.data = '';
				self.rememberLink(this.data,'');
			}
		}
	},
	getActiveLink: function(myLink,v,index){
		var self = this;
		var html = document.documentElement;
		var body = document.body;
		if ((!index) && myLink){
			myLink.innerHTML = 'Убрать<br/>закладку';
			myLink.className += ' active';
			myLink.style.display = 'block';
			this.goToBookmark.onclick = function(e){
				self.stopEvent(e);
				if (myLink){
					document.documentElement.scrollTop = as.getElementPosition(myLink).top;
					document.documentElement.scrollTop == 0 ? document.body.scrollTop = as.getElementPosition(myLink).top : '';
				}
			};
		}
		if (index) {
			this.bookMarks[index].innerHTML = 'Убрать<br/>закладку';
			this.bookMarks[index].className += ' active';
			this.bookMarks[index].style.display = 'block';
			this.goToBookmark.onclick = function(e){
				self.stopEvent(e);
				if (self.bookMarks[index]){
					document.documentElement.scrollTop = as.getElementPosition(self.bookMarks[index]).top;
					document.documentElement.scrollTop == 0 ? document.body.scrollTop = as.getElementPosition(self.bookMarks[index]).top : '';
				}
			};
		}
	},
	createDate: function(){
		var date = new Date();
		var month = date.getMonth();
		var year = date.getFullYear();
		month = month + 1;
		if (month > 11){month = 1;year = year + 1};
		var newMonth = new Date(year+'/'+month+'/'+date.getDate());
		return newMonth;
	},
	showMark: function(){
		var self = this;
		for (var i=0;i<this.paragraph.length;i++){
			(function(i){
				self.paragraph[i].onmouseover = function(){
					self.visibleBookMark(i);
				}
				self.paragraph[i].onmouseout = function(){
					for (var i=0;i<self.bookMarks.length;i++){
						self.bookMarks[i].style.display = 'none';
					}
					if (self.activeLink[0]){self.activeLink[0].style.display = 'block';}
				}
			})(i)
		}
	},
	rememberLink: function(data, expires) {
		document.cookie = this.data + '=' + data + ((expires) ? "; expires=" + expires.toGMTString() : "");
	},
	getCookies: function(data){
		var prefix = data + "=";
        var cookieStartIndex = document.cookie.indexOf(prefix);
        if (cookieStartIndex == -1){return null}
        var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length);
        if (cookieEndIndex == -1){cookieEndIndex = document.cookie.length}
        return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex))
	},
	hiddenVisibleText: function(){
		var self = this;
		this.textHidden.onclick = function(e){
			self.stopEvent(e);
			if (self.text.style.display == 'block'){
				self.text.style.display = 'none';
				this.className = this.className.replace(/\bvisible-text\b/gi,'');
				this.className += ' hidden-text';
			}
			else if (self.text.style.display == 'none'){
				self.text.style.display = 'block';
				this.className = this.className.replace(/\hidden-text\b/gi,'');
				this.className += ' visible-text';
			}
			//this.className.match(/\hidden-text\b/) ? this.className = this.classname.replace('hidden-text','visible-text') : '';
		}
	},
	stopEvent: function(e){
		var event = e || window.event;
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
	}
}
new bookmarks().init();