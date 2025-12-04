<?php
$need_authorisation = true;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
    if (!isset($_POST["name"]) || !isset($_POST["description"])) {
        redirect("http://localhost/pages/projects/create.php?error=have_not_attributes&no_name=" .
            (isset($_POST["name"]) ? "no" : "yes") . "&no_desc=" . (isset($_POST["name"]) ? "no" : "yes"));
    }
    $name = $_POST["name"];
    $description = $_POST["description"];

    $stmt = $bd->prepare("select id from projects where user = :user and name = :name");
    $stmt->execute([
        "user" => $user_id,
        "name" => $name
    ]);
    if (count($stmt->fetchAll()) !== 0) {
        redirect("http://localhost/pages/projects/create.php?error=project_is_exists");
    }

    $stmt = $bd->prepare("insert into projects(name, description, user) values (:name, :description, :user)");
    if (!$stmt->execute([
        "name" => $name,
        "description" => $description,
        "user" => $user_id
    ])) {
        redirect("http://localhost/pages/projects/create.php?error=creation_error");
    } else {
        $stmt = $bd->prepare("select count(*) as cnt from projects where user = :user");
        $stmt->execute(
            ["user" => $user_id]
        );
        $result = $stmt->fetch()["cnt"];
        $page = ceil($result / 10);
        redirect("http://localhost/pages/projects/list.php?page=$page");
    }
} else {
    redirect("http://localhost/");
}
