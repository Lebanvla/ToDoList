<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$address =  "$protocol://$host";
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
?>

<head>
    <title><?= $title ?></title>
    <link href='<?= $address . "/assets/css/bootstrap.min.css" ?>' rel=stylesheet integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="container">