<?
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => '�������', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => '���� �������'));

$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">������</a><span class="marked"><?=$new_friends;?></span></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">��������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">���������</a><span class="marked"><?=$new_msgs;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">� ����</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">������ ������</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons">�������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/add">��������</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/del">�������</a></li>
		</ul>
		<h2>�������</h2>
		<div class="groupsContainer equalsContainer">
			<?foreach ($p['query']->get('persons', array('fan' => $d['cuser']['id']), null, null, null) as $i => $person) {
			    $link = $person['eng_name'];
			    $link = str_replace('-', '_', $link);
			    $link = str_replace('&dash;', '_', $link);
			    $link = '/persons/'.str_replace(' ', '-', $link);			     
			?>
			<dl>
				<dt><img alt="" src="<?=$this->getStaticPath('/upload/_84_120_70_' . $person['main_photo'])?>"  width="80" /></dt>
				<dd><a href="<?=$link;?>"><?=$person['name']?></a></dd>
			</dl>
			<?if (($i + 1) % 6 == 0) {?><div class="divider"></div><?}?>
			<?}?>
		</div>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>