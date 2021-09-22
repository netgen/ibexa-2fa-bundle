<?php

/**
 * NovaeZ2FABundle.
 *
 * @package   NovaeZ2FABundle
 *
 * @author    Maxim Strukov <maxim.strukov@almaviacx.com>
 * @copyright 2021 AlmaviaCX
 * @license   https://github.com/Novactive/NovaeZ2FA/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZ2FABundle\Core;

use PDO;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
final class UserRepository
{
    /**
     * @var SiteAccessAwareQueryExecutor
     */
    private $queryExecutor;

    public function __construct(SiteAccessAwareQueryExecutor $queryExecutor)
    {
        $this->queryExecutor = $queryExecutor;
    }

    public function insertUpdateUserAuthSecret(int $userId, string $secret, string $prefix, string $backupCodes): void
    {
        if (is_array($this->getUserAuthSecretByUserId($userId))) {
            $query = <<<QUERY
                UPDATE user_auth_secret
                SET {$prefix}_authentication_secret = ?, backup_codes = ?
                WHERE user_contentobject_id = ? 
            QUERY;
            ($this->queryExecutor)(
                $query,
                [$secret, $backupCodes, $userId],
                [PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT]
            );
        } else {
            $query = <<<QUERY
                INSERT INTO user_auth_secret (user_contentobject_id, {$prefix}_authentication_secret, backup_codes) 
                VALUES (?, ?, ?)
            QUERY;
            ($this->queryExecutor)(
                $query,
                [$userId, $secret, $backupCodes],
                [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR]
            );
        }
    }

    public function deleteUserAuthSecrets(int $userId): void
    {
        $query = <<<QUERY
                DELETE FROM user_auth_secret
                WHERE user_contentobject_id = ? 
            QUERY;
        ($this->queryExecutor)($query, [$userId], [PDO::PARAM_INT]);
    }

    public function deleteUserAuthSecret(int $userId, string $prefix): void
    {
        $query = <<<QUERY
                UPDATE user_auth_secret
                SET {$prefix}_authentication_secret = '', backup_codes = ''
                WHERE user_contentobject_id = ? 
            QUERY;
        ($this->queryExecutor)($query, [$userId], [PDO::PARAM_INT]);
    }

    public function getUserAuthSecretByUserId(int $userId)
    {
        $query = <<<QUERY
                SELECT *
                FROM user_auth_secret
                WHERE user_contentobject_id = ?
                LIMIT 1
            QUERY;

        return ($this->queryExecutor)($query, [$userId], [PDO::PARAM_INT])->fetchAssociative();
    }

    public function updateBackupCodes(int $userId, string $backupCodes): void
    {
        $query = <<<QUERY
                UPDATE user_auth_secret
                SET backup_codes = ?
                WHERE user_contentobject_id = ? 
            QUERY;
        ($this->queryExecutor)($query, [$backupCodes, $userId], [PDO::PARAM_STR, PDO::PARAM_INT]);
    }
}
