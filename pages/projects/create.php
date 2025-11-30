<?php
$need_authorisation = true;
$title = "Создание проекта";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
?>

<div>
</div>
<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card text-center w-100" style="max-width: 30rem;">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Создание проекта</h5>
        </div>
        <div class="card-body p-4">
            <form action="createHandler.php" method="post">
                <div class="text-danger">
                    <?= isset($_GET["error"]) ? match ($_GET["error"]) {
                        "have_not_attributes" => "Не заполнены обязательные поля ",
                        "project_is_exists" => "Проект с таким именем уже существует",
                        "creation_error" => "Неизвестная ошибка создания"
                    }  : "";
                    ?>
                </div>
                <div class="mb-3 text-start">
                    <label for="projectName" class="form-label">Название проекта</label>
                    <input type="text" class="form-control" name="name" required aria-describedby="nameHelp">
                    <div id="nameHelp" class="form-text">Введите название проекта</div>
                </div>
                <div class="mb-3 text-start">
                    <label for="projectDescription" class="form-label">Описание проекта</label>
                    <textarea class="form-control" name="description" required aria-describedby="descriptionHelp" rows="10"></textarea>
                    <div id="descriptionHelp" class="form-text">Введите описание проекта</div>
                </div>
        </div>
        <button type="submit" class="btn btn-success">Создать проект</button>
        </form>
    </div>
</div>