<?php
$need_authorisation = true;
$title = "Изменение проекта";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
$id = $_GET["id"];
$stmt = $bd->prepare("select name, description, created_at, is_ended, ended_at from projects where id = :project and user = :user");
$stmt->execute(
    [
        "project" => $id,
        "user" => $_SESSION["id"]
    ]
);
$project = $stmt->fetch();
if (!$project) {
    redirect("http://localhost/");
}
?>
<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card text-center w-100" style="max-width: 60rem;"> <!-- Увеличил max-width -->
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Редактирование проекта</h5>
        </div>
        <div class="card-body p-5">
            <form action="updateHandler.php" method="post">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="text-danger mb-4">
                    <?= isset($_GET["error"]) ? match ($_GET["error"]) {
                        "have_not_attributes" => "Не заполнены обязательные поля",
                        "project_is_exists" => "Проект с таким именем уже существует",
                        "update_error" => "Ошибка обновления проекта",
                        "not_found" => "Проект не найден"
                    } : ""; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4 text-start">
                            <label for="name" class="form-label fw-bold">Название проекта</label>
                            <input type="text" class="form-control form-control-lg" name="name" id="name"
                                value="<?= htmlspecialchars($project['name'] ?? '') ?>"
                                required aria-describedby="nameHelp">
                            <div id="nameHelp" class="form-text">Введите название проекта</div>
                        </div>

                        <div class="mb-4 text-start">
                            <label for="is_ended" class="form-label fw-bold">Статус проекта</label>
                            <select class="form-select form-select-lg" name="is_ended" id="is_ended">
                                <option value="0" <?= (!($project['is_ended'] ?? false)) ? 'selected' : '' ?>>Активен</option>
                                <option value="1" <?= ($project['is_ended'] ?? false) ? 'selected' : '' ?>>Завершен</option>
                            </select>
                            <div class="form-text">Выберите статус проекта</div>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold">Дата создания</label>
                            <input type="text" class="form-control form-control-lg"
                                value="<?= date('d.m.Y H:i', strtotime($project['created_at'] ?? '')) ?>"
                                readonly disabled>
                            <div class="form-text">Дата создания проекта</div>
                        </div>

                        <?php if ($project['is_ended'] ?? false): ?>
                            <div class="mb-4 text-start">
                                <label class="form-label fw-bold">Дата завершения</label>
                                <input type="text" class="form-control form-control-lg"
                                    value="<?= date('d.m.Y H:i', strtotime($project['ended_at'] ?? '')) ?>"
                                    readonly disabled>
                                <div class="form-text">Дата завершения проекта</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4 text-start">
                            <label for="description" class="form-label fw-bold">Описание проекта</label>
                            <textarea class="form-control" name="description" id="description"
                                required aria-describedby="descriptionHelp"
                                rows="12" style="min-height: 200px;"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                            <div id="descriptionHelp" class="form-text">Введите описание проекта</div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">Обновить проект</button>
                        <a href="/pages/projects/list.php" class="btn btn-outline-secondary btn-lg">Назад к списку</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>