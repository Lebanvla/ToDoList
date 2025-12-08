<?php
include_once("Model.php");
class User extends Model
{
    protected static ?string $tableName = "users";

    public static function getFields(): array
    {
        return [
            "id" => "i",
            "login" => "s",
            "password" => "s",
            "created_at" => "s"
        ];
    }

    public static function getByLogin(string $login): array
    {
        return User::getBy(conditions: [
            "login" => $login
        ]);
    }
}
