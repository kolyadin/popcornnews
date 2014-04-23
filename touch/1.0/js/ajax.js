/*
	Аякс запросы-ответы
	Класс Ajax нужно наследовать там, где используется аякс
	В функцию ajaxSend передается объект со следующими параметрами:
	- type : строка - обязательное свойство - название, передаваемое на сервер, и соответствующее свойство с разметкой
	- value : объект - обязательное свойство - отправляемые данные 
	- success : функция - необязательное свойство. Запускается в случае успешного выполенния запроса. Если ф-я не передается - выводится попап с сообщением
	- error : функция - необязательное свойство. Запускается в случае отрицательного выполенния запроса. Если ф-я не передается - выводится попап с сообщением
	
*/

function Ajax (){}

Ajax.prototype._ajaxHTML={
	addDialogue:function(item){
		return 	(
			'<li class="dialogue__item dialogue__item_my">'+
				'<div class="dialogue__date">'+item.date+'</div>'+
				'<div class="dialogue__text">'+
					'<div class="cloud">'+item.desc+'</div>'+
				'</div>'+
			'</li>'
		);	
	},
	getNews:function(item){
		var date;
		item.date ? date='<span class="news-list__date">'+item.date+'</span>' : date='';
		return 	(
			'<div class="news-list__item eitem Eitem">'+
				'<div class="news-list__content">'+
				'<a class="Eitem__content"  href="'+item.link+'">'+
					date+
					'<span class="news-list__title">'+item.title+'</span>'+
					'<img class="news-list__photo" src="'+item.photoPreview+'" alt="'+item.photoPreviewAlt+'" />'+
					'<div class="news-list__stat">'+
						'<span class="icon-text icon-text_count">'+
							'<img class="icon-text__icon"  src="i/camera-white.svg" alt="Фотографии" /> '+
							'<span class="icon-text__text">'+item.numPhoto+'</span>'+
						'</span> '+
						'<span class="icon-text icon-text_count">'+
							'<img class="icon-text__icon"  src="i/message-white.svg" alt="Комментарии" /> '+
							'<span class="icon-text__text">'+item.numComments+'</span>'+
						'</span>'+
					'</div>'+
					'<div class="news-list__desc">'+item.desc+'</div>'+
				'</a>'+
				'<div class="eitem__extra Eitem__extra">'+
					'<a href="'+item.vk+'"><img class="eitem__extra-social" src="i/vk-white.svg" alt="" /></a> '+
					'<a href="'+item.fb+'"><img class="eitem__extra-social" src="i/fb-white.svg" alt="" /></a> '+
					'<a href="'+item.tw+'"><img class="eitem__extra-social" src="i/tw-white.svg" alt="" /></a>'+
				'</div>'+
				'</div>'+
			'</div>'
		);	
	},
	getUsers:function(item){
		var isOnline = item.isOnline ? ' users__name_online' : '';
		return 	(
			'<div data-uid="'+item.uid+'" class="users__item eitem eitem Eitem">'+
				'<a class="users__content Eitem__content" href="'+item.userLink+'">'+
					'<div class="users__photo" style="background-image:url('+item.avatar+');"></div>'+
					'<div class="users__info">'+
						'<div class="users__name color-'+item.ratingName+isOnline+'">'+item.nick+'</div>'+
						'<div class="users__row">'+
							'<span class="users__city">'+item.city+'</span>'+
							'<span class="stars color-'+item.ratingName+'"><span style="width:'+item.ratingValue+'%;" class="stars__inner"></span></span>'+							
						'</div>'+
					'</div>'+
				'</a>'+
				'<div class="eitem__extra Eitem__extra">'+
					'<div class="eitem__extra-control">'+
						'<a data-type="deleteUser" class="eitem__extra-item" href="#">'+
							'<img class="eitem__extra-icon" src="i/close-white.svg" alt="Удалить" />'+
						'</a>'+
						'<a class="eitem__extra-item" href="html/dialogue.html?user='+item.uid+'">'+
							'<img class="eitem__extra-icon" src="i/message-white.svg" alt="Написать сообщение" />'+
						'</a>'+
					'</div>'+
				'</div>	'+				
			'</div>'		
		);	
	}
	
}

Ajax.prototype._ajaxFragment=function(data){
	if(!data.fragment.length) data.fragment = new Array (data.fragment);
	
	var inner='';
	for(var i=0, j=data.fragment.length; i<j; i++){
		inner+=this._ajaxHTML[data.name](data.fragment[i]);
	}
	
	var fragment=document.createDocumentFragment();
	$(inner).appendTo(fragment);
	
	//Делаем, чтобы  менюшки справа открывались
	var items=fragment.querySelectorAll('.Eitem');
	if(items.length) eitems.addEitem(items);
	
	return fragment;
}

Ajax.prototype.ajaxSend=function(data){ 
	var self=this;
	data.value.type=data.type;
	$.post(
		'/ajax/' + data.type,
		data.value, 
		function(res){
			if (res.status){
				if (res.fragment) res.fragment = self._ajaxFragment({fragment:res.fragment, name:data.type})
				if(data.success) data.success.call(self, res);
				else alert(res.message);	
			}
			else{
				if(data.error) data.error.call(self, res);
				else alert(res.message);	
			}
		},
		'json'
	);			
}	

