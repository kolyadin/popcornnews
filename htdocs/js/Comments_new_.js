function Comment() {}


// INITIALIZATION
Comment.prototype.init = function(object) {
	var obj=this;
	
	this.comment = object;
	this.registered = USER_LOGGED_IN;
	
	this.$form=$('#comments_form');
	this.$comment=$(this.comment);
	this.$replyBtn=this.$comment.find('.reply');
	
	/*if (!this.registered) {
		return;
	}*/
	
	// BIND
	try {as.e.click(this.getElement('div.post div.details span.delete'), this.deleteOnClick, this);} catch (e) {}
	
	try {as.e.click(this.getElement('div.post div.mark span.up'), this.ratingOnClick, this);} catch (e) {}
	try {as.e.click(this.getElement('div.post div.mark span.down'), this.ratingOnClick, this);} catch (e) {}

	//try {as.e.click(this.getElement('div.post div.details span.reply'), this.replyOnClick, this);} catch (e) {}
	
	this.$replyBtn.click(function(event){
		//event=event||window.event;
		//event.preventDefault ? event.preventDefault() : (event.returnValue=false)
		
		alert(123);
		//obj.$form.css('left', obj.$comment.offset().left);
	});
	
	try {as.e.click(this.getElement('div.post div.details span.complain'), this.complainOnClick, this);} catch (e) {}
	
	try {as.e.click(this.getElement('div.post div.details span.edit'), this.editOnClick, this);} catch (e) {}
	
	this.getType();
	this.getPartitionIds();
}
// \INITIALIZATION


// HELPERS
Comment.prototype.getElement = function(selector) {
	return as.$$(selector, this.comment);
}

Comment.prototype.getUserNick = function() {
	return this.getElement('div.post div.details a.pc-user').innerHTML;
}

Comment.prototype.getDate = function() {
	return this.getElement('div.post div.details span.date').innerHTML;
}

Comment.prototype.getId = function() {
	return parseInt(this.comment.id);
}

Comment.prototype.getType = function() {
	if (this.type) {
		return this.type;
	}
	
	var type = '';
	var uri = document.location.pathname;
	(uri.match(/^\/news\/\d+/) || uri.match(/^\/profile\/\d+\/wrote/) || uri.match(/^\/user\/\d+\/wrote/)) && (type = 'new');
	uri.match(/^\/meet\/\d+/) && (type = 'meet');
	uri.match(/^\/kid\/\d+/) && (type = 'kid');
	uri.match(/^\/(user|profile)\/\d+\/photos/) && (type = 'photos');
	
	uri.match(/^\/community\/group\/\d+\/album\/\d+/) && (type = 'community_group_photos');
	uri.match(/^\/community\/group\/\d+\/topic\/\d+/) && (type = 'community_group_topic');
	uri.match(/^\/yourstyle\/set\/\d+/) && (type = 'yourstyle_set');
	uri.match(/^\/chat\/theme\/\d+\/topic\/\d+/) && (type = 'chat');
	uri.match(/^\/persons\/\w*-*\w*\/talks\/topic\/\d+/) && (type = 'topic');
	uri.match(/^\/persons\/\w*-*\w*\/fanfics\/\d+/) && (type = 'fanfic');
	return this.type = type;
}

Comment.prototype.getPartitionIds = function() {
	if (this.partitionIds) {
		return this.partitionIds;
	}
	
	var ids = {}, matches, uri = document.location.pathname;
	switch (this.getType()) {
		case 'new':
			matches = uri.match(/^\/news\/(\d+)(?:\/|$)/);
			try {
				ids['nid'] = matches[1];
			} catch (e) {}
			break;
		case 'meet':
			matches = uri.match(/^\/meet\/(\d+)(?:\/|$)/);
			ids['mid'] = matches[1];
			break;
		case 'kid':
			matches = uri.match(/^\/kid\/(\d+)(?:\/|$)/);
			ids['kid'] = matches[1];
			break;
		case 'photos':
			matches = uri.match(/^\/(user|profile)\/(\d+)\/photos/);
			ids['photos'] = matches[1];
			break;
		case 'community_group_photos':
			matches = uri.match(/^\/community\/group\/(\d+)\/album\/(\d+)(?:\/|$)/);
			ids['gid'] = matches[1];
			ids['aid'] = matches[2];
			break;
		case 'community_group_topic':
			matches = uri.match(/^\/community\/group\/(\d+)\/topic\/(\d+)(?:\/|$)/);
			ids['gid'] = matches[1];
			ids['tid'] = matches[2];
			break;
		case 'yourstyle_set':
			matches = uri.match(/^\/yourstyle\/set\/(\d+)(?:\/|$)/);
			ids['sid'] = matches[1];
			break;
		case 'chat':
			matches = uri.match(/^\/chat\/theme\/(\d+)\/topic\/(\d+)(?:\/|$)/);
			ids['theme'] = matches[1];
			ids['tid'] = matches[2];
			break;
		case 'topic':
			matches = uri.match(/^\/persons\/(\w*-*\w*)\/talks\/topic\/(\d+)(?:\/|$)/);
			ids['aid'] = matches[1];
			ids['tid'] = matches[2];
			break;
		case 'fanfic':
			matches = uri.match(/^\/persons\/(\w*-*\w*)\/fanfics\/(\d+)(?:\/|$)/);
			ids['aid'] = matches[1];
			ids['fid'] = matches[2];
			break;
		default:
			throw error;
	}
	return this.partitionIds = ids;
}

Comment.prototype.getTarget = function(e) {
	return e.target || e.srcElement;
}

Comment.prototype.json = function(string) {
	if (string.substr(0, 1) != '(') {
		string = '(' + string + ')';
	}
	return eval(string);
}

Comment.prototype.getOriginalCommentText = function() {
	return this.getElement('div.post div.details span.reply').onkeydown();
}

Comment.prototype.setOriginalCommentText = function(text) {
	return this.getElement('div.post div.details span.reply').onkeydown = function() {
		return text;
	}
}

// checkbox, name, hidden, textarea
Comment.prototype.getFormFieldsAsString = function(form) {
	var data = '';
	
	for (var i = 0; i < form.length; i++) {
		var currentElement = form[i];
		switch (currentElement.type) {
			case 'checkbox':
				if (currentElement.checked) {
					data += encodeURIComponent(currentElement.name) + '=' + encodeURIComponent(currentElement.value) + '&';
				}
				break;
			case 'name':
			case 'hidden':
			case 'textarea':
				data += encodeURIComponent(currentElement.name) + '=' + encodeURIComponent(currentElement.value) + '&';
				break;
		}
	}
	return data.substr(0, data.length-1);
}

Comment.prototype.getFormBlock = function() {
	var forms = as.$('form.newComment');
	for (var i = 0; i < forms.length; i++) {
		if (!forms[i].className.match(/\beditComment\b/)) {
			return as.parent(forms[i], 'div');
		}
	}
	throw error;
}

Comment.prototype.trim = function(str) {
	return str.replace(/^\s*(.*)\s*$/g, '\1');
}
// \HELPERS


// EVENTS
/*Comment.prototype.replyOnClick = function(e) {
	e.preventDefault();
	var target = this.getTarget(e);
	
	var replyText = '[b]Ответ на сообщение от ' + this.getUserNick() + ', ' + this.getDate() + '[/b]' + "\n" + 
	                '[quote]' + this.getOriginalCommentText() + '[/quote]' + "\n";
		    
	var form = document.fmr;
	form.re.value = this.getId();
	form.content.value = replyText;
	form.content.focus();
	
	// move cursor to the and of textarea
	if (form.content.setSelectionRange) {
		form.content.setSelectionRange(replyText.length, replyText.length);
	} else if (form.content.createTextRange) {
		var range = form.content.createTextRange();
		range.collapse(false);
		range.select();
	}
}*/

Comment.prototype.deleteOnClick = function(e) {
	e.preventDefault();

	var uri;
	switch (this.getType()) {
		case 'new':
		case 'meet':
		case 'kid':
		case 'photos':
		case 'chat':
		case 'topic':
		case 'fanfic':
			uri = '/messages/delete/' + this.getType() + '/' + this.getId();
			break;
		case 'community_group_photos':
			uri = '/community/group/' + this.partitionIds['gid'] + '/album/' + this.partitionIds['aid'] + '/comment/' + this.getId() + '/delete';
			break;
		case 'community_group_topic':
			uri = '/community/group/' + this.partitionIds['gid'] + '/topic/' + this.partitionIds['tid'] + '/message/' + this.getId() + '/delete';
			break;
		case 'yourstyle_set':
			uri = '/yourstyle/set/' + this.partitionIds['sid'] + '/comment/' + this.getId() + '/delete';
			break;
	}
	as.ajax(uri, as.bind(this.postDeleteHook, this));
}

Comment.prototype.ratingOnClick = function(e) {
	e.preventDefault();
	
	var target = this.getTarget(e);
	if (!this.getTarget(e).className.match(/up/) && !this.getTarget(e).className.match(/down/)) {
		target = as.parent(target, 'span');
	}
	var isUp = target.className.match(/up/);
	
	var uri;
	switch (this.getType()) {
		case 'new':
		case 'meet':
		case 'kid':
			uri = '/ajax/comment_vote/' + this.getId() + '/';
			break;
		case 'photos':
			uri = '/ajax/pix_comment_vote/' + this.getId() + '/';
			break;
		case 'community_group_photos':
			uri = '/community/group/' + this.partitionIds['gid'] + '/album/' + this.partitionIds['aid'] + '/comment/' + this.getId() + '/rating/';
			break;
		case 'community_group_topic':
			uri = '/community/group/' + this.partitionIds['gid'] + '/topic/' + this.partitionIds['tid'] + '/message/' + this.getId() + '/rating/';
			break;
		case 'yourstyle_set':
			uri = '/yourstyle/set/' + this.partitionIds['sid'] + '/comment/' + this.getId() + '/rating/';
			break;
		case 'chat':
			uri = '/ajax/chat_message_vote/' + this.getId() + '/';
			break;
		case 'topic':
			uri = '/ajax/message_vote/' + this.getId() + '/';
			break;
		case 'fanfic':
			uri = '/ajax/fanfics_comments_vote/' + this.getId() + '/';
			break;
	}
	uri += (isUp ? '1' : '-1');
	
	this.ratingIsUp = isUp;
	as.ajax(uri, as.bind(this.postRatingHook, this));
}

Comment.prototype.complainOnClick = function(e) {
	e.preventDefault();
	
	switch (this.getType()) {
		case 'new':
		case 'meet':
		case 'kid':
			break;
		default:
			return;
	}
	this.complainTooltipShown = false;
	as.ajax('/ajax/comment_complain/' + this.getId(), as.bind(this.postComplainHook, this));
}

Comment.prototype.editOnClick = function(e) {
	e.preventDefault();
	
	// already edit some comment
	// restore it view
	var editedComment;
	as.w('div.commentsTrack div.trackItem form.editComment').each(function() {
		editedComment = new Comment();
		editedComment.init(as.parent(this, 'div.trackItem'));
		
		editedComment.getElement('span.edit').style.display = 'inline';
		editedComment.getElement('div.entry p').style.display = 'block';
		as.remove(as.parent(editedComment.getElement('form.editComment'), 'div'));
	});
	
	// edit current comment
	var uri, fields;
	switch (this.getType()) {
		case 'new':
		case 'meet':
		case 'kid':
		case 'chat':
		case 'topic':
			uri = '/';
			fields = {action: 'edit', 'comm_id': this.getId()};
			break;
		case 'fanfic':
			uri = '/';
			fields = {action: 'comment_edit', 'comm_id': this.getId()};
			break;
		case 'community_group_topic':
			uri = '/community/group/' + this.partitionIds['gid'] + '/topic/' + this.partitionIds['tid'] + '/message/' + this.getId() + '/edit';
			break;
		case 'yourstyle_set':
			uri = '/yourstyle/set/' + this.partitionIds['sid'] + '/comment/' + this.getId() + '/edit';
			break;
		default:
			return;
	}
	// hide elements
	var target = this.getTarget(e);
	var entry = this.getElement('div.post div.entry');
	var formBlock = this.getFormBlock();
	target.style.display = 'none';
	as.$$('p', entry).style.display = 'none';
	formBlock.style.display = 'none';
	// create a form
	var newFormBlock = formBlock.cloneNode(true);
	var newForm = as.$$('form', newFormBlock);
	newFormBlock.style.display = 'block';
	newForm.className += ' editComment';
	// update fields
	for (name in fields) {
		if (newForm[name]) {
			newForm[name].value = fields[name];
		} else {
			as.append('<input type="hidden" name="' + name + '" value="' + fields[name] + '" />', newForm);
		}
	}
	
	as.e.submit(newForm, function() {
		if (this.trim(newForm.content.value).length == 0) {
			alert('Введите текст');
			return false;
		}
		
		as.ajax(uri, as.bind(this.postEditHook, this), 'post', this.getFormFieldsAsString(newForm), 30, [{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}]);
		return false;
	}, this);
	//as.$$('textarea', newFormBlock).value = this.getOriginalCommentText();
	as.append(newFormBlock, entry);
	SuperController.createController('Smile', 'smiles', 'div');
}
// \EVENTS


// EVENTS HOOKS
Comment.prototype.postDeleteHook = function(response) {
	as.remove(this.getElement('div.post div.details nobr'));
	as.remove(this.getElement('div.post div.mark'));
	
	return this.getElement('div.post div.entry p').innerHTML = '<span class="deleted">Комментарий удален</span>';
}

Comment.prototype.postRatingHook = function(response) {
	var data = this.json(response);
	
	if (data.status) {
		if (this.ratingIsUp) {
			var rating = this.getElement('div.post div.mark span.up span');
			rating.innerHTML = parseInt(rating.innerHTML)+1;
		} else {
			var rating = this.getElement('div.post div.mark span.down span');
			rating.innerHTML = parseInt(rating.innerHTML)-1;
		}
	}
}

Comment.prototype.postComplainHook = function(response) {
	if (this.complainTooltipShown == true) {
		return;
	}

	response = this.json(response);
	
	var complain = this.getElement('div.post div.details span.complain');
	var coords = as.getElementPosition(complain);
	var body = as.$$('body');
	var tooltip = as.appendElement('div',body);
	tooltip.className += ' tooltip';
	as.style(tooltip, {
		left: coords.left+"px",
		top: coords.top-45+"px"
	});

	if (!this.registered || response.status == 400) {
		tooltip.innerHTML += 'Вы не авторизованы и не можете голосовать.';
	} else if (response.status == 200) {
		tooltip.innerHTML += 'Спасибо! Администрация обратит внимание на этот комментарий.';
	} else if (response.status == 300) {
		tooltip.innerHTML += 'Вы уже жаловались сегодня на этот комментарий.';
	} else {
		tooltip.innerHTML += 'Ошибка. Попробуйте еще раз через несколько минут.';
	}
	this.complainTooltipShown = true;
	window.setTimeout(
		function() {
			as.removeChild(tooltip);
		},2000
	);
	complain.blur();
}

Comment.prototype.postEditHook = function(response) {
	var data = this.json(response);
	
	if (!data.status && data.error) {
		alert(data.error);
		return;
	}
	if (data.status) {
		// if we change subscribe status
		var form = as.$$('form', this.getFormBlock());
		if (form.subscribe) {
			form.subscribe.checked = this.getElement('form.editComment').subscribe.checked;
		}
		this.setOriginalCommentText(this.getElement('form.editComment').content.value);
		this.getElement('span.edit').style.display = 'inline';
		this.getElement('div.entry p').style.display = 'block';
		this.getElement('div.entry p').innerHTML = data.text;
		as.remove(as.parent(this.getElement('form.editComment'), 'div'));
		// show our form to add comments
		this.getFormBlock().style.display = 'block';
	}
}
// \EVENTS HOOKS

as.ready.add(function() {
	if (!USER_LOGGED_IN) {
		return;
	}
	
	// comments
	$('div#commentsTrack div.trackItem').each(function(){
		alert(11);
		new Comment().init(this);
	});
	
});