<?php

class Project extends Model
{
    protected static ?string $tableName = "projects";

    public static function getFields(): array
    {
        return [
            "id" => "i",
            "name" => "s",
            "description" => "s",
            "created_at" => "s",
            "user" => "i",
            "is_ended" => "i",
            "ended_at" => "s"
        ];
    }

    public static function getUsersProjects($user): array
    {
        return User::getBy(conditions: [
            "user" => $user
        ]);
    }
}
