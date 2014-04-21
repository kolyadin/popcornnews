<!DOCTYPE html>
<html>
<head>
    <title>Система управления сайтом "TRAFFIC"</title>
    <link rel="stylesheet" type="text/css" href="/manager/styles/global.css">
    <link rel="stylesheet" type="text/css" href="/manager/styles/additional.css">
    <!--link rel="stylesheet" href="/manager/redactor/css/redactor.css" /-->

    <script type="text/javascript" src="http://v1.popcorn-news.ru/js/jquery/jquery.js"></script>
    <!--script type="text/javascript" src="/manager/redactor/redactor.min.js"></script-->
    <script type="text/javascript" src="/manager/ckeditor/ckeditor.js"></script>
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

        div.upload-photo input.check {
            display: inline;
            width: auto;
        }

        #added-photos {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        #added-photos li {
            display: inline-block;
        }
    </style>
</head>
<body>
<? include 'navigation.php'; ?>
<?
$file = basename($d['content']);
include $file;
?>
</body>
</html>