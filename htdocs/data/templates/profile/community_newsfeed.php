<?
$this->_render('inc_header', array('title' => $d['cuser']['nick'], 'header' => 'Группы', 'top_code' => '<img src="' . $this->getStaticPath($this->getUserAvatar($d['cuser']['avatara'], true)) . '" alt="' . htmlspecialchars($d['cuser']['nick']) . '" class="avaProfile">', 'header_small' => 'Твой профиль'));

$maintance = isset($d['maintace']) ? $d['maintance'] : false;

$new_msgs = $p['query']->get_num('user_msgs', array('uid' => $d['cuser']['id'], 'readed'=>0, 'private' => 1, 'del_uid' => 0));
$new_friends = $p['query']->get_num('user_friends_optimized', array('uid'=>$d['cuser']['id'], 'confirmed'=>0));
?>
<div id="contentWrapper" class="twoCols">
	<div id="content">
		<ul class="menu">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>">профиль</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/friends">друзья</a><span class="marked"><?=$new_friends;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/persons/all">персоны</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/guestbook">гостевая</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/messages">сообщения</a><span class="marked"><?=$new_msgs;?></span></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/wrote">я пишу</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/fanfics">фанфики</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/gifts">подарки</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/sets">your.style</a></li>
			<li><a rel="nofollow" href="/games/guess_star/instructions/profile">угадай звезду</a></li>			
		</ul>
		<ul class="menu bLevel">
			<li><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/groups">группы</a></li>
			<li class="active"><a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/newsfeed">обновления</a></li>
		</ul>
		<h2>Обновления</h2>

        <?php if($maintance) {

        } else {?>

		<?if ($d['num'] > 0) {?>
		<table class="group_update">
			<tbody>
			<?$i = 0; foreach ($d['items'] as &$item) { $i++ ?>
				<tr>
					<td class="first"><a name="feed_<?=$item['_id']?>" href="#feed_<?=$item['_id']?>"><?=$i?></a></td>
					<td>
					<?
					$this->assign('item', $item);
					switch ($item['_type']) {
						case 'messages':
							echo $this->make('../community/group/newsfeed/messages.php');
							break;
						case 'topics':
							echo $this->make('../community/group/newsfeed/topics.php');
							break;
						case 'albums':
							echo $this->make('../community/group/newsfeed/albums.php');
							break;
						case 'photos':
							echo $this->make('../community/group/newsfeed/photos.php');
							break;
						case 'albumsComments':
							echo $this->make('../community/group/newsfeed/albumsComments.php');
							break;
					}
					?>
					</td>
				</tr>
			<?}?>
			</tbody>
		</table>

		<div class="paginator smaller">
			<p class="pages">Страницы:</p>
			<ul>
			<?foreach ($p['pager']->make($d['page'], $d['pages']) as $i => $pi) { ?>
				<li>
				<?if (!isset($pi['current'])) {?>
				<a rel="nofollow" href="/profile/<?=$d['cuser']['id']?>/community/newsfeed/page/<?=$pi['link']?>"><?=$pi['text']?></a>
				<?} else {?>
				<?=$pi['text']?>
				<?}?>
				</li>
			<?}?>
			</ul>
		</div>
		<?} else {?>
		<h4>Здесь будут отображаться обновления в группах, в которых вы состоите.</h4>
		<?}}?>
	</div>
	<?$this->_render('inc_right_column');?>
</div>
<?$this->_render('inc_footer');?>