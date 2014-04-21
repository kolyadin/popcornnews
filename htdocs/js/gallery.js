function Gallery(gallery) {
	this.gallery = gallery;
}

Gallery.prototype = {
	init: function() {
		this.decorate();
		this.initVars();
		this.getRemoteList();
		this.addEvents();
	},
	initVars: function() {
		this.list = as.getBTN("ul",this.gallery)[0];
		this.listWidth = this.list.parentNode.offsetWidth;
		this.visibleList = this.oVisibleList = this.nearActiveList = [];
		this.largePic = as.appendElement("img",as.getBCN("imgContainer","div",this.gallery)[0]);
		this.bw = 1;
		this.scrollInfo = {};
		
		this.decorate(true);
	},
	decorate: function(rest) {
		this.gallery.className = this.gallery.className.replace(/\bundecorated\b/,"");
		as.getBCN("paginator","div").length > 0 ? as.getBCN("paginator","div")[0].style.display = "none" : "";
		if (rest) {
			this.list.style.left = this.bw+"px";
			this.imgDescription = document.createElement("p");
			this.gallery.insertBefore(this.imgDescription,as.getBCN("previewsWrapper","div",this.gallery)[0]);
			this.imgDescription.className += " imgDescription";
		}
	},
	addEvents: function() {
		var _self_ = this;
		as.addEvent(
			as.getBCN("imageLeftScroller","div",this.gallery)[0],
			"click",
			function() {
				_self_.scrollImage("left")
			}
		);
		as.addEvent(
			as.getBCN("imageRightScroller","div",this.gallery)[0],
			"click",
			function() {
				_self_.scrollImage("right")
			}
		);
		as.addEvent(
			as.getBCN("listLeftScroller","div",this.gallery)[0],
			"click",
			function() {
				if (_self_.scrollLock) return;
				_self_.scrollList("left");
			}
		);
		as.addEvent(
			as.getBCN("listRightScroller","div",this.gallery)[0],
			"click",
			function() {
				if (_self_.scrollLock) return;
				_self_.scrollList("right");
			}
		);
		as.addEvent(
			this.list,
			"click",
			function(e) {
				_self_.handleListClick(e);
			}
		);
		as.addEvent(
			document,
			"keyup",
			function(e) {
				e = e || window.event;
				if (e.keyCode == 39 && e.ctrlKey && e.altKey) {as.cancelEvent(e);_self_.scrollList("right");}
				if (e.keyCode == 37 && e.ctrlKey && e.altKey) {as.cancelEvent(e);_self_.scrollList("left");}
				if (e.keyCode == 39 && e.ctrlKey && !e.altKey) {as.cancelEvent(e);_self_.scrollImage("right");}
				if (e.keyCode == 37 && e.ctrlKey && !e.altKey) {as.cancelEvent(e);_self_.scrollImage("left");}
			}
		);
		as.e.mwheel(this.list,function(e,direction) {			
			if (direction == "down") {
				if (this.scrollLock) return;
				this.scrollList("right");
			}
			else if (direction == "up") {
				if (this.scrollLock) return;
				this.scrollList("left");
			}
		},this);
	},
	initMouseScroll: function(event) {
		var _self_ = this;
		as.addEvent(
			this.list,
			event,
			function(e) {
				var e = e || window.event;
				var data  = e.detail || e.wheelDelta;
				var direction = as.getVScrollDirection(data);
				as.cancelEvent(e);
				if (direction == "down") {
					if (_self_.scrollLock) return;
					_self_.scrollList("right");
				}
				else if (direction == "up") {
					if (_self_.scrollLock) return;
					_self_.scrollList("left");
				}
			}
		); 	
	},
	getRemoteList: function() {
		var type, id, _self_ = this;
		if (location.toString().match(/persons\/\w*-*\w*-*\w*/)) {
			type = "persons",
			id = location.toString().match(/persons\/\w*-*\w*-*\w*/)[0].replace("persons\/","");
		}
		if (location.toString().match(/user\/\w*-*\w*-*\w*/)) {
			type = "user",
			id = location.toString().match(/user\/\w*-*\w*-*\w*/)[0].replace("user\/","");
		}
		if (location.toString().match(/profile\/\w*-*\w*-*\w*/)) {
			type = "user",
			id = location.toString().match(/profile\/\w*-*\w*-*\w*/)[0].replace("profile\/","");
		}
		if (location.toString().match(/community\/group\/\w*-*\w*-*\w*/)) {
			type = 'album';
			id = location.toString().match(/album\/\w*-*\w*-*\w*/)[0].replace("album\/","");
		}
		new vpa_ajax().makeRequest(
			"/ajax/gallery/"+type+"/"+id+"/",
			function(remoteList) {
				_self_.handleRemoteList(remoteList);
			}
		);
	},
	handleRemoteList: function(remoteList) {
		this.remoteList = eval(remoteList);
		for (var i=0,l=this.remoteList.length;i<l;i++) {
			this.remoteList[i].num = i;
		}
		if (location.hash) {
			this.buildStartList(location.hash.replace(/#img/,''));
			return;
		}
		if (location.toString().match(/img/)) {
			this.buildStartList(location.toString().match(/img\d*/)[0].replace(/img/,''));	
			return;
		}
		this.buildStartList();		
	},
	buildStartList: function(startElement) {
		this.list.innerHTML = "";
		var iterator = 0;
		if (startElement) {
			for (var i=0,l=this.remoteList.length;i<l;i++) {
				if (this.remoteList[i].id == startElement) {
					startElement = this.remoteList[i];
					iterator = i;
					break;
				}
			}
		}
		else {
			startElement = this.remoteList[0];	
		}
		this.activeItem = iterator;
		this.iterateVisible(iterator);
		this.changeVisible(iterator);
		this.changePic(iterator,true);
		this.setScrollInfo();
	},
	iterateVisible: function(iterator,otherSide) {
		this.visibleList = [];
		if (!otherSide) {
			var oIterator = iterator-1, ownWidth = fwWidth = bwWidth = 0;
			while (ownWidth < this.listWidth && this.remoteList[iterator]) {
				this.visibleList.push(iterator);
				ownWidth += this.remoteList[iterator].width + this.bw;
				iterator++;
				
				if (iterator == this.remoteList.length) {iterator = 0;}
			}
			while (fwWidth < this.listWidth && this.remoteList[iterator]) {
				this.visibleList.push(iterator);
				fwWidth += this.remoteList[iterator].width + this.bw;
				iterator++;
			}
			if (oIterator == -1) {oIterator = this.remoteList.length-1}
			while (bwWidth < this.listWidth && this.remoteList[oIterator]) {
				this.visibleList.push(oIterator);
				bwWidth += this.remoteList[oIterator].width + this.bw;
				oIterator--;
			}
		}
		else {
			var oIterator = iterator+1;ownWidth = fwWidth = bwWidth = 0;
			while (ownWidth < this.listWidth && this.remoteList[iterator]) {
				this.visibleList.push(iterator);
				ownWidth += this.remoteList[iterator].width + this.bw;
				iterator--;
				
				if (iterator == -1) {iterator = this.remoteList.length-1;}
			}
			while (bwWidth < this.listWidth && this.remoteList[iterator]) {
				this.visibleList.push(iterator);
				bwWidth += this.remoteList[iterator].width + this.bw;
				iterator--;
			}
			if (oIterator == this.remoteList.length) {oIterator = 0}
			while (fwWidth < this.listWidth && this.remoteList[oIterator]) {
				this.visibleList.push(oIterator);
				fwWidth += this.remoteList[oIterator].width + this.bw;
				oIterator++;
			}
		}
		if (this.activeChanged) {
			this.iterateNearActive(this.activeItem);
		}
		else {
			this.visibleList = this.union(this.visibleList,this.nearActiveList);
		}
	},
	iterateNearActive: function(active) {
		var bwWidth = fwWidth = 0, bwIterator = active-1, fwIterator = active+1;
		this.nearActiveList = [active];
		while (bwWidth < this.listWidth) {
			if (bwIterator == -1) {bwIterator = this.remoteList.length-1}
			bwWidth += this.remoteList[bwIterator].width;
			this.nearActiveList.push(bwIterator);
			bwIterator--;
		}
		while (fwWidth < this.listWidth) {
			if (fwIterator == this.remoteList.length) {fwIterator = 0}
			fwWidth += this.remoteList[fwIterator].width;
			this.nearActiveList.push(fwIterator);
			fwIterator++;
		}
		this.visibleList = this.union(this.visibleList,this.nearActiveList);
		this.activeChanged = false;
	},
	union: function(list1,list2) {
		var list = list1.concat(list2);
		var iterator = 0;
		list.sort(function(a,b){return a-b});		
		while (iterator < list.length -1) {
			if (list[iterator] == list[iterator+1]) {
				list.splice(iterator+1,1);
				continue;
			}
			iterator++;
		}
		return list;
	},
	changeVisible: function(firstItem,otherSide) {
		var _self_ = this;
		as.foreach(
			this.oVisibleList,
			function(oItem) {
				as.$("i"+oItem).className += " hidden";
			}
		);
		var visibleListWidth = 0;
		var listLeft = 0;
		var listRight = 0;
		as.foreach(
			this.visibleList,
			function(item) {
				_self_.remoteList[item].loaded || _self_.loadItem(item);
				as.$("i"+item).className = as.$("i"+item).className.replace(/\bhidden\b/gi,"");
				visibleListWidth += _self_.remoteList[item].width + _self_.bw;
				if (item < firstItem) {
					listLeft -= _self_.remoteList[item].width + _self_.bw;	
				}
				if (otherSide) {
					if (item > firstItem) {
						listRight += _self_.remoteList[item].width + _self_.bw;
					}
				}
			}
		);
		this.list.style.width = visibleListWidth+"px";
		if (otherSide) {listLeft = -(visibleListWidth - listRight - this.listWidth + this.bw);}
		if (-listLeft + this.listWidth > visibleListWidth) {listLeft = -(visibleListWidth - this.listWidth + this.bw);}
		if (listLeft > 0) {listLeft = 0}
		this.list.style.left = listLeft + this.bw + "px";
		this.list.style.width = visibleListWidth+"px";
		this.oVisibleList = this.visibleList;
	},
	setScrollInfo: function() {
		var visibleList = this.visibleList,
		widthCounter = -this.bw,
		listLeft = -parseInt(this.list.style.left),
		totalListWidth = parseInt(this.list.style.width),
		iterator = 0,
		left = right = 0,
		newLeftIterator,
		newRightIterator,
		attachedToLeft = attachedToRight = false;
		
		for (var i=0,l=visibleList.length;i<l;i++) {
			if (widthCounter == listLeft) {
				iterator = i;
				attachedToLeft = true;
				newLeftIterator = (i > 0) ? (visibleList[i-1]) : (visibleList[visibleList.length-1]);
				break;
			}
			widthCounter += (this.remoteList[visibleList[i]].width + this.bw);			
			if ((widthCounter + this.bw) == (listLeft + this.listWidth)) {
				iterator = i;
				attachedToRight = true;
				newRightIterator = (i < visibleList.length-1) ? (visibleList[i+1]) : (visibleList[0]);
				break;
			}
		}		
		if (attachedToLeft) {
			for (var i=iterator,l=visibleList.length;i<l;i++) {
				if (right > this.listWidth) {
					right -= this.remoteList[visibleList[i-1]].width + this.bw;
					newRightIterator = visibleList[i-1];
					break;
				}
				right += this.remoteList[visibleList[i]].width + this.bw;
			}
			left = this.listWidth - this.bw;
		}		
		if (attachedToRight) {
			for (var i=iterator;i>=0;i--) {
				if (left > this.listWidth) {
					left -= this.remoteList[visibleList[i+1]].width + 1;
					newLeftIterator = visibleList[i+1];
					break;
				}
				left += this.remoteList[visibleList[i]].width + 1;
			}
			right = this.listWidth - this.bw;
		}
		if (left > listLeft - this.bw) {left = listLeft + this.bw}
		if (listLeft == -this.bw) {left = -(totalListWidth - this.listWidth + this.bw)}
		if (listLeft + this.listWidth + right > totalListWidth) {right = totalListWidth - this.listWidth - listLeft}
		if (listLeft + this.listWidth == totalListWidth) {right = -(listLeft + this.bw)}
		this.scrollInfo = {left: {distance: left, iterator: newLeftIterator, otherSide: true}, right: {distance: -right, iterator: newRightIterator, otherSide: false}}
	},
	scrollImage: function(direction) {
		if (direction == "right") {
			if (this.activeItem == this.remoteList.length-1) {
				this.changePic(0);	
			}
			else {
				this.changePic(this.activeItem+1);	
			}
		}
		else {
			if (this.activeItem == 0) {
				this.changePic(this.remoteList.length-1);	
			}
			else {
				this.changePic(this.activeItem-1);	
			}
		}
	},
	scrollList: function(direction, oInfo) {
		var _self_ = this;
		var listLeft = parseInt(this.list.style.left);
		var speed = 20;	
		if (oInfo) {
			var distance = oInfo.distance;
			var iterator = oInfo.iterator;
			var otherSide = oInfo.otherSide;
			speed = oInfo.speed;
		}
		else {
			var distance = this.scrollInfo[direction].distance;
			var iterator = this.scrollInfo[direction].iterator;
			var otherSide = this.scrollInfo[direction].otherSide;
		}
		for (var i=0;i<speed;i++) {
			(function(i) {
				setTimeout(
					function() {
						_self_.list.style.left = listLeft + (distance/speed)*(i+1) + "px";
						if (i == speed-1) {
							_self_.iterateVisible(iterator,otherSide);
							_self_.changeVisible(iterator,otherSide);
							_self_.setScrollInfo();
						}
					},i*15
				)	  
			})(i);	
		}
	},
	loadItem: function(itemNumber) {
		var iterator = itemNumber-1;
		var after = null;
		while (this.remoteList[iterator]) {
			if (this.remoteList[iterator].loaded) {
				after = as.$("i"+this.remoteList[iterator].num);
				break;
			}
			iterator--;
		}
		var item = as.create("li");
		var remoteItem = this.remoteList[itemNumber];
		item.id = "i"+itemNumber;
		item.innerHTML = 
			"<a href='" + remoteItem.lsrc + 
			"'><img src='" + remoteItem.src + 
			"'/></a><div class='active' style='width: " + (remoteItem.width - 8) + "px'></div>"+
			"<span class='numWhite'>"+(itemNumber+1)+"</span>"+
			"<span class='numBlack'>"+(itemNumber+1)+"</span>";
		if (after) {
			if (after.nextSibling) {
				this.list.insertBefore(item,after.nextSibling);		
			}
			else {
				this.list.appendChild(item);	
			}
		}
		else {
			if (this.list.firstChild) {
				this.list.insertBefore(item,this.list.firstChild);
			}
			else {
				this.list.appendChild(item);	
			}
		}
		remoteItem.loaded = true;
	},
	handleListClick: function(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;
		as.cancelEvent(e);
		while (target.parentNode && target.tagName != "LI") {
			if (target.tagName == "A") {target.blur();}
			target = target.parentNode;	
		}
		this.changePic(target.id.replace(/i/,''));
	},
	
	changePic: function(num, nocheck) {
		num = Number(num);
		if (this.activeItem == num && !nocheck) {
			var toChange = (num == this.remoteList.length-1 ? 0 : num+1);
			this.changePic(toChange);
			return;
		}
		this.largePic.src = this.remoteList[num].lsrc;
		as.$("i"+this.activeItem).className = as.$("i"+this.activeItem).className.replace(/\bactive\b/,'');		
		as.$("i"+num).className += " active";
		location.hash = "img"+this.remoteList[num].id;
		this.activeItem = num;
		this.activeChanged = true;
		this.scrollToItem(num);
		this.imgDescription.innerHTML = this.remoteList[num].text;
	},
	
	scrollToItem: function(num) {
		var leftEdge = rightEdge = 0, listLeft = -parseInt(this.list.style.left), listLeftAndOwn = listLeft + this.listWidth;
		for (var i=0,l=this.visibleList.length;i<l;i++) {
			if (this.visibleList[i] == num) {
				rightEdge = leftEdge + this.remoteList[num].width + this.bw;
				break;
			}
			leftEdge += this.remoteList[this.visibleList[i]].width + this.bw;
		}
		leftEdge -= this.bw;
		if (rightEdge > listLeftAndOwn) {
			this.scrollList(null, {distance: listLeftAndOwn-rightEdge, iterator: num, otherSide: true, speed: 8});	
		}
		else if (leftEdge < listLeft) {
			this.scrollList(null, {distance: listLeft - leftEdge, iterator: num, otherSide: false, speed: 8});
		}
	}
}

var galleryController = {
	init: function() {
		this.list = [];
		var _self_ = this;
		var galleries = as.getBCN("gallery","div");
		as.foreach (
			galleries,
			function(gallery) {
				_self_.list[_self_.list.length] = new Gallery(gallery);
				_self_.list[_self_.list.length-1].init();
			}
		);
	}
}