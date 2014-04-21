<script>
    var tags = <?=_json_encode($tags);?>;
    var persons = <?=_json_encode($persons);?>;
    var photoCount = 0;

    function addTagHandler() {
        $('a.add-tag').click(function () {
            var tagBlock = $('#tag-block');
            tagBlock.append('<div class="tag-item"></div>');
            var tagItem = $('div.tag-item:last', tagBlock);
            tagItem.append('<select name="tags[]"></select>');
            var tagItemSelect = $('select', tagItem);
            $.each(tags, function (id, item) {
                tagItemSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
            });

            tagItem.append('<a href="#" class="remove-tag">удалить</a>');
            $('a.remove-tag').click(function () {
                $(this).parent().remove();
                return false;
            });

            return false;
        });
    }

    function addPersonsHandler(block, name) {
        $('a.add-person').click(function () {
            var personBlock = $(block);
            personBlock.append('<div class="person-item"></div>');
            var personItem = $('div.person-item:last', personBlock);
            personItem.append('<select name="'+name+'[]"></select>');
            var personItemSelect = $('select', personItem);
            $.each(persons, function (id, item) {
                personItemSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
            personItem.append('<a href="#" class="remove-person">удалить</a>');
            $('a.remove-person').click(function () {
                $(this).parent().remove();
                return false;
            });
            return false;
        });
    }

    function addPhotoHandler() {
        $('a.add-photo').click(function () {
            photoCount++;

            var photoPersons = {};

            var photoBlock = $('#photo-block');
            photoBlock.append('<div class="upload-photo"></div>');
            var photoItem = $('div.upload-photo:last', photoBlock);

            photoItem.append('<span>Фотография</span>');
            photoItem.append('<input type="file" name="photos[]" />');

            photoItem.append('<span>Название фотографии</span>');
            photoItem.append('<input type="text" name="photoTitle[]" />');

            photoItem.append('<span>Источник</span>');
            photoItem.append('<input type="text" name="photoSource[]" />');

            photoItem.append('<span>Включить увеличение</span>');
            photoItem.append('<input type="hidden" name="photoZoomable[]" value="0" />');
            photoItem.append('<input type="checkbox" class="check" />');
            photoItem.append('<br />');

            var photoZoomable = $('input[type=hidden]:last', photoItem);

            $('.check:last', photoItem).click(function(){
                photoZoomable.val($(this).attr('checked') ? 1 : 0);
            });

            photoItem.append('<span>Описание</span>');
            photoItem.append('<textarea name="photoDescription[]" rows="10" id="editor'+photoCount+'"></textarea>');

            CKEDITOR.replace('editor'+photoCount);

            photoItem.append('<br />');
            photoItem.append('<span>Персоны: </span>');

            photoItem.append('<input type="hidden" name="photoPerson[]" />');
            var personField = $('input:last', photoItem);

            photoItem.append('<span class="photo-persons"></span>');
            var personView = $('span.photo-persons', photoItem);

            photoItem.append('<span class="photo-person-selector"></span>');
            var personSelector = $('span.photo-person-selector', photoItem);

            photoItem.append('<a href="#" class="add-person-to-photo">добавить</a>');
            $('a.add-person-to-photo', photoItem).click(function(){
                var anchor = $(this);
                anchor.hide();
                personSelector.append('<select></select>');
                var select = $('select', personSelector);
                $.each(persons, function(id, item){
                    select.append('<option value="'+item.id+'">'+item.name+'</option>');
                });
                personSelector.append('<a href="#">сохранить</a>');
                $('a:last', personSelector).click(function(){
                    personId = select.val();
                    personName = $('option[value='+personId+']', select).text();
                    notExists = (photoPersons[personId] == undefined);
                    photoPersons[personId] = personId;

                    personSelector.empty();
                    anchor.show();
                    if(notExists) {
                        personView.append('<span class="persons" value="'+personId+'">'+personName+' (<a href="#">X</a>)</span>');
                        $('a:last', personView).click(function(){
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


            return false;
        });
    }

    function initExistsTagsAndPersonsRemover() {
        $('a.remove-tag').click(function () {
            $(this).parent().remove();
            return false;
        });

        $('a.remove-person').click(function () {
            $(this).parent().remove();
            return false;
        });
    }

    $(document).ready(function(){
        addTagHandler();
        addPersonsHandler('#person-block', 'persons');
        addPhotoHandler();
        initExistsTagsAndPersonsRemover();
    })

</script>