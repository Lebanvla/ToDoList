<?php
$need_authorisation = true;
$title = "Список проектов";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/Project.php");
if (!$is_authorised) {
    redirect($address . "/login");
}
$page = $_GET["page"] ?? 1;
$data = Project::getBy(
    conditions: ["user" => $user_id],
    limit: 10,
    offset: ($page - 1) * 10
);
include($_SERVER["DOCUMENT_ROOT"] . "/components/project_table.php");
?>
<form method="get" action="create.php" onkeydown="return event.key !== 'Enter';">
    <button type="submit" class="btn btn-success">
        Создать проект
    </button>
</form>