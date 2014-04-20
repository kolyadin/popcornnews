<?php

require_once 'YourStyle_Factory.php';

/**
 * Class YourStyle_FrontEnd
 * Date begin: 28.02.2011
 *
 * FrontEnd
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class YourStyle_FrontEnd extends YourStyle_Factory {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct(user_base_api &$user_lib, $noInitRoutes = false) {   
		parent::__construct($user_lib, $noInitRoutes);
	}

	public function handlerShowMain() {
	    
	    $ysActiveUsers = new VPA_table_yourstyle_users_rating();
		// stars
		$ysSetsTags = new VPA_table_yourstyle_sets_tags;

		$newStars = $ysSetsTags->getNewStars(0, 20);
		// split by 2 cols
		$newStarsBy2Coll = array();
		$offset = ceil(count($newStars) / 2);
		$i = $current = 0;
		foreach ($newStars as $star) {
			$newStarsBy2Coll[$i][] = $star;
			$current++;

			if ($current > ($i+1)*$offset) {
				$i++;
			}
		}
		$this->tpl->assign('newStarsBy2Coll', $newStarsBy2Coll);
		// to tiles
		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$topTiles = $ysGroupsTiles->getTop(null, 0, 34);
		$this->tpl->assign('topTiles', $topTiles);
		// get top sets
		$ysSets = new VPA_table_yourstyle_sets;
		$topSets = $ysSets->getSets('rate DESC', 0, 20);
		foreach ($topSets as $k => $v) {
		    $r = $ysActiveUsers->getUserWithRating($v['uid']);
		    $topSets[$k]['urating'] = $r['rating'];
		}
		$this->tpl->assign('topSets', $topSets);		
		// get new sets
		$newSets = $ysSets->get_fetch(array('isDraft' => 'n'), array('createtime desc'), 0, 20);
		$this->tpl->assign('newSets', $newSets);
		
		$activeUsers = $ysActiveUsers->getActiveUsers();
		
		$this->tpl->assign('activeUsers', $activeUsers);
		
		$this->tpl->tpl('', '/yourstyle/', 'main.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowSetsTop() {
	    //if (!$this->user) return $this->handler_show_error('no_login');
		$ysSets = new VPA_table_yourstyle_sets;
		$ysRating = new VPA_table_yourstyle_users_rating();
		$topSets = $ysSets->getSets('rate DESC', 0, 24);
		
		foreach ($topSets as $i => $set) {
		    $topSets[$i]['urating'] = $ysRating->getUserWithRating($set['uid']);
		    $topSets[$i]['urating'] = $topSets[$i]['urating']['rating'];		    
		}
		
		$this->tpl->assign('topSets', $topSets);

		$this->tpl->tpl('', '/yourstyle/', 'setsTop.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowSetsNew() {
	    //if (!$this->user) return $this->handler_show_error('no_login');
		$ysSets = new VPA_table_yourstyle_sets;
		$ysRating = new VPA_table_yourstyle_users_rating();
		$newSets = $ysSets->getSets('a.id DESC', 0, 24);

		foreach ($newSets as $i => $set) {
		    $newSets[$i]['urating'] = $ysRating->getUserWithRating($set['uid']);
		    $newSets[$i]['urating'] = $newSets[$i]['urating']['rating'];		    
		}		
		
		$this->tpl->assign('newSets', $newSets);

		$this->tpl->tpl('', '/yourstyle/', 'setsNew.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowRootGroups() {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroups = $ysRootGroups->getWithGroupsAndTiles();
		
		$ysTiles = new VPA_table_yourstyle_groups_tiles();
		
		foreach ($rootGroups as $id => $rgid) {
		    if($rgid['tid'] != 0) {
		        $tile = $ysTiles->get_first_fetch(array('id' => $rgid['tid']));
		        $rootGroups[$id]['tile'] = $tile;
		    }
		    if($rgid['id'] == 6) {
		    	unset($rootGroups[$id]);
		    	continue;
		    }
		}
		
		$this->tpl->assign('rootGroups', $rootGroups);

		$this->tpl->tpl('', '/yourstyle/', 'rootGroups.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
		$this->tpl->assign('allGroups', $this->getAllGroups());
	    $this->tpl->assign('brands', $this->getBrands());
	    $this->tpl->assign('colors', $this->getColors());
	}

	public function handlerShowGroups($rgid) {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$ysGroups = new VPA_table_yourstyle_groups;
		$groups = $ysGroups->getWithTiles($rgid);
		
		$ysTiles = new VPA_table_yourstyle_groups_tiles();
		foreach ($groups as $id => $g) {
		    if($g['tid'] != 0) {
		        $tile = $ysTiles->get_first_fetch(array('id' => $g['tid']));
		        $groups[$id]['tile'] = $tile;
		    }
		}
		
		$ysRootGroup = new VPA_table_yourstyle_root_groups();		
		
		$rootGroup = $ysRootGroup->get_first_fetch(array('id' => $rgid));
		
		$this->tpl->assign('rootGroup', $rootGroup);
		$this->tpl->assign('groups', $groups);

		$this->tpl->tpl('', '/yourstyle/', 'groups.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
		$filters = array('rgid' => $rgid);
		$this->tpl->assign('allGroups', $this->getAllGroups());
	    $this->tpl->assign('brands', $this->getBrands($filters));
		$this->tpl->assign('colors', $this->GetFilteredColors($filters));
	}

	public function handlerShowGroupsTiles($gid, $page = 1, $perPage = 42) {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$this->tpl->tpl('', '/yourstyle/group/', 'tiles.php');

		if ($page < 1) $page = 1;

		$ysGroups = new VPA_table_yourstyle_groups;
		$group = $ysGroups->get_first_fetch(array('id' => (int)$gid));
		if (!$group) {
			throw new Exception('redirector');
		}
		$this->tpl->assign('group', $group);
		// color
		
		$color = !empty($_GET['color']) ? $_GET['color'] : null;
		if (!empty($color) && !($color = $this->isSuchColorExist($color))) {
			$this->tpl->assign('error', 'Фильтр по этому цвету недоступен');
			return false;
		}
		// if we have colro filter
		if (!empty($color)) {
			$this->tpl->assign('color', $color);

			$ysTilesColors = new VPA_table_yourstyle_tiles_colors;
			$tiles = $ysTilesColors->getWithTilesAndFavorite($color['en'], $this->user['id'], $gid, ($page-1)*$perPage, $perPage);
			$this->tpl->assign('tiles', $tiles);

			$tilesNum = $ysTilesColors->getNumTiles($color['en'], $gid);
			$this->tpl->assign('tilesNum', $tilesNum);
		}
		// otherwise - not filter by color
		else {
			$tiles = $ysGroups->getGroupTiles($gid, $this->user['id'], array('a.createtime desc'), ($page-1)*$perPage, $perPage);
			$this->tpl->assign('tiles', $tiles);

			$ysGroupsTiles = new VpA_table_yourstyle_groups_tiles;
			$tilesNum = $ysGroupsTiles->get_num_fetch(array('gid' => $gid));
			$this->tpl->assign('tilesNum', $tilesNum);
		}
		
		$filters = array('rgid' => $group['rgid'], 'gid' => $gid);
		$this->tpl->assign('allGroups', $this->getAllGroups());
	    $this->tpl->assign('brands', $this->getBrands($filters));
		$this->tpl->assign('colors', $this->GetFilteredColors($filters));

		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($tilesNum / $perPage));
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowGroupTile($tid) {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tile = $ysGroupsTiles->getWithBrands(array('id' => $tid));
		if (!$tile) {
			throw new Exception('redirector');
		}
		// hidden
		if ($tile['gid'] == 0) {
			throw new Exception('redirector');
		}
		// user uploaded
		if ($tile['uid']) {
			$users = new VPA_table_users;
			$user = $users->get_first_fetch(array('id' => $tile['uid']));
		} else {
			$user = null;
		}
		// get top
		$ysSetsTiles = new VPA_table_yourstyle_sets_tiles;
		$topSets = $ysSetsTiles->getTopSets($tid, 0, 8);
		$setsCount = $ysSetsTiles->getTileSets($tid);
		// get number of users added
		$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
		$usersAdded = $ysTilesUsers->get_num_fetch(array('tid' => $tid, 'uid_n' => 0));
		$isIAdd = false;
		// if have number of added - see is current user add it self
		if ($usersAdded) {
			$isIAdd = $ysTilesUsers->get_first_fetch(array('tid' => $tid, 'uid' => $this->user['id']));
			$isadd = $isIAdd;
			$isIAdd = !empty($isIAdd);
		}
		
		$ysTilesVotes = new VPA_table_yourstyle_tiles_votes();
		
		$isILike = $ysTilesVotes->get_first_fetch(array('uid' => $this->user['id'], 'tid' => $tid));
		$isILike = !empty($isILike);
				
		$voteCount = count($ysTilesVotes->get_fetch(array('tid' => $tid)));
		
        $ysGroups = new VPA_table_yourstyle_groups();
		$group = $ysGroups->get_fetch(array('id' => $tile['gid']));
		
		$users = $ysTilesUsers->get_fetch(array('tid' => $tid));
		
		$users_ids = array();
		foreach ($users as $u) {
		    if($tile['uid'] != $u['uid']) {
		        $users_ids[] = intval($u['uid']);
		    }
		}

		$users_ids = implode(',', $users_ids);
		
		$ysUsers = new VPA_table_users();
		$users = $ysUsers->get_fetch(array('id_in' => $users_ids));
		
		$otherTiles = $ysGroupsTiles->get_fetch(array('bid' => $tile['bid'], 'id_n' => $tid, 'gid_n' => 0));
		
		$ysBrands = new VPA_table_yourstyle_tiles_brands_new();
		$brand = $ysBrands->get_first_fetch(array('id' => $tile['bid']));
		
		//old
		/*$ysColors = new VPA_table_yourstyle_tiles_colors();
		$colors = $ysColors->get_fetch(array('tid' => $tid), array('pixels DESC'), 0, 3);*/
		$ysColors = new VPA_table_yourstyle_tiles_colors_new();
		$colors = $ysColors->get_fetch(array('tid' => $tid), array('priority'));
		
		$ysUserRating = new VPA_table_yourstyle_users_rating();
		$userRating = $ysUserRating->getUserWithRating($tile['uid']);
		
		if(is_null($userRating)) {
		    $userRating = 0;
		}
		else 
		{
		    $userRating = $userRating['rating'];
		}
		
		foreach ($users as $k => $v) {
		    $r = $ysUserRating->getUserWithRating($v['id']);
		    $users[$k]['rating'] = is_null($r['rating']) ? 0 : $r['rating'];
		}
		
		$this->tpl->assign('tile', $tile);
		$this->tpl->assign('otherTiles', $otherTiles);
		$this->tpl->assign('user', $user);
		$this->tpl->assign('userRating', $userRating);
		$this->tpl->assign('users', $users);
		$this->tpl->assign('topSets', $topSets);
		$this->tpl->assign('setsCount', $setsCount);
		$this->tpl->assign('usersAdded', $usersAdded);
		$this->tpl->assign('isIAdd', $isIAdd);
		$this->tpl->assign('isILike', $isILike);
		$this->tpl->assign('votesCount', $voteCount);
		$this->tpl->assign('group', $group[0]);
		$this->tpl->assign('brand', $brand);
		$this->tpl->assign('colors', $colors);
		$this->tpl->assign('isadd', $isadd);
				
		$this->tpl->tpl('', '/yourstyle/tile/', 'details.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
		
		
		//moder features
		if($this->tpl->isModer()) {
		    $brands = $ysBrands->get_fetch(null, array('title ASC'));
		    $grs = $ysGroups->get_fetch();
		    $ysRoot = new VPA_table_yourstyle_root_groups();
		    $roots = $ysRoot->get_fetch();
		    $groups = array();
		    foreach ($roots as $root) {
		        $groups[$root['id']] = $root; 
		    }
		    foreach ($grs as $gr) {
		        $groups[$gr['rgid']]['groups'][] = $gr;
		    }
		    $this->tpl->assign('brands', $brands);
		    $this->tpl->assign('groups', $groups);
		}		
	}
	
	public function handlerShowTilesTop() {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		//$topTiles = $ysGroupsTiles->getTop(null, 0, 50);
		
		$topTiles = $ysGroupsTiles->getFilteredTop(array());

		$this->tpl->assign('topTiles', $topTiles);
		$this->tpl->tpl('', '/yourstyle/', 'tilesTop.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
		
		$this->tpl->assign('allGroups', $this->getAllGroups());
		$brands = $this->getBrands(array());
	    $colors = $this->GetFilteredColors(array());
		$this->tpl->assign('colors', $colors);
		
		foreach ($brands as $i => $brand) {
		    $exists = false;
		    foreach ($topTiles as $tile) {
		        if($brand['id'] == $tile['bid']) {
		            $exists = true;
		        }
		    }
		    if(!$exists) {
		        unset($brands[$i]);
		    }
		}
		
	    $this->tpl->assign('brands', $brands);
	}

	public function handlerShowStarsNew($page = 1) {
	    if (!$this->user) return $this->handler_show_error('no_login');	    
	    
	    $search = mysql_real_escape_string($this->get_param('search'));
	    if(empty($search)) $search = null;
	    
	    $perPage = 8;
	    $page = intval($page);
	    if($page < 1) $page = 1;
	    
		$ysSetsTags = new VPA_table_yourstyle_sets_tags();
		$ysSets = new VPA_table_yourstyle_sets();
		$ysRating = new VPA_table_yourstyle_users_rating();
		
		// stars
		$stars = $ysSetsTags->getNewStars(($page - 1) * $perPage, $perPage, $search);
		$count = $ysSetsTags->GetCount($search);
		$pages = ceil($count / $perPage);

		require_once dirname(__FILE__).'/../../classes/BaseHandler.php';
		require_once dirname(__FILE__).'/../../classes/PersonsHandler.php';
		$person = new PersonsHandler($this->user_lib);		
		
		foreach ($stars as $key => $star) {
		    $stars[$key]['setsCount'] = $ysSets->GetStarSetsCount($star['id']);
		    $stars[$key]['link'] = $person->Name2URL($star['eng_name']);
		    $sets = $ysSets->GetSetsByStar($star['id'], 0, 2);
		    
		    foreach ($sets as $id => $set) {
		        $sets[$id]['urating'] = $ysRating->getUserWithRating($set['uid']);
		        $sets[$id]['urating'] = $sets[$id]['urating']['rating'];
		        $sets[$id]['image'] = YourStyle_Factory::getWwwUploadSetPath($set['id'], $set['image'], '274x274');
		    }
		    
		    $stars[$key]['sets'] = $sets;
		}		
		
		$this->tpl->assign('stars', $stars);
		
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', $pages);
		$this->tpl->assign('search', $search);
		
		$this->tpl->tpl('', '/yourstyle/', 'starsNew.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowStarsByName() {
	    if (!$this->user) return $this->handler_show_error('no_login');
		$ysSetsTags = new VPA_table_yourstyle_sets_tags;
		$stars = $ysSetsTags->getStars();
		// split by letters
		$starsByNames = array();
		foreach ($stars as &$star) {
			$letter = strtoupper(substr($star['name'], 0, 1));

			$starsByNames[$letter][] = &$star;
		}
		// split by 3 cols
		$current = $i = 0;
		$offset = ceil(count($stars) / 3);
		$starsByNames3Cols = array();
		foreach ($starsByNames as $letter => &$stars) {
			$current += count($stars);
			$starsByNames3Cols[$i][$letter] = &$stars;

			if ($current > (($i + 1) * $offset)) {
				$i++;
			}
		}

		$this->tpl->assign('starsByNames3Cols', $starsByNames3Cols);
		$this->tpl->tpl('', '/yourstyle/', 'starsByNames.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	// @TODO unique index?
	public function handlerAddTileToMy($tid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('error' => 'Неизвестная ошибка'));

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		// fetch tile info (maybe it private)
		$tile = $ysGroupsTiles->get_first_fetch(array('tid' => $tid));
		// not exists
		if (!$tile) {
			return false;
		}
		// private, and tile not of current user
		if ($tile['gid'] == 0 && $tile['uid'] != $this->user['ud']) {
			return false;
		}
		// is already have?
		$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
		$tile = $ysTilesUsers->get_first_fetch(array('tid' => $tid, 'uid' => $this->user['id']));
		$alreadyHave = !empty($tile);
		if (!$alreadyHave) {
			$ysTilesUsers->add($ret, array('tid' => $tid, 'uid' => $this->user['id'], 'createtime' => time()));
		}
		$this->tpl->assign('data', 1);
	}

	public function handlerDeleteTileToMy($tid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('error' => 'Неизвестная ошибка'));

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		// fetch tile info (maybe it private)
		$tile = $ysGroupsTiles->get_first_fetch(array('tid' => $tid));
		if (!$tile) {
			return false;
		}
		// delete it from users favorites
		$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
		$ysTilesUsers->del_where($ret, array('tid' => $tid, 'uid' => $this->user['id']));
		// this is a private tile -> delete it from common table
		if ($tile['gid'] == 0 && $tile['uid'] == $this->user['id']) {
			$ysGroupsTiles->del($ret, $tid);
		}
		$this->tpl->assign('data', 1);
	}

	public function handlerShowSet($sid, $page = 1, $perPage = 50) {
	    //if (!$this->user) return $this->handler_show_error('no_login');
		if ($page < 1) $page = 1;

		$ysSets = new VPA_table_yourstyle_sets;
		$set = $ysSets->get_first_fetch(array('id' => $sid, 'isDraft' => 'n'));
		if (!$set) {
			throw new Exception('redirector');
		}
		// user uploaded
		//$users = new VPA_table_users;
		//$user = $users->get_first_fetch(array('id' => $set['uid']));
		$userRating = new VPA_table_yourstyle_users_rating();
		$user = $userRating->getUserWithRating($set['uid']); 
		/*$rating = $userRating->getUserWithRating($user['id']);		
		$user['rating'] = $rating['rating'];*/
		// get tiles
		$ysSetsTiles = new VPA_table_yourstyle_sets_tiles;
		$tiles = $ysSetsTiles->getSetTiles($sid);
		// Is I likes?
		$ysSetsVotes = new VPA_table_yourstyle_sets_votes;
		$isILike = $ysSetsVotes->get_first_fetch(array('uid' => $this->user['id'], 'sid' => $sid));
		$isILike = !empty($isILike) || ($this->user['id'] == $set['uid']);
		
		$votesCount = $ysSetsVotes->getCount($sid);
		
		// comments
		$ysSetsComments = new VPA_table_yourstyle_sets_comments;
		$commentsNum = $ysSetsComments->get_num_fetch(array('sid' => $sid));
		if ($commentsNum) {
			$comments = $ysSetsComments->getWithUsers($sid, 'a.id', ($page-1)*$perPage, $perPage);
		}
		
		$otherUserSets = $ysSets->get_fetch(array('uid' => $set['uid'], 'isDraft' => 'n', 'id_n' => $sid), null, 0, 5);
		
		$this->tpl->assign('set', $set);
		$this->tpl->assign('comments', $comments);
		$this->tpl->assign('commentsNum', $commentsNum);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('pages', ceil($commentsNum / $perPage));
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('tiles', $tiles);
		$this->tpl->assign('user', $user);
		$this->tpl->assign('otherUserSets', $otherUserSets);
		$this->tpl->assign('isILike', $isILike);
		$this->tpl->assign('votesCount', $votesCount);
		
		/*check & get star*/
		$ysStars = new VPA_table_yourstyle_star();
		$stars = $ysStars->get_fetch(array('sid' => $sid));
		if(!empty($stars)) {
		    foreach ($stars as $key => $item) {		        
		        $name = str_replace('-', '_', $item['eng_name']);
		        $name = str_replace('&dash;', '_', $name);
                $link = "/persons/".str_replace(' ', '-', $name);
		        $stars[$key]['link'] = $link;
		    }
		    $this->tpl->assign('stars', $stars);
		}

		$this->tpl->tpl('', '/yourstyle/set/', 'details.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}
	
	// @TODO unique index?
	public function handlerSetLike($sid, $rate) {
		global $ip;
		if($rate > 5) {
		    $rate = 1;
		}
		$this->tpl->tpl('', '/', 'ajax.php');

		// Is I likes?
		$ysSets = new VPA_table_yourstyle_sets;
		$set = $ysSets->get_fetch(array('sid' => $sid));		
		$ysSetsVotes = new VPA_table_yourstyle_sets_votes;
		$isILike = $ysSetsVotes->get_first_fetch(array('uid' => $this->user['id'], 'sid' => $sid));
		$isILike = !empty($isILike) || ($set['uid'] == $this->user['id']);
		// If not voted
		if (!$isILike) {
			// stat
			$ysSetsVotes->add($ret, array('uid' => $this->user['id'], 'sid' => $sid, 'createtime' => time(), 'ip' => $ip));

			// update rating
			$ysSets->set($set, array('rating' => 'rating+'.$rate), $sid);
		}
		
		$rating = $ysSets->get_first_fetch(array('id' => $sid));
		$votesCount = count($ysSetsVotes->get_fetch(array('sid' => $sid)));
		
		if($votesCount > 0) {
		    $rating = $rating['rating'] / $votesCount;
		} 
		else {
		    $rating = 0;
		}
		$rating = round($rating, 1);
		//$rating = str_replace('.', ',', $rating);
		
		$data = array('rating' => $rating);
		
		$this->tpl->assign('data', $data);
	}

	
	public function handlerTileLike($tid, $rate) {
		global $ip;
		$this->tpl->tpl('', '/', 'ajax.php');

		// Is I likes?
		$ysTile = new VPA_table_yourstyle_groups_tiles();
		$ysSetsVotes = new VPA_table_yourstyle_tiles_votes();
		$isILike = $ysSetsVotes->get_first_fetch(array('uid' => $this->user['id'], 'tid' => $tid));
		$isILike = !empty($isILike);
		// If not voted
		if (!$isILike) {
			// stat
			$ysSetsVotes->add($ret, array('uid' => $this->user['id'], 'tid' => $tid, 'createtime' => time(), 'ip' => ip2long($ip)));

			// update rating
			$ysTile->set($set, array('rate' => 'rate+'.$rate), $tid);
		}
		
		$rate = $ysTile->get_first_fetch(array('id' => $tid));
		$votesCount = count($ysSetsVotes->get_fetch(array('tid' => $tid)));
		
		if($votesCount > 0) {
		    $rating = $rate['rate'] / $votesCount;
		} 
		else {
		    $rating = 0;
		}
		$rating = round($rating, 1);
		
		$data = array('rating' => $rating);
		
		$this->tpl->assign('data', $data);
	}	
	
	public function handlerEditSetComment($sid, $cid) {
		$this->tpl->tpl('', '/', 'ajax.php');

		$comment = $this->tpl->plugins['iconv']->iconv_exchange_once()->iconv(trim(strip_tags($this->get_param('content'))));

		if (!$comment) {
			return $this->handler_show_error('empty_msg');
		}

		$ysSetsComments = new VPA_table_yourstyle_sets_comments;
		if (!$ysSetsComments->set_where($ret, array('edittime' => time(), 'comment' => $comment), array('id' => $cid, 'uid' => $this->user['id']))) {
			return $this->handler_show_error('db_error');
		}

		$this->tpl->assign('data', array('status' => 1, 'text' => $this->tpl->plugins['nc']->get($comment)));
		return true;
	}

	public function handlerUpdateSetCommentRating($sid, $cid, $rating) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('status' => false));

		if (!in_array($rating, array(-1, 1))) return false;

		global $ip;

		$commentsVotes = new VPA_table_yourstyle_sets_comments_votes;
		$params = array('uid' => $this->user['id'], 'cid' => $cid);
		$alreadyVoted = $commentsVotes->get_first_fetch($params);
		if (!$alreadyVoted) {
			$params['createtime'] = time();
			$params['ip'] = $ip;
			$params['rating'] = ($rating > 0 ? 'up' : 'down');

			$commentsVotes->add($ret, $params);

			$setComments = new VPA_table_yourstyle_sets_comments;
			if ($rating > 0) {
				$setComments->set($ret, array('rating_up' => 'rating_up+1'), $cid);
			} else {
				$setComments->set($ret, array('rating_up' => 'rating_down+1'), $cid);
			}

			$this->tpl->assign('data', array('status' => true));
			return true;
		}
		return false;
	}

	public function handlerDeleteSetComment($sid, $cid) {
		$this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', 1);

		// comment
		$ysSetsComments = new VPA_table_yourstyle_sets_comments;
		$comment = $ysSetsComments->get_first_fetch(array('id' => $cid));
		// set
		$ysSets = new VPA_table_yourstyle_sets_comments;
		$set = $ysSets->get_first_fetch(array('id' => $sid));
		// comment not exists | no grants
		if (!$comment || (($comment['uid'] != $this->user['id']) && ($set['uid'] != $this->user['id']))) {
			$this->tpl->assign('data', 0);
			return false;
		}

		// update delete time
		$ysSetsComments->set($ret, array('deletetime' => time()), $cid);
	}

	public function handlerPostSetComment($sid) {
		if ($this->user_lib->handler_test_ban($this->user['id'])) {
			$this->handler_show_error('user_banned');
			return false;
		}

		// save
		$page = (int)$this->get_param('page');
		$comment = trim(strip_tags($this->get_param('content')));
		$re = (int)$this->get_param('re');
		$sid = (int)$sid;

		if ($sid && $page && $comment) {
			$params = array('uid' => $this->user['id'], 'createtime' => time(), 'sid' => $sid, 'comment' => $comment, 're' => $re);
			// check for spam
			if ($this->user_lib->check_for_spam($params['comment'], 'yourstyleComment', $params['sid'])) {
				$this->handler_show_error('user_spamer');
				return false;
			}

			$ysSetsComments = new VPA_table_yourstyle_sets_comments;
			if (!$ysSetsComments->add($id, $params)) {
				$this->handler_show_error('db_error');
				return false;
			}
			$id->get_first($id);
		}

		$this->redirect(sprintf('/yourstyle/set/%u/page/%u', $sid, $page));
	}
	
	public function handlerShowMySets($uid) {
	    //if (!$this->user) return $this->handler_show_error('no_login');
	    $ysSets = new VPA_table_yourstyle_sets;
	    $ysRating = new VPA_table_yourstyle_users_rating();
	    
		$userSets = $ysSets->getUserSets($uid, 'createtime DESC');
		
		foreach ($userSets as $i => $set) {
		    $userSets[$i]['urating'] = $ysRating->getUserWithRating($set['uid']);
		    $userSets[$i]['urating'] = $userSets[$i]['urating']['rating'];		    
		}
		
		$this->tpl->assign('userSets', $userSets);		
	    
	    $this->tpl->tpl('','/profile/', 'yourstyle.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}
	
	public function handlerShowUserSets($uid) {
	    //if (!$this->user) return $this->handler_show_error('no_login');
	    $ysSets = new VPA_table_yourstyle_sets;
	    $ysRating = new VPA_table_yourstyle_users_rating();
	    
		$userSets = $ysSets->getUserSets($uid, 'createtime DESC');
		
		foreach ($userSets as $i => $set) {
		    $userSets[$i]['urating'] = $ysRating->getUserWithRating($set['uid']);
		    $userSets[$i]['urating'] = $userSets[$i]['urating']['rating'];		    
		}
		
		$this->tpl->assign('userSets', $userSets);		
	    
	    $this->tpl->tpl('','/user/', 'yourstyle.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}
	
	public function handlerShowFilteredTiles($page = 1, $perPage = 42) {
	    if (!$this->user) return $this->handler_show_error('no_login');
	    $this->tpl->tpl('', '/yourstyle/', 'filteredTiles.php');
	    
	    if ($page < 1) $page = 1;

	    $filters['bid'] = intval($this->get_param('brand'));
	    $filters['rgid'] = intval($this->get_param('rootGroup'));
	    $filters['gid'] = intval($this->get_param('group'));	    
	    
	    foreach ($filters as $k => $v) {
	        if($v == 0) unset($filters[$k]);
	    }
	    
	    $filters['color'] = $this->get_param('color');
	    	    
	    if(empty($filters['color'])) {
	        unset($filters['color']);
	    }
		$color = !empty($filters['color']) ? $filters['color'] : null;
		if (!is_null($color) && !($color = $this->isSuchColorExist($color))) {
			$this->tpl->assign('error', 'Фильтр по этому цвету недоступен');
			return false;
		}
		if(!is_null($color)) $filters['color'] = $color['rgb'];
	    
		$ysTiles = new VPA_table_yourstyle_groups_tiles();
		
	    $tiles = $ysTiles->getFiltered($filters, ($page - 1) * $perPage, $perPage);
	    $tilesNum = $ysTiles->getCount($filters);	    

	    $this->tpl->assign('allGroups', $this->getAllGroups());
	    $this->tpl->assign('brands', $this->getBrands($filters));
	    $this->tpl->assign('colors', $this->getFilteredColors($filters));
	    
	    if(!is_null($color)) $filters['color'] = $color['en'];
	    
		$this->tpl->assign('color', $color);
	    $this->tpl->assign('filters', $filters);
	    $this->tpl->assign('tiles', $tiles);
	    $this->tpl->assign('tilesNum', $tilesNum);
	    $this->tpl->assign('page', $page);
	    $this->tpl->assign('pages', $tilesNum / $perPage);	    
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);		
	}
	
	public function handlerShowFilteredTilesTop() {
	    if (!$this->user) return $this->handler_show_error('no_login');
	    $this->tpl->tpl('', '/yourstyle/', 'filteredTilesTop.php');
	    
	    $filters['bid'] = intval($this->get_param('brand'));
	    $filters['rgid'] = intval($this->get_param('rootGroup'));
	    $filters['gid'] = intval($this->get_param('group'));	    
	    
	    foreach ($filters as $k => $v) {
	        if($v == 0) unset($filters[$k]);
	    }
	    
	    $filters['color'] = $this->get_param('color');
	    	    
	    if(empty($filters['color'])) {
	        unset($filters['color']);
	    }
		$color = !empty($filters['color']) ? $filters['color'] : null;
		if (!is_null($color) && !($color = $this->isSuchColorExist($color))) {
			$this->tpl->assign('error', 'Фильтр по этому цвету недоступен');
			return false;
		}
		
		if(!is_null($color)) $filters['color'] = $color['rgb'];
		
		$ysTiles = new VPA_table_yourstyle_groups_tiles();
		
	    $tiles = $ysTiles->getFilteredTop($filters);
	    $tilesNum = $ysTiles->getCount($filters);

	    $this->tpl->assign('allGroups', $this->getAllGroups());

	    $brands = $this->getBrands($filters);
	    
	    foreach ($brands as $i => $brand) {
		    $exists = false;
		    foreach ($tiles as $tile) {
		        if($brand['id'] == $tile['bid']) {
		            $exists = true;
		        }
		    }
		    if(!$exists) {
		        unset($brands[$i]);
		    }
		}
	    
	    
	    $this->tpl->assign('brands', $brands);
	    $this->tpl->assign('colors', $this->getFilteredColors($filters));
	    
	    if(!is_null($color)) $filters['color'] = $color['en'];
	    
		$this->tpl->assign('color', $color);
	    $this->tpl->assign('filters', $filters);
	    $this->tpl->assign('tiles', $tiles);
	    $this->tpl->assign('tilesNum', $tilesNum);
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);		
	}
	
	public function handlerShowSetsByTile($tid, $page = 1, $perPage = 50) {
	    if (!$this->user) return $this->handler_show_error('no_login');
	    $page = ($page < 1) ? 1 : $page;	    
	    
	    $ysTiles = new VPA_table_yourstyle_groups_tiles();
	    $ysGroups = new VPA_table_yourstyle_groups();
	    $ysSets = new VPA_table_yourstyle_sets_tiles();	    
        $ysUserRating = new VPA_table_yourstyle_users_rating();
		$ysUsers = new VPA_table_users;
		$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
        
	    $tile = $ysTiles->getWithBrands(array('id' => $tid));	    

	    // hidden
		if ($tile['gid'] == 0) {
			throw new Exception('redirector');
		}
		// user uploaded
		if ($tile['uid']) {
			$user = $ysUsers->get_first_fetch(array('id' => $tile['uid']));
		} else {
			$user = null;
		}	    
		
		$usersAdded = $ysTilesUsers->get_num_fetch(array('tid' => $tid, 'uid_n' => 0));
		$isIAdd = false;
		// if have number of added - see is current user add it self
		if ($usersAdded) {
			$isIAdd = $ysTilesUsers->get_first_fetch(array('tid' => $tid, 'uid' => $this->user['id']));
			$isadd = $isIAdd;
			$isIAdd = !empty($isIAdd);
		}
		
		$userRating = $ysUserRating->getUserWithRating($tile['uid']);
		
		if(is_null($userRating)) {
		    $userRating = 0;
		}
		else 
		{
		    $userRating = $userRating['rating'];
		}	
		
	    $group = $ysGroups->get_first_fetch(array('id' => $tile['gid']));
	    $sets = $ysSets->getTopSets($tid, ($page - 1), $perPage);
	    $setsCount = $ysSets->getTileSets($tid);
		
	    $this->tpl->assign('user', $user);
	    $this->tpl->assign('userRating', $userRating);
	    $this->tpl->assign('sets', $sets);
	    $this->tpl->assign('group', $group);
	    $this->tpl->assign('tile', $tile);
	    $this->tpl->assign('isIAdd', $isIAdd);
	    $this->tpl->assign('page', $page);
	    $this->tpl->assign('pages', $setsCount / $perPage);
	    $this->tpl->tpl('', '/yourstyle/tile/', 'setsByTile.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}

	public function handlerShowUsersByTile($tid, $page = 1, $perPage = 50) {
	    if (!$this->user) return $this->handler_show_error('no_login');
	    $page = ($page < 1) ? 1 : $page;	    
	    
	    $ysTiles = new VPA_table_yourstyle_groups_tiles();
	    $ysGroups = new VPA_table_yourstyle_groups();
        $ysUserRating = new VPA_table_yourstyle_users_rating();
		$ysUsers = new VPA_table_users;
	    $ysTilesUsers = new VPA_table_yourstyle_tiles_users;
		
	    $tile = $ysTiles->getWithBrands(array('id' => $tid));	    

	    // hidden
		if ($tile['gid'] == 0) {
			throw new Exception('redirector');
		}
		// user uploaded
		if ($tile['uid']) {
			$user = $ysUsers->get_first_fetch(array('id' => $tile['uid']));
		} else {
			$user = null;
		}	    
		
		$usersAdded = $ysTilesUsers->get_num_fetch(array('tid' => $tid, 'uid_n' => 0));
		$isIAdd = false;
		// if have number of added - see is current user add it self
		if ($usersAdded) {
			$isIAdd = $ysTilesUsers->get_first_fetch(array('tid' => $tid, 'uid' => $this->user['id']));
			$isadd = $isIAdd;
			$isIAdd = !empty($isIAdd);
		}
		
		$userRating = $ysUserRating->getUserWithRating($tile['uid']);
		
		if(is_null($userRating)) {
		    $userRating = 0;
		}
		else 
		{
		    $userRating = $userRating['rating'];
		}	
		
	    $group = $ysGroups->get_first_fetch(array('id' => $tile['gid']));
		
	    $users = $ysTilesUsers->get_fetch(array('tid' => $tid));
		
		$users_ids = array();
		foreach ($users as $u) {
		    if($tile['uid'] != $u['uid']) {
		        $users_ids[] = intval($u['uid']);
		    }
		}

		$users_ids = implode(',', $users_ids);
		
		$ysUsers = new VPA_table_users();
		$users = $ysUsers->get_fetch(array('id_in' => $users_ids));
	    
		foreach ($users as $k => $v) {
		    $r = $ysUserRating->getUserWithRating($v['id']);
		    $users[$k]['rating'] = is_null($r['rating']) ? 0 : $r['rating'];
		}
		
	    $this->tpl->assign('user', $user);
	    $this->tpl->assign('userRating', $userRating);
	    $this->tpl->assign('users', $users);
	    $this->tpl->assign('group', $group);
	    $this->tpl->assign('tile', $tile);
	    $this->tpl->assign('isIAdd', $isIAdd);
	    $this->tpl->assign('page', $page);
	    $this->tpl->assign('pages', $usersCount / $perPage);
	    $this->tpl->tpl('', '/yourstyle/tile/', 'usersByTile.php');
		$this->tpl->assign('yourStyleUserRating', $this->cUserRating);
	}
	
	public function handlerShowRules() {
	    $this->tpl->tpl('', '/yourstyle/', 'rules.php');
	}
	
	public function handlerSetRemove($sid) {
	    $ret = null;
	    if(!$this->tpl->isModerYS()) {
	        $this->url_jump('/yourstyle/set/'.$sid);
	        return;
	    }
	    $ysSet = new VPA_table_yourstyle_sets();
	    $ysSet->del($ret, $sid);
	    $this->url_jump('/yourstyle');
	}
	
	public function handlerTileRemove($tid) {
	    $ret = null;
	    if(!$this->tpl->isModerYS()) {
	        $this->url_jump('/yourstyle/tile/'.$tid);
	        return;
	    }
	    $ysTile = new VPA_table_yourstyle_groups_tiles();
	    $ysTile->del($ret, $tid);
	    $this->url_jump('/yourstyle');
	}
	
	public function handlerTileHide($tid) {
	    $ret = null;
	    if(!$this->tpl->isModerYS()) {
	        $this->url_jump('/yourstyle/tile/'.$tid);
	        return;
	    }	     
	    $ysTile = new VPA_table_yourstyle_groups_tiles();
	    $ysTile->set($ret, array('gid' => 0), $tid);
	    $this->url_jump('/yourstyle');
	}
	
	public function handlerTileEdit($tid) {
	    $ret = null;
		if(!$this->tpl->isModerYS()) {
	        $this->url_jump('/yourstyle/tile/'.$tid);
	        return;
		}
		$ysTile = new VPA_table_yourstyle_groups_tiles();
	    $brand = $this->get_param('brand');
	    $group = $this->get_param('group');
	    
	    if(!is_null($brand) && $brand != 0) {
	        $ysTile->set($ret, array('bid' => $brand), $tid);
	    }
	    if(!is_null($group) && $group != 0) {
	        $tile = $ysTile->get_first_fetch(array('id' => $tid));
	        $oldPath = YourStyle_Factory::generateUploadTilesPath($tile['gid']).$tile['image'];
	        $newPath = YourStyle_Factory::generateUploadTilesPath($group).$tile['image'];	        
	        /*var_dump($oldPath);
	        var_dump($newPath);*/
	        rename($oldPath, $newPath);
	        $ysTile->set($ret, array('gid' => $group), $tid);
	    }
	     
	    $this->url_jump('/yourstyle/tile/'.$tid);
	}
	
	//brands
	public function handlerShowBrands() {
	    $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
	    $brands = $ysBrands->get_fetch(array('idn' => 140), array('title ASC'));
	    
	    $brandsByNames = array();
	    foreach ($brands as $id => $brand) {
	    	$letter = strtoupper(substr($brand['title'], 0, 1));
	    
	    	$brandsByNames[$letter][] = $brand;
	    }
	    
	    // split by 3 cols
	    $current = $i = 0;
	    $offset = ceil(count($brands) / 3);
	    $brandsByNames3Cols = array();
	    foreach ($brandsByNames as $letter => $brands) {
	    	$current += count($brands);
	    	$brandsByNames3Cols[$i][$letter] = $brands;
	    
	    	if ($current > (($i + 1) * $offset)) {
	    		$i++;
	    	}
	    }
	    
	    $this->tpl->assign('brandsByNames3Cols', $brandsByNames3Cols);
	    
	    $this->tpl->tpl('', '/yourstyle/', 'allBrands.php');
	}
	
	public function handlerShowTopBrands() {
	    $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
	    
	    $brands = $ysBrands->GetTopBrands(52);
	    
	    foreach ($brands as $key => $brand) {
	        $brands[$key]['logo'] = YourStyle_Factory::getWwwUploadBrandsPath($brand['id'], $brand['logo'], '100x100');
	    }
	    
	    $this->tpl->assign('brands', $brands);
	    $this->tpl->tpl('', '/yourstyle/', 'topBrands.php');
	}
	
	public function handlerShowBrand($bid) {
	    $bid = intval($bid);
	    
	    if($bid == 0) $this->redirect();
	    
	    $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
	    $ysGroups = new VPA_table_yourstyle_groups();
	    $ysTiles = new VPA_table_yourstyle_groups_tiles();
	    $ysSets = new VPA_table_yourstyle_sets();
	    
	    $brand = $ysBrands->get_first_fetch(array('id' => $bid));
	    
	    if(is_null($brand)) $this->redirect();
	    
	    $brand['logo'] = (!empty($brand['logo'])) ? YourStyle_Factory::getWwwUploadBrandsPath($bid, $brand['logo'], '140x140') : null;
	    $brand['tags'] = $ysGroups->GetGroupsByBrand($bid);
	    $brand['tiles'] = $ysTiles->getFiltered(array('bid' => $bid), 0, 12);
	    $brand['tiles_count'] = $ysBrands->GetBrandedTilesCount($bid);
	    $brand['sets'] = $ysSets->GetSetsByBrand($bid, 0, 8);
	    $brand['sets_count'] = count($ysSets->GetSetsByBrand($bid));
	    
	    foreach ($brand['tiles'] as $key => $tile) {
	        $brand['tiles'][$key]['image'] = YourStyle_Factory::getWwwUploadTilesPath($tile['gid'], $tile['image'], '70x70');
	    }
	    
	    foreach ($brand['sets'] as $key => $set) {
	        $brand['sets'][$key]['image'] = YourStyle_Factory::getWwwUploadSetPath($set['id'], $set['image'], '150x150');
	    }
	    
	    $this->tpl->assign('brand', $brand);
	    $this->tpl->tpl('', '/yourstyle/brand/', 'details.php');
	}
	
	public function handlerShowBrandSets($bid, $page = 1) {
	    $perPage = 24;
	    $page = intval($page);
	    $bid = intval($bid);
	    
	    if($page < 1) $page = 1;
	    if($bid == 0) $this->redirect();
	    
	    $ysSets = new VPA_table_yourstyle_sets();
	    $count = count($ysSets->GetSetsByBrand($bid));

	    if($count <= 0) $this->url_jump('/yourstyle/brands/'.$bid);
	    $pages = ceil($count / $perPage);
	    
	    $sets = $ysSets->GetSetsByBrandWithInfo($bid, ($page-1)*$perPage, $perPage);
	    
	    foreach ($sets as $key => $set) {
	        $sets[$key]['urating'] = ($set['votes'] != 0) ? round($set['rating'] / $set['votes'], 1) : 0;	        
	    }    
	    
	    $this->tpl->assign('sets', $sets);
	    $this->tpl->assign('bid', $bid);
	    $this->tpl->assign('page', $page);
	    $this->tpl->assign('pages', $pages);
	    
	    $this->tpl->tpl('', '/yourstyle/brand/', 'sets.php');
	}
	
	//stars ajax search
	public function handlerSearchStars($q) {
	    $q = $this->iconv(urldecode(strval($q)));
	    $this->tpl->tpl('', '/', 'ajax.php');
		$this->tpl->assign('data', array('error' => 'Неизвестная ошибка'));
		if(strlen($q) < 2) return;
		
		require_once dirname(__FILE__).'/../../classes/BaseHandler.php';
		require_once dirname(__FILE__).'/../../classes/PersonsHandler.php';
		$person = new PersonsHandler($this->user_lib);
		//$this->tpl->assign('data', $q);
		
		$ysTags = new VPA_table_yourstyle_sets_tags();
		$stars = $ysTags->getNewStars(null, null, $q);
		
		foreach ($stars as $key => $star) {
		    $name = $person->Name2URL($star['eng_name']);
		    $stars[$key]['url'] = '/persons/'.$name.'/sets';
		    unset($stars[$key]['id'], $stars[$key]['sid']);
		}
		unset($person);
		$this->tpl->assign('data', $stars);
	}
	
	public function handlerShowStarSets($star_name, $page = 1) {
	    require_once dirname(__FILE__).'/../../classes/BaseHandler.php';
	    require_once dirname(__FILE__).'/../../classes/PersonsHandler.php';
	    $person = new PersonsHandler($this->user_lib);	    
	    $id = $person->getID($star_name);
	    
	    $perPage = 24;
	    $page = intval($page);
	    if($page < 1) {
	        $this->redirect('/yourstyle/star/'.$star_name, HTTP_STATUS_301);
	    }
	    
	    if($id == 0) {
	        $this->url_jump('/yourstyle/stars');
	        return;
	    }
	    
	    $ysStars = new VPA_table_persons();
	    $ysSets = new VPA_table_yourstyle_sets();
	    $ysRating = new VPA_table_yourstyle_users_rating();
	    $star = $ysStars->get_first_fetch(array('id' => $id));
	    $count = $ysSets->GetStarSetsCount($star['id']);
	    $pages = ceil($count / $perPage);
	    
	    $sets = $ysSets->GetSetsByStar($id, ($page - 1) * $perPage, $perPage);
	    
	    foreach ($sets as $id => $set) {
	    	$sets[$id]['urating'] = $ysRating->getUserWithRating($set['uid']);
	    	$sets[$id]['urating'] = $sets[$id]['urating']['rating'];
	    	$sets[$id]['image'] = YourStyle_Factory::getWwwUploadSetPath($set['id'], $set['image'], '274x274');
	    }
	    
	    $star['sets'] = $sets;
	    
	    unset($star['goods_id'], $star['content'], $star['link'], $star['photo_link'], 
	            $star['rus'], $star['birthday'], $star['in_cloud'], $star['woman'], $star['singer'], 
	            $star['prepositional'], $star['no_adding_facts'], $star['twitter_login']);
	    
	    $this->tpl->assign('handler', $person);
	    $this->tpl->assign('star', $star);
	    $this->tpl->assign('page', $page);
	    $this->tpl->assign('pages', $pages);
	    
	    $this->tpl->tpl('', '/yourstyle/', 'starSets.php');
	}
	
	/**
	 * Init routes
	 *
	 * @return void
	 */
	protected function _initRoutes() {
		// not auth
		//if (!$this->user) return $this->handler_show_error('no_login');

		require_once 'akDispatcher/akDispatcher.class.php';
		$d = akDispatcher::getInstance('/', $this->getRequestUrl(), 'WINDOWS-1251');
		$d->functionReturnsContent = false;

		$d->add('/yourstyle', array(&$this, 'handlerShowMain'));
		$d->add('/yourstyle/sets', array(&$this, 'handlerShowSetsTop'));
		$d->add('/yourstyle/sets/new', array(&$this, 'handlerShowSetsNew'));
		$d->add('/yourstyle/rootGroup/:rgid', array(&$this, 'handlerShowGroups'));
		$d->add('/yourstyle/groups', array(&$this, 'handlerShowRootGroups'));
		$d->add('/yourstyle/tiles/top', array(&$this, 'handlerShowTilesTop'));
		$d->add(array('/yourstyle/stars', '/yourstyle/stars/:page'), array(&$this, 'handlerShowStarsNew'), 'both');
		$d->add('/yourstyle/stars/byName', array(&$this, 'handlerShowStarsByName'));
		$d->add(array('/yourstyle/group/:gid', '/yourstyle/group/:gid/page/:page'), array(&$this, 'handlerShowGroupsTiles'));
		$d->add('/yourstyle/tile/:tid', array(&$this, 'handlerShowGroupTile'));
		$d->add('/yourstyle/tile/:tid/toMy', array(&$this, 'handlerAddTileToMy'));
		$d->add('/yourstyle/tile/:tid/fromMy', array(&$this, 'handlerDeleteTileToMy'));
		$d->add('/yourstyle/tile/:tid/like/:rate', array(&$this, 'handlerTileLike'));		
		$d->add(array('/yourstyle/set/:sid', '/yourstyle/set/:sid/page/:page'), array(&$this, 'handlerShowSet'));
		$d->add('/yourstyle/set/:sid/like/:rate', array(&$this, 'handlerSetLike'));
		$d->add('/yourstyle/set/:sid/comment/:cid/edit', array(&$this, 'handlerEditSetComment'), 'post');
		$d->add('/yourstyle/set/:sid/postComment', array(&$this, 'handlerPostSetComment'), 'post');
		$d->add('/yourstyle/set/:sid/comment/:cid/rating/:rating', array(&$this, 'handlerUpdateSetCommentRating'));
		$d->add('/yourstyle/set/:sid/comment/:cid/delete', array(&$this, 'handlerDeleteSetComment'));
		
		$d->add('/profile/:uid/sets', array(&$this, 'handlerShowMySets'));
		$d->add('/user/:uid/sets', array(&$this, 'handlerShowUserSets'));
		
		$d->add(array('/yourstyle/setsbytile/:tid', '/yourstyle/setsbytile/:tid/page/:page'), array(&$this, 'handlerShowSetsByTile'));
		$d->add(array('/yourstyle/usersbytile/:tid', '/yourstyle/usersbytile/:tid/page/:page'), array(&$this, 'handlerShowUsersByTile'));
		$d->add(array('/yourstyle/tiles', '/yourstyle/tiles/page/:page'), array(&$this, 'handlerShowFilteredTiles'), 'both');
		$d->add('/yourstyle/tiles/top/filtered', array(&$this, 'handlerShowFilteredTilesTop'));

		$d->add('/yourstyle/rules', array(&$this, 'handlerShowRules'));
		
		//sets & tiles moder manipulations
		$d->add('/yourstyle/set/:sid/delete', array(&$this, 'handlerSetRemove'));
		$d->add('/yourstyle/tile/:tid/delete', array(&$this, 'handlerTileRemove'));
		$d->add('/yourstyle/tile/:tid/hide', array(&$this, 'handlerTileHide'));
		$d->add('/yourstyle/tile/:tid/edit', array(&$this, 'handlerTileEdit'));
		
		//brands
		$d->add('/yourstyle/brands', array(&$this, 'handlerShowBrands'));
		$d->add('/yourstyle/brands/top', array(&$this, 'handlerShowTopBrands'));
		$d->add('/yourstyle/brands/:q', array(&$this, 'handlerShowBrand'));
		$d->add(array('/yourstyle/brands/:q/sets', '/yourstyle/brands/:q/sets/:page'), array(&$this, 'handlerShowBrandSets'));
		
		//stars
		$d->add('/yourstyle/stars_search/:q', array(&$this, 'handlerSearchStars'));
		$d->add(array('/persons/:star/sets', '/persons/:star/sets/:page'), array(&$this, 'handlerShowStarSets'));
		
		try {
			$us = new VPA_table_yourstyle_users_rating();
			$this->cUserRating = $us->getUserWithRating($this->user['id']);
			$this->cUserRating = $this->cUserRating['rating'];
			if(is_null($this->cUserRating)) $this->cUserRating = 0;
			$d->run();
		} catch (Exception $e) {
			$this->redirect();
		}
	}
	
	/*helpers*/
	private function getAllGroups() {
	    $ysRootGroups = new VPA_table_yourstyle_root_groups();
	    $ysGroups = new VPA_table_yourstyle_groups();
	    
	    $rg = $ysRootGroups->get_fetch();
	    
	    $rootGroups = array();
	    
	    foreach ($rg as $key => $g) {
	    	if($g['id'] == 6) { unset($rg[$key]); continue; }
	        $rootGroups[$g['id']] = $g;
	        $gr = $ysGroups->get_fetch(array('rgid' => $g['id']), array('title ASC'));
	        $rootGroups[$g['id']]['groups'] = $gr;
	    }
	    
	    return $rootGroups;
	}
		
	private function getBrands($filters) {
	    $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
	    
	    if($filters['gid'] != 0) {
	        $brands = $ysBrands->GetGroupBrands($filters['gid']);
	    }
	    elseif($filters['rgid'] != 0) {
	        $brands = $ysBrands->GetRootGroupBrands($filters['rgid']);	        
	    }
	    else {
	        $brands = $ysBrands->get_fetch(array('idn' => 140), array('title ASC'));
	    }
	    
	    return $brands;
	}
	
	private function GetFilteredColors($filters) {
	    $ysColors = new VPA_table_yourstyle_tiles_colors_new();
	    
	    $colors = $ysColors->GetFilteredColors($filters, $this->getColors());
	    
	    return $colors;
	}
	
	protected function iconv($mixed) {
		$this->tpl->plugins['iconv']->iconv_exchange();
		$mixed = $this->tpl->plugins['iconv']->iconv($mixed);
		$this->tpl->plugins['iconv']->iconv_exchange();
		return $mixed;
	}
	
	private $cUserRating = 0;	
}
