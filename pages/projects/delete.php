<?php
$need_authorisation = true;
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/Project.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"];
    $result = Project::getBy(
        ["id"],
        [
            "id" => $id
        ]
    );
    if (count($result) === 0) {
        redirect("http://localhost/pages/projects?page=1");
    } else {
        Project::deleteById($id);
        redirect("http://localhost/pages/projects?page=1");
    }
} else {
    redirect("http://localhost/pages/projects/?page=1");
}
