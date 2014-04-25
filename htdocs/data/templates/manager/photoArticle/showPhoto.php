<?php
$photo = $d['photo'];
$persons = $d['persons'];
$jsonPersons = _json_encode($photo->getPersons());
if($jsonPersons == "null") {
    $jsonPersons = '{}';
}
?>
<script type="text/javascript">
    var persons = <?=_json_encode($persons);?>;
    var photoPersons = <?=$jsonPersons;?>;
    function personHandler(personSelector, personView, personField) {
        $('a.add-person-to-photo').click(function () {
            var anchor = $(this);
            anchor.hide();
            personSelector.append('<select></select>');
            var select = $('select', personSelector);
            $.each(persons, function (id, item) {
                select.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
            personSelector.append('<a href="#">сохранить</a>');
            $('a:last', personSelector).click(function () {
                personId = select.val();
                personName = $('option[value=' + personId + ']', select).text();
                notExists = (photoPersons[personId] == undefined);
                photoPersons[personId] = personId;

                personSelector.empty();
                anchor.show();
                if (notExists) {
                    personView.append('<span class="persons" value="' + personId + '">' + personName + ' (<a href="#">X</a>)</span>');
                    $('a:last', personView).click(function () {
                        id = $(this).parent().attr('value');
                        delete photoPersons[id];
                        $(this).parent().remove();
                        personField.val(JSON.stringify(photoPersons));
                    });
                }
                personField.val(JSON.stringify(photoPersons));

                return false;
            });

            return false;
        });
    }
    function fillPersons(personView, personField) {
        $.each(photoPersons, function (id, item) {
            personId = item;
            personName = findName(personId);

            personView.append('<span class="persons" value="' + personId + '">' + personName + ' (<a href="#">X</a>)</span>');
            $('a:last', personView).click(function () {
                id = $(this).parent().attr('value');
                delete photoPersons[id];
                $(this).parent().remove();
                personField.val(JSON.stringify(photoPersons));
            });
        });
    }

    function findName(personId) {
        personName = null;
        $.each(persons, function(id, item){
            if(item.id == personId) {
                personName = item.name;
            }
        });
        return personName;
    }

    $(document).ready(function(){

        var personSelector = $('span.photo-person-selector');
        var personField = $('input.person-field');
        var personView = $('span.photo-persons');

        personField.val(JSON.stringify(photoPersons));

        if(photoPersons != null) {
            fillPersons(personView, personField);
        }

        var photoZoomable = $('input[name=photoZoomable]');

        $('input.check').click(function(){
            photoZoomable.val($(this).attr('checked') ? 1 : 0);
        });

        personHandler(personSelector, personView, personField);

        $('a.remove-photo').click(function(){
            return confirm('Удалить фотографию?');
        });
    });
</script>
<div>
    <img src="<?=$this->getStaticPath($photo->getPhotoPathBySize('300x300'));?>"/>
    <a href="<?=PhotoArticleManager::createUrl('showArticle', array('articleId' => $photo->getArticleId()));?>">вернуться к статье</a>
</div>
<div>
    <a href="<?=PhotoArticleManager::createUrl('removePhoto', array('photoId' => $photo->getId()));?>" class="remove-photo">удалить фотографию</a><br/>

    <form method="POST" action="admin.php<?=PhotoArticleManager::createUrl('savePhoto');?>" name="frm" class="Fform"
          enctype="multipart/form-data">
        <input type="hidden" name="type" value="photoarticles"/>
        <input type="hidden" name="action" value="savePhoto"/>
        <input type="hidden" name="photoId" value="<?=$photo->getId();?>"/>

        <div class="upload-photo">
            <span>Название фотографии</span>
            <input type="text" name="photoTitle" value="<?=$photo->getTitle();?>">
            <span>Источник</span>
            <input type="text" name="photoSource" value="<?=$photo->getSource();?>">
            <span>Включить увеличение</span>
            <input type="hidden" name="photoZoomable" value="<?=(int)$photo->isZoomable();?>" />
            <?php
            $checked = $photo->isZoomable() ? 'checked="checked"' : '';
            ?>
            <input type="checkbox" class="check" <?=$checked;?> />
            <br />


            <span>Описание</span>
            <textarea name="photoDescription" rows="10" class="ckeditor"><?=$photo->getDescription();?></textarea>


        </div>
        <br/>
        <span>Персоны: </span>
        <input type="hidden" name="photoPerson" class="person-field">
        <span class="photo-persons"></span>
        <span class="photo-person-selector"></span>
        <a href="#" class="add-person-to-photo">добавить</a>
        <table cellspacing="3" width="100%">
            <tr>
                <td><input tabindex=60 type="submit" value="Сохранить" class="button" style="font-weight:700"></td>
                <td><input tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
                <td align="right" width="100%"></td>
            </tr>
        </table>
    </form>
</div>