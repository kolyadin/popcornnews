function gifts() {}
gifts.prototype = {
	init: function(){
		this.giftLinks = as.$('div.gift-list ul.gift-list li a');
		this.container = document.getElementById('gift-container');
		this.picture = document.getElementById('picture');
		this.noMoney = document.getElementById('noMoney');
		this.money = document.getElementById('money');
		this.attention = document.getElementById('attention');
		this.send = document.getElementById('send');
		this.giftPlace = document.getElementById('gift');
		this.giftsPoint = document.getElementById('giftsPoint');
		this.notEnough = document.getElementById('notEnough');
		this.giftAmount = null;
		
		this.actions();
	},
	actions: function(){
		var self = this;
		for (var i=0;i<this.giftLinks.length;i++){
			(function(i){
				self.giftLinks[i].onclick = function(e){
					var event = e || window.event;
					event.preventDefault ? event.preventDefault() : event.returnValue = false;
					for (var i=0;i<self.giftLinks.length;i++){
						self.giftLinks[i].parentNode.className = self.giftLinks[i].parentNode.className.replace(/\bactive\b/gi,'');
					}
					self.giftAmount = as.$$("img",this).alt;
					this.parentNode.className += ' active';
					self.picture.src = this.href;
					self.send.style.display = 'block';
					var check = self.checking();
					self.container.style.display = 'block';
					if (this.className.match(/\bpaid\b/)){
						if (check){
							self.giftsPoint.innerHTML = render_word(self.giftAmount, 'списан', 'списано', 'списано') + ' <strong>' + self.giftAmount + '</strong> ' + render_word(self.giftAmount, 'балл', 'балла', 'баллов');
							self.send.style.display = 'block';
							self.attention.style.display = 'block';
							self.noMoney.style.display = 'none';
						} else {
							var diff = self.giftAmount - self.money.innerHTML;
							self.notEnough.innerHTML = 'Вам не хватает ' + diff + ' ' + render_word(diff, 'балла', 'баллов', 'баллов');
							self.send.style.display = 'none';
							self.attention.style.display = 'none';
							self.noMoney.style.display = 'block';
						}
						self.giftPlace.innerHTML = '';
						self.getFlash(this.href);
					} else {
						if(check){
							self.send.style.display = 'block';
							self.attention.style.display = 'none';
							self.noMoney.style.display = 'none';
						} else {
							self.send.style.display = 'block';
							self.attention.style.display = 'none';
							self.noMoney.style.display = 'none';
						}
						self.giftPlace.innerHTML = '<img src='+this.href+' width="250" height="250" />';
					}
					document.getElementById('gift_id').value = this.title;

//					try {
//						as.getBCN("submitMessage","input")[0].focus();
//					} catch (e) {}
				}
			})(i)
		}
	},
	checking: function(){
		if (parseInt(this.money.innerHTML) < this.giftAmount) {
			return false;
		} else {
			return true;
		}
	},
	getFlash: function(href){
		var realEstate = new SWFObject(href, "gift", "250", "250", "9.0.0");
		realEstate.addParam("wmode","transparent");
		realEstate.write("gift");
	},
	stopEvent: function(e){
		var event = e || window.event;
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
	}
}
new gifts().init();