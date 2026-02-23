<?php

declare(strict_types=1);

namespace Auth\Service;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Session\Container;

class AuthService
{
    private const SESSION_NAMESPACE = 'goldai_auth';

    public function __construct(private readonly AdapterInterface $db)
    {
    }

    public function login(string $email, string $password): bool
    {
        $sql = <<<'SQL'
            SELECT u.id, u.email, u.username, u.password_hash, r.name AS role
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.email = ? AND u.is_active = TRUE
            LIMIT 1
        SQL;

        $result = $this->db->query($sql, [$email]);
        $user = $result->current();

        if (! $user || ! password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        $session = new Container(self::SESSION_NAMESPACE);
        $session->offsetSet('user', [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'username' => (string) $user['username'],
            'role' => (string) $user['role'],
        ]);

        return true;
    }

    public function logout(): void
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $session->getManager()->destroy();
    }

    public function getIdentity(): ?array
    {
        $session = new Container(self::SESSION_NAMESPACE);
        $user = $session->offsetGet('user');

        return is_array($user) ? $user : null;
    }

    public function hasIdentity(): bool
    {
        return $this->getIdentity() !== null;
    }

    public function isAdmin(): bool
    {
        $identity = $this->getIdentity();

        return $identity !== null && $identity['role'] === 'admin';
    }
}
