<?php

require_once 'YourStyle_Factory.php';
require_once LIB_DIR . '/akSphinx.php';

/**
 * Class YourStyle_EditorAPI
 * Date begin: 28.02.2011
 *
 * Editor API
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class YourStyle_EditorAPI extends YourStyle_Factory {
	/**
	 * Default limit, for fetch records from db, not to load server more than need
	 */
	const DEFAULT_LIMIT = 1000;
	/**
	 * List of rood group ids, for wich, brand is not necessary
	 * 
	 * @var array
	 */
	protected $_brandIsNotNecessary = array(
		6, // background
	);
	/**
	 * Not strip images for defined root group ids
	 * 
	 * @var array
	 */
	protected $_noStripImage = array(
		6, // background
	);


	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct(user_base_api &$user_lib, $noInitRoutes = false) {
		parent::__construct($user_lib, $noInitRoutes);
	}

	public function handlerGetGroups() {
	    
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroups = $ysRootGroups->getWithGroupsAndTiles();
		$ysTiles = new VPA_table_yourstyle_groups_tiles();
		$rootGroups = array_custom_values($rootGroups, array('id', 'title', 'groups', 'tile', 'tid'));

		foreach ($rootGroups as $key => &$rootGroup) {
		    /*if($rootGroup['id'] == 6) {
		        unset($rootGroups[$key]);
		        continue;
		    }*/
			$rootGroup['groups'] = array_custom_values($rootGroup['groups'], array('id', 'title', 'tile', 'tid'));			
			foreach ($rootGroup['groups'] as &$group) {
			    if($group['tid'] != 0) {
			        $group['tile'] = $ysTiles->get_first_fetch(array('id' => $group['tid']));
			    }
				if (!empty($group['tile'])) {
					$group['tile']['image'] = self::getWwwUploadTilesPath($group['id'], $group['tile']['image']);
				}
			}
			if($rootGroup['tid'] != 0) {
			    $rootGroup['tile'] = $ysTiles->get_first_fetch(array('id' => $rootGroup['tid']));
			}
			if (!empty($rootGroup['tile'])) {
				$rootGroup['tile']['image'] = self::getWwwUploadTilesPath($rootGroup['tile']['gid'], $rootGroup['tile']['image']);
			}
		}

		$this->tpl->assign('data', $rootGroups);
		return true;
	}

	public function handlerGetGroupsTiles($gid) {
		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$groupsTiles = $ysGroupsTiles->getUsersTiles($this->user['id'], $gid, null, 0, self::DEFAULT_LIMIT);

		$ysVotes = new VPA_table_yourstyle_tiles_votes();
		
		foreach ($groupsTiles as &$groupsTile) {
			$groupsTile['image'] = self::getWwwUploadTilesPath($groupsTile['gid'], $groupsTile['image'], '300x300');
			$voteCount = count($ysVotes->get_fetch(array('tid' => $groupsTile['id'])));
			
			$groupsTile['rating'] = ($voteCount == 0) ? 0 : round($groupsTile['rate'] / $voteCount, 1);			
			
			unset($groupsTile['rate']);
			unset($groupsTile['gid']);
		}
						
		$this->tpl->assign('data', $groupsTiles);
		return true;
	}

	/**
	 * @example curl -u azat:blablabla --cookie "uid=29288; idp=4d510e2abd53fa39d9f479e817ef1c93;" --data 'type=yourstyle&action=editor&json={"tiles":[{"image":"/upload/yourstyle/tiles/1/0/0/1/humpd8av8z.png","vflip":true,"hflip":true,"leftOffset":10,"topOffset":100,"width":220,"height":150,"tid":1},{"image":"/upload/yourstyle/tiles/1/0/0/1/humpd8av8z.png","vflip":false,"hflip":true,"leftOffset":10,"topOffset":100,"width":220,"height":150,"tid":1}]}' http://dev.popcornnews.ru/yourstyle/editor/saveSet; echo;
	 * @example curl -u azat:blablabla --cookie "uid=29288; idp=4d510e2abd53fa39d9f479e817ef1c93;" --data 'type=yourstyle&action=editor&json={"tiles": [{"hidden":false,"vflip":false,"hflip":false,"underlay":false,"leftOffset":145,"topOffset":120,"tid":3,"image":"http://v0.popcorn-news.ru/upload/yourstyle/tiles/1/0/0/1/v152jqh367.png","width":297,"height":297}]}' http://dev.popcornnews.ru/yourstyle/editor/saveSet; echo;
	 *
	 * @example curl -u azat:blablabla --cookie "uid=29288; idp=4d510e2abd53fa39d9f479e817ef1c93;" --data 'type=yourstyle&action=editor&json={"tiles": [{"hidden":false,"vflip":false,"hflip":false,"underlay":false,"leftOffset":0,"topOffset":0,"tid":30,"image":"http://v0.popcorn-news.ru/upload/yourstyle/tiles/1/0/0/1/7uq8i8sf9y.png","width":590,"height":460},{"hidden":false,"vflip":false,"hflip":false,"underlay":false,"leftOffset":145,"topOffset":120,"tid":24,"image":"http://v0.popcorn-news.ru/upload/yourstyle/tiles/1/0/0/1/69pg8gtttv.png","width":300,"height":300},{"hidden":false,"vflip":false,"hflip":false,"underlay":false,"leftOffset":390,"topOffset":141,"tid":16,"image":"http://v0.popcorn-news.ru/upload/yourstyle/tiles/2/0/0/2/1yr6tfe8b3.png","width":200,"height":200},{"hidden":false,"vflip":false,"hflip":false,"underlay":false,"leftOffset":0,"topOffset":45,"tid":6,"image":"http://v0.popcorn-news.ru/upload/yourstyle/tiles/2/0/0/2/9r78z4v4c5.png","width":300,"height":300}], "id": 80}' http://dev.popcornnews.ru/yourstyle/editor/saveSet; echo;
	 */
	public function handlerSaveSet() {
		$data = $this->transformJson();

		$tiles = &$data['tiles'];
		if (!$tiles) {
			$this->tpl->assign('data', array('error' => 'Нет вещей'));
			return false;
		}
		$id = (int)$data['id'];

		$ysSets = new VPA_table_yourstyle_sets;
		$ysSetsTiles = new VPA_table_yourstyle_sets_tiles;
		// save - update, only for drafts
		if (!empty($id)) {
			$setInfo = $ysSets->get_first_fetch(array('id' => $id));
			if (!$setInfo) {
				$id = null;
			} else {
				// is already publish?
				if ($setInfo['isDraft'] == 'n') {
					$this->tpl->assign('data', array('error' => 'Вы не можете редактировать опубликованные сеты'));
					return false;
				}
				// if this is not set of current user
				if ($setInfo['uid'] != $this->user['id']) {
					$this->tpl->assign('data', array('error' => 'Сет не найден'));
					return false;
				}

				// @TODO
				// maybe not delete ALL old values, and delete values that are modified or not exist
				// and than insert only new items
				$ysSetsTiles->del_where($ret, array('sid' => $id));
				$ysSets->set($ret, array('edittime' => time()), $id);
			}
		}
		// new one
		if (empty($id)) {
			$ysSets->add($newId, array('createtime' => time(), 'isDraft' => 'y', 'uid' => $this->user['id']));
			$newId->get_first($newId);
			$id = $newId;
		}

		// generate image
		$setImageName = random_file_name(self::generateUploadSetPath($id), 'png');
		$setImagePath = self::generateUploadSetPath($id) . $setImageName;
		try {
			YourStyle_BackEnd::getInstance(null, $setImagePath)->generateImage($tiles);
			$ysSets->set($ret, array('image' => $setImageName), $id);
		} catch (Exception $e) {
			$this->tpl->assign('data', array('error' => 'Не удалось сгенерировать уменьшенную копию'));
			return false;
		}

		// insert new tiles of set
		$sequence = 1;
		foreach ($tiles as &$tile) {
			$ok = $ysSetsTiles->add($ret, array(
				'createtime' => time(),
				'sequence' => $sequence++,
				'width' => (int)$tile['width'],
				'height' => (int)$tile['height'],
				'leftOffset' => (int)$tile['leftOffset'],
				'topOffset' => (int)$tile['topOffset'],
				'vflip' => $tile['vflip'] ? 'y' : 'n',
				'hflip' => $tile['hflip'] ? 'y' : 'n',
				'underlay' => $tile['underlay'] ? 'y' : 'n',
				'image' => basename($tile['image']),
				'tid' => (int)$tile['tid'],
				'sid' => (int)$id,
			));
			// @TODO rollback ?
			if (!$ok) {
				$this->tpl->assign('data', array('error' => 'Нет такой вещи'));
				return false;
			}
		}
		$this->tpl->assign('data', $id);
	}

	public function handlerLoadSet($sid) {
		$sid = (int)$sid;

		// set
		$ysSets = new VPA_table_yourstyle_sets;
		$ysSets->get_params($set, array('id' => $sid), null, 0, 1, null, array('id', 'title', 'image', 'isDraft'));
		if ($set->len() === 0) {
			$this->tpl->assign('data', array('error' => 'Вещь не найдена'));
			return false;
		}
		$set->get_first($set);
		$set['image'] = self::getWwwUploadSetPath($set['id'], $set['image']);
		$set['isDraft'] = ($set['isDraft'] == 'y' ? true : false);
		// tiles of set
		$ysSetsTiles = new VPA_table_yourstyle_sets_tiles;
		$setTiles = $ysSetsTiles->getSetTilesForEditor($sid);
		foreach ($setTiles as &$setTile) {
			$setTile['vflip'] = ($setTile['vflip'] == 'y' ? true : false);
			$setTile['hflip'] = ($setTile['hflip'] == 'y' ? true : false);
			$setTile['underlay'] = ($setTile['underlay'] == 'y' ? true : false);
			$setTile['image'] = self::getWwwUploadTilesPath($setTile['gid'], $setTile['image']);
		}
		$this->tpl->assign('data', array('info' => $set, 'tiles' => $setTiles));
	}

	public function handlerPublishSet() {
		$data = $this->transformJson();
		$sid = (int)$data['id'];
		$title = strip_tags($data['title']);
		if (!$title) {
			$this->tpl->assign('data', array('error' => 'Заголовок не задан'));
			return false;
		}
		$tags = array_not_empty(array_map('intval', $data['tags']));
		if (!empty($tags) && count($tags) >= 10) {
			$this->assign('data', array('error' => 'Кол-во тэгов не может быть больше 10'));
			return false;
		}

		$ysSets = new VPA_table_yourstyle_sets;
		$ok = (bool)$ysSets->set_where($ret, array('isDraft' => 'n', 'title' => $title), array('id' => $sid, 'uid' => $this->user['id']));
		$affectedRows = $ysSets->affected_rows();
		if ($affectedRows == 1 && !empty($tags)) {
			$ysSetsTags = new VPA_table_yourstyle_sets_tags;
			foreach ($tags as $tag) {
				$ysSetsTags->add($ret, array('sid' => $sid, 'tid' => $tag, 'uid' => $this->user['id'], 'createtime' => time()));
			}
		}
		/*inc user rating*/
		
		$usr = new VPA_table_users();
		$usr->set($r, array('rating' => 'rating+3'), $this->user['id']);
		unset($usr);
		
		/*---------------*/

		$this->tpl->assign('data', $ok);
	}

	public function handlerGetUsersSets() {
		$ysSets = new VPA_table_yourstyle_sets;
		$sets = $ysSets->get_params_fetch(array('uid' => $this->user['id']), null, null, null, null, array('id', 'title', 'image', 'isDraft'));
		foreach ($sets as &$set) {
			$set['image'] = self::getWwwUploadSetPath($set['id'], $set['image']);
			$set['isDraft'] = ($set['isDraft'] == 'y' ? true : false);
		}
		$this->tpl->assign('data', $sets);
	}

	public function handlerGetUsersTiles() {
		$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
		$tiles = $ysTilesUsers->getUsersTiles($this->user['id']);
		foreach ($tiles as &$tile) {
			$tile['image'] = self::getWwwUploadTilesPath($tile['gid'], $tile['image']);
		}
		$this->tpl->assign('data', $tiles);
	}

	/**
	 * @example curl -u azat:blablabla --cookie "uid=29288; idp=4d510e2abd53fa39d9f479e817ef1c93;" --data 'type=yourstyle&action=editor&json={"title":"def"}' http://dev.popcornnews.ru/yourstyle/editor/saveBookmark; echo;
	 */
	public function handlerSaveBookmark() {
		$data = $this->transformJson();

		if (!$data['title']) {
			$this->tpl->assign('data', array('error' => 'Заголовок не задан'));
			return false;
		}
		//if(isset($data['tabColor'])) $data['tabColor'] = $data['tabColor']['en'];
		if(!empty($data['tabColor']) && !$this->isSuchColorExist($data['tabColor']['en'])) {
			$this->tpl->assign('data', array('error' => 'Фильтр по этому цвету недоступен'));
			return false;
		}
		
		$data['searchText'] = isset($data['tabBrand']) ? $data['tabBrand']['id'] : '';

		$ysBookmarks = new VPA_table_yourstyle_bookmarks;
		$paramsForUpdate = array(
			'title' => $data['title'],
			'searchText' => $data['searchText'],
			'gid' => (int)$data['gid'],
			'rgid' => (int)$data['rgid'],
			'tabColor' => $data['tabColor']['val'],
			'type' => $data['type'] == 'search' ? 'search' : 'group', // default value
		);

		// update
		if ($data['id']) {
			$id = (int)$data['id'];
			$ysBookmarks->set_where($ret, $paramsForUpdate, array('id' => $id, 'uid' => $this->user['id']));
		}
		// add
		else {
			$paramsForAdd = array_merge(array('createtime' => time(), 'uid' => $this->user['id']), $paramsForUpdate);
			$ysBookmarks->add($id, $paramsForAdd);
			$id->get_first($id);
		}
		$this->tpl->assign('data', $id);
	}

	public function handlerDeleteBookmark($bid) {
		$bid = (int)$bid;

		if (!$bid) {
			$this->tpl->assign('data', array('error' => 'Не задана закладка'));
			return false;
		}

		$ysBookmarks = new VPA_table_yourstyle_bookmarks;
		$ok = (bool)$ysBookmarks->del_where($ret, array('id' => $bid, 'uid' => $this->user['id']));
		$this->tpl->assign('data', $ok);
	}

	public function handlerGetUsersBookmarks() {
		$ysBookmarks = new VPA_table_yourstyle_bookmarks;
		$bookmarks = $ysBookmarks->get_params_fetch(array('uid' => $this->user['id']), array('id asc'), null, null, null, array('id', 'title', 'searchText', 'gid', 'type', 'tabColor', 'rgid'));
		
		foreach ($bookmarks as $key => $bookmark) {
		    if(!empty($bookmark['tabColor'])) {
		        $color = YourStyle_BackEnd::$humanColors[$bookmark['tabColor']];
		        $bookmarks[$key]['tabColor'] = array(
		                    'val' => $bookmark['tabColor'],
		                    'en' => $color['en'],
		                    'ru' => $color['ru']
		                );
		    }
		    if(!empty($bookmark['searchText'])) {		        
		        $bid = $bookmark['searchText'];
		        
		        $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
		        $brand = $ysBrands->get_first_fetch(array('id' => $bid));
		        
		        $bookmarks[$key]['tabBrand'] = array('brand' => $brand['title'], 'id' => $bid);
		    }
		    unset($bookmarks[$key]['searchText']);		    
		}
		
		$this->tpl->assign('data', $bookmarks);
	}

	public function handlerDeleteSet($sid) {
		$ysSets = new VPA_table_yourstyle_sets;
		$ok = (bool)$ysSets->del_where($ret, array('id' => $sid, 'uid' => $this->user['id']));
		if (!$ok) {
			$this->tpl->assign('data', array('error' => 'Невозможно удалить сет'));
		} else {
			$this->tpl->assign('data', true);
		}
	}

	public function handlerTileUpload($fromManager = false) {
		$file = $this->get_param('file');
		if($fromManager) {
		    $brand = trimd(strip_tags($this->get_param('title') ?: $this->get_param('brand')));
		    $hidden = ($this->get_param('hidden') == 'on') ? true : false;
	    	if($hidden == 'on') $hidden = true;
		    $gid = !$hidden ? (int)$this->get_param('gid') : 0;
    		//$gid = (int)$this->get_param('gid');
		    $description = strip_tags($this->get_param('description'));
		    $brandIsNecessary = true;
		} else {
		    $brand = null;
		    $hidden = true;
		    $gid = 0;
		    $description = '';
		    $brandIsNecessary = false;
		}

		if (!$file || (!$gid && !$hidden)) {
			$this->tpl->assign('data', array('error' => 'Не все поля заполнены'));
			return false;
		}

		// group info
		$ysGroups = new VPA_table_yourstyle_groups;
		$group = $ysGroups->get_first_fetch(array('id' => $gid));
		if (!$group && !$hidden) {
			$this->tpl->assign('data', array('error' => 'Такой группы не существует'));
			//$this->tpl->assign('data', array('error' => $gid));
			return false;
		}
		// not necessary brand
		if (in_array($group['rgid'], $this->_brandIsNotNecessary)) {
			$brandIsNecessary = false;
		}

		if (!$brand && $brandIsNecessary) {
			$this->tpl->assign('data', array('error' => 'Для этой группы бренд - обязателеное поле'));
			return false;
		}

		// brand
		$bid = null;
		if ($brand) {
			// try to extract brand id
			$ysTilesBrands = new VPA_table_yourstyle_tiles_brands;
			$brandInfo = $ysTilesBrands->get_first_fetch(array('title' => $brand), null, 0, 1);
			if (!$brandInfo) {
				$bid = $ysTilesBrands->add_fetch(array('title' => $brand, 'createtime' => time()));
			} else {
				$bid = $brandInfo['id'];
			}
		}

		$newName = random_file_name(self::generateUploadTilesPath($gid), 'png');
		$newPath = self::generateUploadTilesPath($gid) . $newName;
		try {
			$image = new YourStyle_BackEnd($file['tmp_name'], $newPath);
			// need to strip image
			if (!in_array($group['rgid'], $this->_noStripImage)) {
				$image->imageTransformation();
			}
			// not need to strip image - just create a copy of file
			else {
				$ok = copy($file['tmp_name'], $newPath);
				if (!$ok) {
					throw new Exception();
				}
			}
			$imageInfo = $image->detectImageColors();

			$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
			$newData = array(
				'createtime' => time(),
				'image' => $newName,
				'gid' => $gid,
				'uid' => !$fromManager ? $this->user['id'] : 0,
				'width' => $imageInfo['width'],
				'height' => $imageInfo['height'],
				'description' => $description,
			    'hidden' => intval($hidden),
			    'color_mode' => 'auto'
			);
			if (!is_null($bid)) {
				$newData['bid'] = $bid;
			}
			$id = $ysGroupsTiles->add_fetch($newData);
			if (!$id) {
				throw new Exception();
			}

			// add to users list
			$ysTilesUsers = new VPA_table_yourstyle_tiles_users;
			$ysTilesUsers->add($ret, array('tid' => $id, 'uid' => !$fromManager ? $this->user['id'] : 0, 'createtime' => time()));
			// image colors info
			$ysTilesColors = new VPA_table_yourstyle_tiles_colors;
			foreach ($imageInfo['result'] as &$colorInfo) {
				$ysTilesColors->add($ret, array(
					'createtime' => time(),
					'tid' => $id,
					'html' => $colorInfo['colorInfo']['HTML'],
					'human' => $colorInfo['colorInfo']['human'],
					'red' => $colorInfo['colorInfo']['RGB']['red'],
					'green' => $colorInfo['colorInfo']['RGB']['green'],
					'blue' => $colorInfo['colorInfo']['RGB']['blue'],
					'alpha' => $colorInfo['colorInfo']['RGB']['alpha'],
					'pixels' => $colorInfo['pixels'],
				));
			}
		} catch (Exception $e) {
			$this->tpl->assign('data', array('error' => 'Произошла ошибка. Попробуйте загрузить файл меньшего размера (до 5 mb)'));
			return false;
		}
		$this->tpl->assign('data', $id);
		return true;
	}

	public function handlerTilesSearch() {
		$gid = (int)$this->get_param('gid');
		$q = strip_tags(urldecode($this->get_param('q')));
		
		if (strlen($q) < 3) {
			$this->tpl->assign('data', array('error' => 'Длина поискового запроса, должна быть не меньше 3-х'));
			return false;
		}

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tiles = $ysGroupsTiles->getWithBrands(array_not_empty(array('gid' => $gid, 'q' => $q)), array('createtime desc'), 0, self::DEFAULT_LIMIT, null, array('id', 'title', 'image', 'gid'));
		foreach ($tiles as &$tile) {
			$tile['image'] = self::getWwwUploadTilesPath($tile['gid'], $tile['image']);
		}

		$this->tpl->assign('data', $tiles);
	}

	public function handlerSuggestTagsForSet($q) {
		$q = $this->iconv(strip_tags(urldecode($q)));

		$persons = new VPA_table_persons_tiny_ajax;
		$tags = $persons->get_params_fetch(array('search' => $q), array('name'), 0, 20);

		$outTags = array();
		foreach ($tags as $tag) {
			$outTags[] = array('name' => $tag['name'], 'engName' => $tag['pole1'], 'id' => $tag['id']);
		}
		$this->tpl->assign('data', $outTags);
	}

	public function handlerGetGroupTile($tid) {
		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$groupsTile = $ysGroupsTiles->getUsersWithGroupsTiles($this->user['id'], $tid);
		$groupsTile['image'] = self::getWwwUploadTilesPath($groupsTile['gid'], $groupsTile['image']);
		$groupsTile['rating'] = 0;
		if($groupsTile['c'] > 0) {
		    $rate = $groupsTile['rate'] / $groupsTile['c'];
		    $groupsTile['rating'] = round($rate, 1);		    
		}
		unset($groupsTile['c']);
		unset($groupsTile['rate']);

		$this->tpl->assign('data', $groupsTile);
	}

	public function handlerGetColors() {
		$this->tpl->assign('data', $this->getColors());
	}

	public function handlerGetTilesByColor() {
		$gid = $this->get_param('gid');
		$color = $this->get_param('color');
		
		if($color == "all") {
			return $this->handlerGetGroupsTiles($gid);
		}

		if (!$this->isSuchColorExist($color)) {
			$this->tpl->assign('data', array('error' => 'Фильтр по этому цвету недоступен'));
			return false;
		}
		$ysTilesColors = new VPA_table_yourstyle_tiles_colors;
		$tiles = $ysTilesColors->getWithTiles($color, $gid, 0, self::DEFAULT_LIMIT);
		foreach ($tiles as &$tile) {
			$tile['image'] = self::getWwwUploadTilesPath($tile['gid'], $tile['image']);
		}
		$this->tpl->assign('data', $tiles);
	}

	public function handlerSuggestBrands() {
		$q = $this->iconv(strip_tags($this->get_param('q')));
		if(empty($q)) {
		    $this->tpl->assign('data', array());
		} else {
		    // $this->tpl->assign('data', akSphinx::getInstance()->setMatchMode(SPH_MATCH_PHRASE)->search($q . '*', 'popcornnews_yourstyle_tiles_brands', 0, 25));
	    	$o = new VPA_table_yourstyle_tiles_brands_new;
    		//$brands = $o->get_fetch(array('qt' => $q), null, 0, 25) ?: array();
		    $o->get($brands, array('qt' => $q), null, 0, 25);
		    $brands->get($brands);
		    $data = array();
		    foreach ($brands as &$brand) {
		    	$data[] = array('brand' => $brand['title'], 'id' => $brand['id']);
	    	}
    		//print_r(var_dump($o->get_fetch(array('qt' => $q), null, 0, 25)));
		    $this->tpl->assign('data', $data);
		}
	}
	
	public function handlerGetFiltered() {
		$filters['rgid'] = intval($this->get_param('rgid'));
	    $filters['gid'] = intval($this->get_param('gid'));
	    $filters['bid'] = intval($this->get_param('bid'));
	    $filters['color'] = strval($this->get_param('tabColor'));
	    $page = intval($this->get_param('page'));
	    
	    $offset = null;
	    $limit = 30;
	    if($page > 0) {
	        $offset = ($page - 1) * $limit;	        
	    }
	    
	    foreach ($filters as $k => $v) {
	    	if($v === 0 || $v == 'all') unset($filters[$k]);
	    }
	    
	    $color = !empty($filters['color']) ? $filters['color'] : null;
	    if (!is_null($color) && !($color = $this->isSuchColorExist($color))) {
	    	$this->tpl->assign('error', 'Фильтр по этому цвету недоступен');
	    	return false;
	    }
	    if(!is_null($color)) $filters['color'] = $color['rgb']; else unset($filters['color']);

	    $ysTiles = new VPA_table_yourstyle_groups_tiles();
	    $ysVotes = new VPA_table_yourstyle_tiles_votes();
	    $ysUserTile = new VPA_table_yourstyle_tiles_users();
	    
	    if(is_null($offset)) {
	        $tiles = $ysTiles->getFiltered($filters);
	        $pages = ceil(count($tiles) / $limit);
	    } else {
	        $tiles = $ysTiles->getFiltered($filters, $offset, $limit);
	        $pages = ceil($ysTiles->getCount($filters) / $limit);
	    }
	    
	    foreach ($tiles as $id => $tile) {
	        $tiles[$id]['image'] = YourStyle_Factory::getWwwUploadTilesPath($tile['gid'], $tile['image'], '300x300');
	        
	        $voteCount = count($ysVotes->get_fetch(array('tid' => $tile['id'])));
	        $tiles[$id]['rating'] = ($voteCount == 0) ? 0 : round($tile['rate'] / $voteCount, 1);
	        
	        $users = $ysUserTile->get_fetch(array('tid' => $tile['id'], 'uid' => $this->user['id']));
	        $tiles[$id]['isMine'] = !empty($users);
	        
	        unset($users);
	        unset($tiles[$id]['createtime']);
	        unset($tiles[$id]['bid']);
	        unset($tiles[$id]['width']);
	        unset($tiles[$id]['height']);
	        unset($tiles[$id]['hidden']);
	        unset($tiles[$id]['color_mode']);
	        unset($tiles[$id]['groupTitle']);
	        unset($tiles[$id]['uid']);
	        unset($tiles[$id]['rate']);
	    }
	    
	    $data = array('items' => $tiles, 'pages' => $pages);
	    
	    $this->tpl->assign('data', $data);
	}	

	public function handlerShowEditor($tid = null) {
		$this->tpl->tpl('', '/yourstyle/', 'editor.php');
	}

	/**
	 * Init routes
	 *
	 * @return void
	 */
	protected function _initRoutes() {
		// not auth
		if (!$this->user) {
			return $this->handler_show_error('no_login');
		}

		require_once 'akDispatcher/akDispatcher.class.php';

		$d = akDispatcher::getInstance('/', $this->getRequestUrl(), 'WINDOWS-1251');
		$d->functionReturnsContent = false;

		$d->add(array('/yourstyle/editor', '/yourstyle/editor/withTile/:tid'), array(&$this, 'handlerShowEditor'));
		$d->add('/yourstyle/editor/upload', array(&$this, 'handlerTileUpload'), 'post');
		$d->add('/yourstyle/editor/getGroups', array(&$this, 'handlerGetGroups'));
		$d->add('/yourstyle/editor/getGroupsTiles/:gid', array(&$this, 'handlerGetGroupsTiles'));
		$d->add('/yourstyle/editor/loadSet/:sid', array(&$this, 'handlerLoadSet'));
		$d->add('/yourstyle/editor/saveSet', array(&$this, 'handlerSaveSet'), 'post');
		$d->add('/yourstyle/editor/deleteSet/:sid', array(&$this, 'handlerDeleteSet'));
		$d->add('/yourstyle/editor/publishSet', array(&$this, 'handlerPublishSet'), 'post');
		$d->add('/yourstyle/editor/getUsersSets', array(&$this, 'handlerGetUsersSets'));
		$d->add('/yourstyle/editor/getUsersDrafts', array(&$this, 'handlerGetUsersDrafts'));
		$d->add('/yourstyle/editor/getUsersTiles', array(&$this, 'handlerGetUsersTiles'));
		$d->add('/yourstyle/editor/saveBookmark', array(&$this, 'handlerSaveBookmark'), 'post');
		$d->add('/yourstyle/editor/editBookmark/:bid', array(&$this, 'handlerEditBookmark'), 'post');
		$d->add('/yourstyle/editor/deleteBookmark/:bid', array(&$this, 'handlerDeleteBookmark'));
		$d->add('/yourstyle/editor/getBookmarks', array(&$this, 'handlerGetUsersBookmarks'));
		$d->add('/yourstyle/editor/search', array(&$this, 'handlerTilesSearch'));
		$d->add('/yourstyle/editor/getSetsTags/:q', array(&$this, 'handlerSuggestTagsForSet'));
		$d->add('/yourstyle/editor/getGroupTile/:tid', array(&$this, 'handlerGetGroupTile'));
		$d->add('/yourstyle/editor/getColors', array(&$this, 'handlerGetColors'));
		$d->add('/yourstyle/editor/getTilesByColor', array(&$this, 'handlerGetTilesByColor'));
		$d->add('/yourstyle/editor/suggestBrands', array(&$this, 'handlerSuggestBrands'));
		
		$d->add('/yourstyle/editor/getFiltered', array(&$this, 'handlerGetFiltered'));

		$this->tpl->tpl('', '/', 'ajax.php');
		try {
			$d->run();
		} catch (Exception $e) {
			die('invalid params'); // @TODO
			$this->redirect();
		}
	}

	/**
	 * Iconv
	 *
	 * @param mixed $mixed
	 * @return mixed
	 */
	protected function iconv($mixed) {
		$this->tpl->plugins['iconv']->iconv_exchange();
		$mixed = $this->tpl->plugins['iconv']->iconv($mixed);
		$this->tpl->plugins['iconv']->iconv_exchange();
		return $mixed;
	}

	/**
	 * Tranform json
	 *
	 * @param string $json - custom json (default: $_POST['json'])
	 * @return mixed
	 */
	protected function transformJson($json = null) {
		if (is_null($json)) {
			$json = $_POST['json'];
		}

		$json = stripslashes($json);	
		$json = preg_replace('/(\w+):/i', '"\1":', $json);
		$json = str_replace("\"http\"", 'http', $json);
		$this->tpl->plugins['iconv']->iconv_exchange();
		//var_dump($json);
		$json = json_decode($json, true);
		$json = $this->tpl->plugins['iconv']->iconv($json);
		
		$this->tpl->plugins['iconv']->iconv_exchange();
				
		return $json;
	}
	
	protected function getColors() {
	    $colors = array();
	    
	    foreach(YourStyle_BackEnd::$humanColors as $hex => $color) {
	    	$colors[] = array('val' => $hex, 'en' => $color['en'], 'ru' => $color['ru']);
	    }
	    
	    return $colors;	     
	}
}
