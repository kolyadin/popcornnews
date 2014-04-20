/*
	Выборка. Принимаем объект со следующими параметрами:
	- selectUid : id селекта с помощью которого осуществляем выборку
	- textUid : id текстового блока куда записывается текстовое значение выборки
	- boxUid : id блока куда будем вставлять обработанный результат
	- type : название, передаваемое на сервер, и соответствующая функция обработки ответа (см. ajax.js).
	- pager - необязательный параметр, если он существует, значит к странице подключен пейджер, который нужно апдейтить после выборки
	
*/

Sample=function(data){this.init(data)}
Sample.prototype = new Ajax ();
Sample.prototype.constructor = Sample;

Sample.prototype.init = function(data){
	this.data=data;
	this.createElements();
	this.firstChange();
	this.addChange(data);
	this.addView();
}
Sample.prototype.createElements = function(){
	this.select=document.getElementById(this.data.selectUid);
	this.options=this.select.querySelectorAll('option');
	this.text=document.getElementById(this.data.textUid);
	this.box=document.getElementById(this.data.boxUid);
	this.curVal='';
}
Sample.prototype.firstChange = function(){
	for(var i=0,j=this.options.length;i<j;i++){
		if(this.options[i].selected){
			this.curVal=this.options[i].value;
			this.text.firstChild.nodeValue=this.options[i].firstChild.nodeValue;
			break;
		}
	}
}
Sample.prototype.addChange = function(){
	var self=this;
	this.select.addEventListener('change', function(){
		self.ajaxSend({type:self.data.type, value:{'sample':self.select.value}, success:self.success, error:self.error});
	}, false)
}
Sample.prototype.success = function(data){
	this.box.innerHTML='';
	window.scrollTo(0,0); 
	this.box.appendChild(data.fragment);
	if(this.select.value!=this.curVal) {this.firstChange()}
	if(this.data.pager) this.data.pager.reset(data.pages);
}
Sample.prototype.error = function(data){
	for(var i=0,j=this.options.length;i<j;i++){
		if(this.options[i].value==this.curVal){
			this.options[i].selected='selected';
			break;
		}
	}	
	alert(data.message);
}
Sample.prototype.addView = function(){
	this.select.className=this.select.className+' disabled';
}		
	
	

	

