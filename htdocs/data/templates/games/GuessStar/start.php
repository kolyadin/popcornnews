<?$this->_render('inc_header', array('header' => 'Угадай звезду', 'top_code' => '*', 'title' => 'Угадай звезду'));?>
<style>
a.close {
	font-weight: bold !important;
}
</style>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li class="active">игра</li>
			<li><a href="/games/guess_star/rating">рейтинг участников</a></li>
		</ul>
		<?if (!isset($d['error'])) {?>
		<div class="quess_film_game">
			<div class="game_cont">
				<div id="gameImage" class="game_foto">
					<?/*IMAGE IS HERE*/?>
				</div>
				<span id="gameImageNumber">0</span>
			</div>
			<div class="timeline_cont">
				<span id="remainCounter"><?/*TIMER IS HERE*/?></span>
				<div class="timeline"><div></div></div>
			</div>
			<div class="choice_cont" id="answerOptions">
				<?/*AVALIABLE ASWERS OPTIONS*/?>
			</div>
			<div class="help_quess_cont">
				<a href="#" id="hintFiftyFifty" class="hint active">50x50</a>
				<a href="#" id="hintSkipQuestion" class="hint active">пропустить</a>
				<a href="#" id="hint5Seconds" class="hint active">5 секунд</a>
			</div>
		</div>
		<?} else {?>
		<h1><?=$d['error']?></h1>
		<?}?>

		<script type="text/javascript">
			var GuessStar = {
				data: '<?=$d['data']?>', // data with question
				imgSelector: 'div#gameImage', // image
				answerOptionsSelector: 'div#answerOptions', // answer options
				remainCounter: 'span#remainCounter', // remain counter
				imgPath: '<?=$d['imagePath']?>', // image path
				timer: <?=$d['startTime']?>, // timer
				startTime: <?=$d['startTime']?>, // timer
				secondsPased: 0, // counter of seconds
				gameEnded: false, // is game ended?
				rightAnswer: <?=$d['rightAnswer']?>,
				wrongAnswer: <?=$d['wrongAnswer']?>,
				screenCounterSelector: 'span#gameImageNumber', // counter for screens
				screenCounter: 0, // counter for screens
				timelineSelector: 'div.timeline', // counter for screens

				/**
				 * Main Init
				 *
				 * @return void
				 */
				init: function() {
					var G = GuessStar;

					G.data = G.parseJSON(G.data).result;
					G.processData();
					G.processHints();
					G.timerUpdate();
					G.timerId = setInterval(function() {G.timerUpdate();}, 1000);					
				},
				/**
				 * Process data
				 *
				 * @return void
				 */
				processData: function() {
					var G = GuessStar;

					// update screens counter
					as.$$(GuessStar.screenCounterSelector).innerHTML = (++GuessStar.screenCounter);
					// image
					as.$$(G.imgSelector).innerHTML = '<img src="' + G.imgPath + G.data.screen1 + '" alt="" />';
					// answer options [active,choice_disabled]
					var optionNum = options = key = value = '';
					for (var i in G.data) {
						key = i;
						value = G.data[i];

						if (/star_version[1-4]/i.test(key)) {							
							optionNum = key.match(/star_version([1-4])/i);

							options += '<a href="#" class="active answerOption'+ optionNum[1] + '" onkeydown="return ' + optionNum[1] + '"><span>' + value + '</span></a>';
						}
					}
					as.$$(G.answerOptionsSelector).innerHTML = options;
					// bind click event
					as.w(G.answerOptionsSelector + ' a.active').click(function(e) {
						e.preventDefault();
						// game is ended
						if (G.gameEnded) return false;

						var ob = e.target;
						// if not A -> than get parent A
						if (!/^a$/i.test(ob.tagName)) {
							ob = as.parent(ob, 'a.active');
						}
						
						as.ajax(
							'/games/guess_star/isanswerRight/' + ob.onkeydown(),
							function (response) {
								response = GuessStar.parseJSON(response);
								if (GuessStar.checkError(response)) return false;

								if (response.result) {
									G.timer += G.rightAnswer;
								} else {
									G.timer += G.wrongAnswer;
								}
								if (G.timer < 0) {
									return G.saveResult();
								}
								G.getQuestion();
							},
							'get',
							null,
							30,
							[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
							true
						);
					}).mouseout(function(e) { window.onblur = function() { GuessStar.windowBlur(); } }).mouseover(function(e) { window.onblur = null });
				},
				/**
				 * Get next question
				 *
				 * @return void
				 */
				getQuestion: function() {
					var G = GuessStar;
					as.ajax(
						'/games/guess_star/get',
						function (response) {
							response = GuessStar.parseJSON(response);
							// no more questions are avaliable
							if (GuessStar.checkError(response)) {
								G.saveResult();
								return false;
							}
							
							G.data = response.result;
							G.processData();
						},
						'get',
						null,
						30,
						[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
						true
					);
				},
				/**
				 * Process hints
				 * Bind clicks for buttons and so on...
				 *
				 * @return void
				 */
				processHints: function() {
					var G = GuessStar;

					as.w('div.hint').mouseout(function(e) { window.onblur = function() { GuessStar.windowBlur(); } }).mouseover(function(e) { window.onblur = null });
					// [hint_active,hint_disabled]
					G.hints = [as.$$('a#hintFiftyFifty'), as.$$('a#hintSkipQuestion'), as.$$('a#hint5Seconds')];
					// hintFiftyFifty
					as.w(G.hints[0]).click(function(e) {
						e.preventDefault();
						// game is ended
						if (G.gameEnded) return false;

						var ob = e.target;

						as.ajax(
							'/games/guess_star/fiftyFifty',
							function (response) {
								response = GuessStar.parseJSON(response);
								if (GuessStar.checkError(response)) return false;
								if (response.result) {
									// remove wrong answers
									for (var i in response.result) {
										var el = as.$$(G.answerOptionsSelector + ' a.answerOption' + response.result[i]);
										G.removeClass(el, 'active');
										G.addClass(el, 'inactive');
										as.w(G).declick();
									}
									// change class to disabled & delete callback
									G.removeClass(ob, 'active')
									G.addClass(ob, 'inactive')
									as.w(ob).declick();
								}
							},
							'get',
							null,
							30,
							[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
							true
						);
					});
					// hintSkipQuestion
					as.w(G.hints[1]).click(function(e) {
						e.preventDefault();
						// game is ended
						if (G.gameEnded) return false;

						var ob = e.target;

						as.ajax(
							'/games/guess_star/skipQuestion',
							function (response) {
								response = GuessStar.parseJSON(response);
								if (GuessStar.checkError(response)) return false;
								if (response.result) {
									// change class to disabled & delete callback
									G.removeClass(ob, 'active')
									G.addClass(ob, 'inactive')
									as.w(ob).declick();
									// get next
									G.getQuestion();
								}
							},
							'get',
							null,
							30,
							[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
							true
						);
					});
					// hint5Seconds
					as.w(G.hints[2]).click(function(e) {
						e.preventDefault();
						// game is ended
						if (G.gameEnded) return false;

						var ob = e.target;
						as.ajax(
							'/games/guess_star/get5Seconds',
							function (response) {
								response = GuessStar.parseJSON(response);
								if (GuessStar.checkError(response)) return false;
								if (response.result) {
									// change timer time
									G.timer += 5;
									// change class to disabled & delete callback
									G.removeClass(ob, 'active')
									G.addClass(ob, 'inactive')
									as.w(ob).declick();
								}
							},
							'get',
							null,
							30,
							[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
							true
						);
					});
				},
				/**
				 * Update timer
				 *
				 * @return void
				 */
				timerUpdate: function() {
					var G = GuessStar;
					var time = Math.floor(G.timer / G.startTime) + ':' + (G.timer % G.startTime);
					as.$$(G.remainCounter).innerHTML = time;
					G.timer--;
					G.secondsPased++;

					// change bg & change timeline
					var q = -10;
					var t = G.timer+1;
					var yPos = 0;
					
					var el1 = as.$$(G.timelineSelector);
					var el2 = as.$$(G.timelineSelector + ' div');
					var startLeft = parseInt(as.style(el1, 'width'));
					
					if (t >= 60) {yPos = 0;}
					else if (t >= 50) {yPos = q*1;}
					else if (t >= 40) {yPos = q*2;}
					else if (t >= 30) {yPos = q*3;}
					else if (t >= 20) {yPos = q*4;}
					else if (t >= 10) {yPos = q*5;}
					else if (t >=  0) {yPos = q*6;}

					// change bg
					as.style(el1, {backgroundPosition: '0px ' + yPos + 'px'});
					// update timeline
					as.style(el2, {left: Math.ceil(startLeft - (startLeft / G.startTime * (G.startTime - (G.timer >= 0 ? G.timer : 0)))) + 'px'});
					
					// time ended
					if (G.timer < 0) {
						return G.saveResult();
					}
				},
				/**
				 * Save results & clear timer.
				 *
				 * @param string append - append to message
				 * @return void
				 */
				saveResult: function(append) {
					// game is ended
					if (GuessStar.gameEnded) return false;
					if (!append) append = '';

					as.ajax(
						'/games/guess_star/save/' + GuessStar.secondsPased,
						function (response) {
							response = GuessStar.parseJSON(response);
							GuessStar.checkError(response);
							GuessStar.fancyboxShow(
								append +
								'Игра окончена.<br />' +
								'Вы набрали <strong>' + response.points + '</strong> ' + response.pointsWord +  '.<br />' +
								'Вы на <strong>' + response.allUserGamesPlace + '</strong> месте.'
							);
						},
						'get',
						null,
						30,
						[{name:"Content-Type",value:"application/x-www-form-urlencoded; charset=UTF-8"}],
						true
					);

					GuessStar.endGame();
				},
				/**
				 * End game
				 *
				 * @return void
				 */
				endGame: function() {
					GuessStar.gameEnded = true;
					clearTimeout(GuessStar.timerId);
				},
				/**
				 * Check for error
				 *
				 * @param json response - response to check
				 * @return bool (true if error is occured, otherwise false)
				 */
				checkError: function(response) {
					if (response && response.error) {
						GuessStar.fancyboxShow(response.error);
						return true;
					}
					return false;
				},
				/**
				 * Blur window - end game
				 *
				 * @return void
				 */
				windowBlur: function() {
					// already ended
					if (GuessStar.gameEnded == true) {
						return true;
					}

					GuessStar.saveResult('Во время игры нельзя сворачивать страницу и переходить в другое окно – игра окончена.<br /><br />');
				},
				/**
				 * Parse JSON
				 *
				 * @param string string - string
				 * @return object
				 */
				parseJSON: function(string) {
					try {
						if (string.substr(0, 1) != '(') {
							string = '(' + string + ')';
						}
						return eval(string);
					} catch (e) {}
				},
				/**
				 * Remove class
				 *
				 * @param object el - object
				 * @param string className - class name
				 * @return object
				 */
				removeClass: function(el, className) {
					el.className = el.className.replace(className,"");
				},
				/**
				 * Add class
				 *
				 * @param object el - object
				 * @param string className - class name
				 * @return object
				 */
				addClass: function(el, className) {
					el.className += ' ' + className;
				},
				/**
				 * Show box using fancybox
				 *
				 * @param string text - test of box
				 * @return void
				 */
				fancyboxShow: function(text) {
					new MessageBox().init({
						html: "<p class='vote-error' style='width: 300px;'>" + text + "<a href='/games/guess_star/start' style='color:#60b95d;position: absolute; bottom: 15px; left: 25px;'>Начать заново</a>" + "</p>",
						modalRelative: as.$$("div#gameImage"),
						callback: function() {
							location.href = '/games/guess_star/rating';
						}
					});
				}
			}

			<?if (!isset($error)) {?>GuessStar.init();<?}?>
		</script>

	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>