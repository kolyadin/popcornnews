/*
	добавление Аякс сообщений.
	Принимаем объект с тремя параметрами:
	- uid : id формы с отправляемым сообщением
	- boxUid : id блока куда будем вставлять обработанный результат
	- type : название, передаваемое на сервер, и соответствующая функция обработки ответа (см. ajax.js).
*/

Message = function(data){this.init(data)};
Message.prototype = new Ajax ();
Message.prototype.constructor = Message;

Message.prototype.init = function(data){
	this.createElements(data);
	this.addSubmit(data);
}
Message.prototype.createElements = function(data){
	this.form=document.getElementById(data.uid);
	this.box=document.getElementById(data.boxUid);
}
Message.prototype.addSubmit = function(data){
	var self=this;
	this.form.onsubmit=function(event){
		event.preventDefault();
		var sendData={};
		for(var i=0, j=self.form.elements.length; i<j; i++) sendData[self.form.elements[i].name] = self.form.elements[i].value;
		self.ajaxSend({type:data.type, value:sendData, success:self.ajaxSuccess});
	}
}
Message.prototype.ajaxSuccess = function(data){
	window.scrollTo(0,0); 
	this.box.insertBefore(data.fragment, this.box.firstChild);
	for(var i=0, j=this.form.elements.length; i<j; i++) {
		if(this.form.elements[i].type=='text') this.form.elements[i].value='';
	}
}
	

	

