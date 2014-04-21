/*
	

*/

eitems={
	touchPointId:false,
	startCoords:{},
	startMargin:0,
	curMargin:0,
	timeSwip:0,
	curItem:false,
	events:[],
	
	init:function(){
		this.defineEvents();
		this.addClosePrevItem();	
		this.addEitem(document.querySelectorAll('.Eitem'));
	},
	defineEvents:function(){
		if ('ontouchstart' in document.documentElement) this.events=['touchstart', 'touchmove', 'touchend'];
		else  this.events=['mousedown', 'mousemove', 'mouseup']		
	},
	addClosePrevItem:function(){
		document.body.addEventListener(this.events[0], function(event){
			//закрываем предыдущий нункт
			if(eitems.curItem && eitems.curMargin!=0 ) {
				event.preventDefault();
				eitems.curMargin=0;
				try{eitems.curItem.$extra.stop()} catch(e){}
				eitems.curItem.$extra.animate({marginLeft:eitems.curMargin},{duration:250, easing:'linear'});
			}		
		}, false);		
	},
	addEitem:function(data){
		
		Item=function(el){this.init(el)}
		Item.prototype = new Ajax ();
		Item.prototype.constructor = Item;		
		
		Item.prototype.init = function(el){
			var self=this;
			this.wrapper=el
			this.wrapper.addEventListener(eitems.events[0], function(event){self.touchStart.call(self, event)}, false);
			this.$extra=$(this.wrapper.querySelector('.Eitem__extra'));
			this.$extra.on(eitems.events[0], function(event){event.stopPropagation()});
			this.deleteUserBtn=this.wrapper.querySelector('[data-type="deleteUser"]');
			this.deleteUserBtn2=this.wrapper.querySelector('[data-type="deleteUser2"]');
			this.addUserBtn=this.wrapper.querySelector('[data-type="addUser"]');
			this.confirmUserBtn=this.wrapper.querySelector('[data-type="confirmUser"]');
			this.complainUserBtn=this.wrapper.querySelector('[data-type="complainUser"]');
			this.deleteStatusBtn=this.wrapper.querySelector('[data-type="deleteStatus"]');
			this.addControl();
		}
		Item.prototype.addControl = function () {
			var self=this;

			if(this.deleteUserBtn) {
				this.deleteUserBtn.onclick=function(event){
					event.preventDefault();
					if(confirm('Удалить пользователя из друзей ?')) self.ajaxSend({type:'deleteUser', value:{uid:self.wrapper.getAttribute('data-uid')}, success:self.deleteEitem});
				}
			}
			if(this.deleteUserBtn2) {
				this.deleteUserBtn2.onclick=function(event){
					event.preventDefault();
					if(confirm('Удалить пользователя из друзей ?')) self.ajaxSend({type:'deleteUser', value:{uid:self.wrapper.getAttribute('user-data-uid')}, success:''});
				}
			}
			if(this.addUserBtn) {
				this.addUserBtn.onclick=function(event){
					event.preventDefault();
					self.ajaxSend({type:'addUser', value:{uid:self.wrapper.getAttribute('data-uid')}, success:self.addUser});
				}
			}
			if(this.confirmUserBtn) {
				this.confirmUserBtn.onclick=function(event){
					event.preventDefault();
					self.ajaxSend({type:'confirmUser', value:{uid:self.wrapper.getAttribute('data-uid')}, success:self.confirmUser});
				}
			}
			if(this.complainUserBtn) {
				this.complainUserBtn.onclick=function(event){
					event.preventDefault();
					if(confirm('Пожаловаться на пользователя ?')) self.ajaxSend({type:'complainUser', value:{uid:self.wrapper.getAttribute('user-data-uid')}});
				}
			}
			if(this.deleteStatusBtn) {
				this.deleteStatusBtn.onclick=function(event){
					event.preventDefault();
					if(confirm('Удалить статус ?')) self.ajaxSend({type:'deleteStatus', value:{uid:self.wrapper.getAttribute('data-uid')}, success:self.deleteEitem});
				}
			}
		}
		Item.prototype.deleteEitem = function (data) {
			this.wrapper.parentNode.removeChild(this.wrapper);
			delete eitems.curItem;
			eitems.curMargin=0;
			alert(data.message);		
		}
		Item.prototype.addUser = function (data) {
			this.wrapper.className=this.wrapper.className.replace(/\s*users__item_add/, '');
			alert(data.message);
		}
		Item.prototype.confrmUser = function (data) {
			this.wrapper.className=this.wrapper.className.replace(/\s*users__item_add/, '');
			alert(data.message);
		}
		
		
		
		Item.prototype.touchStart = function(event){
			if(eitems.touchPointId!=false) return false;
			var touchPoint = (typeof event.changedTouches != 'undefined') ? event.changedTouches[0] : event;
			if(touchPoint.pageX<10 || document.body.offsetWidth-20<touchPoint.pageX) return false; //чтобы не было глюков при перелистывании от края экрана
			
			//закрываем предыдущий нункт при  щелчке на другом
			if(eitems.curItem && this.wrapper!=eitems.curItem.wrapper && eitems.curMargin!=0 ) return false;
			
			event.stopPropagation();//чтобы не закрывать пункт при щелчке на боди
			if(eitems.curItem && this.$wrapper==eitems.curItem.wrapper)   try{eitems.curItem.$extra.stop()} catch(e){};
			eitems.curItem=this;
			
			eitems.touchPointId = (typeof touchPoint.identifier != 'undefined') ? touchPoint.identifier : 1;
			eitems.startCoords={x:touchPoint.pageX, y: touchPoint.pageY};
			
			var self=this,
				wrapperDirection=function (event){self.detectDirection.call(self, event, wrapperDirection)};
				
			document.body.addEventListener(eitems.events[1], wrapperDirection, false);				
		}
		Item.prototype.detectDirection = function(event, wrapperDirection){
			//если двигаем другой палец
			var touchPoint = (typeof event.changedTouches != 'undefined') ? event.changedTouches[0] : event;
			touchPoint.identifier=(typeof touchPoint.identifier != 'undefined') ? touchPoint.identifier : 1;
			
			if(eitems.touchPointId!=touchPoint.identifier) return false;	
			
			var offsetX=Math.abs(touchPoint.pageX-eitems.startCoords.x);
			var offsetY=Math.abs(touchPoint.pageY-eitems.startCoords.y);

			if(offsetX>=offsetY) {
				eitems.startCoords={x:touchPoint.pageX, y: touchPoint.pageY};
				eitems.startMargin=parseFloat(this.$extra.css('marginLeft'));
				eitems.timeSwip=new Date().getTime();
				
				var self=this,
					wrapperMove=function(event){self.touchMove.call(self, event)}, 
					wrapperEnd=function(event){self.touchEnd.call(self, event, wrapperMove, wrapperEnd)};
				
				this.extraWidth=this.$extra.outerWidth();
				
				document.body.addEventListener(eitems.events[1], wrapperMove, false);
				document.body.addEventListener(eitems.events[2], wrapperEnd, false);
			}
			else eitems.touchPointId=false;
			
			document.body.removeEventListener(eitems.events[1], wrapperDirection, false);
		}
		Item.prototype.touchMove = function(event){
			event.preventDefault();
			//если двигаем другой палец
			var touchPoint = (typeof event.changedTouches != 'undefined') ? event.changedTouches[0] : event;
			touchPoint.identifier=(typeof touchPoint.identifier != 'undefined') ? touchPoint.identifier : 1;
			if(eitems.touchPointId!=touchPoint.identifier) return false;
			
			eitems.curMargin=Math.max(-this.extraWidth, eitems.startMargin+(touchPoint.pageX-eitems.startCoords.x));
			this.$extra.css('marginLeft', eitems.curMargin);	
			
			var curTime=new Date().getTime();
			if (curTime-eitems.timeSwip>200) {
				eitems.timeSwip=curTime;
				eitems.startMargin=eitems.curMargin;
				eitems.startCoords.x=touchPoint.pageX;
			}				
		}
		Item.prototype.touchEnd = function(event, wrapperMove, wrapperEnd){
			//если отпустили другой палец
			var touchPoint = (typeof event.changedTouches != 'undefined') ? event.changedTouches[0] : event;
			touchPoint.identifier=(typeof touchPoint.identifier != 'undefined') ? touchPoint.identifier : 1;

			if(eitems.touchPointId!=touchPoint.identifier) return false;
					
			var distanceSwip=touchPoint.pageX-eitems.startCoords.x;
			
			if(Math.abs(distanceSwip)>10){//swip
				eitems.curMargin=distanceSwip>0 ? 0 : -this.extraWidth;
			}
			else{
				eitems.curMargin=eitems.curMargin>(-this.extraWidth/2) ? 0 : -this.extraWidth;
			}
			
			this.$extra.animate({marginLeft:eitems.curMargin},{duration:250, easing:'linear'});
			
			eitems.touchPointId=false;
			
			document.body.removeEventListener(eitems.events[1], wrapperMove, false);
			document.body.removeEventListener(eitems.events[2], wrapperEnd, false);	
			
		}
		
		//
		if(!data.length) data=[data];
		for (var i=0,j=data.length;i<j;i++){
			new Item(data[i]);
		}
		
	}
}



