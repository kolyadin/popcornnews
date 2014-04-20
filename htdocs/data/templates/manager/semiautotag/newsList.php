<?php
$tags = $d['tags'];
$persons = $d['persons'];
?>
<script>
    var tags = <?=_json_encode($tags);?>;
    var persons = <?=_json_encode($persons);?>;
    $(document).ready(function(){
        $('a.moreTags').click(function(){
            var label = $('#proto label.personItem').clone();
            $('#tags').append(label);
            updateRemovers();
            return false;
        });
        $('a.moreEvents').click(function(){
            var label = $('#proto label.tagItem').clone();
            $('#events').append(label);
            updateRemovers();
            return false;
        });
        $('a.checkAll').click(function(){
            $('input:checkbox').attr('checked', 'checked');
            return false;
        });
        $('a.uncheckAll').click(function(){
            $('input:checkbox').attr('checked', '');
            return false;
        });
        /*
        $('input:submit').click(function(){
            if($('#tags select').length ==0 || $('#events select').length) {
                alert('Не выбрано ни одной персоны или тега');
                return false;
            }
        });
        */
    });
    function updateRemovers() {
        $('#tags .remove').click(function() {
            $(this).parent().remove();
            return false;
        });
        $('#events .remove').click(function() {
            $(this).parent().remove();
            return false;
        });
    }
</script>
<a href="?type=semiautotag">вернуться к поиску</a>
<form action="admin.php<?= SemiAutoTagManager::createUrl('applyTags'); ?>" method="post">
    <input type="hidden" name="type" value="semiautotag"/>
    <input type="hidden" name="action" value="applyTags"/>

    <h2>Персоны: </h2>

    <div id="tags">
    </div>
    <a href="#" class="moreTags">добавить персону</a><br />
    <h2>Теги: </h2>

    <div id="events">
    </div>
    <a href="#" class="moreEvents">добавить тег</a>

    <br /><br />

    <input type="submit" value="сохранить" />
    <br /><br />

    <div id="items">
        <h2>Новости: </h2>
        (<a href="#" class="checkAll">выделить все</a> | <a class="uncheckAll" href="#">снять выделение</a>)
        <br /><br />
    <?php
    foreach($d['news'] as $new) {
        ?>

        <div class="item">
            <label>
                <input type="checkbox" name="news[]" value="<?= $new['id']; ?>" checked="checked"/>
                <?= $new['name']; ?>
            </label>
            <a href="/news/<?=$new['id'];?>" target="_blank">открыть</a>
        </div>
    <?php } ?>
    </div>
    <br /><br />

    <input type="submit" value="сохранить" />

</form>
<br /><br />
<div id="proto" style="display: none">
    <label style="display: block;" class="personItem">
        <select name="persons[]">
            <?php foreach($persons as $person): ?>
                <option value="<?=$person['id'];?>"><?=$person['name'];?></option>
            <?php endforeach; ?>
        </select>
        <a class="remove" href="#">убрать</a>
    </label>
    <label style="display: block;" class="tagItem">
        <select name="events[]">
            <?php foreach($tags as $tag): ?>
                <option value="<?=$tag['id'];?>"><?=$tag['name'];?></option>
            <?php endforeach; ?>
        </select>
        <a class="remove" href="#">убрать</a>
    </label>
</div>