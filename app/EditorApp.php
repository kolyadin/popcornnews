<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 14:59
 */

namespace popcorn\app;

use popcorn\app\commands\editor\EditorMainCommand;
use popcorn\app\commands\editor\LoginCommand;
use popcorn\app\controllers\DictionaryController;
use popcorn\app\controllers\editor\EditorDictionary;
use popcorn\app\controllers\EditorMain;
use popcorn\app\controllers\editor\EditorNews;

use popcorn\app\controllers\Login;
use popcorn\app\ext\Authorization;
use Slim\Extras\Views\Twig;
use Slim\Slim;
use popcorn\lib\ImageGenerator;

class EditorApp extends Application {

    public function __construct() {

        parent::__construct(array(
			'templates.path' => '../templates/editor',
			'debug'          => true,
			'mode'           => 'development'
		));

        $this->getSlim()->add(new Authorization());
    }

    protected function initRoutes() {
        parent::initRoutes();

		//Главная админки
        $this->addGetCommand('/', new EditorMainCommand(EditorMain::StartPage));


		$this->addPostCommand('/uploadImage/', new Command('DictionaryController', DictionaryController::UploadImage));
		$this->addPostCommand('/cropImage/', new Command('DictionaryController', DictionaryController::CropImage));


		//Новости
		$this->addNewsCommands();

		//Персоны
		$this->addPersonCommands();

		//Справочники
		$this->addDictCommands();

		//Авторизация/выход, etc...
        $this->addLoginCommands();
        // example - /editor/getList/pn_dictionary/name:asc,date:desc

        $this->addGetCommand('/getList/:table(/:orders)', new Command('DictionaryController', DictionaryController::GetList));

		///editor/getListByField/pn_persons/name=Лопез
		$this->addGetCommand('/getListByField/:table/:field', new Command('DictionaryController', DictionaryController::GetListByField));
    }

	private function addNewsCommands(){

		//Список новостей
		$this->addGetCommand('/news'              , new Command('editor\\EditorNews' , EditorNews::NewsList)        );

		//Форма добавления новости
		$this->addGetCommand('/news/new'          , new Command('editor\\EditorNews' , EditorNews::NewsForm)        );

		//Ajax запросы
		$this->addGetCommand('/news/quickSearch'  , new Command('editor\\EditorNews' , EditorNews::NewsQuickSearch) );
		$this->addGetCommand('/news/quickRemove'  , new Command('editor\\EditorNews' , EditorNews::NewsQuickRemove) );

		//Сохранение созданной новости
		$this->addPostCommand('/news/new/save'    , new Command('editor\\EditorNews' , EditorNews::NewsFormSave)    );

		$this->addGetCommand('/news/:id'          , new Command('editor\\EditorNews' , EditorNews::NewsForm)        );

	}

	private function addPersonCommands(){


		$this->getSlim()->get('/persons', array(new EditorMain($this),'personList'));


		//$this->addGetCommand(  '/persons'              , new EditorMainCommand(EditorNews::PersonList)        );
		$this->addGetCommand(  '/persons/new'          , new EditorMainCommand(EditorNews::PersonForm)        );
		$this->addPostCommand( '/persons/new/save'     , new EditorMainCommand(EditorNews::PersonFormSave)    );
		$this->addGetCommand(  '/persons/:id'          , new EditorMainCommand(EditorNews::PersonForm)        )->conditions(array('id' => '[1-9]+'));
		$this->addGetCommand(  '/persons/quickRemove'  , new EditorMainCommand(EditorNews::PersonQuickRemove) );
		$this->addGetCommand(  '/persons/quickSearch'  , new EditorMainCommand(EditorNews::PersonQuickSearch) );

	}

	private function addDictCommands(){
		//AJAX удаление справочника
		$this->addGetCommand(  '/dict/quickRemove'      , new Command('editor\\EditorDictionary', EditorDictionary::DictQuickRemove));

		$this->addGetCommand(  '/dict/:entityName'      , new Command('editor\\EditorDictionary', EditorDictionary::DictList)  );
		$this->addGetCommand(  '/dict/:entityName/new'  , new Command('editor\\EditorDictionary', EditorDictionary::DictNew)   );
		$this->addPostCommand( '/dict/:entityName/save' , new Command('editor\\EditorDictionary', EditorDictionary::DictSave)  );

		//Редактирование справочника
		$this->addGetCommand( '/dict/:entityName/:id'   , new Command('editor\\EditorDictionary', EditorDictionary::DictEdit)  )->conditions(array('id' => '[1-9]+'));
	}

    private function addLoginCommands() {
        $this->addGetCommand('/login/(:error)', new LoginCommand(Login::LoginForm));
        $this->addPostCommand('/login/', new LoginCommand(Login::Login));
        $this->addGetCommand('/logout/', new LoginCommand(Login::Logout));
    }

}