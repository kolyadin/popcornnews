<?
//файл меню - подключение внешних модулей
//ссылка и название задается в массиве
// ссылка должна начинаться с "./"

/*
$modul_link[]="./file.php"; // ссылка, начинается с "./"
$modul_name[]="внешний модуль"; // название
$modul_icon[]="Program_Group.gif"; // иконка, из папки icons
*/


$modul_link[]="./moder_images.php"; // ссылка, начинается с "./"
$modul_name[]="Модерация картинок"; // название
$modul_icon[]="Program_Group.gif"; // иконка, из папки icons

$modul_link[]="./already_moder_images.php"; // ссылка, начинается с "./"
$modul_name[]="Проверенные картинки"; // название
$modul_icon[]="Program_Group.gif"; // иконка, из папки icons

$modul_link[]="./show_users.php"; // ссылка, начинается с "./"
$modul_name[]="Бан пользователей"; // название
$modul_icon[]="Program_Group.gif"; // иконка, из папки icons

$modul_link[]="./show_subscribe.php"; // ссылка, начинается с "./"
$modul_name[]="Рассылка"; // название
$modul_icon[]="Program_Group.gif"; // иконка, из папки icons

$modul_link[] = './admin.php?type=comments&action=news_list';
$modul_name[] = 'Коментарии';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=facts';
$modul_name[] = 'Факты';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=topics';
$modul_name[] = 'Обсуждения';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=countries';
$modul_name[] = 'Страны и города';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=stat';
$modul_name[] = 'Статистика';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=weekly_stat';
$modul_name[] = 'Статистика по неделям';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=users';
$modul_name[] = 'Пользователи';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=fanfics';
$modul_name[] = 'Фанфики';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=tickets';
$modul_name[] = 'Рассылка в личку';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './make_trailer.php';
$modul_name[] = 'Добавление видео';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './konkurs.php';
$modul_name[] = 'Конкурсы';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=ask';
$modul_name[] = 'Вопросы администрации';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './konkurs2.php';
$modul_name[] = 'Еще конкурсы';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './challenge_twilight_words.php';
$modul_name[] = 'Опросы, результат словами (Сумерки)';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './cron/cron.php?xml_for_widget';
$modul_name[] = 'Перегенерировать XML для виджета';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './guess_star.php';
$modul_name[] = 'Угадай звезду';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=community';
$modul_name[] = 'Сообщества';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=yourstyle';
$modul_name[] = 'YourStyle';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=columns';
$modul_name[] = 'Рубрики новостей';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=photoarticles';
$modul_name[] = 'Фото-статьи';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=semiautotag';
$modul_name[] = 'Поиск новостей по персонам';
$modul_icon[] = 'Program_Group.gif';

$modul_link[] = './admin.php?type=commentsettings';
$modul_name[] = 'Настройки комментариев';
$modul_icon[] = 'SystemConfiguration-32.png';