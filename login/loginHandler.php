<?php
$need_authorisation = false;
$path = "http://localhost/login";
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/User.php");
if ($is_authorised) {
    redirect("http://localhost/");
}

$result = User::getByLogin($_POST["login"]);

if (!$result) {
    redirect("$path?error=login_or_password_error");
}
if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9_@!]{7,}$/", $_POST["password"])) {
    redirect("$path?error=incorrect_password_error");
}
$login = $_POST["login"];
if (!password_verify($_POST["password"], $result["password"])) {
    redirect("$path?error=login_or_password_error");
}
$_SESSION["id"] = $result["id"];
redirect("http://localhost");
