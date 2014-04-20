function popup(){
	this.ready();
}
popup.prototype = {
	init: function(){
		this.wrapper = document.getElementById('wrapper');
		this.bg = document.getElementById('popup-bg');
		this.toper = as.$('#voty dl');
		this.dl = as.w('#voty dl');
		this.popup = document.getElementById('popup');
		this.closer = as.w('#popup #close');
		this.photo = document.getElementById('photo');
		this.popup.style.display = 'none';
		this.bg.style.display = 'none';
		this.image = [];
		this.bg.style.width = '980px';           // document.body.offsetWidth + 'px';
		this.bg.style.height = this.wrapper.clientHeight + 'px';
		this.l = document.getElementById('linkL');
		this.r = document.getElementById('linkR');
		this.l.style.display = 'block';
		this.r.style.display = 'block';
		this.name = document.getElementById('name');
		this.spanL = document.getElementById('l');
		this.spanR = document.getElementById('r');
	},
	createPopup: function(i,src,images,name){
		var _self_ = this;
		var toper = this.toper;
		var popup = this.popup;
		var href = [];
		var iter = i;
		if (i == images.length-1){this.r.style.display = 'none';} else{this.r.style.display = 'block';}
		if (i == 0){this.l.style.display = 'none';} else{this.l.style.display = 'block';}
		this.photo.src = src;
		this.name.innerHTML = name[i].innerHTML;
		this.popup.style.display = 'none' ? this.popup.style.display = 'block' : "";
		//this.bg.style.display = 'none' ? this.bg.style.display = 'block' : "";
		for (var i=0;i<toper.length;i++){
			(function(i){
				toper[i].onclick = function(){
					popup.style.top = as.getElementPosition(toper[i]).top - popup.offsetHeight/2 + toper[i].offsetHeight/2  + 'px';
					popup.style.left = as.getElementPosition(toper[i]).left + popup.offsetWidth/2 - 100 + 'px';
					href = as.$("a.decor-voty",this);
					_self_.nextPrev(iter,src,images,name,href);
				}
			})(i)
		}
	},
	nextPrev: function(iter,src,images,name,href){
		var _self_ = this;
		this.r.onclick = function(e){
			var event = e || window.event;
			event.preventDefault ? event.preventDefault() : event.returnValue = false;
			iter = iter+1;
			_self_.l.style.display = 'block';
			if (iter == images.length-1){this.style.display = 'none'; _self_.l.style.display = 'block';}
			_self_.photo.src = href[iter].href;
			_self_.name.innerHTML = name[iter].innerHTML;
		}
		this.l.onclick = function(e){
			var event = e || window.event;
			event.preventDefault ? event.preventDefault() : event.returnValue = false;
			_self_.r.style.display = 'block';
			iter = iter-1;
			if (iter == 0){this.style.display = 'none'; _self_.r.style.display = 'block';}
			_self_.photo.src = href[iter].href;
			_self_.name.innerHTML = name[iter].innerHTML;
		}
	},
	clickOn: function(){
		var _self_ = this;
		this.dl.each(function() {
			as.w("a.decor-voty",this).each(function(i) {
				as.e.click(this,function(e) {
					e.preventDefault();
					var ava = as.$$("a.decor-voty",as.parent(this,"dt")).href;
					var name = as.$('span.name',this.parentNode.parentNode);
					var images = as.$('img.ava',this.parentNode.parentNode);
					_self_.createPopup.call(_self_,i,ava,images,name);
				});
			})
		});
	},
	closePopup: function(e){
		e.preventDefault();
		this.popup.style.display = 'block' ? this.popup.style.display = 'none' : "";
		this.bg.style.display = 'block' ? this.bg.style.display = 'none' : "";
	},
	closeOff: function(){
		this.closer.click(this.closePopup,this);
	},
	ready: function(){
		this.init();
		this.clickOn();
		this.closeOff();
	}
}
new popup();