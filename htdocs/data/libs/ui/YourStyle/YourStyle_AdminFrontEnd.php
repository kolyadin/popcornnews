<?php

require_once 'YourStyle_Factory.php';
require_once 'YourStyle_EditorAPI.php';

/**
 * Class YourStyle_AdminFrontEnd
 * Date begin: 04.05.2011
 *
 * AdminFrontEnd
 *
 * @package popcornnews
 * @author Azat Khuzhin
 */
class YourStyle_AdminFrontEnd extends YourStyle_Factory {
	/**
	 * @var YourStyle_EditorAPI
	 */
	private $editor;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct(user_base_api &$user_lib) {
		parent::__construct($user_lib);
	}

	public function handlerAddRootGroup() {
		$this->tpl->assign('title', 'Добавить главную группу');
		$this->tpl->tpl('', '/manager/yourstyle/', 'add_root_group.php');

		// save
		if ($this->d->isPost()) {
			$title = $this->get_param('title');
			if (!$title) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			$ysRootGroups = new VPA_table_yourstyle_root_groups;
			$ysRootGroups->add($ret, array('title' => $title, 'createtime' => time()));

			$this->url_jump('?type=yourstyle');
		}
	}

	public function handlerEditRootGroup() {
		$this->tpl->tpl('', '/manager/yourstyle/', 'edit_root_group.php');
		$rgid = $this->get_param('rgid');

		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroup = $ysRootGroups->get_first_fetch(array('id' => $rgid));
		if (!$rootGroup) {
			throw new Exception('not found - redirect');
		}
		$this->tpl->assign('rootGroup', $rootGroup);
		$this->tpl->assign('title', 'Редактировать группу ' . $rootGroup['title']);

		// save
		if ($this->d->isPost()) {
			$title = $this->get_param('title');
			if (!$title) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			$ysRootGroups->set($ret, array('title' => $title), $rgid);

			$this->url_jump('?type=yourstyle');
		}
	}

	public function handlerShowRootGroups() {
		$this->tpl->assign('title', 'Список групп');
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroups = $ysRootGroups->get_fetch(null, array('createtime desc'));

		$this->tpl->tpl('', '/manager/yourstyle/', 'root_groups.php');
		$this->tpl->assign('rootGroups', $rootGroups);
	}

	public function handlerAddGroup() {
		$this->tpl->assign('title', 'Добавить категорию');
		$this->tpl->tpl('', '/manager/yourstyle/', 'add_group.php');

		// fetch root groups
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroups = $ysRootGroups->get_fetch(null, array('createtime desc'));

		$this->tpl->tpl('', '/manager/yourstyle/', 'add_group.php');
		$this->tpl->assign('rootGroups', $rootGroups);
		// save
		if ($this->d->isPost()) {
			$title = $this->get_param('title');
			$rgid = $this->get_param('rgid');
			if (!$title || !$rgid) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			$ysGroups = new VPA_table_yourstyle_groups;
			$ysGroups->add($ret, array('title' => $title, 'createtime' => time(), 'rgid' => $rgid));

			$this->url_jump('?type=yourstyle&action=rootGroup&rgid=' . $rgid);
		}
	}

	public function handlerEditGroup() {
		$this->tpl->tpl('', '/manager/yourstyle/', 'edit_group.php');
		$gid = $this->get_param('gid');

		$ysGroups = new VPA_table_yourstyle_groups;
		$group = $ysGroups->get_first_fetch(array('id' => $gid));
		if (!$group) {
			throw new Exception('not found - redirect');
		}
		$this->tpl->assign('group', $group);
		$this->tpl->assign('title', 'Редактировать категорию ' . $group['title']);

		// fetch root groups
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		$rootGroups = $ysRootGroups->get_fetch(null, array('createtime desc'));

		$this->tpl->assign('rootGroups', $rootGroups);
		// save
		if ($this->d->isPost()) {
			$title = $this->get_param('title');
			$rgid = $this->get_param('rgid');
			if (!$title || !$rgid) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			$ysGroups->set($ret, array('title' => $title, 'rgid' => $rgid), $gid);

			$this->url_jump('?type=yourstyle&action=rootGroup&rgid=' . $rgid);
		}
	}

	public function handlerShowRootGroup() {
		$rgid = $this->get_param('rgid');

		$ysGroups = new VPA_table_yourstyle_groups;
		$groups = $ysGroups->get_fetch(array('rgid' => $rgid), array('title asc', 'createtime desc'));
		$this->tpl->assign('title', 'Список групп');

		$this->tpl->tpl('', '/manager/yourstyle/', 'groups.php');
		$this->tpl->assign('groups', $groups);
	}

	public function handlerSearch() {
		$q = $this->get_param('q');
		$page = $this->get_param('page');
		$type = $this->get_param('searchType');
		if ($page < 1) $page = 1;
		$this->tpl->assign('title', 'Поиск по ' . $q);

		$this->tpl->assign('q', $q);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('searchType', $type);

		return call_user_func_array(array(&$this, 'handlerSearch' . $type), array($q, $page));
	}

	public function handlerSearchUsers($q, $page, $perPage = 50) {
		$this->tpl->tpl('', '/manager/yourstyle/', 'search_users.php');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$users = $ysGroupsTiles->getTilesAndSetsForUsers($q, ($page-1)*$perPage, $perPage);
		$num = $ysGroupsTiles->getNumTilesForUsers($q);

		$this->tpl->assign('users', $users);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($num / $perPage));
	}

	public function handlerSearchGroups($q) {
		$this->tpl->tpl('', '/manager/yourstyle/', 'search_groups.php');

		$ysGroups = new VPA_table_yourstyle_groups;
		$groups = $ysGroups->get_fetch(array('q' => $q), array('title'));

		$this->tpl->assign('groups', $groups);
	}

	public function handlerSearchGroupsTiles($q, $page, $perPage = 50) {
		$this->tpl->tpl('', '/manager/yourstyle/', 'search_groups_tiles.php');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tiles = $ysGroupsTiles->getTilesWithUserInfo(null, $q, ($page-1)*$perPage, $perPage);
		$tilesNum = $ysGroupsTiles->get_num_fetch(array('q' => $q));

		$this->tpl->assign('tiles', $tiles);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($tilesNum / $perPage));
	}

	public function handlerShowGroupsTilesByUser($perPage = 50) {
		$uid = $this->get_param('uid');
		$page = $this->get_param('page');
		if ($page < 1) $page = 1;

		$this->tpl->tpl('', '/manager/yourstyle/', 'groups_tiles_by_user.php');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tiles = $ysGroupsTiles->getWithBrands(array('uid' => $uid), array('brand'), ($page-1)*$perPage, $perPage);
		$tilesNum = $ysGroupsTiles->get_num_fetch(array('uid' => $uid));

		$this->tpl->assign('tiles', $tiles);
		$this->tpl->assign('gid', $gid);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($tilesNum / $perPage));
	}

	public function handlerShowGroupsTiles($perPage = 50) {
		$gid = $this->get_param('gid');
		$page = $this->get_param('page');
		if ($page < 1) $page = 1;

		$this->tpl->tpl('', '/manager/yourstyle/', 'group_tiles.php');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tiles = $ysGroupsTiles->getTilesWithUserInfo($gid, null, ($page-1)*$perPage, $perPage);
		$tilesNum = $ysGroupsTiles->get_num_fetch(array('gid' => $gid));

		$this->tpl->assign('tiles', $tiles);
		$this->tpl->assign('gid', $gid);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($tilesNum / $perPage));
	}

	public function handlerEditGroupsTile() {	    
		$tid = $this->get_param('tid');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$ysColors = new VPA_table_yourstyle_tiles_colors_new();
		$tile = $ysGroupsTiles->getWithBrands(array('id' => $tid));

		if (!$tile) {
			throw new Exception('No such tile');
		}
		
		$this->tpl->assign('tile', $tile);

		
		$this->editor->handlerGetGroups();
		$this->tpl->assign('rootGroups', $this->tpl->get_data('data'));

		$this->tpl->tpl('', '/manager/yourstyle/', 'group_tile_edit.php');
		// save
		if ($this->d->isPost()) {
		    	    
		    //colors
		    $ysColors->del_where($ret, array('tid' => $tid));
		    //$colors = $this->get_param('colors');
		    $priority = $this->get_param('priority');

		    $i = 0;
		    
		    foreach ($priority as $c) {
		        $tileColor = array('color' => '#'.$c, 'tid' => $tid, 'priority' => $i);
		        $ysColors->add($ret, $tileColor);
		        $i++;
		    }
		    	    
			$gid = $this->get_param('gid');
			$description = $this->get_param('description');
			$brand = trimd(strip_tags($this->get_param('brand')));
			if (!$brand) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			
			// try to extract brand id
			$ysTilesBrands = new VPA_table_yourstyle_tiles_brands;
			$brandInfo = $ysTilesBrands->get_first_fetch(array('title' => $brand), null, 0, 1);
			if (!$brandInfo) {
				$bid = $ysTilesBrands->add_fetch(array('title' => $brand, 'createtime' => time()));
			} else {
				$bid = $brandInfo['id'];
			}
			
			// @TODO edittime ?
			$ysGroupsTiles->set($ret, array('description' => $description, 'gid' => $gid, 'bid' => $bid, 'color_mode' => 'manual'), $tid);
						// we change gid?
			// need to copy old file to new folder
			if ($gid != $tile['gid']) {
				$source = self::generateUploadTilesPath($tile['gid']) . $tile['image'];
				$dest = self::generateUploadTilesPath($gid) . $tile['image'];

				copy($source, $dest);
			}

			//$referer = $this->get_param('referer');
			$this->url_jump(!empty($referer) ? $referer : '?type=yourstyle&action=groupsTiles&gid=' . $gid);
		}
		
		$colors = YourStyle_BackEnd::$humanColors;
		$tileColors = $ysColors->get_fetch(array('tid' => $tid), array('priority ASC'));
		$gen = $this->get_param('gen');
		
		if($gen) {
		    $image = self::generateUploadTilesPath($tile['gid']).$tile['image'];
		    $tileColors = YourStyle_BackEnd::DetectColors($image);
		
		    foreach ($tileColors['full_data'] as $k => $color) {
		        $colors[$color['hex']]['have'] = true;
		        $tileColors['full_data'][$k]['color'] = $color['hex'];
		    }
		
		    $this->tpl->assign('tileColors', $tileColors['full_data']);
		} else {
		    foreach ($colors as $k => $c) {
		        $colors[$k]['have'] = false;
		    }
		    foreach ($tileColors as $color) {
		        $colors[$color['color']]['have'] = true;
		    }
		    $this->tpl->assign('tileColors', $tileColors);
		}
		$this->tpl->assign('colors', $colors);		
	}

	public function handlerDuplicateGroupsTile() {
		$tid = $this->get_param('tid');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tile = $ysGroupsTiles->getWithBrands(array('id' => $tid));
		if (!$tile) {
			throw new Exception('No such tile');
		}
		$this->tpl->assign('tile', $tile);

		if ($this->d->isPost()) {
			// set old tile, if not uploaded the new one
			$file = $this->get_param('file');
			if (empty($file) || !$file['size']) {
				$_FILES['file'] = array('tmp_name' => self::generateUploadTilesPath($tile['gid']) . $tile['image']);
			}

			$ok = $this->editor->handlerTileUpload(true);
			if ($ok) {
				$referer = $this->get_param('referer');
				$this->url_jump('?type=yourstyle&action=groupsTiles&gid=' . $this->get_param('gid'));
			} else {
				$data = $this->tpl->get_data('data');
				$this->tpl->assign('error', $data['error']);
			}
		}
		$this->editor->handlerGetGroups();
		$this->tpl->assign('rootGroups', $this->tpl->get_data('data'));

		$this->tpl->tpl('', '/manager/yourstyle/', 'group_tile_duplicate.php');
	}

	public function handlerDeleteGroupsTile() {
		$tid = $this->get_param('tid');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$ysGroupsTiles->del($ret, $tid);
		$this->url_jump(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?type=yourstyle');
	}

	public function handlerShowSetsByUser() {
		$uid = $this->get_param('uid');

		$this->tpl->tpl('', '/manager/yourstyle/', 'sets_by_user.php');

		$ysSets = new VPA_table_yourstyle_sets;
		$sets = $ysSets->get_fetch(array('uid' => $uid), array('title'));

		$this->tpl->assign('sets', $sets);
	}

	public function handlerDeleteSet() {
		$sid = $this->get_param('sid');

		$ysSets = new VPA_table_yourstyle_sets;
		if (!$ysSets->del($ret, $sid)) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
			return false;
		}

		$this->url_jump(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?type=yourstyle');
	}

	public function handlerUploadTile() {
	    $this->editor->handlerGetGroups();
	    $this->tpl->assign('rootGroups', $this->tpl->get_data('data'));
	    
		// save
		if ($this->d->isPost()) {
			$ok = $this->editor->handlerTileUpload(true);
			if ($ok) {
				//$referer = $this->get_param('referer');
				$this->url_jump('?type=yourstyle&action=editGroupsTile&gen=true&tid=' . $this->tpl->get_data('data'));
				return;
			} else {
				$data = $this->tpl->get_data('data');
				$this->tpl->assign('error', $data['error']);
			}
		}
		
        $ysBrands = new VPA_table_yourstyle_tiles_brands_new();
		$brands = $ysBrands->get_fetch(array(), 'title ASC');
		
		
		$this->tpl->assign('brands', $brands);
		$this->tpl->tpl('', '/manager/yourstyle/', 'upload_tile.php');
	}	

	public function handlerDeleteRootGroup() {
		$rgid = $this->get_param('rgid');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		if (!$ysGroupsTiles->del_where($ret, array('rgid' => $rgid))) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
			return false;
		}
		$ysGroups = new VPA_table_yourstyle_groups;
		if (!$ysGroups->del_where($ret, array('rgid' => $rgid))) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
			return false;
		}
		$ysRootGroups = new VPA_table_yourstyle_root_groups;
		if (!$ysRootGroups->del($ret, $rgid)) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
		}

		$this->url_jump(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?type=yourstyle');
	}

	public function handlerDeleteGroup() {
		$gid = $this->get_param('gid');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		if (!$ysGroupsTiles->del_where($ret, array('gid' => $gid))) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
			return false;
		}
		$ysGroups = new VPA_table_yourstyle_groups;
		if (!$ysGroups->del($ret, $gid)) {
			$this->tpl->assign('error', 'Ошибка при удаление записи из БД');
			return false;
		}

		$this->url_jump(!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?type=yourstyle');
	}

	public function handlerEditTileBrand() {
		
	    $this->tpl->tpl('', '/manager/yourstyle/', 'edit_tile_brand.php');
		$bid = $this->get_param('bid');

		$ysTilesBrands = new VPA_table_yourstyle_tiles_brands_new();
		$brand = $ysTilesBrands->get_first_fetch(array('id' => $bid));
		if (!$brand) {
			throw new Exception('not found - redirect');
		}
		if(empty($brand['logo'])) {
		    $brand['logo'] = "/img/fan-3.jpg";
		}
		else {
		    $brand['logo'] = self::getWwwUploadBrandsPath($bid, $brand['logo'], '140x140');
		}
		$this->tpl->assign('brand', $brand);

		// save
		if ($this->d->isPost()) {
			$title = $this->get_param('title');
			$logo = $this->get_param('file');
			$descr = $this->get_param('descr');
			if (!$title) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			
			if($logo['size'] > 0) {
			
	    		$logo_file = random_file_name(self::generateUploadBrandsPath($bid), 'png');
    			$logo_file = self::generateUploadBrandsPath($bid).$logo_file;			
			
		        $ok = copy($logo['tmp_name'], $logo_file);
			    if (!$ok) {
				    throw new Exception();
			    }
			} else {
			    $logo_file = null;
			}
			
			$ysTilesBrands->set_fetch(array('title' => $title, 'logo' => basename($logo_file), 'descr' => $descr), $bid);

			$referer = $this->get_param('referer');
			$this->url_jump(!empty($referer) ? $referer : '?type=yourstyle&action=tilesBrands');
		}
	}

	public function handlerAddTileBrand() {
		$this->tpl->tpl('', '/manager/yourstyle/', 'add_tile_brand.php');		

		// save
		if ($this->d->isPost()) {
			$title = trimd($this->get_param('title'));
			$logo = $this->get_param('file');
			$descr = $this->get_param('descr');
			
			if (!$title || !$logo) {
				$this->tpl->assign('error', 'Не все поля заполнены');
				return false;
			}
			
			$ysTilesBrands = new VPA_table_yourstyle_tiles_brands;
			$bid = $ysTilesBrands->add_fetch(array('title' => $title, 'descr' => $descr));

			$logo_file = random_file_name(self::generateUploadBrandsPath($bid), 'png');
			$logo_file = self::generateUploadBrandsPath($bid).$logo_file;			
			
		    $ok = copy($logo['tmp_name'], $logo_file);
			if (!$ok) {
				throw new Exception();
			}

			$ysTilesBrands->set_fetch(array('logo' => basename($logo_file)), $bid);
			
			$referer = $this->get_param('referer');
			$this->url_jump(!empty($referer) ? $referer : '?type=yourstyle&action=tilesBrands');
			return;
		}
	}

	public function handlerDeleteTileBrand() {
		$bid = $this->get_param('bid');

		$ysTilesBrands = new VPA_table_yourstyle_tiles_brands;
		$ysTilesBrands->del($ret, $bid);

		$referer = $_SERVER['HTTP_REFERER'];
		$this->url_jump(!empty($referer) ? $referer : '?type=tilesBrands');
	}
	
	public function handlerShowBrandTiles($perPage = 50) {
		$bid = $this->get_param('bid');
		$page = $this->get_param('page');
		if ($page < 1) $page = 1;

		$this->tpl->tpl('', '/manager/yourstyle/', 'brand_tiles.php');

		$ysGroupsTiles = new VPA_table_yourstyle_groups_tiles;
		$tiles = $ysGroupsTiles->getTilesWithUserInfoAndGroups($bid, null, ($page-1)*$perPage, $perPage);
		$tilesNum = $ysGroupsTiles->get_num_fetch(array('bid' => $bid));

		$this->tpl->assign('tiles', $tiles);
		$this->tpl->assign('bid', $bid);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($tilesNum / $perPage));
	}

	public function handlerShowTilesBrands($perPage = 50) {
		$page = $this->get_param('page');
		if ($page < 1) $page = 1;

		$this->tpl->tpl('', '/manager/yourstyle/', 'tiles_brands.php');

		$ysTilesBrands = new VPA_table_yourstyle_tiles_brands;
		$brands = $ysTilesBrands->get_fetch(null, array('title'), ($page-1)*$perPage, $perPage);
		$brandsNum = $ysTilesBrands->get_num_fetch();

		$this->tpl->assign('brands', $brands);
		$this->tpl->assign('page', $page);
		$this->tpl->assign('perPage', $perPage);
		$this->tpl->assign('pages', ceil($brandsNum / $perPage));
	}
	
	//for autocomplete in admin
	public function handlerSuggestBrands() {
		$q = $this->iconv(strip_tags($this->get_param('q')));
		if(empty($q)) exit;

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
		$this->tpl->tpl('', '/', 'ajax.php');
	}
	
	public function handlerSetDefaultGroupTile() {
	    $tid = $this->get_param('tid');
	    $gid = $this->get_param('gid');
	    
	    $ysGroups = new VPA_table_yourstyle_groups();
	    $ysGroups->set($ret, array('tid' => $tid), $gid);	    
	    
	    $this->url_jump('?type=yourstyle&action=groupsTiles&gid='.$gid);
	}
	
	public function handlerSetDefaultRootGroupTile() {
	    $tid = $this->get_param('tid');
	    $gid = $this->get_param('gid');
	    
	    $ysGroups = new VPA_table_yourstyle_groups();
	    $rgid = $ysGroups->get_first_fetch(array('id' => $gid));
	    $rgid = $rgid['rgid'];
	    
	    $ysRootGroup = new VPA_table_yourstyle_root_groups();
	    
	    $ysRootGroup->set($ret, array('tid' => $tid), $rgid);
	    
	    $this->url_jump('?type=yourstyle&action=groupsTiles&gid='.$gid);
	}

	/**
	 * Get request Uri
	 *
	 * @return string
	 */
	protected function getRequestUrl() {
		$requestUri = preg_replace('@(&|\?).*@', '', $this->get_param('action'));
		if (!$requestUri || $requestUri == 'default') return '/';

		if (substr($requestUri, 0, 1) != '/') $requestUri = '/' . $requestUri;
		if (substr($requestUri, -1) == '/') $requestUri = substr($requestUri, 0, -1);

		return $requestUri;
	}
	
	protected function iconv($mixed) {
		$this->tpl->plugins['iconv']->iconv_exchange();
		$mixed = $this->tpl->plugins['iconv']->iconv($mixed);
		$this->tpl->plugins['iconv']->iconv_exchange();
		return $mixed;
	}

	/**
	 * Init routes
	 *
	 * @return void
	 */
	protected function _initRoutes() {
		$this->editor = new YourStyle_EditorAPI($this->user_lib, true);
		$this->tpl->tpl('', '/manager/yourstyle/', 'default.php');

		require_once 'akDispatcher/akDispatcher.class.php';
		$this->d = $d = akDispatcher::getInstance('/', $this->getRequestUrl(), 'WINDOWS-1251');
		$d->functionReturnsContent = false;

		$d->add('/', array(&$this, 'handlerShowRootGroups'));
		$d->add('/addGroup', array(&$this, 'handlerAddGroup'), 'both');
		$d->add('/editGroup', array(&$this, 'handlerEditGroup'), 'both');
		$d->add('/addRootGroup', array(&$this, 'handlerAddRootGroup'), 'both');
		$d->add('/editRootGroup', array(&$this, 'handlerEditRootGroup'), 'both');
		$d->add('/rootGroup', array(&$this, 'handlerShowRootGroup'));
		$d->add('/groupsTilesByUser', array(&$this, 'handlerShowGroupsTilesByUser'));
		$d->add('/search', array(&$this, 'handlerSearch'));
		$d->add('/groupsTiles', array(&$this, 'handlerShowGroupsTiles'));
		$d->add('/editGroupsTile', array(&$this, 'handlerEditGroupsTile'), 'both');
		$d->add('/deleteGroupsTile', array(&$this, 'handlerDeleteGroupsTile'));
		$d->add('/setsByUser', array(&$this, 'handlerShowSetsByUser'));
		$d->add('/deleteSet', array(&$this, 'handlerDeleteSet'));
		$d->add('/uploadTile', array(&$this, 'handlerUploadTile'), 'both');
		$d->add('/deleteRootGroup', array(&$this, 'handlerDeleteRootGroup'));
		$d->add('/deleteGroup', array(&$this, 'handlerDeleteGroup'));
		$d->add('/duplicateGroupsTile', array(&$this, 'handlerDuplicateGroupsTile'), 'both');
		$d->add('/editTileBrand', array(&$this, 'handlerEditTileBrand'), 'both');
		$d->add('/addTileBrand', array(&$this, 'handlerAddTileBrand'), 'both');
		$d->add('/deleteTileBrand', array(&$this, 'handlerDeleteTileBrand'));
		$d->add('/brandTiles', array(&$this, 'handlerShowBrandTiles'));
		$d->add('/tilesBrands', array(&$this, 'handlerShowTilesBrands'));
		$d->add('/suggestBrands', array(&$this, 'handlerSuggestBrands'));
		$d->add('/setDefaultGroupTile', array(&$this, 'handlerSetDefaultGroupTile'));
		$d->add('/setDefaultRootGroupTile', array(&$this, 'handlerSetDefaultRootGroupTile'));

		try {
			$d->run();
		} catch (Exception $e) {
			$this->url_jump('?type=yourstyle');
		}
	}
}
