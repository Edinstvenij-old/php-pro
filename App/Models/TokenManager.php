<?php

namespace App\Models;

use Core\DB;

class TokenManager
{
    public static function storeToken(int $userId, string $token, string $expiration): bool
    {
        $db = DB::connect();

        // Подготовленный запрос для вставки токена
        $stmt = $db->prepare("INSERT INTO tokens (user_id, token, token_expired_at) VALUES (:user_id, :token, :token_expired_at)");

        // Привязываем параметры к подготовленному запросу
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
        $stmt->bindParam(':token_expired_at', $expiration, \PDO::PARAM_STR);

        $result = $stmt->execute();

        return $result;
    }
}
