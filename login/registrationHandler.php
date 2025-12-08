<?php
$need_authorisation = false;
$path = "http://localhost/login/registration.php";
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/User.php");
if ($is_authorised) {
    redirect("http://localhost/");
}
if ($passwordCheck !== $password) {
    redirect("$path?error=password_repeat_error");
}
if (count(User::getByLogin($_POST["login"])) > 0) {
    redirect("$path?error=user_is_exist");
}
if (!filter_var($_POST["login"], FILTER_VALIDATE_EMAIL)) {
    redirect("$path?error=incorrect_login_error");
}
if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9_@!]{7,}$/", $_POST["password"])) {
    redirect("$path?error=incorrect_password_error");
}
$login = $_POST["login"];
$passwordHash = password_hash($_POST["password"], PASSWORD_ARGON2I);
$_SESSION["id"] = User::create([
    "login" => $login,
    "password" => $passwordHash
]);
redirect("http://localhost");
