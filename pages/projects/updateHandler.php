<?php
$need_authorisation = true;
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $bd->prepare(
        "
                            select 
                                name, description, created_at,
                                is_ended, ended_at 
                            from 
                                    projects 
                            where 
                                id = :project and user = :user
                        "
    );
    $stmt->execute([
        "project" => $_POST["id"],
        "user" => $_SESSION["id"]
    ]);
    $result = $stmt->fetch();
    if (!$result) {
        redirect("http://localhost/");
    }
    extract($result);
    $updates = [];
    $values = [];

    if ($name !== $_POST["name"]) {
        $updates[] = "name = ?";
        $values[] = $_POST["name"];
    }

    if ($description !== $_POST["description"]) {
        $updates[] = "description = ?";
        $values[] = $_POST["description"];
    }

    if ($is_ended !== $_POST["is_ended"]) {
        $updates[] = "is_ended = ?";
        $values[] = $_POST["is_ended"];
        $is_ended = isset($_POST["is_ended"]) && $_POST["is_ended"];
        $updates[] = "ended_at = " . ($is_ended ? "CURRENT_TIMESTAMP()" : "NULL");
    }
    if (count($values) === 0) {
        redirect("/pages/projects/list.php");
    }
    $sql = 'update projects set ' . implode(", ", $updates) . " where id = " . $_POST["id"];
    $stmt = $bd->prepare($sql);
    $stmt->execute($values);
    redirect("/pages/projects/list.php");
} else {
    redirect("http://localhost/");
}
