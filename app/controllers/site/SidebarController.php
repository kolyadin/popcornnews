<?php
namespace popcorn\app\controllers\site;

use popcorn\app\controllers\ControllerInterface;
use popcorn\app\controllers\GenericController;
use popcorn\model\dataMaps\NewsCommentDataMap;
use popcorn\model\dataMaps\NewsPostDataMap;
use popcorn\model\dataMaps\TagDataMap;
use popcorn\model\posts\PostCategory;
use popcorn\model\posts\PostFactory;
use popcorn\model\tags\TagFactory;

/**
 * Class SidebarController
 * @package popcorn\app\controllers\site
 */
class SidebarController extends GenericController implements ControllerInterface {

	private $twigData = [];

	public function getRoutes() {

	}

	public function build(){
		$this->buildTags();

		$this->getTwig()->addGlobal('sidebar',$this->twigData);
	}

	private function buildTags(){
		$tagDataMap = new TagDataMap();
		$tags = $tagDataMap->getTop();

		usort($tags,function($a,$b){
			return strcmp($a['name'],$b['name']);
		});

		$tagMax = max($tags);
		$tagMin = min($tags);

		foreach ($tags as &$tag){
			$color = ceil( ($tag['overall']*7) / $tagMax['overall'] );
			$color = 7-$color ?: 1;

			$tag['color'] = $color;
		}

		$this->twigData['tags'] = $tags;
	}

}