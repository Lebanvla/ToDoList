<?php
$need_authorisation = true;
$title = "Подробнее о проекте";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/Project.php");
$id = $_GET["id"];
$project = Project::getBy(conditions: [
    "id" => $id,
    "user" => $user_id
])[0];

if (count($project) === 0) {
    redirect("http://localhost/");
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="bi bi-kanban me-2"></i>Информация о проекте
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-card-heading me-1"></i>Название проекта
                    </h6>
                    <p class="fs-5 fw-bold"><?= htmlspecialchars($project['name'] ?? 'Не указано') ?></p>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-flag me-1"></i>Статус проекта
                    </h6>
                    <span class="badge fs-6 py-2 px-3 <?= (!($project['is_ended'] ?? false)) ? 'bg-success' : 'bg-secondary' ?>">
                        <?= (!($project['is_ended'] ?? false)) ? 'Активен' : 'Завершен' ?>
                    </span>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-calendar-plus me-1"></i>Дата создания
                    </h6>
                    <p class="fs-5">
                        <?= isset($project['created_at']) ? date('d.m.Y H:i', strtotime($project['created_at'])) : 'Не указана' ?>
                    </p>
                </div>

                <?php if ($project['is_ended'] ?? false): ?>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-calendar-check me-1"></i>Дата завершения
                        </h6>
                        <p class="fs-5">
                            <?= isset($project['ended_at']) ? date('d.m.Y H:i', strtotime($project['ended_at'])) : 'Не указана' ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-text-paragraph me-1"></i>Описание проекта
                    </h6>
                    <div class="card border-light bg-light-subtle">
                        <div class="card-body p-3">
                            <p class="card-text">
                                <?= nl2br(htmlspecialchars($project['description'] ?? 'Описание отсутствует')) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($project['updated_at'] ?? false): ?>
        <div class="card-footer text-muted small">
            <i class="bi bi-clock-history me-1"></i>Последнее обновление:
            <?= date('d.m.Y H:i', strtotime($project['updated_at'])) ?>
        </div>
    <?php endif; ?>

    <div>
        <div class="text-center">
            <form method="post" action="delete.php" onkeydown="return event.key !== 'Enter';">
                <input type="hidden" name="id" value='<?= $row["id"] ?>'>
                <button type="submit" class="btn btn-danger">
                    Удалить проект
                </button>
                <a href='http://localhost/pages/projects/update.php?id=<?= $id ?>' class="btn btn-success">
                    Изменить проект
                </a>
            </form>
        </div>
    </div>
</div>