/*
	Поиск 
	Класс принимает следующие параметры
	- uid - id инпута
	- boxUid - id контейнера в который будем вставлять результат
	- type - название, передаваемое на сервер, и соответствующая функция обработки ответа (см. ajax.js).
*/

Search=function(data){this.init(data)}
Search.prototype = new Ajax ();
Search.prototype.constructor = Search;

Search.prototype.init = function(data){
	this.createElements(data);
	this.addSearch();
}
Search.prototype.createElements = function(data){
	this.field=document.getElementById(data.uid);
	this.box=document.getElementById(data.boxUid);
	this.type=data.type;
}
Search.prototype.addSearch = function(){
	var self=this;
	this.field.onkeyup=function(){
		if(this.value.length!=0 && this.value.length < 3) return false;
		self.ajaxSend({type:self.type, value:{val:this.value}, success:self.success});
	}
}
Search.prototype.success = function(data){
	this.box.innerHTML='';
	this.box.appendChild(data.fragment);
}
