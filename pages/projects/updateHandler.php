<?php
$need_authorisation = true;
include($_SERVER["DOCUMENT_ROOT"] . "/logic/common_entities.php");
include($_SERVER["DOCUMENT_ROOT"] . "/model/Project.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["id"])) {
        redirect("http://localhost/");
    }
    $project_id = $_POST["id"];
    $projects = Project::getBy(
        null,
        [
            "id" => [
                "value" => $project_id,
                "operation" => "="
            ],
            "user" => [
                "value" => $user_id,
                "operation" => "="
            ]
        ]
    );

    if (empty($projects)) {
        redirect("http://localhost/");
    }

    $project = $projects[0];

    $updates = [];

    $new_name = trim($_POST["name"] ?? '');
    $new_description = trim($_POST["description"] ?? '');
    $new_is_ended = isset($_POST["is_ended"]);

    if ($project["name"] !== $new_name && $new_name !== '') {
        $updates["name"] = $new_name;
    }

    if ($project["description"] !== $new_description) {
        $updates["description"] = $new_description;
    }

    $current_is_ended = (bool) ($project["is_ended"] ?? false);

    if ($current_is_ended !== $new_is_ended) {
        $updates["is_ended"] = $new_is_ended ? 1 : 0;
        $updates["ended_at"] = $new_is_ended ? date('Y-m-d H:i:s') : null;
    }

    if (!empty($updates)) {
        Project::updateById($project_id, $updates);
    }

    redirect("/pages/projects");
} else {
    redirect("http://localhost/");
}
