<?php

/**
 * @author Azat Khuzhin
 *
 * Community
 */

class Community {
	/**
	 * WWW upload path for avatars
	 */
	const uploadPathAvatars = '/upload/community/groups/avatars/';
	/**
	 * WWW upload path for albums photos
	 */
	const uploadPathAlbumsPhotos = '/upload/community/groups/albums/';
	/**
	 * Max topics polls options
	 */
	const topicsPollsMaxOptions = 7;
	/**
	 * VPA SQL
	 *
	 * @var object of VPA_sql
	 */
	protected $sql;
	/**
	 * VPA Memcache
	 *
	 * @var object of VPA_Memcache
	 */
	protected $memcache;
	/**
	 * VPA tpl
	 *
	 * @var object of VPA_tpl or VPA_template
	 */
	protected $tpl;
	/**
	 * VPA sess
	 *
	 * @var object of session
	 */
	protected $sess;
	/**
	 * User info
	 *
	 * @var array
	 */
	protected $user;
	/**
	 * user_base_api
	 *
	 * @var user_base_api
	 */
	protected $user_lib;
	/**
	 * MongoDB Collection
	 *
	 * @var object
	 */
	protected $newsfeed;
	/**
	 * Old include path
	 *
	 * @var string
	 */
	protected $oldIncPath;
	/**
	 * Dispatcher
	 *
	 * @var akDispatcher
	 */
	protected $d;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function  __construct(user_base_api &$user_lib, $noInitRoutes = false) {
		// Base
		$this->sess = session::getInstance();
		$this->memcache = VPA_memcache::getInstance();
		$this->tpl = VPA_template::getInstance();
		$this->sql = VPA_sql::getInstance();
        try {
		    $this->newsfeed = VPA_MongoDB::getInstance()->newsfeed;
        }
        catch(Exception $e) {
            $this->newsfeed = null;
        }

		$this->user = $this->sess->restore_var('sess_user');
		$this->user_lib = $user_lib;
		$this->oldIncPath = set_include_path(get_include_path() . ':' . AKLIB_DIR);

		if (!$noInitRoutes) $this->_initRoutes();
	}

	/**
	 * Init routes
	 *
	 * @return void
	 */
	protected function _initRoutes() {
		// not auth
		if (!$this->user && $_SERVER['REQUEST_URI'] != '/community/groups') return $this->handler_show_error('no_login');

		require_once 'akDispatcher/akDispatcher.class.php';
		$this->d = $d = akDispatcher::getInstance('/', null, 'WINDOWS-1251');
		$d->functionReturnsContent = false;

		$d->add('/community/getTags/:q', array(&$this, 'getTags'));
		$d->add('/community/getMembers/:gid/:q', array(&$this, 'getMembersUsers'));
		$d->add('/community/getAssistants/:gid/:q', array(&$this, 'getAssistantsUsers'));
		$d->add('/community/group/add', array(&$this, 'addGroup'), 'both');
		$d->add('/community/group/:gid/edit', array(&$this, 'editGroup'), 'both');
		// $d->add(array('/community/group/:gid/delete'), array(&$this, 'deleteGroup'));
		$d->add(array('/community/group/:gid/newsfeed', '/community/group/:gid/newsfeed/page/:page'), array(&$this, 'showGroupNewsFeed'));
		$d->add(array('/community/group/:gid/invites', '/community/group/:gid/invites/page/:page'), array(&$this, 'addGroupInvites'));
		$d->add(array('/community/group/:gid/members', '/community/group/:gid/members/page/:page'), array(&$this, 'showGroupMembers'));
		$d->add(array('/community/group/:gid/editMembers', '/community/group/:gid/editMembers/page/:page'), array(&$this, 'editGroupMembers'));
		$d->add('/community/group/:gid/member/add/:uid', array(&$this, 'addGroupMember'));
		$d->add('/community/group/:gid/assistant/add/:uid', array(&$this, 'addGroupAssistant'));
		$d->add('/community/group/:gid/assistant/delete/:uid', array(&$this, 'deleteGroupAssistant'));
		$d->add('/community/group/:gid/member/delete/:uid', array(&$this, 'deleteGroupMember'));
		$d->add('/community/group/:gid/member/enter', array(&$this, 'addGroupMember'));
		$d->add('/community/group/:gid/member/leave', array(&$this, 'deleteGroupMember'));
		$d->add('/community/group/:gid/private/confirm', array(&$this, 'confirmGroupMember'));
		$d->add('/community/group/:gid/private/leave', array(&$this, 'leaveGroupMember'));
		$d->add(array('/community/group/:gid/albums', '/community/group/:gid/albums/page/:page'), array(&$this, 'showGroupAlbums'));
		$d->add('/community/group/:gid/album/add', array(&$this, 'addGroupAlbum'), 'both');
		$d->add('/community/group/:gid/album/:aid/delete', array(&$this, 'deleteGroupAlbum'));
		$d->add('/community/group/:gid/album/:aid/edit', array(&$this, 'editGroupAlbum'), 'both');
		$d->add('/community/group/:gid/album/:aid', array(&$this, 'showAlbumPhotos'));
		$d->add('/community/group/:gid/album/:aid/postComment', array(&$this, 'addAlbumComment'), 'post');
		$d->add('/community/group/:gid/album/:aid/comment/:cid/rating/:rating', array(&$this, 'updateAlbumCommentRating'));
		$d->add('/community/group/:gid/album/:aid/comment/:cid/delete', array(&$this, 'deleteAlbumComment'));
		$d->add('/ajax/gallery/album/:aid', array(&$this, 'getAlbumPhotos'));
		$d->add('/community/group/:gid/album/:aid/addPhotos', array(&$this, 'addAlbumPhotos'), 'both');
		$d->add('/community/group/:gid/album/:aid/deletePhotos', array(&$this, 'deleteAlbumPhotos'), 'both');
		$d->add(array('/community/group/:gid/topics', '/community/group/:gid/topics/page/:page', '/community/group/:gid/topics/page/:page/sort/:sort_:order'), array(&$this, 'showGroupTopics'));
		$d->add('/community/group/:gid/topic/add', array(&$this, 'addGroupTopic'), 'both');
		$d->add('/community/group/:gid/topic/addPoll', array(&$this, 'addGroupTopicPoll'), 'both');
		$d->add('/community/group/:gid/topic/:tid/message/:mid/edit', array(&$this, 'editTopicMessage'), 'post');
		$d->add('/community/group/:gid/topic/:tid/message/:mid/delete', array(&$this, 'deleteTopicMessage'));
		$d->add('/community/group/:gid/topic/:tid/message/:mid/rating/:rating', array(&$this, 'updateMessageRating'));
		$d->add('/community/group/:gid/topic/:tid/rating/:rating', array(&$this, 'updateTopicRating'));
		$d->add('/community/group/:gid/topic/:tid/postMessage', array(&$this, 'addTopicMessage'), 'post');
		$d->add('/community/group/:gid/topic/:tid/edit', array(&$this, 'editGroupTopic'), 'both');
		$d->add('/community/group/:gid/topic/:tid/delete', array(&$this, 'deleteGroupTopic'));
		$d->add(array('/community/group/:gid/topic/:tid', '/community/group/:gid/topic/:tid/page/:page'), array(&$this, 'showGroupTopic'));
		$d->add('/community/group/:gid', array(&$this, 'showGroup'));
		$d->add('/community/groups', array(&$this, 'showGroups'));
		$d->add(array('/community/groups/search', '/community/groups/search/:q', '/community/groups/search/:q/page/:page'), array(&$this, 'showSearchGroups'));
		$d->add('/community/groups/new', array(&$this, 'showNewGroups'));
		$d->add('/community/groups/top', array(&$this, 'showTopGroups'));
		$d->add('/community/groups/tags', array(&$this, 'showGroupsTags'));
		$d->add(array('/community/groups/tag/:tid', '/community/groups/tag/:tid/page/:page'), array(&$this, 'showGroupsByTag'));
		$d->add(array('/profile/:uid/community/groups', '/profile/:uid/community/groups/page/:page', '/user/:uid/community/groups', '/user/:uid/community/groups/page/:page'), array(&$this, 'showUsersGroups'));
		$d->add(array('/profile/:uid/community/newsfeed', '/profile/:uid/community/newsfeed/page/:page'), array(&$this, 'showUserGroupsNewsFeed'));
		$d->add('/community/group/:gid*', array(&$this, 'getGroupInfo'), 'both', akDispatcher::NOT_FINAL_ROUTE);
		$d->add('/community/groups/rules', array(&$this, 'showGroupsRules'));
		$d->add('/community/group/:gid/topic/:tid/submitPoll/:answer', array(&$this, 'topicsSubmitPoll'));

		try {
			$d->run();
		} catch (Exception $e) {
			$this->redirect();
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		set_include_path($this->oldIncPath);
	}

	/**
	 * Get list of tags: persons, events
	 */
	public function getTags($q, $limit = 10) {
		$q = $this->iconvDecode($q);
		if (empty($q)) return null;
		if (!$limit || $limit > 200) $limit = 10;

		$tagsObject = new VPA_table_community_tags;
		$tags = $tagsObject->get_fetch(array('q' => strip_tags($q)), array('name'), 0, $limit);

		$this->tpl->assign('data', $tags);
		$this->tpl->tpl('', '/', 'ajax.php');
		return true;
	}

	/**
	 * Get list of users for members
	 *
	 * @param string $q - query
	 * @return string
	 */
	public function getMembersUsers($gid, $q, $limit = 10) {
		$q = trim(strip_tags($this->iconvDecode($q)));
		if (empty($q) || !$gid) return null;
		if (!$limit || $limit > 200) $limit = 10;

		$usersObject = new VPA_table_community_groups_suggest_members;
		$users = $usersObject->get_fetch(array('gid' => (int)$gid, 'q' => $q), array('b.id'), 0, $limit, array('b.id'));
		$this->tpl->assign('data', $users);
		$this->tpl->tpl('', '/', 'ajax.php');
		return true;
	}

	/**
	 * Get list of users for assistants
	 *
	 * @param string $q - query
	 * @param string $gid - group id
	 * @return string
	 */
	public function getAssistantsUsers($gid, $q, $limit = 10) {
		$q = trim(strip_tags($this->iconvDecode($q)));
		if (empty($q) || !$gid) return null;
		if (!$limit || $limit > 200) $limit = 10;

		$suggestions = new VPA_table_community_groups_suggest_assistants;
		$users = $suggestions->get_fetch(array('gid' => (int)$gid, 'q' => $q), array('b.id'), 0, $limit, array('b.id'));
		$this->tpl->assign('data', $users);
		$this->tpl->tpl('', '/', 'ajax.php');
		return true;
	}


	public function addGroup() {
		// save
		if ($this->d->isPost()) {
			$title = strip_tags(trim($this->get_param('title')));
			$description = substr(strip_tags(trim($this->get_param('description'))), 0, 600);
			$type = ($this->get_param('group') == 'public') ? 'public' : 'private';
			$tags = array_unique(array_map('intval', $this->get_param('getTags')));

			$file = $this->get_param('image');
			$fileInfo = pathinfo($file['name']);

			if (!$title || !$description || (!$file || $file['error'] !== 0) || !$tags) {
				$this->tpl->assign('error', 'Не все поля заполнены. Пожалуйста заполните все поля.');
			} elseif (!in_array(strtolower($fileInfo['extension']), array('jpg', 'jpeg', 'gif', 'png'))) {
				$this->tpl->assign('error', 'Такой формат изображения мы не поддерживаем. Попробуйте закачать другой.');
			} elseif (count($tags) > 2) {
				$this->tpl->assign('error', 'Тэгов не может быть больше двух.');
			} else {
				$path = $this->getPhysicalUploadAvatarPath();
				$image = random_file_name($path, $fileInfo['extension']);
				copy($file['tmp_name'], $path . $image);
				split_image($path . $image);

				$groups = new VPA_table_community_groups;
				$groups->add($ret, array('title' => $title, 'description' => $description, 'type' => $type, 'createtime' => time(), 'uid' => $this->user['id'], 'image' => $image));
				$ret->get_first($gid);
				// no auto_increment new value
				if (!$gid) {
					$this->handler_show_error('db_error');
					return false;
				}

				$groupsTags = new VPA_table_community_groups_tags;
				foreach ($tags as &$tag) {
					$groupsTags->add($ret, array('tid' => $tag, 'createtime' => time(), 'gid' => $gid));
				}
				// add it self to group members
				$groupsMembers = new VPA_table_community_groups_members;
				$groupsMembers->add($ret, array('uid' => $this->user['id'], 'muid' => $this->user['id'], 'createtime' => time(), 'gid' => $gid, 'confirm' => 'y'));

				$this->redirect('/community/group/' . $gid);
				return true;
			}
		}

		$this->tpl->tpl('', '/community/', 'addGroup.php');
	}

	public function showGroup($gid) {
		$group = $this->tpl->get_data('group');
		$this->isAMember();
		$hasGrants = $this->checkGrants();

		// get tags
		$groupsTags = new VPA_table_community_groups_tags_with_info;
		$tags = $groupsTags->get_fetch(array('gid' => $gid), array('a.createtime'));
		$this->tpl->assign('communityTags', $tags);

		// creator
		$users = new VPA_table_users;
		$creator = $users->get_first_fetch(array('id' => $group['uid']));
		$this->tpl->assign('creator', $creator);

		// get assistents
		$groupsAssistants = new VPA_table_community_groups_assistants_with_info;
		$assistants = $groupsAssistants->get_fetch(array('gid' => $gid), array('a.createtime'));
		$this->tpl->assign('assistants', $assistants);

		// photos
		$groupsAlbums = new VPA_table_community_groups_albums;
		$albumsPhotos = new VPA_table_community_albums_photos;
		$albumsNum = $groupsAlbums->get_num_fetch(array('gid' => $gid));
		$this->tpl->assign('albumsNum', $albumsNum);
		if ($albumsNum > 0) {
			$albums = $groupsAlbums->get_fetch(array('gid' => $gid), array('createtime DESC'), 0, 4);
			// get last photo
			foreach ($albums as &$album) {
				$album['lastPhoto'] = $albumsPhotos->get_first_fetch(array('aid' => $album['id']), array('createtime DESC'));
				$album['photos'] = $albumsPhotos->get_num_fetch(array('aid' => $album['id']));
			}
			$this->tpl->assign('albums', $albums);
		}

		// topics
		$topicsMessages = new VPA_table_community_topics_messages_with_info;
		$groupsTopics = new VPA_table_community_groups_topics_with_info;
		$topics = $groupsTopics->get_fetch(array('gid' => $gid), array('a.createtime DESC'), 0, 8);
		// get last message & number of messages
		foreach ($topics as &$topic) {
			$topic['lastMessage'] = $topicsMessages->get_first_fetch(array('tid' => $topic['id']), array('a.createtime DESC'));
			$topic['messagesNum'] = $topicsMessages->get_num_fetch(array('tid' => $topic['id']));
		}
		$topicsNum = $groupsTopics->get_num_fetch(array('gid' => $gid));
		$this->tpl->assign('topics', $topics);
		$this->tpl->assign('topicsNum', $topicsNum);

		// members
		$groupsMembers = new VPA_table_community_groups_members_with_info;
		$members = $groupsMembers->get_fetch(array('gid' => $gid, 'confirm' => 'y'), array('a.createtime'), 0, 20);
		$membersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'y'));
		$this->tpl->assign('members', $members);
		$this->tpl->assign('membersNum', $membersNum);
		// new members
		if ($hasGrants) {
			$newMembersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'n'));
			$this->tpl->assign('newMembersNum', $newMembersNum);
		}

		$this->tpl->tpl('', '/community/group/', 'details.php');
	}

	public function showGroupNewsFeed($gid, $page = 1, $perPage = 50) {
		$this->showNewsFeed($gid, null, $page, $perPage);
		$this->tpl->tpl('', '/community/group/', 'newsfeed.php');
	}

	public function showUserGroupsNewsFeed($uid, $page = 1, $perPage = 50) {
        try {
		    $this->showNewsFeed(null, $uid, $page, $perPage);
        }
        catch(Exception $e) {
            $this->tpl->assign('maintance', true);
        }
		$this->tpl->tpl('', '/profile/', 'community_newsfeed.php');
	}

	private function showNewsFeed($gid = null, $uid = null, $page = 1, $perPage = 50) {
		if (!$gid && !$uid) {
			throw new Exception('No gid or uid - redirect');
		}

        if(is_null($this->newsfeed)) {
            throw new Exception('off');
        }

		if ($page < 1) $page = 1;
		$iconv = &$this->tpl->plugins['iconv'];

		$options = array('createtime' => array('$gt' => new MongoDate(strtotime('-7 day'))));
		if ($gid) $options['gid'] = (int)$gid;
		if ($uid && !$gid) {
			$groupsMembers = new VPA_table_community_groups_members;
			$groups = $groupsMembers->get_params_fetch(array('confirm' => 'y', 'muid' => $this->user['id']), null, null, null, null, array('gid'));
			$options['gid'] = array('$in' => array_map('intval', clever_array_values($groups)));
		}

		$items = $this->newsfeed->find($options)->sort(array('createtime' => -1, 'id' => -1))->skip(($page-1)*$perPage)->limit($perPage);
		$allItems = iterator_to_array($items);
		if ($allItems) {
			// id to int
			foreach ($allItems as &$item) {
				$item['_id'] = (string)$item['_id'];
				$item['createtime'] = $item['createtime']->sec;
				$item['_createGroupingTime'] = $item['_createGroupingTime']->sec;
			}
			// change charset
			$iconv->iconv_exchange();
			$allItems = $iconv->iconv($allItems);
			$iconv->iconv_exchange();

			// fetch user info
			$users = new VPA_Table_users;
			$allUsers = $users->get_fetch(array('id_in' => join(',', array_unique(clever_array_values($allItems, 'uid')))));
			// fetch albums info
			$groupsAlbums = new VPA_table_community_groups_albums;
			$allAlbums = $groupsAlbums->get_fetch(array('id_in' => join(',', array_unique(clever_array_values($allItems, 'aid')))));
			// fetch topics info
			$groupsTopics = new VPA_table_community_groups_topics;
			$allTopics = $groupsTopics->get_fetch(array('id_in' => join(',', array_unique(clever_array_values($allItems, 'tid')))));
			// append additional info
			foreach ($allItems as &$item) {
				// user info
				foreach ($allUsers as &$user) {
					if ($user['id'] == $item['uid']) {
						$item['userInfo'] = &$user;
					}
				}
				// albums info
				if (isset($item['aid'])) {
					foreach ($allAlbums as &$album) {
						if ($album['id'] == $item['aid']) {
							$item['albumInfo'] = &$album;
						}
					}
				}
				// topics info
				if (isset($item['tid'])) {
					foreach ($allTopics as &$topic) {
						if ($topic['id'] == $item['tid']) {
							$item['topicInfo'] = &$topic;
						}
					}
				}
			}
			$this->tpl->assign('items', $allItems);
		}

		$this->tpl->assign('num', $items->count());
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($this->tpl->get_data('num') / $perPage));
	}

	public function getTopGroups($count) {
	    
	$tm = new VPA_table_community_groups_members_count();
		$topMemebersSrc = $tm->get_fetch(null, array('members DESC'));
		
		$tt = new VPA_table_community_groups_topics_count();
		$topTopicsSrc = $tt->get_fetch(null, array('topics DESC'));
		
		$ta = new VPA_table_community_groups_albums_count();
		$topAlbumsSrc = $ta->get_fetch(null, array('albums DESC'));
		
		$topGroups = array();
		foreach ($topMemebersSrc as $t) {
		    $topGroups[$t['gid']]['id'] = $t['gid'];
		    $topGroups[$t['gid']]['members'] = $t['members'];
		    $topGroups[$t['gid']]['count'] = $t['members'];		    
		}		
		foreach ($topTopicsSrc as $t) {
		    $topGroups[$t['gid']]['count'] += $t['topics'];		    
		}		
		foreach ($topAlbumsSrc as $t) {
		    $topGroups[$t['gid']]['count'] += $t['albums'];		    
		}
		usort($topGroups, function($a, $b) {
		    return $a['count'] < $b['count'];
		});
		
		$topGroupsSrc = array_slice($topGroups, 0, $count);
		$tg = new VPA_table_community_groups();
		$ids = array();
		$topGroups = array();
		foreach ($topGroupsSrc as $t) {
		    $ids[] = $t['id'];
		    $topGroups[$t['id']] = $t;
		}		
		$ids = implode(',', $ids);
		
		$groupsInfo = $tg->get_fetch(array('id_in'=>$ids), array('FIELD(id,'.$ids.')'), 0, $count);
		foreach ($groupsInfo as $g) {
		    $topGroups[$g['id']] = array_merge($topGroups[$g['id']], $g);
		}
		return $topGroups;
	}
	
	public function showGroups() {
		// get top groups [num=9]
		$topGroups = $this->getTopGroups(9);
		$this->tpl->assign('topGroups', $this->transformGroupsTags($topGroups));
		// get new groups [num=6]
		$topGroupsOb = new VPA_table_community_groups;
		$newGroups = $topGroupsOb->get_fetch(null, array('id DESC'), 0, 6);
		$this->tpl->assign('newGroups', $newGroups);
		// get tags [num=20]
		$this->tpl->assign('communityTags', $this->getTagsWithRating(20));

		$this->tpl->tpl('', '/community/', 'groups.php');
	}

	public function showSearchGroups($q, $page = 1, $perPage = 50) {
		$q = $this->iconvDecode($q);
		$topGroupsOb = new VPA_table_community_groups;
		$newGroups = $topGroupsOb->get_fetch(array('q' => $q), array('title'), 0, 50);
		$newGroupsNum = $topGroupsOb->get_num_fetch(array('q' => $q));

		$this->tpl->assign('newGroups', $newGroups);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($newGroupsNum / $perPage));
		$this->tpl->assign('num', $newGroupsNum);
		$this->tpl->assign('q', $q);

		$this->tpl->tpl('', '/community/', 'searchGroups.php');
	}

	public function showNewGroups() {
		$groupsOb = new VPA_table_community_groups;
		$newGroups = $groupsOb->get_fetch(null, array('createtime DESC'), 0, 50);
		$this->tpl->assign('newGroups', $newGroups);

		$this->tpl->tpl('', '/community/', 'newGroups.php');
	}

	public function showTopGroups() {
		$topGroups = $this->getTopGroups(50);
		$this->tpl->assign('topGroups', $this->transformGroupsTags($topGroups));

		$this->tpl->tpl('', '/community/', 'topGroups.php');
	}

	public function showGroupsTags() {
		$this->tpl->assign('communityTags', $this->getTagsWithRating());

		$this->tpl->tpl('', '/community/', 'groupsTags.php');
	}

	public function showGroupsByTag($tid, $page = 1, $perPage = 50) {
		if ($page < 1) $page = 1;

		$tagsInfo = new VPA_table_tags_with_info;
		$tagInfo = $tagsInfo->get_first_fetch(array('id' => $tid));
		if (!$tagInfo) {
			$this->redirect();
		}
		$this->tpl->assign('tag', $tagInfo);

		$groupsTags = new VPA_table_community_groups_tags;
		$num = $groupsTags->get_num_fetch(array('tid' => $tid));
		$this->tpl->assign('num', $num);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($num / $perPage));
		$groupsIds = $groupsTags->get_params_fetch(array('tid' => $tid), array('createtime desc'), ($page-1)*$perPage, $perPage, null, array('gid'));
		$groupsIds = clever_array_values($groupsIds, 'gid');
		$groups = new VPA_table_community_groups;
		$groupsInfo = $groups->get_fetch(array('id_in' => join(',', $groupsIds)), array('null'), ($page-1)*$perPage, $perPage);
		$this->tpl->assign('groups', $groupsInfo);

		$this->tpl->tpl('', '/community/', 'groupsByTag.php');
	}

	public function editGroup($gid) {
		if (!$this->checkGrants() && !$this->tpl->isCommunityModer()) {
			$this->redirect();
			return false;
		}

		$groupOldInfo = $this->tpl->get_data('group');

		// get tags
		$groupsTags = new VPA_table_community_groups_tags_with_info;
		$oldTags = $groupsTags->get_fetch(array('gid' => $gid), array('a.createtime'));
		$this->tpl->assign('communityTags', $oldTags);

		// save
		if ($this->d->isPost()) {
			$title = strip_tags(trim($this->get_param('title')));
			$description = substr(strip_tags(trim($this->get_param('description'))), 0, 600);
			$type = ($this->get_param('group') == 'public') ? 'public' : 'private';
			$tags = array_unique(array_map('intval', $this->get_param('getTags')));

			$file = $this->get_param('image');
			$fileInfo = $file['error'] === 0 ? pathinfo($file['name']) : null;

			if (!$title || !$description || !$tags) {
				$this->tpl->assign('error', 'Не все поля заполнены. Пожалуйста заполните все поля.');
			} elseif ($fileInfo && !in_array(strtolower($fileInfo['extension']), array('jpg', 'jpeg', 'gif', 'png'))) {
				$this->tpl->assign('error', 'Такой формат изображения мы не поддерживаем. Попробуйте закачать другой.');
			} elseif (count($tags) > 2) {
				$this->tpl->assign('error', 'Тэгов не может быть больше двух.');
			} else {
				$image = $groupOldInfo['image'];
				if ($fileInfo) {
					$path = $this->getPhysicalUploadAvatarPath();
					$image = random_file_name($path, $fileInfo['extension']);
					copy($file['tmp_name'], $path . $image);
					split_image($path . $image);
				}
				// delete old photo
				if ($fileInfo || ($this->get_param('deletePhoto') == 1 && $groupOldInfo['image'])) {
					unlink($this->getPhysicalUploadAvatarPath() . $groupOldInfo['image']);
					if (!$file || $file['error'] !== 0) $image = null;
				}

				$groups = new VPA_table_community_groups;
				$ok = $groups->set($ret, array('title' => $title, 'description' => $description, 'type' => $type, 'edittime' => time(), 'image' => $image), $gid);
				// no auto_increment new value
				if (!$ok) {
					$this->handler_show_error('db_error');
					return false;
				}

				// TAGS
				$oldTagsArray = clever_array_values($oldTags, 'id');
				$groupsTagsSimple = new VPA_table_community_groups_tags;
				if ($oldTagsArray) {
					// count tags for delete
					$tagsForDelete = array();
					foreach ($oldTagsArray as &$oldTag) {
						if (!in_array($oldTag, $tags)) $tagsForDelete[] = $oldTag;
					}
					// delete already existed tags
					foreach ($tags as $i => &$tag) {
						if (in_array($tag, $oldTagsArray)) unset($tags[$i]);
					}
					// delete old tags
					if (count($tagsForDelete) > 0) {
						$groupsTagsSimple->del_where($ret, array('tid_in' => join(',', $tagsForDelete), 'gid' => $gid));
					}
				}
				// add new tags
				if (is_array($tags) && count($tags) > 0) {
					foreach ($tags as &$tag) {
						if (!$tag) continue;
						$groupsTagsSimple->add($ret, array('tid' => $tag, 'createtime' => time(), 'gid' => $gid));
					}
				}

				$this->redirect('/community/group/' . $gid);
				return true;
			}
		}

		// new members
		$groupsMembers = new VPA_table_community_groups_members;
		$newMembersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'n'));
		$this->tpl->assign('newMembersNum', $newMembersNum);

		$this->tpl->tpl('', '/community/group/', 'edit.php');
	}

	public function addGroupAlbum() {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		// save
		if ($this->d->isPost()) {
			$title = strip_tags(trim($this->get_param('title')));

			if (!$title) {
				$this->tpl->assign('error', 'Пустое название альбома');
			} else {
				$groupInfo = $this->tpl->get_data('group');
				$groupAlbums = new VPA_table_community_groups_albums;
				$params = array('title' => $title, 'createtime' => time(), 'gid' => $groupInfo['id'], 'uid' => $this->user['id']);
				if (!$groupAlbums->add($albumId, $params)) {
					$this->handler_show_error('db_error');
					return false;
				}
				$albumId->get_first($albumId);
				$this->addFeed(array_merge(array('id' => $albumId), $params), 'albums');

				$this->redirect(sprintf('/community/group/%u/album/%u', $groupInfo['id'], $albumId));
				return true;
			}
		}

		$this->tpl->tpl('', '/community/group/album/', 'add.php');
	}

	public function deleteGroupAlbum($gid, $aid) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		$albumsPhotos = new VPA_table_community_albums_photos;
		$photos = $albumsPhotos->get_num_fetch(array('aid' => $aid));
		// has childrens - photos
		if ($photos != 0) {
			$this->handler_show_error('community_delete_albums_have_photos');
			return false;
		}

		$groupAlbums = new VPA_table_community_groups_albums;
		if (!$groupAlbums->del($ret, $aid)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$albumsCommentsVotes = new VPA_table_community_albums_comments_votes;
		$albumsCommentsVotes->del_where($ret, array('aid' => $aid));
		// delete news feed
		$this->deleteFeed(array('id' => $aid), 'albums');

		$this->redirect(sprintf('/community/group/%u/albums', $gid));
	}

	public function editGroupAlbum($gid, $aid) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		$groupsAlbums = new VPA_table_community_groups_albums;
		$album = $groupsAlbums->get_fetch(array('id' => $aid));
		// has childrens - photos
		if (!$album) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('album', $album);

		if ($this->d->isPost()) {
			$title = strip_tags(trim($this->get_param('title')));

			if (!$title) {
				$this->tpl->assign('error', 'Пустое название альбома');
			} else {
				$groupsAlbums->set($ret, array('title' => $title, 'edittime' => time()), $aid);
				$this->redirect(sprintf('/community/group/%u/album/%u', $gid, $aid));
			}
		}

		$this->tpl->tpl('', '/community/group/album/', 'edit.php');
	}

	public function showAlbumPhotos($gid, $aid) {
		$isAMember = $this->isAMember();
		$groupInfo = $this->tpl->get_data('group');
		if (!$isAMember && $groupInfo['type'] == 'private' && !$this->tpl->isCommunityModer()) {
			$this->handler_show_error('community_no_access_to_album');
			return false;
		}

		$groupAlbums = new VPA_table_community_groups_albums;
		$album = $groupAlbums->get_first_fetch(array('id' => $aid));
		if (!$album) {
			$this->redirect();
		}
		$this->tpl->assign('album', $album);
		$this->checkGrants();

		// photos count
		$albumsPhotos = new VPA_table_community_albums_photos;
		$photosNum = $albumsPhotos->get_num_fetch(array('aid' => $album['id']));
		$this->tpl->assign('photosNum', $photosNum);

		// comments
		$albumsComments = new VPA_table_community_albums_comments_with_info;
		$comments = $albumsComments->get_fetch(array('aid' => $aid), array('a.createtime'));
		$this->tpl->assign('comments', $comments);

		$this->tpl->tpl('', '/community/group/album/', 'photos.php');
	}

	public function addAlbumComment($gid, $aid) {
		$comment = trim(htmlspecialchars($this->get_param('content'), ENT_NOQUOTES));

		if (empty($comment)) {
			$this->handler_show_error('empty_msg');
			return false;
		}
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		$albumsComments = new VPA_table_community_albums_comments;
		$params = array(
			'createtime' => time(),
			'aid' => (int)$aid,
			'uid' => $this->user['id'],
			'comment' => $comment,
		);
		// check for spam
		if ($this->user_lib->check_for_spam($params['comment'], 'communityAlbumsComments', $params['aid'])) {
			$this->handler_show_error('user_spamer');
			return false;
		}

		if (!$albumsComments->add($id, $params)) {
			$this->handler_show_error('db_error');
			return false;
		}
		$id->get_first($id);
		$this->addFeed(array_merge(array('id' => $id), $params), 'albumsComments');

		$this->redirect(sprintf('/community/group/%u/album/%u', $gid, $aid));
	}

	public function getAlbumPhotos($aid) {
		$albumsPhotos = new VPA_table_community_albums_photos;
		$photos = $albumsPhotos->get_fetch(array('aid' => $aid), array('createtime desc'));
		$imgs = array();
		foreach ($photos as $i => $photo) {
			$data = getimagesize($this->getPhysicalUploadAlbumsPhotosPath($aid, $photo['image']));
			$width = (int)$data[0];
			$height = (int)$data[1];
				
			if($height > 0) {
			    if($width >= $height) {
			        $w = 300;
			    } else {
			        $w = round($width / ($height / 150));
			    }
			} else {
			    $w = 0;
			}

			$imgs[$i]['src'] = $this->tpl->getStaticPath(self::getWWWAlbumPhotoPath($aid, $photo['image'], '300x150'));
			$imgs[$i]['width'] = $w;
			$imgs[$i]['lsrc'] = $this->tpl->getStaticPath(self::getWWWAlbumPhotoPath($aid, $photo['image'], '540x490'));
			$imgs[$i]['id'] = (int)$photo['id'];
			$imgs[$i]['text'] = null;
		}
		$this->tpl->assign('data', $imgs);
		$this->tpl->tpl('', '/', 'ajax.php');
		return true;
	}

	public function addAlbumPhotos($gid, $aid) {
		if (!$this->isAMember()) {
			$this->redirect();
			return false;
		}
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}
		$this->checkGrants();

		$groupAlbums = new VPA_table_community_groups_albums;
		$album = $groupAlbums->get_first_fetch(array('id' => $aid));
		$this->tpl->assign('album', $album);

		// save
		if ($this->d->isPost()) {
			$imgs = $this->get_param('photo');
			$albumsPhotos = new VPA_table_community_albums_photos;

			if (count($imgs['name']) > 3) {
				$this->handler_show_error('too_many_files');
				return false;
			}

			for ($i = 0; $i < count($imgs['name']); $i++) {
				// transform
				$img = array('name' => $imgs['name'][$i], 'tmp_name' => $imgs['tmp_name'][$i], 'type' => $imgs['type'][$i], 'error' => $imgs['error'][$i], 'size' => $imgs['size'][$i]);

				$fileInfo = pathinfo($img['name']);

				if (!in_array(strtolower($fileInfo['extension']), array('jpg', 'jpeg', 'gif', 'png'))) {
					continue;
				}

				if ($img['error'] === 0) {
					$path = $this->getPhysicalUploadAlbumsPhotosPath($aid);
					$image = random_file_name($path, $fileInfo['extension']);
					copy($img['tmp_name'], $path . $image);
					split_image($path . $image);

					$params = array('createtime' => time(), 'aid' => $aid, 'image' => $image, 'uid' => $this->user['id']);
					$albumsPhotos->add($id, $params);
					$id->get_first($id);
					$this->addFeed(array_merge(array('id' => $id), $params), 'photos');
				}
			}

			$this->redirect(sprintf('/community/group/%u/album/%u', $gid, $aid));
			return true;
		}

		$this->tpl->tpl('', '/community/group/album/', 'addPhotos.php');
	}

	public function deleteAlbumPhotos($gid, $aid) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}
		$groupAlbums = new VPA_table_community_groups_albums;
		$album = $groupAlbums->get_first_fetch(array('id' => $aid));
		if (!$album) {
			throw new Exception('redirector');
		}
		$this->tpl->assign('album', $album);

		$albumsPhotos = new VPA_table_community_albums_photos;
		// delete
		if ($this->d->isPost()) {
			$ids = array_unique(array_map('intval', array_keys($_POST['p'])));
			if (!$albumsPhotos->del_where($ret, array('id_in' => join(',', $ids)))) {
				$this->handler_show_error('db_error');
			}
			// delete news feeds
			$this->deleteFeed(array('id' => array('$in' => $ids)), 'photos');

			$this->redirect(sprintf('/community/group/%u/album/%u', $gid, $aid));
			return true;
		}

		$photos = $albumsPhotos->get_fetch(array('aid' => $aid), array('createtime desc'));
		// no photos - redirect to add photos page
		if (!$photos) {
			$this->redirect(sprintf('/community/group/%u/album/%u/addPhotos', $gid, $aid));
			return false;
		}
		$this->tpl->assign('photos', $photos);

		$this->tpl->tpl('', '/community/group/album/', 'deletePhotos.php');
	}

	public function showGroupAlbums($gid, $page = 1, $perPage = 50) {
		if ($page < 1) $page = 1;

		$groupAlbums = new VPA_table_community_groups_albums;
		$albumsPhotos = new VPA_table_community_albums_photos;

		$albumsNum = $groupAlbums->get_num_fetch(array('gid' => $gid));
		$this->tpl->assign('albumsNum', $albumsNum);

		if ($albumsNum > 0) {
			$albums = $groupAlbums->get_fetch(array('gid' => $gid), array('createtime desc'), ($page-1)*$perPage, $perPage);
			// get last photo
			foreach ($albums as &$album) {
				$album['lastPhoto'] = $albumsPhotos->get_first_fetch(array('aid' => $album['id']), array('createtime desc'));
				$album['photos'] = $albumsPhotos->get_num_fetch(array('aid' => $album['id']));
			}
		} else {
			$albums = null;
		}

		$this->tpl->assign('albums', $albums);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($albumsNum / $perPage));
		$this->checkGrants();

		$this->tpl->tpl('', '/community/group/', 'albums.php');
	}

	public function addGroupMember($gid, $uid = -1) {
		if ($uid == -1) {
			$uid = $this->user['id'];
		}
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		// check user
		$users = new VPA_table_users;
		$user = $users->get_first_fetch(array('id' => $uid));
		if (!$user) {
			return false;
		}
		$hasGrants = $this->checkGrants();
		$group = $this->tpl->get_data('group');

		// no grants for add user, to members, except it self
		if ($uid != $this->user['id'] && !$hasGrants) {
			return false;
		}

		// check for already is a member
		$members = new VPA_table_community_groups_members;
		if ($this->isAMember(null, $uid)) {
			$this->tpl->assign('data', array('status' => true));
			return true;
		}

		// not confirmed member
		$notConfirmedMember = $members->get_first_fetch(array('muid' => $uid, 'gid' => $gid, 'confirm' => 'n'));
		if ($notConfirmedMember) {
			// group is closed
			if ($uid == $this->user['id'] && $group['type'] == 'private' && !$hasGrants) {
				$this->tpl->assign('data', array('status' => -1));
				return false;
			}
			// can confirm only itself or this a user request
			if (($uid == $this->user['id'] && $group['type'] == 'public') || ($notConfirmedMember['request'] == 'y')) {
				$members->set_where($ret, array('confirm' => 'y'), array('muid' => $uid, 'gid' => $gid));
				$this->tpl->assign('data', array('status' => true));
				return true;
			}
			return false;
		} else {
			// if user not add it self
			if ($uid != $this->user['id']) {
				// deny in profile
				if (!$user['can_invite_to_community_groups']) {
					return false;
				}
				// not a friend
				$userFriends = new VPA_table_user_friends_optimized;
				$hasFriend = $userFriends->get_num_fetch(array('confirmed' => 1, 'uid' => $this->user['id'], 'fid' => $uid));
				if ($hasFriend == 0) {
					return false;
				}
			}

			// group is closed - send request
			if ($uid == $this->user['id'] && $group['type'] == 'private' && !$hasGrants) {
				$members->add($ret, array('uid' => $this->user['id'], 'muid' => $uid, 'createtime' => time(), 'gid' => $gid, 'request' => 'y'));
				$this->tpl->assign('data', array('status' => -1));
				return true;
			}
			// group open
			if ($uid != $this->user['id']) {
				$members->add($ret, array('uid' => $this->user['id'], 'muid' => $uid, 'createtime' => time(), 'gid' => $gid));
			} else {
				$members->add($ret, array('uid' => $this->user['id'], 'muid' => $uid, 'createtime' => time(), 'gid' => $gid, 'confirm' => 'y'));
			}
		}

		// send message, only if it is not it self
		if ($uid != $this->user['id']) {
			$msg = sprintf(
				'<p>Пользователь <a href="/profile/%u">%s</a> пригласил Вас в группу <a href="/community/group/%u">%s</a></p><p>Чтобы вступить перейдите по <a href="/community/group/%u/private/confirm">ссылке</a></p><p>Чтобы отклонить перейдите по <a href="/community/group/%u/private/leave">ссылке</a></p>',
				$this->user['id'], $this->user['nick'], $group['id'], $group['title'], $group['id'], $group['id']
			);
			$this->user_lib->add_private_message($uid, $msg, 0, 57);
		}

		$this->tpl->assign('data', array('status' => true));
		return true;
	}

	public function addGroupAssistant($gid, $uid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		// check user
		$users = new VPA_table_users;
		$user = $users->get_first_fetch(array('id' => $uid));
		if (!$user) {
			return false;
		}
		$hasGrants = $this->checkGrants();
		$group = $this->tpl->get_data('group');

		// no grants for add assistant
		if (!$hasGrants) {
			return false;
		}
		// already assistant
		if ($uid == $this->user['id']) {
			return true;
		}

		$assistants = new VPA_table_community_groups_assistants;
		// max number of assistants = 3, so.. check
		$num = $assistants->get_num_fetch(array('gid' => $gid));
		if ($num >= 3) {
			$this->tpl->assign('data', array('status' => -1));
			return false;
		}
		// not confirmed member
		$assistants->add($ret, array('uid' => $this->user['id'], 'auid' => $uid, 'createtime' => time(), 'gid' => $gid));

		$this->tpl->assign('data', array('status' => true));
		return true;
	}

	public function deleteGroupAssistant($gid, $uid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		// no grants for delete user, from assistants, except it self
		if ($uid != $this->user['id'] && !$this->checkGrants()) {
			return false;
		}

		$assistants = new VPA_table_community_groups_assistants;
		if (!$assistants->del_where($ret, array('auid' => $uid, 'gid' => $gid))) {
			return false;
		}
		$this->tpl->assign('data', array('status' => true));
		return true;
	}

	public function confirmGroupMember($gid) {
		$members = new VPA_table_community_groups_members;
		$members->set_where($ret, array('confirm' => 'y'), array('muid' => $this->user['id'], 'gid' => $gid));
		$this->redirect('/community/group/' . $gid);
	}

	public function deleteGroupMember($gid, $uid = -1) {
		if ($uid == -1) {
			$uid = $this->user['id'];
		}
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		// no grants for delete user, from members, except it self
		if ($uid != $this->user['id'] && !$this->checkGrants()) {
			return false;
		}

		// drop from members
		$members = new VPA_table_community_groups_members;
		if (!$members->del_where($ret, array('muid' => $uid, 'gid' => $gid))) {
			return false;
		}
		// drop from assistants
		$assistants = new VPA_table_community_groups_assistants;
		if (!$assistants->del_where($ret, array('auid' => $uid, 'gid' => $gid))) {
			return false;
		}
		$this->tpl->assign('data', array('status' => true));
		return true;
	}

	public function leaveGroupMember($gid) {
		$members = new VPA_table_community_groups_members;
		$members->del_where($ret, array('muid' => $this->user['id'], 'gid' => $gid));
		$this->redirect('/community/group/' . $gid);
	}

	public function addGroupInvites($gid, $page = 1, $perPage = 50) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		if ($page < 1) $page = 1;
		// invites
		$groupsInvites = new VPA_table_community_groups_invites;
		$friends = $groupsInvites->get_fetch(array('uid' => $this->user['id'], 'gid' => $gid), array('nick'), $perPage*($page-1), $perPage);
		$this->tpl->assign('friends', $friends);
		// num
		$friendsNum = $groupsInvites->get_num_fetch(array('uid' => $this->user['id'], 'gid' => $gid));
		$this->tpl->assign('friends', $friends);
		$this->tpl->assign('friendsNum', $friendsNum);
		// new members
		$groupsMembers = new VPA_table_community_groups_members;
		$newMembersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'n'));
		$this->tpl->assign('newMembersNum', $newMembersNum);

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($friendsNum / $perPage));

		$this->tpl->tpl('', '/community/group/', 'addInvites.php');
	}

	public function showGroupMembers($gid, $page = 1, $perPage = 50) {
		if ($page < 1) $page = 1;
		// members
		$this->checkGrants();
		$groupsMembers = new VPA_table_community_groups_members_with_info;
		$members = $groupsMembers->get_fetch(array('gid' => $gid, 'confirm' => 'y'), array('a.createtime'), ($page-1)*$perPage, $perPage);
		$membersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'y'));
		$this->tpl->assign('members', $members);
		$this->tpl->assign('membersNum', $membersNum);

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($membersNum / $perPage));

		$this->tpl->tpl('', '/community/group/', 'members.php');
	}

	public function editGroupMembers($gid, $page = 1, $perPage = 50) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		if ($page < 1) $page = 1;
		// members
		$groupsMembers = new VPA_table_community_groups_members_assistant_with_info;
		$members = $groupsMembers->get_fetch(array('gid' => $gid, 'muid_not' => $this->user['id']), array('a.confirm desc', 'a.createtime'), ($page-1)*$perPage, $perPage); // @TODO - optimize
		$groupInfo = $this->tpl->get_data('group');
		$membersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'muid_not' => $this->user['id']));
		$this->tpl->assign('members', $members);
		$this->tpl->assign('membersNum', $membersNum);

		// new members
		$groupsMembers = new VPA_table_community_groups_members;
		$newMembersNum = $groupsMembers->get_num_fetch(array('gid' => $gid, 'confirm' => 'n'));
		$this->tpl->assign('newMembersNum', $newMembersNum);

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($membersNum / $perPage));

		$this->tpl->tpl('', '/community/group/', 'editMembers.php');
	}

	public function showGroupTopics($gid, $page = 1, $sort = 'cdate', $order = 'desc', $perPage = 50) {
		if ($page < 1) $page = 1;
		$this->tpl->assign('sort', $sort . '_' . $order);

		static $sortAvaliable = array(
			'cdate' => 'a.createtime',
			'rating' => 'a.rating',
			'comment' => 'b.comment',
			'ldate' => 'b.last_message_date',
		);
		$sort = (!isset($sortAvaliable[$sort]) ? $sortAvaliable[0] : $sortAvaliable[$sort]) . ' '
			. (!in_array($order, array('asc', 'desc')) ? 'desc' : $order);

		$groupsTopics = new VPA_table_community_groups_topics_sort;
		$topicsNum = $groupsTopics->get_num_fetch(array('gid' => $gid));
		$this->tpl->assign('topicsNum', $topicsNum);
		$topics = $groupsTopics->get_fetch(array('gid' => (int)$gid), array($sort), ($page-1)*$perPage, $perPage);
		$this->tpl->assign('topics', $topics);

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($topicsNum / $perPage));
		$this->checkGrants();
		$this->isAMember();

		$this->tpl->tpl('', '/community/group/', 'topics.php');
	}

	public function addGroupTopic($gid) {
		if (!$this->isAMember()) {
			$this->redirect();
			return false;
		}
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}		
		// save
		if ($this->d->isPost()) {
			$title = trim(strip_tags($this->get_param('title')));
			$description = trim(strip_tags($this->get_param('description')));

			if (!$title || !$description) {
				$this->tpl->assign('error', 'Не все поля заполнены. Пожалуйста заполните все поля.');
			} else {
				$params = array('uid' => $this->user['id'], 'createtime' => time(), 'gid' => $gid, 'title' => $title, 'description' => $description);
				$groupsTopics = new VPA_table_community_groups_topics;
				$groupsTopics->add($id, $params);
				$id->get_first($id);
				if (!$id) {
					$this->handler_show_error('db_error');
					return false;
				}
				// newsfeed
				$this->addFeed(array_merge(array('id' => $id), $params), 'topics');
				$this->redirect(sprintf('/community/group/%u/topic/%u', $gid, $id));
				return true;
			}
		}

		$this->tpl->tpl('', '/community/group/topic/', 'add.php');
	}

	public function addGroupTopicPoll($gid) {
		if (!$this->isAMember()) {
			$this->redirect();
			return false;
		}

		// save
		if ($this->d->isPost()) {
			$title = trim(strip_tags($this->get_param('title')));
			$description = trim(strip_tags($this->get_param('description')));
			$options = array_not_empty(array_unique(array_map('strip_tags', $this->get_param('options'))));

			if (!$title || !$description || !$options) {
				$this->tpl->assign('error', 'Не все поля заполнены. Пожалуйста заполните все поля.');
			} elseif ($options && count($options) > self::topicsPollsMaxOptions) {
				$this->tpl->assign('error', 'Вариантов опроса не может быть больше ' . self::topicsPollsMaxOptions);
			} else {
				$params = array('uid' => $this->user['id'], 'createtime' => time(), 'gid' => $gid, 'title' => $title, 'description' => $description, 'poll' => true);
				$groupsTopics = new VPA_table_community_groups_topics;
				$groupsTopics->add($id, $params);
				$id->get_first($id);
				if (!$id) {
					$this->handler_show_error('db_error');
					return false;
				}
				// poll
				$topicsPollsOptions = new VPA_table_community_topics_polls_options;
				foreach ($options as $option) {
					$topicsPollsOptions->add($ret, array('uid' => $this->user['id'], 'createtime' => time(), 'title' => $option, 'tid' => $id));
				}
				// newsfeed
				$this->addFeed(array_merge(array('id' => $id), $params), 'topics');
				$this->redirect(sprintf('/community/group/%u/topic/%u', $gid, $id));
				return true;
			}
		}

		$this->tpl->tpl('', '/community/group/topic/', 'addPoll.php');
	}

	public function showGroupTopic($gid, $tid, $page = 1, $perPage = 50) {
		if ($page < 1) $page = 1;

		$groupsTopics = new VPA_table_community_groups_topics_with_info;
		$topic = $groupsTopics->get_first_fetch(array('id' => $tid));
		if (!$topic) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('topic', $topic);

		$topicsMessages = new VPA_table_community_topics_messages_with_info;
		$messagesNum = $topicsMessages->get_num_fetch(array('tid' => $tid));
		$this->tpl->assign('messagesNum', $messagesNum);
		if ($messagesNum > 0) {
			$messages = $topicsMessages->get_fetch(array('tid' => $tid), array('a.createtime'), ($page-1)*$perPage, $perPage);
			$this->tpl->assign('messages', $messages);
		}
		$isAMember = $this->isAMember();
		// poll
		if ($topic['poll']) {
			$this->tpl->assign('pollOptions', $this->countPollWithPercents($tid));
			// check is already vote
			if ($isAMember) {
				$topicsPollsStatistics = new VPA_table_community_topics_polls_statistics;
				$userVote = $topicsPollsStatistics->get_first_fetch(array('tid' => $tid, 'uid' => $this->user['id']));
				$this->tpl->assign('userVote', !empty($userVote));
			}
		}

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($messagesNum / $perPage));
		$this->tpl->assign('perPage', $perPage);
		$this->checkGrants();

		$this->tpl->tpl('', '/community/group/topic/', 'details.php');
	}

	public function deleteGroupTopic($gid, $tid) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		$groupsTopics = new VPA_table_community_groups_topics;
		$topicsMessages = new VPA_table_community_topics_messages;
		$topicsVotes = new VPA_table_community_topics_votes;
		$messagesVotes = new VPA_table_community_messages_votes;

		if ($groupsTopics->del($ret, $tid)) {
			$messagesVotes->del_where($ret, array('tid' => $tid));
			$topicsMessages->del_where($ret, array('tid' => $tid));
			$topicsVotes->del_where($ret, array('tid' => $tid));
			$this->deleteFeed(array('id' => $tid), 'topics');
			$this->deleteFeed(array('tid' => $tid), 'messages');

			$this->redirect(sprintf('/community/group/%u/topics', $gid));
			return true;
		}
		return false;
	}

	public function editGroupTopic($gid, $tid) {
		if (!$this->checkGrants()) {
			$this->redirect();
			return false;
		}

		$groupsTopics = new VPA_table_community_groups_topics;
		// fetch info
		$topic = $groupsTopics->get_first_fetch(array('id' => $tid));
		if (!$topic) {
			$this->redirect();
			return false;
		}
		// is a poll - no editing
		if ($topic['poll']) {
			$this->redirect();
			return false;
		}
		$this->tpl->assign('topic', $topic);

		// save
		if ($this->d->isPost()) {
			$title = trim(strip_tags($this->get_param('title')));
			$description = trim(strip_tags($this->get_param('description')));

			if (!$title || !$description) {
				$this->tpl->assign('error', 'Не все поля заполнены. Пожалуйста заполните все поля.');
			} else {
				$groupsTopics->set($ret, array('edittime' => time(), 'title' => $title, 'description' => $description), $tid);
				$this->redirect(sprintf('/community/group/%u/topic/%u', $gid, $tid));
				return true;
			}
		}

		$this->tpl->tpl('', '/community/group/topic/', 'edit.php');
	}


	public function addTopicMessage($gid, $tid) {
		if (!$this->isAMember()) {
			$this->handler_show_error('community_user_is_not_a_member');
			return false;
		}
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		// save
		$page = (int)$this->get_param('page');
		$message = trim(strip_tags($this->get_param('content'), '<object>,<embed>,<param>'));
		if (preg_match('@<\s*object|<\s*embed|<\s*param@Uis', $message)) {
			$message = preg_replace('/(?:\s|"|\')(on([\S]+?))(\s|\/>|>)/is', ' $3', $message);
		}
		$re = (int)$this->get_param('re');
		$tid = (int)$tid;

		if ($tid && $page && $message) {
			$params = array('uid' => $this->user['id'], 'createtime' => time(), 'tid' => $tid, 'message' => $message, 're' => $re);
			// check for spam
			if ($this->user_lib->check_for_spam($params['message'], 'communityMessages', $params['tid'])) {
				$this->handler_show_error('user_spamer');
				return false;
			}

			$topicsMessage = new VPA_table_community_topics_messages;
			if (!$topicsMessage->add($id, $params)) {
				$this->handler_show_error('db_error');
				return false;
			}
			$id->get_first($id);
			$this->addFeed(array_merge(array('id' => $id), $params), 'messages');
		}

		$this->redirect(sprintf('/community/group/%u/topic/%u/page/%u', $gid, $tid, $page));
	}

	public function editTopicMessage($gid, $tid, $mid) {
		$this->tpl->tpl('', '/', 'ajax.php');

		$message = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(strip_tags($this->get_param('content'), '<object>,<embed>,<param>')));
		if (preg_match('@<\s*object|<\s*embed|<\s*param@Uis', $message)) {
			$message = preg_replace('/(?:\s|"|\')(on([\S]+?))(\s|\/>|>)/is', ' $3', $message);
		}

		if (!$message) {
			return $this->handler_show_error('empty_msg');
		}

		$topicsMessages = new VPA_table_community_topics_messages;
		if (!$topicsMessages->set_where($ret, array('edittime' => time(), 'message' => $message), array('id' => $mid, 'uid' => $this->user['id']))) {
			return $this->handler_show_error('db_error');
		}

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($message)));
		return true;
	}

	public function updateTopicRating($gid, $tid, $rating) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		if (!$this->isAMember() || !in_array($rating, array(-1, 1))) return false;

		global $ip;

		$this->tpl->assign('data', array('status' => true));

		$topicsVotes = new VPA_table_community_topics_votes;
		$params = array('uid' => $this->user['id'], 'tid' => $tid);
		$alreadyVoted = $topicsVotes->get_first_fetch($params);
		if (!$alreadyVoted) {
			$params['createtime'] = time();
			$params['ip'] = $ip;
			$params['rating'] = ($rating > 0 ? 'up' : 'down');

			$topicsVotes->add($ret, $params);

			$groupsTopics = new VPA_table_community_groups_topics;
			$groupsTopics->set($ret, array('rating' => 'rating' . ($rating > 0 ? '+1' : '-1')), $tid);
		}
	}

	public function updateMessageRating($gid, $tid, $mid, $rating) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		if (!$this->isAMember() || !in_array($rating, array(-1, 1))) return false;

		global $ip;

		$messagesVotes = new VPA_table_community_messages_votes;
		$params = array('uid' => $this->user['id'], 'mid' => $mid);
		$alreadyVoted = $messagesVotes->get_first_fetch($params);
		if (!$alreadyVoted) {
			$params['createtime'] = time();
			$params['ip'] = $ip;
			$params['rating'] = ($rating > 0 ? 'up' : 'down');

			$messagesVotes->add($ret, $params);

			$groupsTopicsMessages = new VPA_table_community_topics_messages;
			if ($rating > 0) {
				$groupsTopicsMessages->set($ret, array('rating_up' => 'rating_up+1'), $mid);
			} else {
				$groupsTopicsMessages->set($ret, array('rating_up' => 'rating_down+1'), $mid);
			}

			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function updateAlbumCommentRating($gid, $aid, $cid, $rating) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		if (!$this->isAMember() || !in_array($rating, array(-1, 1))) return false;

		global $ip;

		$this->tpl->assign('data', array('status' => true));

		$albumsCommentsVotes = new VPA_table_community_albums_comments_votes;
		$params = array('uid' => $this->user['id'], 'cid' => $cid);
		$alreadyVoted = $albumsCommentsVotes->get_first_fetch($params);
		if (!$alreadyVoted) {
			$params['createtime'] = time();
			$params['ip'] = $ip;
			$params['rating'] = ($rating > 0 ? 'up' : 'down');

			$albumsCommentsVotes->add($ret, $params);

			$groupsTopics = new VPA_table_community_albums_comments;
			if ($rating > 0) {
				$groupsTopics->set($ret, array('rating_up' => 'rating_up+1'), $cid);
			} else {
				$groupsTopics->set($ret, array('rating_down' => 'rating_down+1'), $cid);
			}

			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function deleteTopicMessage($gid, $tid, $mid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => true));

		$topicsMessages = new VPA_table_community_topics_messages;
		$message = $topicsMessages->get_first_fetch(array('id' => $mid));
		// message not exists | no grants
		if (!$message || ($message['uid'] != $this->user['id'] && !$this->checkGrants())) {
			$this->tpl->assign('data', array('status' => false));
			return false;
		}

		// update delete time
		$topicsMessages->set($ret, array('deletetime' => time()), $mid);
		// delete news feed
		$this->deleteFeed(array('id' => $mid), 'messages');
	}

	public function showGroupsInAdmin($q, $page, $perPage = 50) {
		if ($page < 1) $page = 1;
		$params = array();
		if ($q) $params['q'] = strip_tags($q);

		$groupsOb = new VPA_table_community_groups;
		$groups = $groupsOb->get_fetch($params, array('createtime DESC'), ($page-1)*$perPage, $perPage);
		$this->tpl->assign('groups', $groups);
		$groupsNum = $groupsOb->get_num_fetch($params);
		$this->tpl->assign('groupsNum', $groupsNum);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($groupsNum / $perPage));
		$this->tpl->assign('q', $q);

		$this->tpl->tpl('', '/manager/', 'communityGroups.php');
	}

	public function deleteAlbumComment($gid, $aid, $cid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => true));

		$albumsComments = new VPA_table_community_albums_comments;
		$comment = $albumsComments->get_first_fetch(array('id' => $cid));
		// message not exists | no grants
		if (!$comment || ($comment['uid'] != $this->user['id'] && !$this->checkGrants())) {
			$this->tpl->assign('data', array('status' => false));
			return false;
		}

		// delete comment
		if (!$albumsComments->del($ret, $cid)) {
			$this->tpl->assign('data', array('status' => false));
			return false;
		}
		$albumsCommentsVotes = new VPA_table_community_albums_comments_votes;
		$albumsCommentsVotes->del_where($ret, array('cid' => $cid));
		// delete news feed
		$this->deleteFeed(array('id' => $cid), 'albumsComments');
	}

	/**
	 * Get group info
	 * For all /community/group/:gid*
	 *
	 * @param int $gid - group id
	 */
	public function getGroupInfo($gid) {
		if ($gid == 'add') return;
		if (!$gid) {
			throw new Exception('No gid - redirect');
		}

		$groups = new VPA_table_community_groups;
		$group = $groups->get_first_fetch(array('id' => $gid));
		if (!$group) {
			$this->redirect();
			throw new Exception('No such group - redirect');
		}
		$this->tpl->assign('group', $group);
	}

	/**
	 * Get users groups (where hi is a member)
	 *
	 * @param int $uid - user id
	 */
	public function showUsersGroups($uid, $page = 1, $perPage = 20) {
		if ($page < 1) $page = 1;

		$groups = new VPA_table_community_groups;
		$groupsMembers = new VPA_table_community_groups_members;
		// get num
		$groupsNum = $groupsMembers->get_num_fetch(array('muid' => $uid, 'confirm' => 'y'));
		$this->tpl->assign('num', $groupsNum);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($groupsNum / $perPage));

		if ($groupsNum > 0) {
			// get gids
			$groupsIds = $groupsMembers->get_params_fetch(array('muid' => $uid, 'confirm' => 'y'), array('createtime desc'), ($page-1)*$perPage, $perPage, null, array('gid'));
			// fetch info
			$groupsIdsJoined = join(',', clever_array_values($groupsIds));
			if ($groupsIdsJoined) {
				$groupsList = $groups->get_fetch(array('id_in' => $groupsIdsJoined), array('FIELD(id,' . $groupsIdsJoined . ')'));
			}
		} else {
			$groupsList = null;
		}

		$this->tpl->assign('groups', $groupsList);
		if ($this->user['id'] == $uid) {
			$this->tpl->tpl('', '/profile/', 'community_groups.php');
		} else {
			$this->tpl->tpl('', '/user/', 'community_groups.php');
		}
	}

	/**
	 * Delete group
	 *
	 * @param int $gid
	 * @param bool $fromManager - from manager (force grants and no redirect)
	 */
	public function deleteGroup($gid, $fromManager = false) {
		if (!$this->tpl->isCommunityModer() && !$fromManager) {
			throw new Exception('Access deny');
		}

		// main
		$groups = new VPA_table_community_groups;
		$groups->del($ret, $gid);
		// sub
		$groupsAssistants = new VPA_table_community_groups_assistants;
		$groupsAssistants->del_where($ret, array('gid' => $gid));
		$groupsMembers = new VPA_table_community_groups_members;
		$groupsMembers->del_where($ret, array('gid' => $gid));
		$groupsTags = new VPA_table_community_groups_tags;
		$groupsTags->del_where($ret, array('gid' => $gid));

		// *** NOT ONE LEVEL *** - parent delete - last
		{ // #1
			$albumsCommentsVotes = new VPA_table_community_albums_comments_votes;
			$albumsCommentsVotes->del_where($ret, array('gid' => $gid));
			$albumsComments = new VPA_table_community_albums_comments;
			$albumsComments->del_where($ret, array('gid' => $gid));
			$albumsPhotos = new VPA_table_community_albums_photos;
			$albumsPhotos->del_where($ret, array('gid' => $gid));
			$groupsAlbums = new VPA_table_community_groups_albums;
			$groupsAlbums->del_where($ret, array('gid' => $gid));
		}

		{ // #2
			$topicsMessagesVotes = new VPA_table_community_messages_votes;
			$topicsMessagesVotes->del_where($ret, array('gid' => $gid));
			$topicsMessages = new VPA_table_community_topics_messages;
			$topicsMessages->del_where($ret, array('gid' => $gid));
			$topicsVotes = new VPA_table_community_topics_votes;
			$topicsVotes->del_where($ret, array('gid' => $gid));
			$groupsTopics = new VPA_table_community_groups_topics;
			$groupsTopics->del_where($ret, array('gid' => $gid));
		}
		// \*** NOT ONE LEVEL ***
		if (!$fromManager) {
			$this->redirect('/community/groups');
		}
	}

	/**
	 * Rules
	 */
	public function showGroupsRules() {
		$this->tpl->tpl('', '/community/', 'rules.php');
	}

	public function topicsSubmitPoll($gid, $tid, $answer) {
		global $ip;

		$this->tpl->tpl('', '/', 'ajax.php');

		if (!$this->isAMember()) {
			$this->tpl->assign('data', array('error' => 'Необходимо вступить в группу'));
			return false;
		}
		$pollStatistics = new VPA_table_community_topics_polls_statistics;
		$userVote = $pollStatistics->get_first_fetch(array('uid' => $this->user['id'], 'tid' => $tid));
		if ($userVote) {
			$this->tpl->assign('data', array('error' => 'Вы уже голосовали'));
			return false;
		}
		$pollStatistics->add($ret, array('uid' => $this->user['id'], 'tid' => $tid, 'ip' => $ip, 'createtime' => time(), 'poid' => $answer));

		$pollOptions = new VPA_table_community_topics_polls_options;
		$pollOptions->set_where($options, array('rating' => 'rating+1'), array('id' => $answer, 'tid' => $tid));
		$this->tpl->assign('data', array('fields' => $this->countPollWithPercents($tid)));
		return true;
	}

	/**
	 * Count poll percents
	 *
	 * @param int $tid - topic id
	 * @return array
	 */
	private function countPollWithPercents($tid) {
		$pollOptions = new VPA_table_community_topics_polls_options;
		$options = $pollOptions->get_params_fetch(array('tid' => $tid), null, 0, self::topicsPollsMaxOptions, null, array('title', 'id', 'rating'));

		$min = 0;
		$max = max(clever_array_values($options, 'rating'));

		foreach ($options as &$option) {
			$option['percent'] = $max ? ( (100 / $max) * $option['rating'] ) : 0;
		}

		return $options;
	}

	/**
	 * Get physical upload path for avatars
	 *
	 * @return string
	 */
	private function getPhysicalUploadAvatarPath() {
		return realpath($_SERVER['DOCUMENT_ROOT'] . self::uploadPathAvatars) . '/';
	}

	/**
	 * Get physical upload path for albums photos
	 * Also create dirs
	 *
	 * @return string
	 */
	private function getPhysicalUploadAlbumsPhotosPath($aid, $image = null, $size = null) {
		$revAid = strrev($aid);
		$path = realpath($_SERVER['DOCUMENT_ROOT'] . ($size ? '/k/community/' . $size . '/' : null) . self::uploadPathAlbumsPhotos) . '/';
		for ($i = 0; $i < 3; $i++) {
			$path .= (isset($revAid[$i]) ? $revAid[$i] : 0) . '/';
			// @todo m.b. drop create of dirs?
			if (!is_dir($path)) {
				mkdir($path, 0777);
			}
		}
		return realpath($path) . '/' . $image;
	}

	/**
	 * Check is current user is a member of current group
	 * And also set tpl var
	 *
	 * @param int $gid - custom gid
	 * @param int $uid - user id
	 * @return bool
	 */
	private function isAMember($gid = null, $uid = null) {
		if (!$gid) {
			$group = $this->tpl->get_data('group');
			if ($group) {
				$gid = $group['id'];
			}
		} elseif (!is_null($gid)) {
				$groups = new VPA_table_community_groups;
				$group = $groups->get_first_fetch(array('id' => $gid));
				if (!$group) {
					return false;
				}
		}
		if (!$uid) $uid = $this->user['id'];

		$groupsMembers = new VPA_table_community_groups_members;
		$isAMember = $groupsMembers->get_first_fetch(array('muid' => $uid, 'gid' => $gid, 'confirm' => 'y'));
		$isAMember = !empty($isAMember);
		$this->tpl->assign('isAMember', $isAMember);
		return $isAMember;
	}

	/**
	 * Check current user grants for group with id = $gid
	 * And also set tpl var
	 *
	 * @param int $gid - custom gid
	 * @return bool
	 */
	private function checkGrants($gid = null) {
		$this->tpl->assign('canModifyGroup', false);

		if (!$gid) {
			$group = $this->tpl->get_data('group');
		} elseif (!is_null($gid)) {
			$groups = new VPA_table_community_groups;
			$group = $groups->get_first_fetch(array('id' => $gid));
			if (!$group) {
				return false;
			}
		}

		// owner
		if ($group['uid'] == $this->user['id']) {
			$this->tpl->assign('canModifyGroup', true);
			return true;
		}
		// assistants
		else {
			$assistants = new VPA_table_community_groups_assistants;
			$assistant = $assistants->get_first_fetch(array('auid' => $this->user['id'], 'gid' => $group['id']));
			if ($assistant) {
				$this->tpl->assign('canModifyGroup', true);
				return true;
			}
		}

		return false;
	}

	/**
	 * Add feed
	 *
	 * @param array $data
	 * @return bool
	 */
	private function addFeed($data, $type) {
		$groupInfo = $this->tpl->get_data('group');

		$data['gid'] = (int)$groupInfo['id'];
		$data['_type'] = $type;
		$data['_createGroupingTime'] = new MongoDate(mktime(date('H', $data['createtime']), 0, 0, date('m', $data['createtime']), date('d', $data['createtime']), date('Y', $data['createtime'])));
		$data['createtime'] = new MongoDate($data['createtime']);
		// change charset
		$data = $this->tpl->plugins['iconv']->iconv($data);

		// condition for grouping
		switch ($type) {
			case 'photos':
				$modifiersData['$push'] = array('images' => array('id' => $data['id'], 'image' => $data['image']));
				$modifiersOptions = array('aid' => $data['aid']);
				unset($data['image'], $data['id']);
				break;
		}
		$options = array(
			'_type' => $data['_type'],
			'_createGroupingTime' => $data['_createGroupingTime'],
			'gid' => (int)$groupInfo['id'],
			/*'uid' => $data['uid'],*/ // maybe ?
		);
		$options = array_merge($options, $modifiersOptions);
		// update / insert
		if (isset($modifiersData)) {
			$item = $this->newsfeed->findOne($options);
			if (!$item) {
				$this->newsfeed->insert($data);
				$item = &$data;
			}
			return $this->newsfeed->update(array('_id' => $item['_id']), $modifiersData);
		} else {
			return $this->newsfeed->insert($data);
		}
	}

	/**
	 * Delete feed
	 *
	 * @param array $data
	 * @return bool
	 */
	private function deleteFeed($data, $type) {
		$groupInfo = $this->tpl->get_data('group');

		$data['gid'] = (int)$groupInfo['id'];
		$data['_type'] = $type;
		// change charset
		$data = $this->tpl->plugins['iconv']->iconv($data);

		switch ($type) {
			case 'photos':
				$modifiersData['$pull'] = array('images' => array('id' => $data['id']));
				unset($data['id']);
				break;
		}
		// update / delete
		if (isset($modifiersData)) {
			$this->newsfeed->update($data, $modifiersData);
			// delete empty containers
			$data['images'] = array();
			return $this->newsfeed->remove($data);
		} else {
			return $this->newsfeed->remove($data);
		}
	}

	/**
	 * Transform groups tags to normal way
	 *
	 * @param array $groups - groups
	 * @return array
	 */
	private function transformGroupsTags(&$groups) {
		if (!is_array($groups) || count($groups) < 1) return $groups;

		$groupsTags = new VPA_table_community_groups_tags_with_info;
		foreach ($groups as &$group) {
			$group['tags'] = $groupsTags->get_fetch(array('gid' => $group['id']));
		}
		return $groups;
	}

	/**
	 * Get tags with ratings
	 *
	 * @param int $limit - limit
	 * @return array
	 */
	private function getTagsWithRating($limit = null) {
		$tags = new VPA_table_community_groups_tags_with_rating;
		if ($limit) {
			$topTags = $tags->get_fetch(null, array('cnt DESC'), 0, $limit, array('a.id'));
		} else {
			$topTags = $tags->get_fetch(null, array('cnt DESC'), null, null, array('a.id'));
		}
		if (!$topTags) {
			return false;
		}

		return $this->user_lib->transform_tags($topTags, 12);
	}

	/**
	 * Iconv + decode (for search, ajax suggest e.t.c.)
	 *
	 * @param string $mixed
	 * @return string
	 */
	private function iconvDecode($mixed) {
		return $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(urldecode($mixed));
	}

	/**
	 * Get WWW path for avatars
	 *
	 * @return string
	 */
	static public function getWWWAvatarPath($image, $size = '130') {
		return sprintf('/k/community/%s%s%s', $size, self::uploadPathAvatars, $image);
	}

	/**
	 * Get WWW album photo path
	 *
	 * @return string
	 */
	static public function getWWWAlbumPhotoPath($aid, $image, $size = '130') {
		if (!$image) {
			return '/i/null.gif';
		}

		$revAid = strrev($aid);
		return sprintf('/k/community/%s%s%u/%u/%u/%s', $size, self::uploadPathAlbumsPhotos, $revAid[0], isset($revAid[1]) ? $revAid[1] : 0, isset($revAid[2]) ? $revAid[2] : null, $image);
	}

	/**
	 * @alias for user_lib::get_param
	 */
	protected function get_param() {
		return call_user_func_array(array(&$this->user_lib, 'get_param'), func_get_args());
	}

	/**
	 * @alias for user_lib::redirect
	 */
	protected function redirect() {
		return call_user_func_array(array(&$this->user_lib, 'redirect'), func_get_args());
	}

	/**
	 * @alias for user_lib::handler_show_error
	 */
	protected function handler_show_error() {
		return call_user_func_array(array(&$this->user_lib, 'handler_show_error'), func_get_args());
	}
}
