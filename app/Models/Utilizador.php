<?php

declare(strict_types=1);

final class Utilizador
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findActiveByEmail(string $email): ?array
    {
        $sql = "
            SELECT
                u.id,
                u.uuid,
                u.papel_id,
                u.nome_completo,
                u.email,
                u.senha_hash,
                u.bloqueado,
                u.ativo,
                p.nome AS papel_nome,
                p.nivel AS papel_nivel
            FROM utilizador u
            INNER JOIN papel p ON p.id = u.papel_id
            WHERE u.email = :email
              AND u.ativo = 1
              AND u.bloqueado = 0
              AND u.eliminado_em IS NULL
              AND p.ativo = 1
              AND p.eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                u.id,
                u.uuid,
                u.papel_id,
                u.nome_completo,
                u.email,
                u.telefone,
                u.ativo,
                u.bloqueado,
                p.nome AS papel_nome,
                p.nivel AS papel_nivel
            FROM utilizador u
            INNER JOIN papel p ON p.id = u.papel_id
            WHERE u.id = :id
              AND u.ativo = 1
              AND u.bloqueado = 0
              AND u.eliminado_em IS NULL
              AND p.ativo = 1
              AND p.eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function createCitizen(
        string $name,
        string $email,
        ?string $phone,
        string $password
    ): int {
        // O papel é definido no servidor, nunca pelo formulário.
        $stmt = $this->db->prepare("
            SELECT id
            FROM papel
            WHERE id = 1
              AND nome = 'Cidadão'
              AND ativo = 1
              AND eliminado_em IS NULL
            LIMIT 1
        ");
        $stmt->execute();

        $citizenRoleId = $stmt->fetchColumn();

        if ($citizenRoleId === false) {
            throw new RuntimeException(
                'O papel de cidadão não está disponível.'
            );
        }

        $uuid = $this->generateUuid();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($hash === false) {
            throw new RuntimeException(
                'Não foi possível proteger a palavra-passe.'
            );
        }

        $sql = "
            INSERT INTO utilizador (
                uuid,
                papel_id,
                nome_completo,
                email,
                telefone,
                senha_hash
            ) VALUES (
                :uuid,
                :papel_id,
                :nome_completo,
                :email,
                :telefone,
                :senha_hash
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uuid' => $uuid,
            'papel_id' => (int) $citizenRoleId,
            'nome_completo' => $name,
            'email' => $email,
            'telefone' => $phone !== '' ? $phone : null,
            'senha_hash' => $hash,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE utilizador
            SET ultimo_login = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);
    }

    private function generateUuid(): string
    {
        $bytes = random_bytes(16);

        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}