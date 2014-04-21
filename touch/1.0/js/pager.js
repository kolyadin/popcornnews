/*
	скролл пейджер, принимает объект со следующими параметрами
	- boxUid : id блока куда будем вставлять обработанный результат
	- type : название, передаваемое на сервер, и соответствующая функция обработки ответа (см. ajax.js).
	- pages : количество подгружаемых "страниц"
	
	Имеет публичный метод reset, который принимает общее количество страниц. Нужен, например, при выборке.
	
*/

Pager = function(data){this._init(data)};
Pager.prototype = new Ajax ();
Pager.prototype.constructor = Pager;

Pager.prototype._init = function(data){
	this.data=data;
	this._createElements();
	this.reset(this.data.pages);
}
Pager.prototype._createElements = function(){
	var self=this;
	this.offset=500; // расстояние в px до конца прокручиваемой области, преодоление которого отправляется аякс
	this.isLoading; //загружаем или нет данные аяксом 
	this.allPages; //всего "страниц"
	this.curPage; // текущее количество загруженных "страниц"
	this.box=document.getElementById(this.data.boxUid);
	this.scrollName=document.documentElement.scrollHeight>document.body.scrollHeight ? 'documentElement' : 'body';
	this.addScrollingCall=function(event){self.addScrolling.call(self, event)};	
}
Pager.prototype.reset = function(pages){
	var self=this;
	this.isLoading=false;
	this.allPages=pages;
	this.curPage=1; // текущее количество загруженных "страниц"
	window.removeEventListener('scroll', this.addScrollingCall, false);
	window.addEventListener('scroll', this.addScrollingCall, false);
}
Pager.prototype.addScrolling = function(event){
	if(this.isLoading || document[this.scrollName].scrollHeight-document[this.scrollName].scrollTop-document.documentElement.clientHeight-this.offset>0) return false;
	this.isLoading=true;
	this.curPage++;
	this.ajaxSend({type:this.data.type, value:{page:this.curPage}, success:this.ajaxSuccess});
}
Pager.prototype.ajaxSuccess = function(data){
	this.box.appendChild(data.fragment);
	if(this.allPages==this.curPage) window.removeEventListener('scroll', this.addScrollingCall, false);
	else this.isLoading=false;
}

	

