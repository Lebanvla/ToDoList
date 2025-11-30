<?php
$bd = new PDO("mysql:dbname=todo_list;host=127.0.0.1", "root", "qq");
$is_authorised = isset($_SESSION["id"]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect(string $location)
{
    header("Location: $location");
    exit;
}
