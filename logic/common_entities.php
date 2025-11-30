<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$bd = new PDO("mysql:dbname=todo_list;host=127.0.0.1", "root", "qq");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_authorised = isset($_SESSION["id"]);
if ($is_authorised) {
    $user_id = $_SESSION["id"];
}


function redirect(string $location)
{
    header("Location: $location");
    exit;
}


function getExcerpt($text, $maxLength = 50, $encoding = 'UTF-8')
{
    // Обрезаем до максимальной длины
    if (mb_strlen($text, $encoding) <= $maxLength) {
        return $text;
    }

    $excerpt = mb_substr($text, 0, $maxLength, $encoding);

    // Находим последний пробел (уже с учётом многобайтовости)
    $lastSpace = mb_strrpos($excerpt, ' ', 0, $encoding);
    if ($lastSpace !== false) {
        $excerpt = mb_substr($excerpt, 0, $lastSpace, $encoding);
    }

    return $excerpt . '...';
}
