<?php

class vpa_tpl_smiles {

	/**
	 * Dir with smiles on server or site
	 * like http://popcornnews.ru/smiles/
	 *
	 * @var string
	 */
	public $dir = '/smiles/';
	/**
	 * Main list of smiles
	 *
	 * @var array
	 */
	public $smiles;
	/**
	 * Smiles for template files
	 *
	 * @var array
	 */
	public $tpl_smiles;
	/**
	 * Old smiles - that deleted
	 * For compatibility only
	 *
	 * @var array
	 */
	public $old_smiles;
	/**
	 * New smiles + old smiles
	 * Date: 17.04.2010
	 *
	 * @var array
	 */
	public $new_smiles;

	public function vpa_tpl_smiles() {
		$this->old_smiles = array(
		    '[linux]' => 'mnIBC0.gif',
		    '[windows]' => 'y6OyFX.gif',
		);
		$this->new_smiles = array(
		    // old smiles
		    '[china]' => 'z9b7QQ.gif',
		    '[fight]' => 'ldQxDk.gif',
		    '[abuse]' => 'SrSvXY.gif',
		    '[indian]' => 'sdD9j6.gif',
		    '[lamo]' => 'i2N7vJ.gif',
		    '[admin]' => 'KCrHmv.gif',
		    '[ufo]' => 'Gp9BDA.gif',
		    '[batman]' => 'Gdr64d.gif',
		    '[shooter]' => '4x3EhV.gif',
		    '[Smile]' => 'CtEz60.gif',
		    '[Indignation]' => 'WVR9M7.gif',
		    '[Perfectly]' => 'BLGK92.gif',
		    '[Super]' => 'dFUNyA.gif',
		    '[Unsure]' => 'mEUvNS.gif',
		    '[Wink]' => 'ZWn1tj.gif',
		    '[Yes]' => 'OOhcuC.gif',
		    '[Tears]' => 'uPiana.gif',
		    '[Idea]' => 'QIP6bL.gif',
		    '[Banter]' => 'Mqpz2v.gif',
		    '[Sad]' => 'RLAG0f.gif',
		    '[Exclaim]' => 'AJyWH7.gif',
		    '[Fun]' => 'yVKObZ.gif',
		    '[Confusion]' => 'UlqVod.gif',
		    '[Tease]' => 'ozECBi.gif',
		    '[Tongue]' => 'BOyuQb.gif',
		    '[Arrow]' => '73DpCq.gif',
		    '[Surprise]' => 'C9aOi2.gif',
		    '[Sly]' => 'FqfWBo.gif',
		    '[Shock]' => 'Ih7Fmz.gif',
		    '[Sorrow]' => 'YtGGcN.gif',
		    '[Angry ]' => '9Ht6It.gif',
		    '[Grumbler]' => '2AHxlR.gif',
		    '[Dreamer]' => 'o64AMP.gif',
		    '[Side-splitting]' => 'ynXVAF.gif',
		    '[Question]' => 'AdHcV5.gif',
		    '[Plus]' => 'K5a67L.gif',
		    '[No]' => 'eX6aFP.gif',
		    '[Minus]' => 'az9tBN.gif',
		    '[Peace]' => 'YdZoVd.gif',
		    '[Surprise]' => 'gfZ40n.gif',
		    '[Secret]' => 'RERdpz.gif',
		    '[Severe]' => 'Q2oUot.gif',
		    '[Obstinate]' => 'LUO2sV.gif',
		    '[Relaxation]' => 'qNFdKB.gif',
		    '[Dance]' => 'ooYNKL.gif',
		    '[Fury]' => 'oVfucu.gif',
		    '[Bye]' => 'GnfX7a.gif',
		    '[Grin]' => 'SCY5iN.gif',
		    '[Sob]' => 'xmES85.gif',
		    '[Booby]' => '5idIWE.gif',
		    '[Grief]' => 'uziHuY.gif',
		    '[Puzzle]' => 'CiDby4.gif',
		    '[Merry]' => 'bpbtIy.gif',
		    '[Satisfied]' => 'SNrGmJ.gif',
		    '[Redden]' => 'lp2Pkj.gif',
		    '[Blush]' => 'bWEbXR.gif',
		    '[Discomfiture]' => 'oBsIeA.gif',
		    '[Mad]' => 'xRfvZM.gif',
		    '[Dribble]' => 'UsmjJK.gif',
		    '[censored]' => 'YXPBLb.gif',
		    '[jester]' => 'dJARPP.gif',
		    '[beer1]' => '9IlObF.gif',
		    '[dirty_trick]' => 'QYxDef.gif',
		    '[transformer]' => 'pBm3kl.gif',
		    '[read]' => 'ad08kE.gif',
		    '[boxer]' => 'ZS4n0g.gif',
		    '[vanishing]' => 'tR44nJ.gif',
		    '[bravo]' => 'tuqZQu.gif',
		    '[invalid]' => 'x20JJK.gif',
		    '[devil]' => 'hyNAnw.gif',
		    '[injection]' => '4ZBYnK.gif',
		    '[athlene]' => 'r0Ky83.gif',
		    '[wall]' => 'NK8eS5.gif',
		    '[fourth]' => 'UOvGfg.gif',
		    '[judge]' => 'rLhCnd.gif',
		    '[help]' => 'Ohfgxo.gif',
		    '[in_love]' => 'EcTBvD.gif',
		    '[dancer]' => '7zdkv5.gif',
		    '[lamp]' => 'nu0CjD.gif',
		    '[mobile]' => 'nNca1u.gif',
		    '[no-no]' => 'kvgfuS.gif',
		    '[God]' => 'LVId3C.gif',
		    '[yo]' => 'NmiIDh.gif',
		    '[congratulate]' => '1If5LG.gif',
		    '[beer]' => 'eEqByU.gif',
		    '[crazy]' => 'zYS4Um.gif',
		    '[piska]' => 'GD2VqM.gif',

		    // new smiles
		    '[acute]' => 'acute.gif',
		    '[aggressive]' => 'aggressive.gif',
		    '[agree]' => 'agree.gif',
		    '[air_kiss]' => 'air_kiss.gif',
		    '[alcoholic]' => 'alcoholic.gif',
		    '[bad]' => 'bad.gif',
		    '[beee]' => 'beee.gif',
		    '[black_eye]' => 'black_eye.gif',
		    '[blum2]' => 'blum2.gif',
		    //'[blum3]' => 'blum3.gif',
		    '[blush]' => 'blush.gif',
		    '[blush2]' => 'blush2.gif',
		    '[boast]' => 'boast.gif',
		    '[boredom]' => 'boredom.gif',
		    '[censored]' => 'censored.gif',
		    '[clapping]' => 'clapping.gif',
		    '[cray]' => 'cray.gif',
		    //'[cray2]' => 'cray2.gif',
		    '[crazy_pilot]' => 'crazy_pilot.gif',
		    '[dance]' => 'dance.gif',
		    //'[dance2]' => 'dance2.gif',
		    '[dance3]' => 'dance3.gif',
		    '[dance4]' => 'dance4.gif',
		    //'[dash1]' => 'dash1.gif',
		    '[dash2]' => 'dash2.gif',
		    '[dash3]' => 'dash3.gif',
		    '[declare]' => 'declare.gif',
		    '[derisive]' => 'derisive.gif',
		    '[dirol]' => 'dirol.gif',
		    '[dntknw]' => 'dntknw.gif',
		    '[don-t_mention]' => 'don-t_mention.gif',
		    '[download]' => 'download.gif',
		    '[drag]' => 'drag.gif',
		    '[drinks]' => 'drinks.gif',
		    '[focus]' => 'focus.gif',
		    '[fool]' => 'fool.gif',
		    '[friends]' => 'friends.gif',
		    '[gamer1]' => 'gamer1.gif',
		    //'[gamer2]' => 'gamer2.gif',
		    '[gamer3]' => 'gamer3.gif',
		    '[gamer4]' => 'gamer4.gif',
		    '[girl_crazy]' => 'girl_crazy.gif',
		    '[girl_hospital]' => 'girl_hospital.gif',
		    '[girl_wacko]' => 'girl_wacko.gif',
		    '[good]' => 'good.gif',
		    '[good2]' => 'good2.gif',
		    //'[good3]' => 'good3.gif',
		    '[grin]' => 'grin.gif',
		    //'[hang1]' => 'hang1.gif',
		    //'[hang2]' => 'hang2.gif',
		    '[hang3]' => 'hang3.gif',
		    '[heat]' => 'heat.gif',
		    '[help]' => 'help.gif',
		    '[hunter]' => 'hunter.gif',
		    '[i-m_so_happy]' => 'i-m_so_happy.gif',
		    '[ireful1]' => 'ireful1.gif',
		    //'[ireful2]' => 'ireful2.gif',
		    //'[ireful3]' => 'ireful3.gif',
		    //'[laugh1]' => 'laugh1.gif',
		    //'[laugh2]' => 'laugh2.gif',
		    '[laugh3]' => 'laugh3.gif',
		    '[lazy]' => 'lazy.gif',
		    '[lazy2]' => 'lazy2.gif',
		    //'[lazy3]' => 'lazy3.gif',
		    '[locomotive]' => 'locomotive.gif',
		    '[mail1]' => 'mail1.gif',
		    '[man_in_love]' => 'man_in_love.gif',
		    '[mda]' => 'mda.gif',
		    '[meeting]' => 'meeting.gif',
		    '[mosking]' => 'mosking.gif',
		    '[nea]' => 'nea.gif',
		    '[negative]' => 'negative.gif',
		    '[no2]' => 'no2.gif',
		    '[not_i]' => 'not_i.gif',
		    '[ok]' => 'ok.gif',
		    '[on_the_quiet]' => 'on_the_quiet.gif',
		    //'[on_the_quiet2]' => 'on_the_quiet2.gif',
		    '[pardon]' => 'pardon.gif',
		    '[party]' => 'party.gif',
		    '[pilot]' => 'pilot.gif',
		    '[pleasantry]' => 'pleasantry.gif',
		    '[polling]' => 'polling.gif',
		    '[popcorm1]' => 'popcorm1.gif',
		    //'[popcorm2]' => 'popcorm2.gif',
		    //'[prankster]' => 'prankster.gif',
		    '[prankster2]' => 'prankster2.gif',
		    '[preved]' => 'preved.gif',
		    '[punish]' => 'punish.gif',
		    //'[punish2]' => 'punish2.gif',
		    '[read]' => 'read.gif',
		    '[resent]' => 'resent.gif',
		    '[rofl]' => 'rofl.gif',
		    '[russian_roulette]' => 'russian_roulette.gif',
		    '[sad]' => 'sad.gif',
		    '[sarcastic]' => 'sarcastic.gif',
		    '[sarcastic_blum]' => 'sarcastic_blum.gif',
		    //'[sarcastic_hand]' => 'sarcastic_hand.gif',
		    '[scare]' => 'scare.gif',
		    '[scare2]' => 'scare2.gif',
		    '[sclerosis]' => 'sclerosis.gif',
		    '[scratch_one-s_head]' => 'scratch_one-s_head.gif',
		    '[search]' => 'search.gif',
		    '[secret]' => 'secret.gif',
		    '[shout]' => 'shout.gif',
		    //'[slow]' => 'slow.gif',
		    //'[slow_en]' => 'slow_en.gif',
		    '[smile3]' => 'smile3.gif',
		    '[smoke]' => 'smoke.gif',
		    '[snooks]' => 'snooks.gif',
		    '[sorry]' => 'sorry.gif',
		    '[sorry2]' => 'sorry2.gif',
		    '[stink]' => 'stink.gif',
		    '[stinker]' => 'stinker.gif',
		    '[stop]' => 'stop.gif',
		    '[suicide2]' => 'suicide2.gif',
		    //'[suicide_fool-edit]' => 'suicide_fool-edit.gif',
		    '[superstition]' => 'superstition.gif',
		    '[swoon]' => 'swoon.gif',
		    '[swoon2]' => 'swoon2.gif',
		    '[take_example]' => 'take_example.gif',
		    '[taunt]' => 'taunt.gif',
		    '[tease]' => 'tease.gif',
		    '[telephone]' => 'telephone.gif',
		    '[thank_you]' => 'thank_you.gif',
		    //'[thank_you2]' => 'thank_you2.gif',
		    '[this]' => 'this.gif',
		    '[threaten]' => 'threaten.gif',
		    '[to_become_senile]' => 'to_become_senile.gif',
		    '[to_clue]' => 'to_clue.gif',
		    '[to_pick_ones_nose]' => 'to_pick_ones_nose.gif',
		    '[to_pick_ones_nose2]' => 'to_pick_ones_nose2.gif',
		    //'[to_pick_ones_nose3]' => 'to_pick_ones_nose3.gif',
		    //'[to_pick_ones_nose_eat]' => 'to_pick_ones_nose_eat.gif',
		    '[to_take_umbrage]' => 'to_take_umbrage.gif',
		    '[tongue]' => 'tongue.gif',
		    '[umnik]' => 'umnik.gif',
		    //'[umnik2]' => 'umnik2.gif',
		    '[victory]' => 'victory.gif',
		    '[wacko]' => 'wacko.gif',
		    //'[wacko2]' => 'wacko2.gif',
		    //'[whistle]' => 'whistle.gif',
		    //'[whistle2]' => 'whistle2.gif',
		    '[whistle3]' => 'whistle3.gif',
		    '[wink3]' => 'wink3.gif',
		    '[yahoo]' => 'yahoo.gif',
		    //'[yes]' => 'yes.gif',
		    '[yes2]' => 'yes2.gif',
		    '[yes3]' => 'yes3.gif',
		    //'[yes4]' => 'yes4.gif',
		    '[yu]' => 'yu.gif',
		);
		// for tpl
		$this->tpl_smiles = $this->new_smiles;
		foreach ($this->tpl_smiles as $i => &$smile) {
			$smile = VPA_template::getInstance()->getStaticPath($this->dir . $smile);
		}

		// for compatibility with old smiles
		$this->smiles = array_merge($this->old_smiles, $this->new_smiles);

		foreach ($this->smiles as $i => &$smile) {
			$smile = '<img src="' . VPA_template::getInstance()->getStaticPath($this->dir . $smile) . '"  hspace="2" vspace="2" border="0" alt="" />';
		}
	}

	public function parse($str) {
		$what = array_keys($this->smiles);
		return str_replace($what, $this->smiles, $str);
	}
}

?>