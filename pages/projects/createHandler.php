<?php
$need_authorisation = true;
include($_SERVER["DOCUMENT_ROOT"] . "/model/Project.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
    if (!isset($_POST["name"]) || !isset($_POST["description"])) {
        redirect("http://localhost/pages/projects/create.php?error=have_not_attributes&no_name=" .
            (isset($_POST["name"]) ? "no" : "yes") . "&no_desc=" . (isset($_POST["name"]) ? "no" : "yes"));
    }
    $name = $_POST["name"];
    $description = $_POST["description"];

    $result = Project::getBy(["id"], [
        "user" => $user_id,
        "name" => $name
    ]);
    if (count($result) !== 0) {
        redirect("http://localhost/pages/projects/create.php?error=project_is_exists");
    }
    $id = Project::create(
        [
            "name" => $name,
            "description" => $description,
            "user" => $user_id
        ]
    );
    $result = Project::countBy([
        "user" => [
            "operation" => "=",
            "value" => $user_id
        ]
    ]);
    $page = ceil($result / 10);
    redirect("http://localhost/pages/projects?page=$page");
} else {
    redirect("http://localhost/");
}
