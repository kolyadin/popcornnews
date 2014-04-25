<?$this->_render('inc_header',array('title'=>'Регистрация','header'=>'Регистрация','top_code'=>'*','header_small'=>'Регистрация на сайте'));?>
<script type="text/javascript">
function test_reg(frm)
{
	str='';
	if (frm.nick.value=='')
	{
		str='Вы не указали свой ник !';
	}
	else if (frm.email.value.indexOf('@')==-1)
	{
		str='Вы не указали ваш E-mail или его формат некорректен !';
	}
	else if (frm.pass1.value=='' || frm.pass2.value=='')
	{
		str='Вы указали пустой пароль !';
	}
	else if (frm.pass1.value!=frm.pass2.value)
	{
		str='Пароли не совпадают !';
	}
	else if (frm.city.value=='')
	{
		str='Вы не указали свой город !';
	}
	if (str!='')
	{
		alert (str);
		return false;
	}

	/*if (!frm.rules.checked)
	{
		alert ("Для завершения регистрации, вы должны принять правила !");
		return false;
	}*/
	return true;
}
</script>
<div id="contentWrapper" class="twoCols">
<div id="content">
							<?if (isset($d['rewrite'][1]) && $d['rewrite'][1]=='err_nick') {?>
							<h4>Такой Ник уже зарегистрирован в системе.</h4>
							<?}?>
							<?if (isset($d['rewrite'][1]) && $d['rewrite'][1]=='err_fields') {?>
							<h4>Вы не заполнили все обязательные поля.</h4>
							<?}?>
							<?if (isset($d['rewrite'][1]) && $d['rewrite'][1]=='dif_pass') {?>
							<h4>Пароли не совпадают.</h4>
							<?}?>
							<?if (isset($d['rewrite'][1]) && $d['rewrite'][1]=='err_email') {?>
							<h4>Такой E-mail уже зарегистрирован в системе.</h4>
							<?}?>
							<?if (isset($d['rewrite'][1]) && $d['rewrite'][1]=='rules') {?>
							<h4>Для завершения регистрации вы должны принять правила.</h4>
							<?}?>
							<form class="questionnaireForm" action="/index.php" method="POST" enctype="multipart/form-data" name="fr" onsubmit="return test_reg(this);">
							<input type="hidden" name="type" value="register">
							<input type="hidden" name="action" value="add">
							<label>
							<strong>Ник <sup>*</sup></strong>
							<input type="text" name="nick" value="<?=htmlspecialchars($d['user_data']['nick'], ENT_IGNORE, 'cp1251', false);?>">
							</label>
							<label>
							<strong>Имя <sup></sup></strong>
							<input type="text" name="uname" value="<?=$d['user_data']['name']?>">
							</label>
							<label>
							<strong>Ваше кредо</strong>
							<textarea name="credo"><?=$d['user_data']['credo']?></textarea>
							</label>
							<label>
							<strong>Аватара</strong>
							<input type="file" name="avatara" value="">
							</label>
							<label>
							<strong>E-mail <sup>*</sup></strong>
							<input type="text" name="email" value="<?=$d['user_data']['email']?>">
							</label>
							<label>
							<strong>Пароль <sup>*</sup></strong>
							<input type="password" name="pass1" value="">
							</label>
							<label>
							<strong>Повторите пароль <sup>*</sup></strong>
							<input type="password" name="pass2" value="">
							</label>
							<fieldset>
							<strong>Дата рождения <sup>*</sup></strong>
							<span class="cont">
								<select name="day">
									<option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option>
									<option>9</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option>
									<option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option><option>24</option>
									<option>25</option><option>26</option><option>27</option><option>28</option><option>29</option><option>30</option><option>31</option>
								</select>
								<select name="month">
									<option value="1">января</option><option value="2">февраля</option><option value="3">марта</option><option value="4">апреля</option><option value="5">мая</option>
									<option value="6">июня</option><option value="7">июля</option><option value="8">августа</option><option value="9">сентября</option>
									<option value="10">октября</option><option value="11">ноября</option><option value="12">декабря</option>
								</select>
								<select name="year">
									<?for ($i=date('Y');$i>=1900;$i--) { ?>
                                    <option value="<?=$i?>"><?=$i?></option>
                                    <?}?>
								</select>
								<span>показывать в профиле</span>
								<input type="checkbox" name="show_bd" value="1"/>
							</span>
						</fieldset>
                                                <label><strong>Страна</strong>
                                                <select name="country">
                                                        <?foreach ($p['query']->get('countries',null,array('rating'),0,500) as $i => $city) {?>
														<option value="<?=$city['id']?>" <?=$d['user_data']['country_id']==$city['id'] ? 'selected="selected"' : ''?>><?=$city['name']?></option>
														<?}?>
														<option value="0" <?=$d['user_data']['city_id']=='0' ? 'selected="selected"' : ''?>>Другая...</option>
                                                </select>
                                                </label>
                                                <label>
                                                        <strong>Город <sup>*</sup></strong>
														<select name="city">

														</select>
                                                </label>
                                                <label>
                                                        <strong>Пол</strong>
                                                        <select name="sex">
                                                                <option value="0">-</option>
                                                                <option value="1" <?if ($d['user_data']['sex']==1){?>selected="selected"<?}?>>мужской</option>
                                                                <option value="2" <?if ($d['user_data']['sex']==2){?>selected="selected"<?}?>>женский</option>
                                                        </select>
                                                </label>
                                                <label>
                                                        <strong>Семья</strong>
                                                        <select name="family">
                                                                <option value="0"-</option>
                                                                <option value="1"<?=$d['user_data']['family']==1 ? 'selected="selected"' :''?>>женат/замужем</option>
                                                                <option value="2"<?=$d['user_data']['family']==2 ? 'selected="selected"' :''?>>холост/холоста</option>
                                                        </select>
                                                </label>
                                                <label>
                                                        <strong>Я хотел<?=$d['user_data']['sex']==1 ? '': 'а';?> бы встретиться с</strong>
                                                        <select name="meet_actor">
                                                                <option value="0">ни с кем</option>
                                                                <?foreach ($p['query']->get('persons',null,array('name'),null,null) as $i => $person){?>
                                                                <option value="<?=$person['id']?>"<?=$d['user_data']['meet_actor']==$person['id'] ? 'selected="selected"' :''?>><?=$person['name']?></option>
                                                                <?}?>
                                                        </select>
                                                </label>
												<span><input type="checkbox" name="daily_sub" value="1" checked="checked">&nbsp;Я хочу получать ежедневную рассылку новостей с сайта </span><br><br>
												<span><input type="checkbox" name="rules" value="1">&nbsp;Я прочитал <a href="/rules" onclick="window.open('/rules','_blank','width=800, height=600, scrollbars=yes'); return false;">правила</a> и принимаю их</span>
												<br><br>
                                                <input type="submit" value="сохранить" />

                                        </form>
                                </div>

                        <?$this->_render('inc_right_column');?>
                        </div>
<?$this->_render('inc_footer');?>
