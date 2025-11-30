<?php
$title = "Вход";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
if ($is_authorised) {
    redirect("http://localhost/");
}
?>

<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card text-center w-100" style="max-width: 30rem;">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Вход</h5>
        </div>
        <div class="card-body p-4">
            <form action="loginHandler.php" method="post">
                <div class="text-danger">
                    <?= isset($_GET["error"]) ? match ($_GET["error"]) {
                        "login_or_password_error" => "Пользователь не существует или введён неверный пароль ",
                        "incorrect_password_error" => "Пароль не соответствует требованиям",
                        "incorrect_login_error" => "Передана неправильная почта"
                    }  : "";
                    ?>
                </div>
                <div class="mb-3 text-start">
                    <label for="login" class="form-label">Логин</label>
                    <input type="email" required class="form-control" name="login" id="login" aria-describedby="loginHelp" required>
                    <div id="loginHelp" class="form-text">Введите электронную почту.</div>
                </div>
                <div class="mb-3 text-start">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9_@!]{7,}$" class="form-control" name="password" id="password" required>
                    <div id="loginHelp" class="form-text">Введите пароль</div>
                </div>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
        </div>
    </div>
</div>