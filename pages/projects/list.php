<?php
$title = "Список проектов";
include($_SERVER["DOCUMENT_ROOT"] . "/components/head.php");
if (!$is_authorised) {
    redirect($address . "/login");
}
$page = $_GET["page"] ?? 1;
$stmt = $bd->prepare("
                        select 
                            id, name, description, 
                            created_at, is_ended, ended_at 
                        from 
                            projects 
                        where 
                            user = :user 
                        LIMIT 10 OFFSET :page");
$stmt->bindValue(':user', $_SESSION["id"]);
$stmt->bindValue(':page', $page - 1, PDO::PARAM_INT); // ← Важно указать тип
$stmt->execute();
$data = $stmt->fetchAll();
include($_SERVER["DOCUMENT_ROOT"] . "/components/project_table.php");
?>
<form method="get" action="create.php" onkeydown="return event.key !== 'Enter';">
    <button type="submit" class="btn btn-success">
        Создать проект
    </button>
</form>