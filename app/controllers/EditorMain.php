<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 15:22
 */

namespace popcorn\app\controllers;

use popcorn\lib\GenPic;
use popcorn\model\content\Image;
use popcorn\model\content\ImageFactory;
use popcorn\model\voting\VotingFactory;
use popcorn\model\content\ImageBuilder;
use popcorn\model\dataMaps\ImageDataMap;
use popcorn\model\persons\PersonBuilder;


use popcorn\model\persons\Person;
use popcorn\model\persons\PersonFactory;
use popcorn\model\content\NullImage;
use popcorn\model\voting\TenVoting;

class EditorMain extends GenericController {

    const StartPage       = 'startPage';

	const PersonList        = 'personList';
	const PersonForm        = 'personForm';
	const PersonFormSave    = 'personFormSave';
	const PersonQuickRemove = 'personQuickRemove';
	const PersonQuickSearch = 'personQuickSearch';




	final protected function ajaxReturnFail()
	{
		die(json_encode(array(
			'status' => 'fail'
		)));
	}


	public function personQuickRemove(){
		$id = (int)$this->getRequest()->get('personId');

		if (!isset($id)) $this->ajaxReturnFail();

		PersonFactory::removePerson(array('id'=>$id));

		die(json_encode(array(
			'status' => 'success'
		)));
	}

	public function personQuickSearch(){
		$found = PersonFactory::searchPersons($this->getRequest()->get('q'), 0, 50);

		die( json_encode($found) );
	}

	public function personList(){

		$persons = PersonFactory::getPersons();

		$this
			->getTwig()
			->display('person/PersonList.twig'
				,array(
					'persons' => $persons
				)
			)
		;
	}

	/**
	 * @param int $personId
	 */
	public function personForm($personId = 0){

		$tpl = array();

		//Режим редактирования
		if ($personId > 0) {

			$personData = PersonFactory::getPerson($personId);

			$tpl['form'] = array(
				'edit'   => $personId,
				'person' => $personData
			);

		//Режим создания новой персоны
		} else {

		}

		$this->getTwig()->display('person/PersonForm.twig',$tpl);

	}

	/**
	 * Сохранение новой или отредактированной персоны
	 */
	public function personFormSave(){

		$request = $this->getSlim()->request();

		$personId = (int)$request->post('edit');

		$img = new NullImage();

		/*
		//Режим редактирования, меняем фотку
		if ($request->post('mainPhoto')){
			$img = new Image();

			if ($personId)
				$img->setId($personData->getPhoto()->getId());

			$img->setName($request->post('mainPhoto'));
			$img->setSource($request->post('photoSource'));

			$dataMap = new ImageDataMap();
			$dataMap->save($img);

			$person->setPhoto($img);
		}*/

		//


		if ($request->post('mainPhoto')){

			$img = new Image();
			$img->setName($request->post('mainPhoto'));
			$img->setTitle('');
			$img->setDescription('');
			$img->setSource($request->post('photoSource'));
			$img->setZoomable(false);
			ImageFactory::save($img);

			//ImageFactory::($img);
		}

		//Редактирование персоны
		if ($personId){

			$person = PersonFactory::getPerson($personId);

			$person->setName($request->post('name'));
			$person->setEnglishName($request->post('englishName'));
			$person->setGenitiveName($request->post('genitiveName'));
			$person->setPrepositionalName($request->post('prepositionalName'));
			$person->setInfo($request->post('info'));
			$person->setSource($request->post('source'));
			$person->setNameForBio($request->post('nameForBio'));
			$person->setPhoto($img);


			PersonFactory::savePerson($person);


			#echo '<pre>',print_r($person,true),'</pre>';


			/*
			

			$person->setLook(VotingFactory::createTenVoting());
			$person->setStyle(VotingFactory::createTenVoting());
			$person->setTalent(VotingFactory::createTenVoting());
*/

			/*$person->setPhoto($this->photo);
			$person->setBirthDate($this->birthDate);
			$person->setShowInCloud($this->showInCoud);
			$person->setSex($this->sex);
			$person->setIsSinger($this->isSinger);
			$person->setAllowFacts($this->allowFacts);
			$person->setIsWidgetAvailable($this->isWidgetAvailable);
			$person->setWidgetPhoto($this->widgetPhoto);
			$person->setWidgetFullPhoto($this->widgetFullPhoto);
			$person->setVkPage($this->vkPage);
			$person->setTwitterLogin($this->twitterLogin);
			$person->setPageName($this->pageName);
			$person->setNameForBio($this->nameForBio);
			($this->publish) ? $person->publish() : $person->unPublish();

			$name = str_replace('-', '_', $this->englishName);
			$name = str_replace('&dash;', '_', $name);
			$name = str_replace(' ', '-', $name);
			$person->setUrlName($name);


			*/


		}else{

			//region Создание новой персоны

			$person = new Person();

			$person->setName($request->post('name'));
			$person->setEnglishName($request->post('englishName'));
			$person->setGenitiveName($request->post('genitiveName'));
			$person->setPrepositionalName($request->post('prepositionalName'));
			$person->setInfo($request->post('info'));
			$person->setSource($request->post('source'));
			$person->setBirthDate(new \DateTime(vsprintf('%3$04u-%2$02u-%1$02u', sscanf($request->post('birthDate'),'%02u.%02u.%04u'))));
			$person->setVkPage($request->post('vkPage'));
			$person->setTwitterLogin($request->post('twitterLogin'));

			$person->setLook(VotingFactory::createTenVoting());
			$person->setStyle(VotingFactory::createTenVoting());
			$person->setTalent(VotingFactory::createTenVoting());

			//$person->setPhoto($img);

			$person->setShowInCloud( $request->post('showInCloud') ? true : false );
			$person->setSex( $request->post('sex') ? Person::FEMALE : Person::MALE );
			$person->setIsSinger( $request->post('isSinger') ? true : false );
			$person->setIsWidgetAvailable( $request->post('isWidgetAvailable') ? true : false );
			$person->setAllowFacts( $request->post('allowFacts') ? true : false );

			PersonFactory::savePerson($person);

			//endregion

		}

		$this->getSlim()->redirect(sprintf('/editor/persons/%u',$person->getId()));
		die;
	}

    public function startPage() {
        $this->addData(array('hello' => 'hello, world!'));
        $this->template('generic');
    }

}