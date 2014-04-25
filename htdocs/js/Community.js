/**
 * JS For Community
 */

Community = function() {
	this.parentClass = 'group_info';
	this.enterClass = 'enter';
	this.leaveClass = 'leave';
	this.membersCountClass = 'participant';
}

/**
 * Enter the community
 */
Community.prototype.enter = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.className = ob.className.replace(self.enterClass, self.leaveClass);
			ob.href = ob.href.replace('enter', 'leave');
			ob.onclick = function (e) {self.leave(e);}
			var cnt = as.$$('.' + self.parentClass + ' .' + self.membersCountClass);
			num = parseInt(cnt.innerHTML)+1;
			cnt.innerHTML = num + ' ' + render_word(num, 'участник', 'участника', 'участников');
		} else if (response.status == -1) {
			new MessageBox().init({
				html: "<p class='vote-error'>Заявка отправлена.</p>",
				modalRelative: as.parent(ob, 'div.group_info')
			});
		}
	});
}

/**
 * Leave community
 */
Community.prototype.leave = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.className = ob.className.replace(self.leaveClass, self.enterClass);
			ob.href = ob.href.replace('leave', 'enter');
			ob.onclick = function (e) {self.enter(e);}
			var cnt = as.$$('.' + self.parentClass + ' .' + self.membersCountClass);
			num = parseInt(cnt.innerHTML)-1;
			cnt.innerHTML = num + ' ' + render_word(num, 'участник', 'участника', 'участников');
		}
	});
}

/**
 * Vote for topic
 */
Community.prototype.topicVote = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var isUp = (/down/.test(ob.className) ? false : true);
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			var elToUpdate = as.$$('span.rating', as.parent(ob, 'div.markTopic'));
			elToUpdate.innerHTML = parseInt(elToUpdate.innerHTML) + (isUp ? 1 : -1);
		}
	});
}

/**
 * Parse JSON
 */
Community.prototype.json = function(string) {
	if (string.substr(0, 1) != '(') {
		string = '(' + string + ')';
	}
	return eval(string);
}

/**
 * Normal searching form
 */
Community.prototype.searchInit = function() {
	as.$$('form.searchbox').onsubmit = function() {
		var searchVal = as.$$('input[name=q]', this).value;
		if (searchVal) {
			location.href = this.action + encodeURIComponent(searchVal);
		}
		return false;
	}
}

/**
 * Delete member
 */
Community.prototype.deleteMember = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			as.remove(as.parent(ob, 'tr'));
		}
	});
}

/**
 * Confirm/add member
 */
Community.prototype.addMember = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			as.remove(as.$$('td small', as.parent(ob, 'tr')));
			ob.innerHTML = 'Удалить';
			ob.href = ob.href.replace('add', 'delete');
			ob.onclick = function (e) {self.deleteMember(e);}
			
			var secondButton = next(next(ob));
			secondButton.innerHTML = 'Назначить модератором';
			secondButton.href = secondButton.href.replace('member/delete', 'assistant/add');
			secondButton.onclick = function (e) {self.addAssistant(e);}
		}
	});
}

/**
 * Add assistant
 */
Community.prototype.addAssistant = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.innerHTML = 'Убрать из модераторов';
			ob.href = ob.href.replace('add', 'delete');
			ob.onclick = function (e) {self.deleteAssistant(e);}
		} else if (response.status == -1) {
			new MessageBox().init({
				html: "<p class='vote-error'>Администраторов не может быть больше 3. Выбирайте только самых важных.</p>",
				modalRelative: ob
			});
		}
	});
}

/**
 * Delete assistant
 */
Community.prototype.deleteAssistant = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.innerHTML = 'Назначить модератором';
			ob.href = ob.href.replace('delete', 'add');
			ob.onclick = function (e) {self.addAssistant(e);}
		}
	});
}

/**
 * Add invite
 */
Community.prototype.addInvite = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.innerHTML = 'Отменить';
			ob.href = ob.href.replace('add', 'delete');
			ob.onclick = function (e) {self.deleteInvite(e);}
		}
	});
}

/**
 * Drop invite
 */
Community.prototype.deleteInvite = function(e) {
	e = e || window.event;
	e.preventDefault();
	var ob = e.target || e.srcElement;
	
	var self = this;
	as.ajax(ob.href, function(response) {
		response = self.json(response);
		if (response.status == 1) {
			ob.innerHTML = 'Пригласить';
			ob.href = ob.href.replace('delete', 'add');
			ob.onclick = function (e) {self.addInvite(e);}
		}
	});
}

/**
 * Poll submit
 */
Community.prototype.pollSubmit = function(form) {
	var answer = get_checked_value(form['option']);

	if (!answer) {
		new MessageBox().init({
			html: "<p class='vote-error'>Выберите один из вариантов</p>",
			modalRelative: as.$$("div.poll div#options")
		});
		return false;
	}
	
	var	gid = document.location.pathname.match(/group\/(\d+)/)[1],
		tid = document.location.pathname.match(/topic\/(\d+)/)[1];
		
	var self = this;
	as.ajax(
		'/community/group/' + gid + '/topic/' + tid + '/submitPoll/' + answer,
		function (response) {
			response = self.json(response);
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
		}
	);

	return false;
}
