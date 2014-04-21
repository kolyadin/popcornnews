function render_word(num, word1, word2, word3) {
	try {
		last = num.substr(-2);
	} catch (e) {
		num += '';
		last = num.substr(-2);
	}
	if (last >= 10 && last <= 20) {
		return word3;
	}
	last = num.substr(-1);
	if (last == 1) {
		return word1;
	} else if (last >= 2 && last <= 4) {
		return word2;
	} else {
		return word3;
	}
}

function htmlentities(s) {
	var div = document.createElement('div');
	var text = document.createTextNode(s);
	div.appendChild(text);
	return div.innerHTML;
}

function htmlspecialchars(text) {
	var chars = Array("&", "<", ">", '"', "'");
	var replacements = Array("&amp;", "&lt;", "&gt;", "&quot;", "'");
	for (var i=0; i<chars.length; i++) {
		var re = new RegExp(chars[i], "gi");
		if(re.test(text)) {
			text = text.replace(re, replacements[i]);
		}
	}
	return text;
}

/* hide or display objects by selector*/
function hide_display(value, selector) {
	as.w(selector).each(function() {
		this.style.display = (value ? 'block' : 'none');
	});
	return true;
}
/* \hide or display objects by selector*/

/*next element*/
function next(element) {
	if (element.nextElementSibling) {
		return element.nextElementSibling;
	} else if (element.nextSibling) {
		return element.nextSibling;
	}
}
/*\next element*/

/*prev element*/
function prev(element) {
	if (element.previousElementSibling) {
		return element.previousElementSibling;
	} else if (element.previousSibling) {
		return element.previousSibling;
	}
}
/*\prev element*/

/*form ctrl+enter submit*/
function form_from_textarea_submit(event, form) {
	if ( ((event.keyCode == 13) || (event.keyCode == 10)) && (event.ctrlKey == true) ) {
		form.submit();
	}
}
/*\form ctrl+enter submit*/

var userRating = {
	activateDots: function() {
		/*@cc_on
			@if (@_jscript_version < 5.7)
				return;
			@end
		@*/
		var addDots = function(parent, elements) {
			for (var i=0,l=elements.length;i<l;i++) {
				var dot = document.createElement("div");
				dot.className = "dot "+elements[i];
				parent.appendChild(dot);
			}
		}
		var userRatings = as.getBCN("userRating","div");
		as.foreach(
			userRatings,
			function(item) {
				addDots(
					item,
					["ltdot","rtdot","rbdot","lbdot"]
				);
			}
		);
	}
}

function GonnaExit(ge) {
	this.ge = ge;
}
GonnaExit.prototype = {
	getDLs: function() {
		this.DLs = as.getBTN("dl",this.ge);
	},
	addStrikesFades: function() {
		as.foreach(
			this.DLs,
			function(dl) {
				var dt = as.getBTN("dt",dl)[0];
				var strikeDiv = as.appendElement("div",dt);
				strikeDiv.className+=" strike";
				var fadeDiv = as.appendElement("div",dt);
				fadeDiv.className+=" fade";
			}
		)
	},
	initHoverStriking: function() {
		var _self_ = this;
		as.foreach(
			this.DLs,
			function(dl) {
				as.addEvent(
					dl,
					"mouseenter",
					function(e) {
						dl.className += " hovered";
					}
				);
				as.addEvent(
					dl,
					"mouseleave",
					function(e) {
						e = e || window.event;
						dl.className = dl.className.replace(/\bhovered\b/gi,"")
					}
				);
			}
		);

	},
	initFading: function() {
		var _self_ = this;
		as.foreach(
			this.DLs,
			function(dl) {
				as.addEvent(
					dl,
					"click",
					function() {
						var cb = as.getBTN("input",dl)[0];
						cb.checked = !cb.checked;
						dl.className.match(/\bfaded\b/) ? (dl.className = dl.className.replace(/\bfaded\b/,"")) : (dl.className += " faded");
						dl.focus();
					}
				);
			}
		);
	},
	decorate: function() {
		this.ge.className = this.ge.className.replace(/\bundecorated\b/,"")
	},
	init: function() {
		this.getDLs();
		this.decorate();
		this.addStrikesFades();
		/*@cc_on
			@if (@_jscript_version < 5.7)
				this.initHoverStriking();
			@end
		@*/
		this.initFading();
	}
}

var EqualsContainer = function(equalsContainer) {
	this.ec = equalsContainer;
}
EqualsContainer.prototype = {
	equalTerms: function() {
		this.terms = as.getBTN("dt",this.ec);
		this.defs = as.getBTN("dd",this.ec);
		if (this.terms.length==0) {
			this.terms = as.getBTN("li",this.ec);
		}
		var maxHeight = 0;
		as.foreach(
			this.terms,
			function(term) {
				if (term.offsetHeight > maxHeight) {
					maxHeight = term.offsetHeight;
				}
			}
		);
		as.foreach(
			this.terms,
			function(term) {
				term.style.height = maxHeight+"px";
			}
		);
		if (this.defs.length) {
			var dmh = 0;
			as.foreach(
				this.defs,
				function(def) {
					if (def.offsetHeight > dmh) {
						dmh = def.offsetHeight;
					}
				}
			);
			as.foreach(
				this.defs,
				function(def) {
					def.style.height = dmh+"px";
				}
			);
		}
		this.ec.className+=" equaled";
	},
	initVars: function() {
		this.images = as.getBTN("img",this.ec);
	},
	tryOnImagesLoad: function() {
		var _self_ = this;
		if (/MSIE/.test(navigator.userAgent)) {
			as.addEvent(window,"load",function(){_self_.onImagesLoad();});
			return;
		}
		var imagesload = window.setInterval(
			function() {
				var complete = true;
				as.foreach(
					_self_.images,
					function(image,i) {
						if (!complete) return;
						if (!image.complete) {
							complete = false;
							return;
						}
					}
				);
				if (!complete) return;
				_self_.onImagesLoad();
				window.clearInterval(imagesload);
			},300
		);
	},
	onImagesLoad: function() {
		this.equalTerms();
	},
	init: function() {
		this.initVars();
		this.tryOnImagesLoad();
	}
}


var gonnaExitController = {
	init: function() {
		this.list = [];
		var _self_ = this;
		var gonnaExitList = as.getBCN("gonnaExit","div");
		as.foreach(
			gonnaExitList,
			function(listItem) {
				_self_.list[_self_.list.length] = new GonnaExit(listItem);
				_self_.list[_self_.list.length-1].init();
			}
		);
	}
}

var equalsContainersController = {
	init: function() {
		this.list = [];
		var _self_ = this;
		var equalsContainersList = as.getBCN("equalsContainer");;
		as.foreach(
			equalsContainersList,
			function(listItem) {
				_self_.list[_self_.list.length] = new EqualsContainer(listItem);
				_self_.list[_self_.list.length-1].init();
			}
		);
	}
}

var messages = {
	initShowing: function() {
		var shower = as.getBCN("showMessages","a");
		if (shower.length == 0) return false;
		shower[0].onclick = function() {
			var hiddens = as.getBCN("hidden","*",as.$("content"));
			as.foreach(
				hiddens,
				function(hidden) {
					hidden.className = hidden.className.replace(/\bhidden\b/,"");
				}
			);
			this.parentNode.removeChild(this);
			return false;
		}
	},
	init: function() {
		this.initShowing();
	}
}



function Smile(smile) {
	this.smile = smile;
}
Smile.prototype = {
	showSmiles: function() {
		this.smile.className = this.smile.className.replace(/\bhiddenBlock\b/,"");
	},
	setElements: function() {
		this.header = as.getBCN("header","span",this.smile)[0];
		this.smContainer = as.getBCN("smilesContainer","div",this.smile)[0];
		this.textarea = as.getBTN("textarea",this.smile.parentNode)[0];
		this.smilesHidden = /\bhiddenSmiles\b/.test(this.smile.className);
		this.hideSmilesBlock();
	},
	initHiding: function() {
		var _self_ = this;
		as.addEvent(
			this.header,
			"click",
			function() {
				_self_.showHideSmilesBlock();
			}
		);
	},
	showHideSmilesBlock: function() {
		if (this.smilesHidden) {
			this.showSmilesBlock();
		}
		else {
			this.hideSmilesBlock();
		}
	},
	showSmilesBlock: function() {
		this.smile.className = this.smile.className.replace(/\bhiddenSmiles\b/gi,"");
		this.header.innerHTML = "Скрыть смайлы";
		this.smilesHidden = false;
	},
	hideSmilesBlock: function() {
		this.smile.className += " hiddenSmiles";
		this.header.innerHTML = "Показать смайлы";
		this.smilesHidden = true;
	},
	getPosition: function() {
		this.textarea.focus();
		if (typeof(this.textarea.selectionStart)=="number") {
			return this.textarea.selectionStart;
		}
		else if (document.selection) {
			var selection = document.selection.createRange();
			var copy = selection.duplicate();
			selection.collapse(true);
			copy.moveToElementText(this.textarea);
			copy.setEndPoint("EndToEnd",selection);
			return copy.text.length;
		}
		return this.textarea.value.length-1;
	},
	setPosition: function(length) {
		this.textarea.focus();
		if (this.textarea.setSelectionRange) {
			this.textarea.setSelectionRange(length,length);
		}
		else if (document.selection) {
			var textRange = this.textarea.createTextRange();
			textRange.collapse(true);
			textRange.select();
			var range = document.selection.createRange();
			range.moveStart("character",length);
			range.moveEnd("character",0);
			range.select();
		}
	},
	initSmileInserting: function() {
		var _self_ = this;
		as.addEvent(
			this.smContainer,
			"click",
			function(e) {
				e = e || window.event;
				var target = e.target || e.srcElement;
				if (target.tagName != "IMG") {
					return;
				}
				var value = _self_.textarea.value;
				var position = _self_.getPosition();
				var text = target.onclick().text;
				_self_.textarea.value = value.substring(0,position)+text+value.substring(position,value.length);
				_self_.setPosition(position+text.length);
			}
		);
	},
	init: function() {
		this.showSmiles();
		this.setElements();
		this.initHiding();
		this.initSmileInserting();
	}
}


function UlMark(ulMark) {
	this.ulMark = ulMark;
}

UlMark.prototype = {
	initVars: function() {
		this.links = as.getBTN("a",this.ulMark);
		this.registered = USER_LOGGED_IN;
		UlMark.tooltipShown = false;
	},
	initHandleUnregistered: function() {
		var _self_ = this;
		as.addEvent(
			this.ulMark,
			"click",
			function(e) {
				_self_.handleUnregistered(e);
			}
		)
	},
	handleUnregistered: function(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;
		as.cancelEvent(e);
		this.showTooltip(target);
	},
	showTooltip: function(place) {
		if (UlMark.tooltipShown == true) return;
		var _self_ = this;
		var coords = as.getElementPosition(place);
		var body = as.getBTN("body")[0];
		var tooltip = as.appendElement("div",body);
		tooltip.className+=" tooltip";
		as.style(tooltip,{left: coords.left+"px",top: coords.top-45+"px"});
		if (!this.registered) {
			tooltip.innerHTML+="Вы не авторизованы и не можете голосовать";
		}
		else {
			tooltip.innerHTML+="Ваш голос принят";
		}
		UlMark.tooltipShown = true;
		window.setTimeout(
			function() {
				as.removeChild(tooltip);
				UlMark.tooltipShown = false;
			},2000
		);
		place.blur();
	},
	init: function() {
		this.initVars();
		this.initHandleUnregistered();
	}
}


/*** PRIVATE MESSAGES */
function PrivateMessage(form) {
	this.form = form;
}
PrivateMessage.prototype = {
	init: function() {
		this.initVars();
		this.initCTRLENTERSubmit();
		if (!this.select) {return;}
		this.replaceSelect();
		this.initSuggest();
	},
	initVars: function() {
		this.select = as.getBCN("selectReciever","select",this.form)[0];
		this.submit = as.getBCN("submitMessage","input",this.form)[0];
		this.URLPrefix = "/ajax/users/";
		this.textarea = as.getBTN("textarea",this.form)[0];
		this.TXTCTRL = false;
	},
	initCTRLENTERSubmit: function() {
		var _self_ = this;
		as.addEvent(
			this.textarea,
			"keydown",
			function(e) {
				e = e || window.event;
				if (e.keyCode == "17") {
					_self_.TXTCTRL = true;
				}
				if (e.keyCode == "13" && _self_.TXTCTRL) {
					_self_.form.submit();
				}
			}
		);
		as.addEvent(
			this.textarea,
			"keyup",
			function(e) {
				e = e || window.event;
				if (e.keyCode == "17") {
					_self_.TXTCTRL = false;
				}
			}
		);
	},
	replaceSelect: function() {
		this.suggestContainer = as.insertBefore("div",this.form,this.select);
		this.suggestContainer.className += " suggestContainer";
		this.suggestContainer.innerHTML += "<input type='hidden' name='uid' id='suggesterHidden' />"
		this.suggesterHidden = as.$("suggesterHidden");
		this.suggester = as.appendElement("input",this.suggestContainer);
		this.suggester.className += " suggester";
		this.suggestListContainer = as.appendElement("div",this.suggestContainer);
		this.suggestListContainer.className += " suggestListContainer";
		this.suggester.focus();
		as.removeChild(this.select);
	},
	initSuggest: function() {
		var _self_ = this;
		as.addEvent(
			this.form,
			"submit",
			function(e) {
				e = e || window.event;
				as.cancelEvent(e);
				return false;
			}
		);
		as.addEvent(
			this.submit,
			"click",
			function() {
				_self_.form.submit();
			}
		);
		as.addEvent(
			this.suggester,
			"keydown",
			function(e) {
				_self_.handleKeyDown(e);
			}
		);
		as.addEvent(
			this.suggester,
			"keypress",
			function(e) {
				_self_.handleKeyPress(e);
			}
		);
		as.addEvent(
			this.suggester,
			"click",
			function() {
				if (!_self_.lock) {
					_self_.lock = true;
					window.setTimeout(
						function() {
							_self_.suggest();
							_self_.lock = false;
						},300
					);
				}
			}
		);
		as.addEvent(
			this.suggester,
			"blur",
			function(e) {
				window.setTimeout(
					function() {
						_self_.clearSuggest();
					},200
				)
			}
		);
	},
	handleKeyPress: function(e) {
		e = e || window.event;
		if (e.keyCode == "13") {
			as.cancelEvent(e);
			if (this.suggestList) {
				this.selectItem();
			}
			else {
				this.form.submit();
			}
			return;
		}
	},
	handleKeyDown: function(e) {
		e = e || window.event;
		var _self_ = this;
		/* enter press */
		if (e.keyCode == "9") {
			this.clearSuggest();
			return;
		}
		if (e.keyCode == "13") {return;}
		/* up/down press */
		else if (e.keyCode == "38") {
			if (this.suggestList) {
				this.activeUp();
			}
			else {
				this.suggest();
			}
			return;
		}
		else if (e.keyCode == "40") {
			if (this.suggestList) {
				this.activeDown();
			}
			else {
				this.suggest();
			}
			return;
		}
		/* else */
		if (!this.lock) {
			this.lock = true;
			window.setTimeout(
				function() {
					_self_.suggest();
					_self_.lock = false;
				},300
			)
		}

	},
	suggest: function(e) {
		var _self_ = this;
		this.clearSuggest();
		var ajaxSuggest = new vpa_ajax();
		ajaxSuggest.makeRequest(
			this.URLPrefix+this.suggester.value,
			function(suggestHash){
				_self_.createSuggest(suggestHash);
			},
			this
		);
	},
	selectItem: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		if (!active.length) {return;}
		var active = active[0];
		this.suggesterHidden.value = active.id;
		this.suggester.value = active.innerHTML;
		this.clearSuggest();
	},
	activeUp: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		var items = as.getBTN("li",this.suggestList);
		if (!active.length) {
			items[items.length-1].className+=" active";
		}
		else {
			var activeNum;
			for (var i=0;items[i];i++) {
				if (items[i] == active[0]) {
					activeNum = i;
				}
			}
			if (activeNum==0) {
				items[items.length-1].className+=" active";
			}
			else {
				items[activeNum-1].className+=" active";
			}
			active[0].className = active[0].className.replace(/\bactive\b/,"");
		}
	},
	activeDown: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		var items = as.getBTN("li",this.suggestList);
		if (!active.length) {
			items[0].className+=" active";
		}
		else {
			var activeNum;
			for (var i=0;items[i];i++) {
				if (items[i] == active[0]) {
					activeNum = i;
				}
			}
			if (activeNum==items.length-1) {
				items[0].className+=" active";
			}
			else {
				items[activeNum+1].className+=" active";
			}
			active[0].className = active[0].className.replace(/\bactive\b/,"");
		}
	},
	clearSuggest: function() {
		if (!this.suggestList) {return;}
		as.removeChild(this.suggestList);
		this.suggestList = null;
	},
	createSuggest: function(suggestHash) {
		var suggestHash = eval(suggestHash);
		if (!suggestHash.length) {return}
		var _self_ = this;
		this.suggestListContainer.innerHTML = "";
		this.suggestList = as.appendElement("ul",this.suggestListContainer);
		var i = 0;
		while (suggestHash[i] && i<10) {
			var li = as.appendElement("li",this.suggestList);
			li.innerHTML = suggestHash[i].name;
			li.id = suggestHash[i].id;
			li.onmouseover = function() {
				this.className+=" active";
			}
			li.onmouseout = function() {
				this.className = this.className.replace(/\bactive\b/,"");
			}
			li.onclick = function() {
				_self_.suggesterHidden.value = this.id;
				_self_.suggester.value = this.innerHTML;
				_self_.clearSuggest();
			}
			i++;
		}
		if (suggestHash.length > 10) {
			var lastLi = as.appendElement("div",this.suggestList);
			lastLi.innerHTML = "...";
		}
	}
}

/*\\ PRIVATE MESSAGES */

/*** FANFICS */
function Fanfics(form) {
	this.form = form;
}
Fanfics.prototype = {
	init: function() {
		this.initVars();
		this.initCTRLENTERSubmit();
		if (!this.select) {return;}
		this.replaceSelect();
		this.initSuggest();
	},
	initVars: function() {
		this.select = as.getBCN("selectReciever","select",this.form)[0];
		this.submit = as.getBCN("submitFanfic","input",this.form)[0];
		this.URLPrefix = "/ajax/persons/";
		this.textarea = as.getBTN("textarea",this.form)[0];
		this.TXTCTRL = false;
	},
	initCTRLENTERSubmit: function() {
		var _self_ = this;
		as.addEvent(
			this.textarea,
			"keydown",
			function(e) {
				e = e || window.event;
				if (e.keyCode == "17") {
					_self_.TXTCTRL = true;
				}
				if (e.keyCode == "13" && _self_.TXTCTRL) {
					_self_.form.submit();
				}
			}
		);
		as.addEvent(
			this.textarea,
			"keyup",
			function(e) {
				e = e || window.event;
				if (e.keyCode == "17") {
					_self_.TXTCTRL = false;
				}
			}
		);
	},
	replaceSelect: function() {
		this.suggestContainer = as.insertBefore("div",this.form,this.select);
		this.suggestContainer.className += " suggestContainer";
		this.suggestContainer.innerHTML += "<input type='hidden' name='pid' id='suggesterHidden' />"
		this.suggesterHidden = as.$("suggesterHidden");
		this.suggester = as.appendElement("input",this.suggestContainer);
		this.suggester.className += " suggester";
		this.suggestListContainer = as.appendElement("div",this.suggestContainer);
		this.suggestListContainer.className += " suggestListContainer";
		this.suggester.focus();
		as.removeChild(this.select);
	},
	initSuggest: function() {
		var _self_ = this;
		as.addEvent(
			this.form,
			"submit",
			function(e) {
				e = e || window.event;
				as.cancelEvent(e);
				return false;
			}
		);
		as.addEvent(
			this.submit,
			"click",
			function() {
				_self_.form.submit();
			}
		);
		as.addEvent(
			this.suggester,
			"keydown",
			function(e) {
				_self_.handleKeyDown(e);
			}
		);
		as.addEvent(
			this.suggester,
			"keypress",
			function(e) {
				_self_.handleKeyPress(e);
			}
		);
		as.addEvent(
			this.suggester,
			"click",
			function() {
				if (!_self_.lock) {
					_self_.lock = true;
					window.setTimeout(
						function() {
							_self_.suggest();
							_self_.lock = false;
						},300
					);
				}
			}
		);
		as.addEvent(
			this.suggester,
			"blur",
			function(e) {
				window.setTimeout(
					function() {
						_self_.clearSuggest();
					},200
				)
			}
		);
	},
	handleKeyPress: function(e) {
		e = e || window.event;
		if (e.keyCode == "13") {
			as.cancelEvent(e);
			if (this.suggestList) {
				this.selectItem();
			}
			else {
				this.form.submit();
			}
			return;
		}
	},
	handleKeyDown: function(e) {
		e = e || window.event;
		var _self_ = this;
		/* enter press */
		if (e.keyCode == "9") {
			this.clearSuggest();
			return;
		}
		if (e.keyCode == "13") {return;}
		/* up/down press */
		else if (e.keyCode == "38") {
			if (this.suggestList) {
				this.activeUp();
			}
			else {
				this.suggest();
			}
			return;
		}
		else if (e.keyCode == "40") {
			if (this.suggestList) {
				this.activeDown();
			}
			else {
				this.suggest();
			}
			return;
		}
		/* else */
		if (!this.lock) {
			this.lock = true;
			window.setTimeout(
				function() {
					_self_.suggest();
					_self_.lock = false;
				},300
			)
		}

	},
	suggest: function(e) {
		var _self_ = this;
		this.clearSuggest();
		var ajaxSuggest = new vpa_ajax();
		ajaxSuggest.makeRequest(
			this.URLPrefix+this.suggester.value,
			function(suggestHash){
				_self_.createSuggest(suggestHash);
			},
			this
		);
	},
	selectItem: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		if (!active.length) {return;}
		var active = active[0];
		this.suggesterHidden.value = active.id;
		this.suggester.value = active.innerHTML;
		this.clearSuggest();
	},
	activeUp: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		var items = as.getBTN("li",this.suggestList);
		if (!active.length) {
			items[items.length-1].className+=" active";
		}
		else {
			var activeNum;
			for (var i=0;items[i];i++) {
				if (items[i] == active[0]) {
					activeNum = i;
				}
			}
			if (activeNum==0) {
				items[items.length-1].className+=" active";
			}
			else {
				items[activeNum-1].className+=" active";
			}
			active[0].className = active[0].className.replace(/\bactive\b/,"");
		}
	},
	activeDown: function() {
		if (!this.suggestList) {return;}
		var active = as.getBCN("active","li",this.suggestList);
		var items = as.getBTN("li",this.suggestList);
		if (!active.length) {
			items[0].className+=" active";
		}
		else {
			var activeNum;
			for (var i=0;items[i];i++) {
				if (items[i] == active[0]) {
					activeNum = i;
				}
			}
			if (activeNum==items.length-1) {
				items[0].className+=" active";
			}
			else {
				items[activeNum+1].className+=" active";
			}
			active[0].className = active[0].className.replace(/\bactive\b/,"");
		}
	},
	clearSuggest: function() {
		if (!this.suggestList) {return;}
		as.removeChild(this.suggestList);
		this.suggestList = null;
	},
	createSuggest: function(suggestHash) {
		var suggestHash = eval(suggestHash);
		if (!suggestHash.length) {return}
		var _self_ = this;
		this.suggestListContainer.innerHTML = "";
		this.suggestList = as.appendElement("ul",this.suggestListContainer);
		var i = 0;
		while (suggestHash[i] && i<10) {
			var li = as.appendElement("li",this.suggestList);
			li.innerHTML = suggestHash[i].name;
			li.id = suggestHash[i].id;
			li.onmouseover = function() {
				this.className+=" active";
			}
			li.onmouseout = function() {
				this.className = this.className.replace(/\bactive\b/,"");
			}
			li.onclick = function() {
				_self_.suggesterHidden.value = this.id;
				_self_.suggester.value = this.innerHTML;
				_self_.clearSuggest();
			}
			i++;
		}
		if (suggestHash.length > 10) {
			var lastLi = as.appendElement("div",this.suggestList);
			lastLi.innerHTML = "...";
		}
	}
}
/*\\ FANFICS */


/*** QUESTIONNAIRE */
function Questionnaire(qst) {
	this.qst = qst;
}
Questionnaire.prototype = {
	init: function() {
		this.initVars();
		if (!this.countrySelect) {return;}
		this.initCitySelect();
		this.onLoadCitySelect();
	},
	initVars: function() {
		var _self_ = this;
		this.countryMap = {};
		this.countryMapFull = {};
		var selects = as.getBTN("select",this.qst);
		as.foreach(
			selects,
			function(sel) {
				if (sel.name == "country") {
					_self_.countrySelect = sel;
				}
				if (sel.name == "city") {
					_self_.citySelect = sel;
				}
			}
		);
		this.requestPrefix = "/ajax/cities/";
	},
	initCitySelect: function() {
		var _self_ = this;
		as.addEvent(
			this.countrySelect,
			"change",
			function() {
				var value = _self_.countrySelect.options[_self_.countrySelect.selectedIndex].value;
				_self_.getCitiesList(value);
			}
		);
	},
	onLoadCitySelect: function() {
		var value = this.countrySelect.options[this.countrySelect.selectedIndex].value;
		this.getCitiesList(value);
	},
	getCitiesList: function(value) {
		var _self_ = this;
		this.showLoading();
		if (this.countryMap[value]) {
			this.createList(this.countryMap[value],value);
		}
		else {
			var citiesList = new vpa_ajax();
			citiesList.makeRequest(
				this.requestPrefix + value,
				function(list) {
					_self_.createList(list,value);
				}
			);
		}
	},
	createList: function(list,id) {
		var _self_ = this;
		list = eval( list );
		this.countryMap[id] = list;
		var citySelect = as.after("select",this.citySelect);
		as.remove(this.citySelect);
		this.citySelect = citySelect;
		this.citySelect.name = "city";
		this.citySelect.className = "city";
		as.foreach(
			list,
			function(item) {
				var option = as.create("option");
				if(item.selected) {
					option.selected = "selected";
				}
				option.value = item.id;
				option.innerHTML = item.name;
				_self_.citySelect.appendChild(option);
			}
		);
		this.prevOption = _self_.citySelect.options[_self_.citySelect.selectedIndex];
		var lastOption = as.create("option");
		lastOption.value = "0";
		lastOption.innerHTML = "Другой";
		this.citySelect.appendChild(lastOption);
		this.stopShowLoading();
		this.citySelect.onchange = function() {
			if (_self_.citySelect.options[_self_.citySelect.selectedIndex].value == "0") {
				if (_self_.countryMapFull[id]) {
					setTimeout(
						function() {
							_self_.createFullCityList(_self_.countryMapFull[id],id);
						},10
					);
				}
				else {
					var fullCityList = new vpa_ajax();
					fullCityList.makeRequest(
						_self_.requestPrefix + id + "/full",
						function(fullList) {
							_self_.createFullCityList(fullList,id);
						}
					);
				}
			}
			else {
				_self_.prevOption = _self_.citySelect.options[_self_.citySelect.selectedIndex];
			}
		}
	},
	createFullCityList: function(fullList,id) {
		var _self_ = this;
		fullList = eval( fullList );
		this.countryMapFull[id] = fullList;
		if (this.countryMap[id].length == fullList.length) {return;}
		var bgw = as.create("div");
		var flw = as.create("div");
		var flwHeader = as.create("div");

		bgw.className = "bgOverlayWrapper";
		flw.className = "fullCityList";
		flwHeader.className = "flwHeader";

		as.appendElement(flw,bgw);

		as.appendElement("h3",flwHeader,"Название города");
		var overlayCloser = as.appendElement("a",flwHeader,"Закрыть");
		overlayCloser.href = "#";
		var sInput = as.appendElement("input",flwHeader);
		as.appendElement(flwHeader,flw);

		var ul = as.appendElement("ul",flw);
		var ulItems = [];
		as.foreach(
			fullList,
			function(city) {
				var li = as.create("li");
				var link = as.appendElement("a",li);
				link.href = "#";
				link.innerHTML = city.name;
				link.cityId = city.id;
				as.appendElement(li,ul);
				ulItems.push(link);
			}
		);
		sInput.onkeyup = function() {
			if (!sInput.lock) {
				sInput.lock = true;
				window.setTimeout(
					function() {
						var re = new RegExp(sInput.value,"ig");
						as.foreach(
							ulItems,
							function(item) {
								item.parentNode.className = "";
								if (item.innerHTML.toLowerCase().search(re)!=0) {
									item.parentNode.className = "hidden";
								}
							}
						);
						sInput.lock = false;
					},250
				);
			}
		}
		ul.onclick = function(e) {
			e = e || window.event;
			var target = e.target || e.srcElement;
			if (target.tagName == "A") {
				var ai = false;
				for (var i=0,l=_self_.citySelect.options.length;i<l;i++) {
					if (_self_.citySelect.options[i].value == target.cityId) {
						_self_.citySelect.options[i].selected = "selected";
						ai = true;
						break;
					}
				}
				if (!ai) {
					var option = as.create("option");
					option.value = target.cityId;
					option.innerHTML = target.innerHTML;
					_self_.citySelect.insertBefore(option,_self_.citySelect.options[_self_.citySelect.options.length-1]);
					setTimeout(
						function() {
							option.selected = "selected";
						},100
					)
				}
				_self_.removeFLW();
			}
			as.cancelEvent(e);
		}
		overlayCloser.onclick = function(e) {
			e = e || window.event;
			as.cancelEvent(e);
			_self_.removeFLW();
			_self_.prevOption.selected = "selected";
		}
		this.fullListWrapper = as.overlay(
			326,
			276,
			"overlay",
			function() {
				as.appendElement(bgw,_self_.fullListWrapper);
				_self_.fullListWrapper.className += " overlayDone";
			},
			true
		);
	},
	removeFLW: function() {
		as.removeChild(this.fullListWrapper);
	},
	showLoading: function() {
		if (!this.loader) {
			this.loader = as.appendElement("div",this.qst);
			this.loader.className += " loader";
		}
		this.loader.className += " visible";
	},
	stopShowLoading: function() {
		this.loader.className = this.loader.className.replace(/\bvisible\b/,"");
	}
}
/*\\ QUESTIONNAIRE */

var SuperController = {
	list: {},
	createController: function(controllerName,className,tagName) {
		this.list[controllerName] = {
			init: function() {
				this.list = [];
				var _self_ = this;
				var elements = as.getBCN(className,tagName);

				as.foreach(
					elements,
					function(element) {
						_self_.list[_self_.list.length] = new window[controllerName](element);
						_self_.list[_self_.list.length-1].init();
					}
				);
			}
		}
		this.list[controllerName].init();
	}
}


function newYear() {
	var logoHeader = as.getBCN("logoHeader","div")[0];
	var so = new SWFObject("/i/popcorn_logo_ny.swf","new-year-logo","504","114","6");
	so.addParam("allowScriptAccess", "always");
	so.addParam("wmode", "transparent");
	so.useExpressInstall("/swfobject/expressinstall.swf");
	so.write(logoHeader);
}

/* StatusHistory Start */
function StatusHistory(history) {
	this.history = as.append("div", document.body);
	this.history.className = "StatusHistory hiddenStatusHistory";
}
StatusHistory.prototype = {
	showStatusHistory: function() {
		this.history.className = this.history.className.replace(/\bhiddenBlock\b/,"");
	},
	setElements: function() {
		this.btnShowHide = as.getBCN("btn_status_history","div", as.$$("div.topHeadline"))[0];
		if (!this.btnShowHide) {
			throw new error('no history element');
		}
		this.msg = as.getBCN("status_msg","div", as.$$("div.topHeadline"))[0];
		this.text = as.getBCN("StatusMsg","input", this.history)[0];
		this.btnSave = as.getBCN("btnStatusAdd","img", this.history)[0];

		this.shadow = as.append("div", document.body);
		this.shadow.className = "StatusHistoryBg hiddenStatusHistory";

		this.shHidden = /\bhiddenSmiles\b/.test(this.history.className);
		this.hideStatusHistoryBlock();
	},
	saveStatus: function(status) {
		if (this.shHidden)
			return;

		var ajax = new vpa_ajax(),
			_self_ = this,
			status = status || (typeof this.text == "object" ? this.text.value : "");

		ajax.makeRequestPost("/", function(sJson){
			var oJson = eval("(" + sJson + ")");

			if (oJson.status)
				_self_.msg.innerHTML = oJson.new_status;
		}, "type=status&action=save&status="+status);

		if (this.text == "object")
			this.text.value = "";
		this.hideStatusHistoryBlock();
	},
	initHiding: function() {
		var _self_ = this;
		as.addEvent(
			this.btnShowHide,
			"click",
			function(e) {
				_self_.showHideStatusHistoryBlock();

				e = e || window.event;
				e.stopPropagation ? e.stopPropagation() : (e.cancelBubble=true);
			}
		);

		as.addEvent(
			document.body,
			"click",
			function(e) {
				if (!this.shHidden)
					_self_.saveStatus();
			}
		);

		as.addEvent(
			this.history,
			"click",
			function(e) {
				e = e || window.event;
				e.stopPropagation ? e.stopPropagation() : (e.cancelBubble=true);
			}
		);
	},
	showHideStatusHistoryBlock: function() {
		if (this.shHidden) {
			this.showStatusHistoryBlock();
		}
		else {
			this.hideStatusHistoryBlock();
		}
	},
	showStatusHistoryBlock: function() {
		var ajax = new vpa_ajax(),
			_self_ = this,
			re = /http:\/\/[^\/]+\/profile\/([0-9]+)/i,
			uid = (re.exec(location.href))[1] * 1;

		ajax.makeRequest("/statuses/get/"+uid, function(sJson){
			var oJson = eval("(" + sJson + ")"),
				bIsHistory = false;

			if (!_self_.shContainer)
			{
				if (oJson.my == 1)
				{
					as.append(as.create('<fieldset><input type="text" name="StatusMsg" class="StatusMsg" value="" /> <img src="/img/form-save-submit.gif" class="btnStatusAdd" title="Сохранить" /></fieldset>'), _self_.history);
					_self_.text = as.getBCN("StatusMsg","input", _self_.history)[0];
					_self_.btnSave = as.getBCN("btnStatusAdd","img", _self_.history)[0];

					as.addEvent(
						_self_.btnSave,
						"click",
						function() {
							_self_.saveStatus();
						}
					);

					as.addEvent(
						_self_.text,
						"keydown",
						function(e) {
							e = e || window.event;
							if (e.keyCode == "13") {
								_self_.saveStatus();
							}
						}
					);
				}

				_self_.shContainer =  as.append("ul", _self_.history);
				_self_.shContainer.className = "StatusHistoryContainer";
			}

			_self_.shContainer.innerHTML = "";
			for (var i = 0, j = oJson.statuses.length; i < j; i++)
			{
				as.append(as.create('<li><div class="msg">'+oJson.statuses[i].status+'</div><div class="time">'+oJson.statuses[i].date+'</div></li>'), _self_.shContainer);
				bIsHistory = true;
			}

			if (bIsHistory || oJson.my == 1)
			{
				if (oJson.my == 1) {
					_self_.initStatusHistoryInserting();
				}
				_self_.history.className = _self_.history.className.replace(/\bhiddenStatusHistory\b/gi,"");
				_self_.shadow.className = _self_.shadow.className.replace(/\bhiddenStatusHistory\b/gi,"");
				_self_.btnShowHide.title = "Скрыть";
				_self_.shHidden = false;
			}
		}, "");
	},
	hideStatusHistoryBlock: function() {
		this.history.className += " hiddenStatusHistory";
		this.shadow.className += " hiddenStatusHistory";
		this.btnShowHide.title = "Показать";
		this.shHidden = true;
	},
	initStatusHistoryInserting: function() {
		var _self_ = this,
			position = as.getElementPosition(this.msg),
			top = position.top - 18,
			left = position.left - 10,
			width = (position.width > 540 ? 540 : (position.width < 410 ? 410 : position.width)),
			height = position.height + 30;

		this.history.style.top = top + "px";
		this.history.style.width = width + "px";
		this.history.style.paddingTop = height + "px";

		this.shadow.style.top = (top + 10) + "px";
		this.shadow.style.width = width + "px";
		this.shadow.style.paddingTop = height + "px";


		if (this.text)
			this.text.style.width = (width - 115) + "px";

		as.w("div.StatusHistory ul.StatusHistoryContainer div.msg").each(function(){
			this.style.width = (width - 130) + "px";

			as.addEvent(
				this,
				"click",
				function() {
					_self_.saveStatus(this.innerHTML);
				}
			);
		});
	},
	init: function() {
		try {
			this.showStatusHistory();
			this.setElements();
			this.initHiding();
			this.initStatusHistoryInserting();
		} catch (e) {}
	}
}
/* StatusHistory End */

function Suggest() {}
Suggest.prototype = {
	init: function(input) {
		this.input = input;
		this.initVars();
		this.addEvents();
	},
	initVars: function() {
		this.defaultName = this.input.name;
		this.input.name = "as-suggest" + new Date().getTime();
		this.url = this.input.onclick().url;
		this.cache = {};
		this.items = [];
		this.active = null;
	},
	onFormSubmit: function(e) {
		e = e || window.event;
		e.preventDefault ? e.preventDefault() : e.returnValue = false;
		this.input.name = this.defaultName;
		this.input.form.submit();
	},
	addEvents: function() {
		as.e.submit(this.input.form,this.onFormSubmit,this);
		as.addEvent(
			this.input,
			"keydown",
			function(e) {
				this.handleKeyDown(e);
			}.bind(this)
		);
		as.addEvent(
			this.input,
			"click",
			function() {
				if (!this.lock) {
					this.lock = true;
					window.setTimeout(
						function() {
							this.suggest();
							this.lock = false;
						}.bind(this),300
					);
				}
			}.bind(this)
		);
		as.addEvent(
			this.input,
			"blur",
			function(e) {
				this.ct = window.setTimeout(
					function() {
						this.clearSuggest();
					}.bind(this),200
				)
			}.bind(this)
		);
	},
	handleKeyDown: function(e) {
		e = e || window.event;
		var _self_ = this;
		/* enter press */
		if (e.keyCode == "9") {
			this.clearSuggest();
			return;
		}
		else if (e.keyCode == "13") this.chooseActive(e);
		else if (e.keyCode == "38") this.changeActive("prev");
		else if (e.keyCode == "40") this.changeActive("next");
		/* else */
		else {
			if (!this.lock) {
				this.lock = true;
				window.setTimeout(
					function() {
						_self_.suggest();
						_self_.lock = false;
					},300
				)
			}
		}
	},
	suggest: function() {
		this.clearSuggest();
		if (this.input.value) {
			if (this.cache[this.input.value]) {
				this.createSuggestList(this.cache[this.input.value]);
			}
			else {
				var value = this.input.value;
				new vpa_ajax().makeRequest(
					this.url+this.input.value,
					function(results) {
						try {
							results = eval("(" + results + ")");
						}
						catch(e) {/*console.log(e)*/}
						this.createSuggestList(results);
						this.cache[value] = results;
					}.bind(this)
				);
			}
		}
	},
	chooseActive: function() {
		this.input.blur();
		try {
			location.href = this.active.href;
		} catch (e) {
			this.onFormSubmit();
		}
	},
	changeActive: function(d) {
		if (!this.active) {
			this.active = this.items[0];
		}
		else {
			this.active.className = this.active.className.replace(/\bactive\b/gi,"")
			this.active = this[d](this.active);
		}
		//window.console && console.log(this.active + " | " + this.active.innerHTML);
		this.active.className += " active";
		this.ct && clearTimeout(this.ct);
		this.autoScroll();
	},
	next: function(item) {
		item = item.parentNode;
		while (item.nextSibling) {
			item = item.nextSibling;
			if (item.nodeType == 1 && item.tagName.toLowerCase() == "li") return as.getBTN("a",item)[0];
		}
		return as.getBTN("a",as.getBTN("li",item.parentNode)[0])[0];
	},
	prev: function(item) {
		item = item.parentNode;
		while (item.previousSibling) {
			item = item.previousSibling;
			if (item.nodeType == 1 && item.tagName.toLowerCase() == "li") return as.getBTN("a",item)[0];
		}
		return as.getBTN("a",as.getBTN("li",item.parentNode)[as.getBTN("li",item.parentNode).length-1])[0];
	},
	autoScroll: function() {
		//this.active.parentNode.parentNode.scrollBy(as.getElementPosition(this.active).top - (as.getElementPosition(this.active.parentNode.parentNode).top + this.active.parentNode.parentNode.offsetHeight));
		//this.active.scrollIntoView();
		//this.active.parentNode.parentNode.scrollTop = "10px";
	},
	createSuggestListItem: function(result,i) {
		if (i>9) return;
		var li = as.create("li");
		li.innerHTML = "<a class='suggest-list-item' href='" + result.url + "'>" + result.name + "</a>"
		this.items.push(as.getBTN("a",li)[0]);
		this.suggestList.appendChild(li);
	},
	createSuggestList: function(results) {
		this.suggestList = as.create("ul");
		this.suggestList.className = "as-suggest";
		as.foreach(results, as.bind(this.createSuggestListItem,this));
		var cs = as.getElementPosition(this.input);
		as.style(this.suggestList,{
			top: cs.top+cs.height+"px",
			left: cs.left+"px",
			width: cs.width+"px"
		});
		as.append(this.suggestList,as.$$("body"));
		this.currentScrollTop = document.documentElement.scrollTop;
	},
	clearSuggest: function() {
		this.suggestList && document.body.removeChild(this.suggestList);
		this.suggestList = null;
		this.items = [];
		this.active = null;
	}
};

function personSearch() {
	as.foreach(as.getBCN("suggest","input"),function(suggest) {
		new Suggest().init(suggest);
	});
}

function SelectReplacerSuggest() {}
SelectReplacerSuggest.prototype = new Suggest();
SelectReplacerSuggest.prototype.init = function(form) {
	var select = as.$$('select',form);
	this.input = as.after("<input type='text' class='suggest-input' value='Начинай вводить ник' />",select);
	this.hidden = as.$$("input#uid",form);
	this.input.onclick = function() {
		return {url: '/ajax/users/'}
	}
	this.input.onfocus = function() {
		if (this.value == 'Начинай вводить ник') {
			this.value = '';
		}
	}
	this.input.onblur = function() {
		if (this.value == '') {
			this.value = 'Начинай вводить ник';
		}
	}

	select.style.display = "none";

	this.initVars();
	this.addEvents();
	as.e.click(document.body,this.checkClickTarget,this);
	as.e.click(as.$$("input[type='submit']",form),function() {form.submit();});
}
SelectReplacerSuggest.prototype.chooseActive = function(e) {
	e && e.preventDefault();
	this.input.value = this.active.innerHTML;
	this.hidden.value = this.active.id;
	this.clearSuggest();
}
SelectReplacerSuggest.prototype.checkClickTarget = function(e) {
	var target = e.target;
	if (target.className.match(/suggest-list-item/)) {
		e.preventDefault();
		this.active = target;
		this.chooseActive();
		this.clearSuggest();
	}
}
SelectReplacerSuggest.prototype.createSuggestListItem = function(result,i) {
	if (i>9) return;
	var li = as.create("li");
	li.className = "suggest-list-item";
	li.innerHTML = result.name;
	li.id = result.id;
	this.items.push(li);
	this.suggestList.appendChild(li);
}
SelectReplacerSuggest.prototype.onFormSubmit = function(e) {
	e.preventDefault();
}
SelectReplacerSuggest.prototype.next = function(item) {
	while (item.nextSibling) {
		item = item.nextSibling;
		if (item.nodeType == 1 && item.tagName.toLowerCase() == "li") return item;
	}
	return as.w("li",item.parentNode).first();
},
SelectReplacerSuggest.prototype.prev = function(item) {
	while (item.previousSibling) {
		item = item.previousSibling;
		if (item.nodeType == 1 && item.tagName.toLowerCase() == "li") return item;
	}
	return as.w("li",item.parentNode).last();
}/*
SelectReplacerSuggest.prototype. = function() {

}*/
/***************************************************************************** MESSAGE BOX ***/
function MessageBox(params) {};
MessageBox.prototype = {
	init: function(params) {
		params = params || {};
		this.html = params.html || "";
		this.closable = params.closable || true;
		this.modal = params.modal || false;
		this.iShow = params.iShow || true;
		this.modalRelative = params.modalRelative || document.body;
		this.callback = params.callback;
		this.build();
		this.addEvents();
		this.iShow && this.show();
	},
	build: function() {
		this.messageBox = as.create(
			"<div class='message-box'>"+
				"<iframe></iframe>"+
				"<div class='message-box-overlay'>"+
					"<div class='message-box-layout'>"+
						"<div class='message-box-inside'></div>"+
					"</div>"+
				"</div>"+
			"</div>"
		);
		this.boxLayout = as.$$("div.message-box-inside",this.messageBox);
		this.putHTML(this.html);
	},
	putHTML: function(html) {
		this.boxLayout.innerHTML = html;
	},
	addEvents: function() {
		var keyup = as.bind(function(e) {
			if (e.keyCode == 27) {
				this.close();
				as.e.dekeyup(document,keyup);
			}
		},this);
		this.closable &&
		as.e.click(as.prepend("<a href='#' class='close'>Закрыть</a>",this.messageBox),as.bind(function(e) {this.close();e.preventDefault();return false;},this)) &&
		as.e.keyup(document,keyup);
	},
	close: function() {
		as.remove(this.messageBox);
		this.madeClosable = true;
		try {
			this.callback && this.callback();
		}
		catch(e) {}
	},
	show: function() {
		as.append(this.messageBox, this.modalRelative);
		var cs = as.getElementPosition(this.modalRelative);
		as.style(
			this.messageBox,
			{
				left: (cs.width - this.messageBox.offsetWidth)/2 + "px",
				top: (cs.height - this.messageBox.offsetHeight)/2 + "px"
			}
		);
		this.messageBox.className += " message-box-dynamic";
	}
}
/***************************************************************************** MESSAGE BOX \\\\\\\\\\***/
/***************************************************************************** VOTINGS ***/
var Votings = {};
Votings.init = function() {
	this.initMeetVote();
	this.initBattleVote();
}
Votings.errorList = {
	"NOT_REGISTERED": 'Только зарегистрированные пользователи могут голосовать.',
	"VOTED_TODAY": 'Вы уже голосовали сегодня.',
	"VOTED_ALREADY": 'Вы уже голосовали.',
	"THANKS_FOR_THE_VOICE": 'Спасибо за ваш голос!',
	"NOT_ENOUGH_RATING": 'Для голосования нужно иметь рейтинг не менее 20'
}
// MEETS
Votings.initMeetVote = function() {
	as.foreach(as.$('div.pair ul.dkvoter2 span'),as.bind(function(vl) {
		new this.VoteLink().init({
			vl: vl,
			id: as.parent(vl, 'ul.dkvoter2').onclick(),
			vote: (/\bup\b/.test(vl.parentNode.className)) ? 'up' : 'down',
			prefix: (/^\/meet/.test(document.location.pathname)) ? 'meet_vote' : 'kid_vote',
			modalRelative: as.parent(vl, 'div.stats'),
			votedAlready: 'VOTED_TODAY',
			updateFunction: this.updateMeetVote
		});
	},this));
}
Votings.updateMeetVote = function(vl,response) {
	var parent = as.parent(vl, 'ul.dkvoter2');
	
	as.$$('li.up span', parent).innerHTML = '<big>' + (response.rating_up.num ? parseInt(response.rating_up.num) : 0) + '</big><br />' + response.rating_up.word;
	as.$$('li.down span', parent).innerHTML = '<big>' + (response.rating_down.num ? parseInt(response.rating_down.num) : 0) + '</big><br />' + response.rating_down.word;
}
// BATTLES
Votings.initBattleVote = function() {
	as.foreach(as.$("div.battle div.wrs a"),as.bind(function(vl) {
		new this.VoteLink().init({
			vl: vl,
			id: as.parent(vl,"div.battle").onclick().id,
			vote: (/\blwr\b/.test(vl.className)) ? 1 : 2,
			prefix: 'new_vote',
			modalRelative: as.$$("div.entry-details"),
			votedAlready: "VOTED_ALREADY",
			updateFunction: this.updateBattleVote
		});
	},this));
}
Votings.updateBattleVote = function(vl,response) {
	var battleContainer = as.parent(vl,"div.battle");
	as.$$("div.lwr",battleContainer).style.width = response.p1-0.1+"%";
	as.$$("div.rwr",battleContainer).style.width = response.p2-0.1+"%";
	as.$$("div.lwr span",battleContainer).innerHTML = response.v1;
	as.$$("div.rwr span",battleContainer).innerHTML = response.v2;
}
/*** VOTE LINK ***/
Votings.VoteLink = function() {};
Votings.VoteLink.prototype = {
	init: function(params) {
		this.vl = params.vl;
		this.id = params.id;
		this.vote = params.vote;
		this.prefix = params.prefix;
		this.modalRelative=params.modalRelative || document.body;
		this.votedAlready = params.votedAlready;
		this.updateFunction = params.updateFunction;
		this.addVoteEvent();
	},
	addVoteEvent: function() {
		as.e.click(this.vl,as.bind(function(e){
			e.preventDefault();
			new as.ajax().makeRequest('/ajax/'+this.prefix+'/'+this.id+'/'+this.vote,as.bind(function(response){
				try {
					response = eval('(' + response + ')');
					if (response.registered == false) Votings.showMessageBox({message:"NOT_REGISTERED",vl:this.modalRelative});
					else if (response.notEnoughRating) Votings.showMessageBox({message:"NOT_ENOUGH_RATING",vl:this.modalRelative});
					else if (response.votedAlready) Votings.showMessageBox({message:this.votedAlready,vl:this.modalRelative});
					else {
						Votings.showMessageBox({message:"THANKS_FOR_THE_VOICE",vl:this.modalRelative});
						this.updateFunction.call(Votings,this.vl,response);
					}
				}
				catch(e) {/*window.console && console.log(e)*/}
			},this),this);
		},this));
	}
}
/***\\ VOTE LINK \\***/
/*** MESSAGE BOX ***/
Votings.showMessageBox = function(params) {
	if (!params.message) return;
	var message = as.create("<div class='message-box-inside'></div>");
	as.append("<p>"+this.errorList[params.message]+"</p>",message);
	params.message == "NOT_REGISTERED" && (message.innerHTML += "<a href='/auth/' class='enter'><span></span>Войти</a><a href='/register/'>Регистрация</a>");
	var messageBox = new Votings.MessageBox();
	messageBox.init({html:message,modalRelative:params.vl});
	params.message != "NOT_REGISTERED" && (messageBox.messageBox.className += " simple-votings-message-box");
}
Votings.MessageBox = function(){};
Votings.MessageBox.prototype = new MessageBox();
Votings.MessageBox.prototype.putHTML = function(html) {
	this.boxLayout.innerHTML = "";
	if (typeof html == "object") as.append(html,this.boxLayout);
	else if (typeof html == "string") this.boxLayout.innerHTML = html;
}
Votings.MessageBox.prototype.build = function() {
	MessageBox.prototype.build.call(this);
	this.messageBox.className += " votings-message-box";
}
/***\\ MESSAGE BOX \\***/
/***************************************************************************** VOTINGS \\\\\\\\\\***/
// VOTING
function poll_submit(form) {
	var id = form['id'].value;
	var anwser = get_checked_value(form['anwser']);

	if (!id) {
		return false;
	} else if (!anwser) {
		new MessageBox().init({
			html: "<p class='vote-error'>Выберите один из вариантов</p>",
			modalRelative: as.$$("div#questions")
		});
		return false;
	}
	var data = 'type=poll&action=submit&id=' + id + '&anwser=' + anwser;
	as.ajax(
		'/',
		function (response) {
			response = eval('(' + response + ')');
			var fields = response.fields;
			if (fields) {
				var html = '<h4>' + response.name + '</h4><ul>';
				for (var i=0,l=fields.length;i<l;i++) {
					html += "<li><span class='name'>" + fields[i].name + "</span><span class='count'>" + fields[i].votes + "</span>" + "<span class='percent'><span style='width: " + fields[i].percent + "%;'></span></span>";
				}
				html += "</ul>";
				as.w('div#questions').each(function() {this.innerHTML = html});
			}
			if (response.error) {
				new MessageBox().init({
					html: "<p class='vote-error'>" + response.error + "</p>",
					modalRelative: as.$$("div#questions")
				});
			}
		},
		'post',
		data,
		30,
		[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}]
	);

	return false;
}

function news_poll_submit(form) {
	var answer = get_checked_value(form['option']);

	if (!answer) {
		new MessageBox().init({
			html: "<p class='vote-error'>Выберите один из вариантов</p>",
			modalRelative: as.$$("div.poll div#options")
		});
		return false;
	}
	
	var nid = document.location.pathname.match(/news\/(\d+)/)[1];
		
	var self = this;
	var data = 'type=news_poll&action=submit&nid=' + nid + '&answer=' + answer;
	as.ajax(
		'/',
		function (response) {
			response = eval('(' + response + ')');
			var fields = response.fields;
			if (fields) {
				var html = '<ul class="poll">';
				for (var i=0,l=fields.length;i<l;i++) {
					html += "<li><span class='name'>" + fields[i].title + "</span><span class='count'>" + fields[i].rating + "</span>" + "<span class='percent'><span style='width: " + fields[i].percent + "%;'></span></span>";
				}
				html += "</ul>";
				as.w('div.poll div#options').each(function() {this.innerHTML = html});
			}
			if (response.error) {
				new MessageBox().init({
					html: "<p class='vote-error'>" + response.error + "</p>",
					modalRelative: as.$$("div.poll div#options")
				});
			}
		},
		'post',
		data,
		30,
		[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}]
	);

	return false;
}

function get_checked_value(radioObj) {
	if(!radioObj) return false;
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		if (radioObj.checked) return radioObj.value;
		else return false;
	}
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return false;
}
//\VOTING

// RIGHT BUTTON DISABLED
function rightButtonDisabled(element) {
	function ds(e) {
		var target = e.target;
		if (e.button == 2) {
			if (target == element || as.parent(target, element)) {
				e.preventDefault();
				e.stopPropagation();
				e.cancelBubble = true;
			}
		}
	}
	as.w(document).mouseup(ds).mousedown(ds).click(ds);
	document.oncontextmenu = function(evt) {
		var evt = evt || window.event;
		var target = evt.srcElement || evt.target;
		if (target == element || as.parent(target, element)) {
			window.event.returnValue = false;
		}
	}
}
// \RIGHT BUTTON DISABLED

// LEFT BUTTON SELECT DISABLE
function preventSelection(element){
	var preventSelection = false;

	function addHandler(element, event, handler){
		if (!element) return;

		if (element.attachEvent) element.attachEvent('on' + event, handler);
		else if (element.addEventListener) element.addEventListener(event, handler, false);
	}
	function removeSelection(){
		if (window.getSelection) {
			window.getSelection().removeAllRanges();
		}
		else if (document.selection && document.selection.clear)
			document.selection.clear();
	}
	function killCtrlA(event){
		var event = event || window.event;
		var sender = event.target || event.srcElement;

		if (sender.tagName.match(/INPUT|TEXTAREA/i))
			return;

		var key = event.keyCode || event.which;
		if (event.ctrlKey && key == 'A'.charCodeAt(0))  // 'A'.charCodeAt(0) можно заменить на 65
		{
			removeSelection();

			if (event.preventDefault)
				event.preventDefault();
			else
				event.returnValue = false;
		}
	}
	addHandler(element, 'mousemove', function(){
		if(preventSelection)
			removeSelection();
	});
	addHandler(element, 'mousedown', function(event){
		var event = event || window.event;
		var sender = event.target || event.srcElement;
		preventSelection = !sender.tagName.match(/INPUT|TEXTAREA/i);
	});
	addHandler(element, 'mouseup', function(){
		if (preventSelection)
			removeSelection();
		preventSelection = false;
	});
	addHandler(element, 'keydown', killCtrlA);
	addHandler(element, 'keyup', killCtrlA);
}
//\ LEFT BUTTON SELECT DISABLE


/*
 * year results votes
 */
var div = null;
function year_results_vote(id, div_id){
	if (!id || !div_id) return;

	div = div_id;
	var data='type=voting&action=do_vote&id='+id;
	ajax=new vpa_ajax();
	ajax.makeRequestPost('/', year_results_vote_return, data);
}

function year_results_vote_return(data){
	if (data){
		document.getElementById(div).innerHTML = data;
		// переназначим ссылки для попапа с увеличеной картинкой
		new popup;
	}
	else alert('Ошибка!');
}
/*
 * \year results votes
 */

/**
 * Check comments form
 */
function checkCommentsForm(frm)
{
	frm.submit.disabled=true;
	var str = '';
	if (frm.content.value=='') {
		str = 'Комментарий не может быть пуст !';
	}

	if (str != '') {
		alert(str);
		frm.submit.disabled = false;
		return false;
	}
	return true;
}

function checkTextArea(ob, limit) {
	if (ob.value.length > limit) {
		ob.value = ob.value.substr(0, limit);
	}
}
/**
 * \Check comments form
 */

var USER_LOGGED_IN = false;
function init() {
	USER_LOGGED_IN = /\bregistered\b/.test(as.$("wrapper").className);
	
// 	newYear();
	userRating.activateDots();
	gonnaExitController.init();
	equalsContainersController.init();
	messages.init();
	galleryController.init();
//	SuperController.createController("UlMark","mark","ul");
	SuperController.createController("Smile","smiles","div");
//	SuperController.createController("bbCode","bbCode","div");
	SuperController.createController("PrivateMessage","newMessage","form");
//	SuperController.createController("PrivateMessage","giftsForm","form");
	SuperController.createController("StatusHistory","topHeadline","div");
	as.w('form.giftsForm').each(function() {new SelectReplacerSuggest().init(this)});
	SuperController.createController("Fanfics","addFanfic","form");
	SuperController.createController("Questionnaire","questionnaireForm","form");
	personSearch();
	Votings.init();

	// news/98328 - widget
	if (/news\/\d+/.test(document.location.pathname) && !/news\/98328/.test(document.location.pathname)) {
		rightButtonDisabled(as.$$('div.newsTrack'));
		preventSelection(as.$$("div.newsTrack"));
	}
	
	as.w('form.checkCommentsForm').each(function() {
		this.onsubmit = function() {return checkCommentsForm(this);}
	})
	
	
	if (USER_LOGGED_IN) {
		check_mail();
		setInterval('check_mail()', 60000);
	}
}
as.ready.add(init);
as.ready.init();