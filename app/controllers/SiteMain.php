<?php
/**
 * User: anubis
 * Date: 16.09.13 15:11
 */

namespace popcorn\app\controllers;

use popcorn\model\posts\PostFactory;

class SiteMain extends GenericController {
    
    const StartPage    = 'startPage';
	const NewsItemPage = 'newsItemPage';



    public function startPage() {

		$this->getTwig()->display('MainPage.twig',array('showSidebar'=>false));

		/*
		$topCornNews = PostFactory::getPosts(0,7);



		#print '<pre>'.print_r($topCornNews,true).'</pre>';

		$this->addData(array(
			'topCornNews' => $topCornNews
		));

		*/

        //
//		$topNews = PostFactory::getTopPosts(6);
        //$news = PostFactory::getPosts(0, 6);
        //

        //TODO $freezeFrames = PostFactory::getFreezeFrames(4);
    }

	function newsItemPage($newsId){

		$item = PostFactory::getPost($newsId);

		#print '<pre>'.print_r($item,true).'</pre>';

		$this->addData(array(
			'newsItem' => $item
		));

		$this->template('news');

	}



}