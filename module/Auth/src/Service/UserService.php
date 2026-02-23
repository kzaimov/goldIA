<?php

declare(strict_types=1);

namespace Auth\Service;

use Laminas\Db\Adapter\AdapterInterface;

class UserService
{
    public function __construct(private readonly AdapterInterface $db)
    {
    }

    public function fetchAll(): array
    {
        $sql = <<<'SQL'
            SELECT u.id, u.username, u.email, u.is_active, u.created_at, r.name AS role
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            ORDER BY u.id ASC
        SQL;

        return $this->db->query($sql, [])->toArray();
    }

    public function create(string $username, string $email, string $password, string $role): void
    {
        $sqlInsert = <<<'SQL'
            INSERT INTO users (username, email, password_hash)
            VALUES (?, ?, ?)
            RETURNING id
        SQL;

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertResult = $this->db->query($sqlInsert, [$username, $email, $passwordHash]);
        $userId = (int) $insertResult->current()['id'];

        $roleSql = 'SELECT id FROM roles WHERE name = ? LIMIT 1';
        $roleResult = $this->db->query($roleSql, [$role]);
        $roleRow = $roleResult->current();
        if (! $roleRow) {
            return;
        }

        $this->db->query('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)', [$userId, (int) $roleRow['id']]);
    }

    public function delete(int $userId): void
    {
        $this->db->query('DELETE FROM users WHERE id = ?', [$userId]);
    }
}
