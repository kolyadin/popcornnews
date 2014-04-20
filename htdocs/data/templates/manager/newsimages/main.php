<script type="text/javascript">
    $(document).ready(function(){
        $('a.search').click(function(){
            searchNews();
            return false;
        });
        $('#news-search').keypress(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                searchNews();
            }
        });
    });

    function updateNewsList(data) {
        if (data.status == true) {
            var newsList = $('#news-list');
            newsList.empty();
            $.each(data.news, function (id, item) {
                newsList.append('<li><a class="select-news" href="' + item.img + '">' +
                                    '<img src="' + item.tmb + '" /></a></li>');
            });
            $('a.select-news').click(function() {
                $(parent.document).find('#extern_main_photo').val($(this).attr('href'));
                $(parent.document).find('td.FileFormInput img').attr('src', $('img', $(this)).attr('src'));
                $(parent.document).find('div.main-photo-selector').hide();
                return false;
            });
        } else {
            alert('Ничего не найдено');
        }
    }
    function searchNews() {
        var value = $('#news-search').val();
        if(value.length == 0) {
            alert('Нужно что-то ввести, что бы что-то найти');
            return;
        }
        $.getJSON('<?=NewsImagesManager::createUrl('search');?>&q=' + encodeURI(value), function(data){
            updateNewsList(data);
        });
    }
</script>
<style>
    #news-searcher {
        margin-bottom: 20px;
    }
    #news-searcher label{
        font-weight: bold;
        display: block;
    }
    #news-search {
        width: 400px;
    }
    #news-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    #news-list li {
        padding: 2px 10px;
        display: inline-block;
    }
    a.select-news {
        display: block;
        padding: 4px;
    }
    a.select-news:hover {
        background-color: #c2d1dC;
    }
    /*a.select-news img {
        float: left;
    }
    a.select-news span {
        padding-left: 20px;
    }*/
</style>
<div id="news-searcher">
    <label for="news-search">Название или текст новости</label>
    <input type="text" id="news-search"/>
    <a href="#" class="search">искать</a>
</div>
<div class="list">
    <ul id="news-list"></ul>
</div>