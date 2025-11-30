<?php
$path = "http://localhost/login/registration.php";
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
echo "Here";
if ($passwordCheck !== $password) {
    redirect("$path?error=password_repeat_error");
}
$stmt = $bd->prepare("select * from users where login = :login");
$stmt->execute([
    "login" => $_POST["login"]
]);

if (count($stmt->fetchAll()) > 0) {
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
$bd->prepare("insert into users(login, password) values (:login, :password)")->execute([
    "login" => $login,
    "password" => $passwordHash
]);
$_SESSION["id"] = $bd->lastInsertId();
redirect("http://localhost");
