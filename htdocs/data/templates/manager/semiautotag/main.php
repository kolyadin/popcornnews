<script type="text/javascript">
    $(document).ready(function () {
        $('#search').click(function () {
            var item = $('div.search-item:last');
            var clone = item.clone();
            $('input', clone).val('');
            item.parent().append(clone);
            return false;
        });
    });
</script>
<?php

if(isset($d['error'])) {
    echo '<div><strong>'.$d['error'].'</strong></div>';
}

?>
<form action="admin.php<?=SemiAutoTagManager::createUrl('search');?>" method="post">
    <input name="type" value="semiautotag" type="hidden"/>
    <input name="action" value="search" type="hidden"/>

    <div id="persons">
        <div class="search-item">
            <label for="person[]">Поиск</label>
            <input type="text" id="person[]" name="person[]"/>
        </div>
    </div>
    <a href="#" id="search">добавить ещё одно условие поиска</a>

    <div><input value="искать" type="submit"/></div>

</form>