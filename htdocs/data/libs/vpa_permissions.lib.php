<?php

class vpa_permissions {

	public $client;
	public $agent;
	public $type;
	public $action;
	public $user_type;

	public function vpa_permissions($user, $type, $action, $rewrite) {
		$this->user = $user;
		$this->type = $type;
		$this->action = $action;

		if (empty($this->user)) {
			$this->user_type = 'anonym';
		} elseif (!empty($this->user)) {
			$this->user_type = 'user';
		}
	}

	public function test() {
		$types = array();
		// прописываем правила
		// страница характеризуется типом и действием
		// для каждого действия (или * - если для всех действий) указываем через pipe всех user_type-ов, имеющих доступ к этой странице
		$types['*'] = array('*' => 'anonym');
		$types['profile'] = array('default' => 'user', '*' => 'user');
		$types['opinion'] = array('*' => 'user');
		$types['message'] = array('post' => 'user');
		$types['ajax'] = array(
			'*' => 'user',
			'users' => 'anonym|user',
			'cities' => 'anonym|user',
			'persons_list' => 'anonym|user',
			'gallery' => 'anonym|user',
			'person_search' => 'anonym|user',
			'meet_vote' => 'anonym|user',
			'kid_vote' => 'anonym|user',
			'new_vote' => 'anonym|user',
			'person_vote' => 'anonym|user',
			'fact_vote' => 'anonym|user'
		);
		$types['comment'] = array('add' => 'user', 'edit' => 'user');
		$types['message'] = array('post' => 'user', 'edit' => 'user');
		$types['chat_message'] = array('post' => 'user', 'edit' => 'user');
		$types['fanfic'] = array('comment_add' => 'user', 'comment_edit' => 'user', 'add' => 'user', 'edit' => 'user', '*' => 'anonym|user');
		$types['user'] = array('*' => 'user');
		$types['status'] = array('*' => 'user');
		$types['statuses'] = array('*' => 'user');
		$types['unsub'] = array('*' => 'user');

		// проверяем, присутствует ли наша страница в списке, требующих привилегии
		// если нет, то пишем что чел право имеет
		if (!isset($types[$this->type])) {
			return true;
		}
		$actions = $types[$this->type];

		foreach ($actions as $action => $perm) {
			$perms = explode("|", $perm);
			// если мы требуем привилегированного доступа ко всем действиям - то можно написать просто *
			// либо перечень действий, которые закрываем от открытого доступа
			if (($action == '*' || $action == $this->action) && in_array($this->user_type, $perms)) {
				return true;
			}
		}
		// если до этого момента ни одно правило не сработало - значит к нам пришел "не человек, право имеющий, а тварь дрожащая" (с) Достоевский
		return false;
	}

}

?>