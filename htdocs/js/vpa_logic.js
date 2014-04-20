function fact_vote(fact,rubric,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/fact_vote/'+fact+'/'+vote+'/'+rubric,show_fact_votes,this);
}

function show_fact_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('f_'+data.fid+'_'+data.rubric);
	d.innerHTML=data.rating;
}

function new_vote(new_id,rubric)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/new_vote/'+new_id+'/'+rubric,show_new_votes,this);
}

function check_mail()
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/check_mail', show_new_mail);
}

function show_new_mail(str)
{
	var d=document.getElementById('check_mail');
	if ((str.length > 0) && (str.length < 200)) d.innerHTML = str;
}

function show_new_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('v'+data.nid);
	var ds=d.getElementsByTagName('div');
	ds[0].style.width=(data.p1-0.1)+'%';
	ds[1].style.width=(data.p2-0.1)+'%';
	var ss=d.getElementsByTagName('span');
	ss[0].innerHTML=data.v1;
	ss[1].innerHTML=data.v2;
}


function person_vote(person,rubric,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/person_vote/'+person+'/'+vote+'/'+rubric,show_person_votes,this);
}

function show_person_votes(str,xml)
{
	data={'rating':0};
	eval('data='+str);
	var d=document.getElementById('p_'+data.aid+'_'+data.rubric);
	d.innerHTML=data.rating;
	var d2=document.getElementById('p_'+data.aid);
	d2.innerHTML=data['total_rating'];
}

function topic_vote(id,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/topic_vote/'+id+'/'+vote,show_topic_votes,this);
}

function show_topic_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('t_'+data.id);
	d.innerHTML=data.rating;
}

function chat_topic_vote(id,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/chat_topic_vote/'+id+'/'+vote,show_chat_topic_votes,this);
}

function show_chat_topic_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('t_'+data.id);
	d.innerHTML=data.rating;
}

function fanfics_vote(id,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/fanfics_vote/'+id+'/'+vote,fanfics_votes,this);
}

function fanfics_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('fanfic_'+data.fanfic_id);
	d.innerHTML=data.rating;
}

function meet_vote(id,vote)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/meet_vote/'+id+'/'+vote,show_meet_votes,this);
}

function show_meet_votes(str,xml)
{
	eval('data='+str);
	var d=document.getElementById('m_'+data.mid);
	d.innerHTML=data.rating;
	var v=document.getElementById('v_'+data.mid);
	v.innerHTML=data.vote;
}

function friend_fetch(num) {
	friends_num = as.$$('div#contentWrapper div#content ul.menu li span.marked');
	friends_num.innerHTML = parseInt(friends_num.innerHTML) + num;
}

function c_del_friend(nick,id)
{
	if (window.confirm('Вы точно хотите удалить '+nick+' из списка друзей ?'))
	{
		ajax=new vpa_ajax();
		ajax.makeRequest('/ajax/remove_friend/'+id,show_remove_friend,this);
	}
}

function show_remove_friend(str,xml)
{
	eval('data='+str);
	if (data.status==1)
	{
		var d=document.getElementById('f_'+data.id);
		d.parentNode.removeChild(d);
	}
	else
	{
		alert ('Произошла ошибка, попробуйте повторить операцию чуть позже.');
	}
}

function c_reject_friend(id)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/reject_friend/'+id,show_reject_friend,this);
}

function show_reject_friend(str,xml)
{
	eval('data='+str);
	if (data.status==1)
	{
		friend_fetch(-1);
		
		var d=document.getElementById('f_'+data.id);
		d.parentNode.removeChild(d);
	}
	else
	{
		alert ('Произошла ошибка, попробуйте повторить операцию чуть позже.');
	}
}

function c_confirm_friend(id)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/confirm_friend/'+id, show_confirm_friend, this);
}

function show_confirm_friend(str,xml)
{
	eval('data='+str);
	if (data.status==1)
	{
		var d=document.getElementById('f_'+data.id);

		as.remove(as.$$('td.user small', d));
		as.remove(as.$('td.actions a', d));
		as.$$('td.actions', d).innerHTML = '<a href="#" onclick="c_del_friend(\'' + htmlentities(as.$$('td.user a span', d).innerHTML) + '\',' + data.id + '); return false;">Удалить</a>';

		friend_fetch(-1);
	}
	else
	{
		alert ('Произошла ошибка, попробуйте повторить операцию чуть позже.');
	}
}

function c_add_friend(id)
{
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/add_friend/'+id, show_add_friend, this);
}

function show_add_friend(str,xml)
{
	eval('data='+str);
	if (data.status==1)
	{
		var d = as.$$('div#contentWrapper div#content div.userDetails div.actions');
		as.remove(as.next(as.$$('a.addToFriends', d)));
		as.remove(as.$$('a.addToFriends', d));
	}
	else
	{
		alert ('Произошла ошибка, попробуйте повторить операцию чуть позже.');
	}
}

function contest_work_vote(id,vote) {
	ajax=new vpa_ajax();
	ajax.makeRequest('/ajax/contest_work_vote/'+id,contest_work_votes,this);
}

function contest_work_votes(str,xml) {
	eval('data='+str);
	as.$$('#cw_'+data.id).innerHTML = data.rating;
}

/*
 * deleting message
 */
var del_msg_form_id = '';
var text = '<p style="color: black;" class="delete">Подождите...</p>';

function delete_msg(id, type) {
	if (del_msg_form_id == '') {
		del_msg_form_id = id;
		del_msg_ajax(id, type);
	} else {
		var str = document.getElementById(id).innerHTML;
		if (str.substring(str.length-text.length) != text) {
			document.getElementById(id).innerHTML += text;
		}
	}
}

function del_msg_ajax(id, type) {
	if (!id || !type) return false;

	ajax = new vpa_ajax();
	ajax.makeRequest('/messages/delete/'+type+'/'+id, del_msg_ajax_return);
}

function del_msg_ajax_return(data) {
	document.getElementById(del_msg_form_id).innerHTML = '<p class="delete">'+data+'</p>';
	del_msg_form_id = '';
}

/*
 * restore message
 */
function restore_msg(id, type){
	if (del_msg_form_id == ''){
		del_msg_form_id = id;
		restore_msg_ajax(id, type);
	}else{
		var str = document.getElementById(id).innerHTML;
		if (str.substring(str.length-text.length) != text) {
			document.getElementById(id).innerHTML += text;
		}
	}
}

function restore_msg_ajax(id, type){
	if (!id) return;
	if (!type) return;

	ajax = new vpa_ajax();
	ajax.makeRequest('/messages/restore/'+type+'/'+id, restore_msg_ajax_return);
}

function restore_msg_ajax_return(data){
	document.getElementById(del_msg_form_id).innerHTML = '<p class="delete">'+data+'</p>';
	del_msg_form_id = '';
}
