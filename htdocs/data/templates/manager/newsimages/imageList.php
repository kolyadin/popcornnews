<?php
$images = $d['images'];
?>
<ul class="image-list">
    <?php foreach($images as $image) { ?>
        <li><a href="<?=$image['src'];?>"><img src="<?= $image['tmb']; ?>"/></a></li>
    <?php } ?>
</ul>