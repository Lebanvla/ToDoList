<?php
$need_authorisation = true;
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $stmt = $bd->prepare("select user from projects where id = :project");
    $stmt->execute(
        ["project" => $id]
    );
    $result = $stmt->fetch();
    if (!$result || $result["user"] !== $_SESSION["id"]) {
        redirect("http://localhost/pages/projects/list.php?page=1");
    } else {
        $id = $_POST["id"];
        $stmt = $bd->prepare("delete from projects where id = :project");
        $stmt->execute(
            ["project" => $id]
        );
        redirect("http://localhost/pages/projects/list.php?page=1");
    }
} else {
    redirect("http://localhost/pages/projects/list.php?page=1");
}
