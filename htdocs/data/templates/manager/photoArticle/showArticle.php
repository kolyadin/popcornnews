<?php
$article = $d['article'];
$tags = $d['tags'];
$persons = $d['persons'];

include 'articleEditorScripts.php';

?>

<style>
    div.upload-photo {
        padding: 2px;
        margin: 4px 0;
        border: 1px solid #fff;
    }

    span.persons {
        padding-right: 6px;
    }

    div.upload-photo input {
        display: block;
        margin-bottom: 5px;
        width: 50%;
    }

    #added-photos {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    #added-photos li {
        display: inline;
    }
</style>

<a name="form"></a>
<form method="POST" action="admin.php<?=PhotoArticleManager::createUrl('saveArticle');?>" name="frm" class="Fform" enctype="multipart/form-data">
    <input type="hidden" name="type" value="photoarticles" />
    <input type="hidden" name="action" value="saveArticle" />
    <input type="hidden" name="articleId" value="<?=$article->getId();?>" />

    <table width="100%">
        <tr>
            <td><label for="title">Название фото-статьи</label></td>
            <td><input type="text" name="title" id="title" size="90%" value="<?=$article->getTitle();?>" /></td>
        </tr>
        <tr>
            <td valign="top">Теги</td>
            <td>
                <a class="add-tag" href="#">добавить тег</a>
                <div id="tag-block">
                    <?php
                    if(count($article->getTags()) > 0) {
                        foreach($article->getTags() as $tagId) {
                            ?>
                            <div class="tag-item">
                                <select name="tags[]">
                                    <?php
                                    foreach($tags as $tag) {
                                        if($tag['id'] == $tagId) {
                                            echo "<option value=\"{$tag['id']}\" selected=\"selected\">{$tag['name']}</option>\r\n";
                                        } else {
                                            echo "<option value=\"{$tag['id']}\">{$tag['name']}</option>\r\n";
                                        }
                                    }
                                    ?>
                                </select>
                                <a href="#" class="remove-tag">удалить</a>
                            </div>
                            <?
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td valign="top">Персоны</td>
            <td>
                <a class="add-person" href="#">добавить персону</a>
                <div id="person-block">
                    <?php
                    if(count($article->getPersons()) > 0) {
                        foreach($article->getPersons() as $personId) {
                            ?>
                            <div class="person-item">
                                <select name="persons[]">
                                    <?php
                                    foreach($persons as $person) {
                                        if($person['id'] == $personId) {
                                            echo "<option value=\"{$person['id']}\" selected=\"selected\">{$person['name']}</option>\r\n";
                                        } else {
                                            echo "<option value=\"{$person['id']}\">{$person['name']}</option>\r\n";
                                        }
                                    }
                                    ?>
                                </select>
                                <a href="#" class="remove-person">удалить</a>
                            </div>
                            <?
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td valign="top">Фотографии</td>
            <td>
                <ul id="added-photos">
                    <?php
                    $photos = $article->getPhotos();
                    foreach($photos as $photo) {
                        ?>
                        <li>
                            <a href="<?=PhotoArticleManager::createUrl('showPhoto', array(
                                                                                         'photoId' => $photo->getId(),
                                                                                         'articleId' => $article->getId()
                                                                                    ));?>" title="правка фотографии">
                            <img src="<?=$this->getStaticPath($photo->getPhotoPathBySize('70'));?>" />
                            </a>
                        </li>
                        <?
                    }
                    ?>
                </ul>
                <a class="add-photo" href="#">добавить фотографию</a>
                <div id="photo-block"></div>
            </td>
        </tr>
    </table>

    <table cellspacing="3" width="100%">
        <tr>
            <td><input tabindex=60 type="submit" value="Сохранить" class="button" style="font-weight:700"></td>
            <td><input tabindex=61 type="reset" value="Отменить изменения" class="button"></td>
            <td align="right" width="100%"></td>
        </tr>
    </table>
</form>