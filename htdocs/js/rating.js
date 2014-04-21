//подключает рейтинг
//Разметка:
//	<div id="_8085005" class="rating">
//		<h3>рейтинг <span class="num">3.3</span></h3>
//		<span class="vote false"></span>
//	</div>
//	<script type="text/javascript">new Rating({id:'_8085005', ajax:'путь к запросу'});
// </script>


var Rating;
if (Rating) throw new Error('Rating уже существует');

Rating=function(param){
	this.rating=document.getElementById(param.id);
	if (!this.rating) {alert('Невозможно создать рейтинг, такого id не существует'); return false;}
	this.ajaxPath=param.ajax;
	this.init();
}
Rating.prototype={
	init:function(){
		this.findItems();
	},
	sendVoiting:function(){
		this.sendRequest(this.firstChild.nodeValue);
	},	
	addVoiting:function(m){
		var m=eval("("+m+")");
		this.stars.style.width=m.rating*20+'px';//m.rating - число
		this.num.firstChild.nodeValue=(m.rating+'').replace(/\./, ',');
		this.rating.className=this.rating.className.replace(' allow_vote', '');
	},	
	findItems:function(){
		var obj=this;
		this.star=[];//массив звезд
		var dom=this.rating.getElementsByTagName('*');
		for(var i=0,j=dom.length;i<j;i++){
			switch(dom[i].nodeName){
				case 'SPAN':{
					if(dom[i].className.indexOf('vote')!=-1) this.vote=dom[i];
					else if(dom[i].className.indexOf('num')!=-1) this.num=dom[i];
					else if(dom[i].className.indexOf('stars')!=-1) this.stars=dom[i];
					break;
				}
				case 'A':{
					if(dom[i].className.indexOf('star')!=-1){
						dom[i].onclick=function(){obj.sendRequest(this.firstChild.nodeValue); return false}
						this.star.push(dom[i]);
					}
					break;
				}
			}
		}
	},
	createRequest:function (){	
		request = null;
		try {request = new XMLHttpRequest();}
		catch (trymicrosoft)
			{
				try {request = new ActiveXObject("Msxml2.XMLHTTP");}
				catch (othermicrosoft)
					{
						try {request = new ActiveXObject ("Microsoft.XMLHTTP");}
						catch (failed) {request = null;}
					}
			}
		if (request == null)
			{
				alert ('Внимание! Объект запроса не создан. Обратитесь к разработчику');
			} 
		else {return request;}
	},
	sendRequest:function(val){
		var obj=this;
		var url=this.ajaxPath+val+'&dummy='+new Date().getTime();
		var request=this.createRequest();
		request.open ("GET", url, true); 
		request.onreadystatechange =function (){obj.getRequest.call(obj, request)};
		request.send();
	},
	getRequest:function(request){
		if (request.readyState == 4){
			if (request.status == 200){
				var message = request.responseText //передаем полученные данные переменной
				this.addVoiting(message);
				//f.call(this, message);
			}
		}
	}
}





