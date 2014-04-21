/*
	Делаем фотки в блоке на весь экран и обратно. Принимаем объект со следующими параметрами:
	- menuUid : id меню
	- boxUid : id блока с фотографиями
	
*/

Photoblock=function(data){this.init(data)};
Photoblock.prototype={
	init:function(data){
		this.createElements(data);
		this.addwitch();
		
	},
	addwitch:function(){
		var obj=this;
		this.$miniLink.click(function(event){
			event.preventDefault();
			if(obj.$miniLink.hasClass('active')) return false;
			obj.$miniLink.addClass('active');
			obj.$maxLink.removeClass('active');
			obj.changeView();
		});
		this.$maxLink.click(function(event){
			event.preventDefault();
			if(obj.$maxLink.hasClass('active')) return false;
			obj.$maxLink.addClass('active');
			obj.$miniLink.removeClass('active');
			obj.changeView();
		});
	},
	changeView:function(){
		this.$photoBlock.toggleClass('photo-block_wide');
	},
	createElements:function(data){
		this.$menu=$('#'+data.menuUid);
		this.$miniLink=this.$menu.find('[data-photo-view="mini"]');
		this.$maxLink=this.$menu.find('[data-photo-view="max"]');
		this.$photoBlock=$('#'+data.boxUid);
	}
}