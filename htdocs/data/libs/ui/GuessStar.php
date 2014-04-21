<?php

/**
 * @author Azat Khuzhin
 *
 * Guess star by pic
 */

require_once dirname(__FILE__) . '/user.lib.php';

class GuessStar {
    
    private $oldIncPath;
	/**
	 * Path to images
	 * For web
	 *
	 * @var string
	 */
	const imagePath = 'http://popcornnews.ru/tmp_photos/guess_star/';
	/**
	 * Start time in seconds
	 *
	 * @var int
	 */
	const startTime = 50;
	/**
	 * Plus for time if answer is wrong
	 *
	 * @var int
	 */
	const wrongAnswer = -10;
	/**
	 * Plus for time if answer is right
	 *
	 * @var int
	 */
	const rightAnswer = 3;
	/**
	 * Game data
	 * Link to _SESSION array
	 *
	 * @var array
	 */
	protected $gameData;
	/**
	 * Link to questions in gameData
	 *
	 * @var array
	 */
	protected $gameQuestions;
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
	 * VPA_Games_GuessStar
	 *
	 * @var object of VPA_Games_GuessStar
	 */
	protected $questionObject;
	/**
	 * VPA_Games_GuessStarStatistic
	 *
	 * @var VPA_Games_GuessStarStatistic
	 */
	protected $statisticObject;
	/**
	 * User info
	 *
	 * @var array
	 */
	protected $user;
	/**
	 * Errors
	 * Code => Description
	 *
	 * @var array
	 */
	static $errors = array(
		'default' => 'Произошла ошибка',
		'alreadyPlay' => 'Вы уже играете',
		'notPlay' => 'Вы еще не начали играть',
		'hackCheck' => 'Так нельзя',
		'alreadyGetFiftyFifty' => 'Вы уже брали подсказку "50х50"',
		'alreadyGetSkipQuestion' => 'Вы уже брали подсказку "пропустить кадр"',
		'alreadyGet5Seconds' => 'Вы уже брали подсказку "5 секунд"',
		'noMoreQuestions' => 'Вы отгадали все кадры! Поздравлям',
		'wrongAnwser' => 'Ответ не верный, правильны ответ "%s". Спасибо за игру', // example with sprintf
		'limitExhausted' => 'Лимит исчерпан.<br />За одну неделю можно играть максимум 3 раза.',
	);


	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function  __construct(user_base_api &$user_lib) {
		// @TODO
		#if (!VPA_template::isDeveloper()) {
		#	die;
		#}

		// Base
		$this->sess = session::getInstance();
		$this->memcache = VPA_memcache::getInstance();
		$this->tpl = VPA_template::getInstance();
		$this->sql = VPA_sql::getInstance();
		$this->questionObject = new VPA_table_Games_GuessStar;
		$this->statisticObject = new VPA_table_Games_GuessStarStatistic;
		$this->user = $this->sess->restore_var('sess_user');
		$this->user_lib = $user_lib;
		// not auth
		if (!$this->user) return $this->user_lib->handler_show_error('no_login');

		// Init game data
		$this->gameData = &$this->sess->restore_link_to_var('game_GuessStar');		
		/*if (!isset($this->gameData['questions'])) $this->gameData['questions'] = array();
		$this->gameQuestions = &$this->gameData['questions'];*/

		$this->oldIncPath = set_include_path(get_include_path() . ':' . AKLIB_DIR);

		require_once 'akDispatcher/akDispatcher.class.php';
		$d = akDispatcher::getInstance('/', null, 'WINDOWS-1251');
		$d->functionReturnsContent = false;

		$d->add('/games/guess_star/get', array(&$this, 'getQuestionHTML'));
		$d->add('/games/guess_star/save/:time', array(&$this, 'saveResult'));
		$d->add('/games/guess_star/fiftyFifty', array(&$this, 'fiftyFifty'));
		$d->add('/games/guess_star/skipQuestion', array(&$this, 'skipQuestion'));
		$d->add('/games/guess_star/get5Seconds', array(&$this, 'get5Seconds'));
		$d->add('/games/guess_star/isAnswerRight/:anwser', array(&$this, 'isAnswerRight'));
		$d->add('/games/guess_star/instructions', array(&$this, 'instructions'));
	    $d->add('/games/guess_star/instructions/user', array(&$this, 'instructions'));
		$d->add('/games/guess_star/instructions/profile', array(&$this, 'instructionsProfile'));
		$d->add(array('/games/guess_star/rating', '/games/guess_star/rating/page/:page'), array(&$this, 'verboseTop'));
		$d->add('/games/guess_star/rating/profile', array(&$this, 'verboseTop'));
		$d->add('/games/guess_star/start', array(&$this, 'start'));

		try {
			$d->run();
		} catch (akException $e) {
			$this->user_lib->redirect();
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		set_include_path($this->oldIncPath);
	}

	/**
	 * Get question
	 */
	protected function getQuestion() {
	    $data = array();
		// update lastUpdate time
		$this->gameData['lastUpdate'] = time();
        
		$this->gameQuestions = $this->gameData['questions'];
		
		// get already anwsering question
		if (count($this->gameQuestions) > 0) {
			$ids = array();
			$countRight = 0;

			foreach ($this->gameQuestions as $question) {
				if (isset($question['amIAnwserRight']) && $question['amIAnwserRight']) $countRight++;
				$ids[] = $question['id'];
			}
		}

		// detect difficulty
		if (isset($countRight)) {
			if ($countRight < 5) $difficulty = 1;
			elseif ($countRight >= 5 && $countRight < 10) $difficulty = 2;
			elseif ($countRight >= 10 && $countRight < 15) $difficulty = 3;
			elseif ($countRight >= 15 && $countRight < 20) $difficulty = 4;
			else $difficulty = 5;
		}

		if (isset($ids) && isset($countRight)) {
			// such difficult, or more
			$this->questionObject->get($result, array('ids_not' => join(',', $ids), 'difficulty_more' => $difficulty), array('difficulty ASC, RAND()'), 0, 1);
			// no such questions, try such difficult or less
			if (!$result->len()) $this->questionObject->get($result, array('ids_not' => join(',', $ids), 'difficulty_less' => $difficulty), array('difficulty DESC, RAND()'), 0, 1);

			$result->get_first($data);
		} else {
			$this->questionObject->get($result, null, array('difficulty ASC, RAND()'), 0, 1);
			$result->get_first($data);
		}

		if (!$data) {
			return $this->showError('noMoreQuestions');
		}		
		
		$keys = array('star_version1','star_version2','star_version3','star_version4');
		shuffle($keys);
		$ans = array();
		foreach ($keys as $key) {
		    $ans[$key] = $data[$key];
		}
		
		$preOut = $data;
		
		unset($preOut['star_version1'], $preOut['star_version2'], $preOut['star_version3'], $preOut['star_version4']);		
		
		$preOut = array_merge($preOut, $ans);		
		
		$data = $preOut;
		
		$this->gameData['questions'][] = $preOut;

		$outputData = $preOut;
		unset($outputData['id'], $outputData['right_version']);
        
		return array('result' => $outputData);
	}

	/**
	 * Get question HTML
	 */
	public function getQuestionHTML() {
		$this->tpl->assign('data', $this->getQuestion());
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * Save results
	 */
	public function saveResult($time) {
		// not playing
		if (!$this->alreadyPlay()) return $this->showError('notPlay');
		
		$this->sess->delete_var('gs_started');
		$this->gameQuestions = $this->gameData['questions'];
		
		// count right answers
		$answersRight = $answersWrong = 0;
		foreach ($this->gameQuestions as $question) {
			if (isset($question['amIAnwserRight']) && $question['amIAnwserRight']) $answersRight++;
			else $answersWrong++;
		}

		if ($this->hackCheck($time, $answersRight, $answersWrong)) {
			// flush game data
			$this->endGame();

			return $this->showError('hackCheck');
		}

		$result = $this->statisticObject->add($ret, array(
			'uid' => $this->user['id'],
			'answers_right' => $answersRight,
			'answers_wrong' => $answersWrong,
			'time' => $time,
			'createtime' => time(),
			'ip' => (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']),
			'hint_skip_question' => (isset($this->gameData['skipQuestion']) && $this->gameData['skipQuestion']),
			'hint_fifty_fifty' => (isset($this->gameData['fiftyFifty']) && $this->gameData['fiftyFifty']),
			'hint_get_5_seconds' => (isset($this->gameData['get5Seconds']) && $this->gameData['get5Seconds']),
		));
		// flush game data
		$this->endGame();
		// can`t save results
		if (!$result) return $this->showError();
		// get best game for user place
		$allUserGames = $this->statisticObject->getUserBestGame($this->user['id']);

		$places = $this->statisticObject->getUserPlace($allUserGames['answers_right']);
	    $place = 'Нет';
		foreach ($places as $i => $p) {
		    if($p['uid'] == $this->user['id']) {
		        $place = $i;
		        break;
		    }
		}
		
		$outputData = array(
			'result' => true,
			'allUserGamesPlace' => $place,
			'points' => $answersRight,
			'pointsWord' => $this->tpl->plugins['declension']->get($answersRight, 'очко', 'очка', 'очков'), // to not render word in JS
		);
		$this->tpl->assign('data', $outputData);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * End game
	 * If user close window, of go to another tab
	 *
	 * @return void
	 */
	protected function endGame() {
		// flush
		$this->gameData = array();
		$this->sess->delete_var('game_GuessStar');
	}

	/**
	 * Check is anwser right
	 *
	 * @param int $anwser - user anwser
	 */
	public function isAnswerRight($anwser) {
	    
		// not playing
		if (!$this->alreadyPlay()) return $this->showError('notPlay');
		// update lastUpdate time
		$this->gameData['lastUpdate'] = time();

		$this->gameQuestions = $this->gameData['questions'];
		
		$last = &$this->gameQuestions[count($this->gameQuestions)-1];
		if (!$last) return $this->showError();
		$lastID = count($this->gameQuestions) - 1;

		if ($anwser != $last['right_version']) {
			//$last['amIAnwserRight'] = false;
			$this->gameData['questions'][$lastID]['amIAnwserRight'] = false;
			$result = array('result' => false);
		} else {
			//$last['amIAnwserRight'] = true;
			$this->gameData['questions'][$lastID]['amIAnwserRight'] = true;
			$result = array('result' => true);
		}
		$this->tpl->assign('data', $result);
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * Delete 2 wrong answers (50x50)
	 */
	public function fiftyFifty() {
		// not playing
		if (!$this->alreadyPlay()) return $this->showError('notPlay');
		// update lastUpdate time
		$this->gameData['lastUpdate'] = time();
		$this->gameQuestions = $this->gameData['questions'];

		// already get this hint
		if (isset($this->gameData['fiftyFifty']) && $this->gameData['fiftyFifty']) return $this->showError('alreadyGetFiftyFifty');
		// update status of hint
		$this->gameData['fiftyFifty'] = true;

		$last = &$this->gameQuestions[count($this->gameQuestions)-1];
		if (!$last) return $this->showError();
		// for delete
		$first = 0;
		while ($first == 0 || $first == (int)$last['right_version']) $first = rand(1, 4);

		$second = 0;
		while ($second == 0 || $second == $first || $second == (int)$last['right_version']) $second = rand(1, 4);		
		
		$this->tpl->assign('data', array('result' => array($first, $second)));
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * Skip question
	 */
	public function skipQuestion() {
		// not playing
		if (!$this->alreadyPlay()) return $this->showError('notPlay');
		// update lastUpdate time
		$this->gameData['lastUpdate'] = time();
		$this->gameQuestions = $this->gameData['questions'];

		// already get this hint
		if (isset($this->gameData['skipQuestion']) && $this->gameData['skipQuestion']) return $this->showError('alreadyGetSkipQuestion');
		// update status of hint
		$this->gameData['skipQuestion'] = true;

		$this->tpl->assign('data', array('result' => array('result' => true)));
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * Get 5 seconds +
	 */
	public function get5Seconds() {
		// not playing
		if (!$this->alreadyPlay()) return $this->showError('notPlay');
		// update lastUpdate time
		$this->gameData['lastUpdate'] = time();
		$this->gameQuestions = $this->gameData['questions'];

		// already get this hint
		if (isset($this->gameData['get5Seconds']) && $this->gameData['get5Seconds']) return $this->showError('alreadyGet5Seconds');
		// update status of hint
		$this->gameData['get5Seconds'] = true;

		$this->tpl->assign('data', array('result' => array('result' => true)));
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * Check for Hack
	 *
	 * @param int $time - time in seconds
	 * @param int $answersRight - right answers
	 * @param int $answersWrong - wrong answers
	 * @example Smth with timer
	 * @return bool (true if smth wrong, otherwise false)
	 */
	protected function hackCheck($time = 0, $answersRight = 0, $answersWrong = 0) {
		$seconds = self::startTime;
		if (isset($this->gameData['get5Seconds']) && $this->gameData['get5Seconds']) $seconds += 5;
		if ($answersWrong && isset($this->gameData['skipQuestion']) && $this->gameData['skipQuestion']) $answersWrong--;
		$seconds += ($answersRight * self::rightAnswer);
		$seconds += ($answersWrong * self::wrongAnswer);

		if ($seconds < -30 || $time < 0 || abs($seconds - $time) > 60) return true; // to big difference
		if (abs(time() - $this->gameData['startedTime'] - $time) > 30) return true; // to big difference
		// no hack stuff
		return false;
	}

	/**
	 * Is already play?
	 *
	 * @return bool (true if already playing, otherwise false)
	 */
	protected function alreadyPlay() {
		return (isset($this->gameData['lastUpdate']) && $this->gameData['lastUpdate'] >= strtotime('-5 minutes') ? true : false);
	}

	/**
	 * Show error
	 *
	 * @param string $code - code
	 * @param mixed $arg1 - for sprintf
	 * @param mixed $arg2 - for sprintf
	 * @param mixed $argN - for sprintf
	 */
	protected function showError() {
		$args = func_get_args();
		$code = array_shift($args);

		$error = (isset(self::$errors[$code]) ? vsprintf(self::$errors[$code], $args) : self::$errors['default']);

		$this->tpl->assign('data', array('result' => array('error' => $error)));
		$this->tpl->tpl('', '/', 'ajax.php');
	}

	/**
	 * See this::showError()
	 *
	 * @return string
	 */
	protected function getError() {
		$args = func_get_args();
		$code = array_shift($args);

		return (isset(self::$errors[$code]) ? vsprintf(self::$errors[$code], $args) : self::$errors['default']);
	}

	/**
	 * Start game
	 */
	public function start() {
	    $started = $this->sess->restore_var('gs_started');
	    
	    if($started) {
	        $this->sess->delete_var('gs_started');
	        return;
	    }
	    else {
	        $this->sess->delete_var('gs_started');
	    }
		
		$timeBegin = $this->getWeekBegin();
		$timeEnd = strtotime('+1 week', $timeBegin);
		$this->statisticObject->get_num($numPlaysPerWeek, array('createtime_more' => $timeBegin, 'createtime_less' => $timeEnd, 'uid' => $this->user['id']));
		$numPlaysPerWeek->get_first($numPlaysPerWeek);
		$numPlaysPerWeek = (int)$numPlaysPerWeek['count'];
		// already plays 3 times, per week
		// @TODO
		if (false && $numPlaysPerWeek >= 3) {
			$this->tpl->assign('error', $this->getError('limitExhausted'));
			$this->tpl->tpl('', '/games/GuessStar/', 'start.php');
			return true;
		}
	
		$this->gameData = array('startedTime' => time(), 'lastUpdate' => time(), 'questions' => array()); // flush
		$this->gameQuestions = &$this->gameData['questions'];
		
		$this->tpl->assign('data', json_encode($this->tpl->plugins['iconv']->iconv($this->getQuestion())));				
		$this->tpl->assign('imagePath', self::imagePath);
		$this->tpl->assign('startTime', self::startTime);
		$this->tpl->assign('rightAnswer', self::rightAnswer);
		$this->tpl->assign('wrongAnswer', self::wrongAnswer);
		$this->tpl->tpl('', '/games/GuessStar/', 'start.php');
		$this->sess->save_var('gs_started', true);
		return true;
	}

	/**
	 * Show instructions
	 *
	 * @return string
	 */
	public function instructions() {
		$this->tpl->assign('ratingData', $this->statisticObject->getTop(0, 10));
		$this->tpl->assign('firstTime', ($this->statisticObject->getUserAllGames($this->user['id']) === false));
		$this->tpl->tpl('', '/games/GuessStar/', 'instructions.php');
		return true;
	}
	
	/**
	 * Show instructions from profile
	 *
	 * @return string
	 */
	public function instructionsProfile() {
	    $this->tpl->assign('userID', $this->user['id']);
		$this->tpl->assign('firstTime', ($this->statisticObject->getUserAllGames($this->user['id']) === false));
		$this->tpl->tpl('', '/games/GuessStar/', 'from_profile.php');
		return true;
	}
	
	/**
	 * Show verbose top
	 *
	 * @param int $page - page
	 *
	 * User top + top table
	 */
	public function verboseTop($page = 1) {
		$tpl = array();
		// page
		$tpl['page'] = $page;

		// @TODO
		$timeBegin = null;
		$timeEnd = null;
		$limit = 100;
		$offset = (($page - 1) * $limit);

		$add = array();
		if ($timeBegin || $timeEnd) {
			if ($timeBegin) $add['createtime_more'] = (int)$timeBegin;
			if ($timeEnd) $add['createtime_less'] = (int)$timeEnd;
		}

		$tpl['act'] = $act;
		// top table
		$tpl['ratingData'] = $this->statisticObject->getTop($offset, $limit, $timeBegin, $timeEnd);
		// number of all rows
		//$this->statisticObject->get_num($ret, $add, array('uid'));
		//$ret->get_first($ret);
		$tpl['num'] = 100;//$ret['count'];
		$tpl['numPages'] = ceil($tpl['num'] / 100);
		// all games & place
		$tpl['currentUserAllGames'] = $this->statisticObject->getUserAllGames($this->user['id'], $timeBegin, $timeEnd);
		// best game
		$tpl['currentUserBestGame'] = $this->statisticObject->getUserBestGame($this->user['id'], $timeBegin, $timeEnd);
		// place
		//$tpl['place'] = $this->statisticObject->getUserPlace($tpl['currentUserBestGame']['answers_right'], $timeBegin, $timeEnd);
		$places = $this->statisticObject->getUserPlace($tpl['currentUserBestGame']['answers_right'], $timeBegin, $timeEnd);
		$place = 'Нет';
		foreach ($places as $i => $p) {
		    if($p['uid'] == $this->user['id']) {
		        $place = $i;
		        break;
		    }
		}
		$tpl['place'] = $place;
		// number of games
		$this->statisticObject->get_num($ret, array_merge($add, array('uid' => $this->user['id'])), array('uid'));		
		$ret->get_first($ret);		
		$tpl['numberOfGames'] = $ret['count'];

		foreach ($tpl as $k => &$v) {
			$this->tpl->assign($k, $v);
		}
		$this->tpl->tpl('', '/games/GuessStar/', 'top.php');
		return true;
	}

	/**
	 * Get week begin
	 *
	 * @param itn $unixtimeStamp - unixtime stamp to begin (defult current timestamp)
	 * @return int - unixtime stamp
	 */
	protected function getWeekBegin($unixtimeStamp = null) {
		if (!$unixtimeStamp) $unixtimeStamp = time();

		// @fucking PHP 4
		if ((float)PHP_VERSION >= (float)'5.1.0') {
			$stamp = strtotime(sprintf('-%u days', (date('N', $unixtimeStamp)-1)), $unixtimeStamp);
		} else {
			static $dayOfWeeks = array(
				'Monday' =>		1,
				'Tuesday' =>	2,
				'Wednesday' =>	3,
				'Thursday' =>	4,
				'Friday' =>		5,
				'Saturday' =>	6,
				'Sunday' =>		7,
			);

			$stamp = strtotime(sprintf('-%u days', ($dayOfWeeks[date('l', $unixtimeStamp)]-1)), $unixtimeStamp);
		}
		return mktime(null, null, null, date('m', $stamp), date('d', $stamp), date('Y', $stamp));
	}

	/**
	 * Get month begin
	 *
	 * @param itn $unixtimeStamp - unixtime stamp to begin (defult current timestamp)
	 * @return int - unixtime stamp
	 */
	protected function getMonthBegin($unixtimeStamp = null) {
		if (!$unixtimeStamp) $unixtimeStamp = time();
		$stamp = strtotime(sprintf('-%u days', (date('j', $unixtimeStamp)-1)), $unixtimeStamp);
		return mktime(null, null, null, date('m', $stamp), date('d', $stamp), date('Y', $stamp));
	}
}
