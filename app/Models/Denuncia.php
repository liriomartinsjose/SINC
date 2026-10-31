<?php

declare(strict_types=1);

final class Denuncia
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Gera um UUID v4.
     */
    private function gerarUuid(): string
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

    /**
     * Devolve os tipos de ocorrência activos.
     */
    public function getTiposAtivos(): array
    {
        $sql = "
            SELECT id, nome, descricao, gravidade_padrao
            FROM tipo_ocorrencia
            WHERE ativo = 1
              AND eliminado_em IS NULL
            ORDER BY nome ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devolve os bairros activos.
     */
    public function getBairrosAtivos(): array
    {
        $sql = "
            SELECT id, nome, comuna_id
            FROM bairro
            WHERE ativo = 1
              AND eliminado_em IS NULL
            ORDER BY nome ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Confirma se o tipo de ocorrência está disponível.
     */
    public function tipoExiste(int $tipoId): bool
    {
        $sql = "
            SELECT id
            FROM tipo_ocorrencia
            WHERE id = :id
              AND ativo = 1
              AND eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $tipoId]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Confirma se o bairro está disponível.
     */
    public function bairroExiste(int $bairroId): bool
    {
        $sql = "
            SELECT id
            FROM bairro
            WHERE id = :id
              AND ativo = 1
              AND eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $bairroId]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Cria localização, denúncia e anexos na mesma transacção.
     *
     * Cada anexo deve conter:
     * uuid, nome_original, nome_arquivo, tipo_mime, extensao,
     * tamanho_bytes, caminho, hash_sha256.
     *
     * ficheiro_absoluto é opcional e serve apenas para limpeza
     * dos ficheiros se a operação falhar.
     */

public function criar(
    array $dados,
    int $utilizadorId,
    array $anexos = []
): int {
    if ($utilizadorId <= 0) {
        throw new InvalidArgumentException(
            'O utilizador autenticado é inválido.'
        );
    }

    $etapa = 'iniciar transação';

    try {
        $this->db->beginTransaction();

        /*
         * 1. Criar a localização da denúncia.
         */
        $etapa = 'preparar inserção da localização';

        $sqlLocalizacao = "
            INSERT INTO localizacao (
                uuid,
                bairro_id,
                rua,
                numero,
                referencia,
                latitude,
                longitude,
                tipo_geometria
            ) VALUES (
                :uuid,
                :bairro_id,
                :rua,
                :numero,
                :referencia,
                :latitude,
                :longitude,
                'PONTO'
            )
        ";

        $stmtLocalizacao = $this->db->prepare($sqlLocalizacao);

        $etapa = 'executar inserção da localização';

        $stmtLocalizacao->execute([
            ':uuid' => $this->gerarUuid(),
            ':bairro_id' => (int) $dados['bairro_id'],
            ':rua' => ($dados['rua'] ?? '') !== ''
                ? $dados['rua']
                : null,
            ':numero' => ($dados['numero'] ?? '') !== ''
                ? $dados['numero']
                : null,
            ':referencia' => ($dados['referencia'] ?? '') !== ''
                ? $dados['referencia']
                : null,
            ':latitude' => $dados['latitude'],
            ':longitude' => $dados['longitude'],
        ]);

        $localizacaoId = (int) $this->db->lastInsertId();

        if ($localizacaoId <= 0) {
            throw new RuntimeException(
                'Não foi possível obter o ID da localização criada.'
            );
        }

        /*
         * 2. Criar a denúncia associada à localização.
         */
        $etapa = 'preparar inserção da denúncia';

        $sqlDenuncia = "
            INSERT INTO denuncia (
                uuid,
                utilizador_id,
                tipo_ocorrencia_id,
                localizacao_id,
                titulo,
                descricao,
                anonima,
                origem
            ) VALUES (
                :uuid,
                :utilizador_id,
                :tipo_ocorrencia_id,
                :localizacao_id,
                :titulo,
                :descricao,
                :anonima,
                'WEB'
            )
        ";

        $stmtDenuncia = $this->db->prepare($sqlDenuncia);

        $etapa = 'executar inserção da denúncia';

        $stmtDenuncia->execute([
            ':uuid' => $this->gerarUuid(),
            ':utilizador_id' => $utilizadorId,
            ':tipo_ocorrencia_id' => (int) $dados['tipo_ocorrencia_id'],
            ':localizacao_id' => $localizacaoId,
            ':titulo' => $dados['titulo'],
            ':descricao' => $dados['descricao'],
            ':anonima' => !empty($dados['anonima']) ? 1 : 0,
        ]);

        $denunciaId = (int) $this->db->lastInsertId();

        if ($denunciaId <= 0) {
            throw new RuntimeException(
                'Não foi possível obter o ID da denúncia criada.'
            );
        }

        /*
         * 3. Guardar os metadados dos anexos, se existirem.
         *
         * Os ficheiros físicos devem ter sido previamente
         * validados e guardados pelo controlador.
         */
        if (!empty($anexos)) {
            $etapa = 'preparar inserção dos anexos';

            $sqlAnexo = "
                INSERT INTO anexo (
                    uuid,
                    utilizador_id,
                    denuncia_id,
                    nome_original,
                    nome_arquivo,
                    tipo_mime,
                    extensao,
                    tamanho_bytes,
                    caminho,
                    hash_sha256,
                    descricao
                ) VALUES (
                    :uuid,
                    :utilizador_id,
                    :denuncia_id,
                    :nome_original,
                    :nome_arquivo,
                    :tipo_mime,
                    :extensao,
                    :tamanho_bytes,
                    :caminho,
                    :hash_sha256,
                    :descricao
                )
            ";

            $stmtAnexo = $this->db->prepare($sqlAnexo);

            foreach ($anexos as $indice => $anexo) {
                $etapa = 'validar metadados do anexo ' . ($indice + 1);

                $camposObrigatorios = [
                    'nome_original',
                    'nome_arquivo',
                    'tipo_mime',
                    'extensao',
                    'tamanho_bytes',
                    'caminho',
                    'hash_sha256',
                ];

                foreach ($camposObrigatorios as $campo) {
                    if (!array_key_exists($campo, $anexo)) {
                        throw new InvalidArgumentException(
                            "Falta o campo '{$campo}' no anexo."
                        );
                    }
                }

                $etapa = 'executar inserção do anexo ' . ($indice + 1);

                $stmtAnexo->execute([
                    ':uuid' => $anexo['uuid'] ?? $this->gerarUuid(),
                    ':utilizador_id' => $utilizadorId,
                    ':denuncia_id' => $denunciaId,
                    ':nome_original' => $anexo['nome_original'],
                    ':nome_arquivo' => $anexo['nome_arquivo'],
                    ':tipo_mime' => $anexo['tipo_mime'],
                    ':extensao' => $anexo['extensao'],
                    ':tamanho_bytes' => $anexo['tamanho_bytes'],
                    ':caminho' => $anexo['caminho'],
                    ':hash_sha256' => $anexo['hash_sha256'],
                    ':descricao' => $anexo['descricao'] ?? null,
                ]);
            }
        }

        /*
         * 4. Confirmar todas as operações.
         */
        $etapa = 'confirmar transação';

        $this->db->commit();

        return $denunciaId;

    } catch (Throwable $e) {
        /*
         * Registar a etapa e o erro técnico no log do PHP.
         * Não expor os detalhes internos ao utilizador.
         */
        error_log(
            '[SINC] Falha ao ' . $etapa
            . ' | ' . get_class($e)
            . ' | ' . $e->getMessage()
        );

        /*
         * Reverter as alterações à base de dados.
         */
        if ($this->db->inTransaction()) {
            try {
                $this->db->rollBack();
            } catch (Throwable $erroRollback) {
                error_log(
                    '[SINC] Falha no rollback: '
                    . $erroRollback->getMessage()
                );
            }
        }

        /*
         * Limpar os ficheiros físicos associados a esta tentativa.
         * O controlador deve fornecer ficheiro_absoluto apenas
         * para ficheiros temporários desta submissão.
         */
        foreach ($anexos as $anexo) {
            $caminhoAbsoluto = $anexo['ficheiro_absoluto'] ?? null;

            if (
                is_string($caminhoAbsoluto)
                && $caminhoAbsoluto !== ''
                && is_file($caminhoAbsoluto)
            ) {
                if (!@unlink($caminhoAbsoluto)) {
                    error_log(
                        '[SINC] Não foi possível limpar um ficheiro '
                        . 'após falha na criação da denúncia.'
                    );
                }
            }
        }

        throw $e;
    }
}

    /**
     * Lista exclusivamente as denúncias do utilizador indicado.
     */
    public function getByUserId(int $utilizadorId): array
    {
        $sql = "
            SELECT
                d.id,
                d.uuid,
                d.titulo,
                d.descricao,
                d.status,
                d.anonima,
                d.criado_em,
                t.nome AS tipo_ocorrencia,
                l.rua,
                l.numero,
                l.referencia,
                b.nome AS bairro
            FROM denuncia d
            INNER JOIN tipo_ocorrencia t
                ON t.id = d.tipo_ocorrencia_id
            INNER JOIN localizacao l
                ON l.id = d.localizacao_id
            INNER JOIN bairro b
                ON b.id = l.bairro_id
            WHERE d.utilizador_id = :utilizador_id
              AND d.ativo = 1
              AND d.eliminado_em IS NULL
            ORDER BY d.criado_em DESC, d.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilizador_id' => $utilizadorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta uma denúncia apenas se pertencer ao utilizador.
     */
    public function findByIdAndUserId(
        int $denunciaId,
        int $utilizadorId
    ): ?array {
        $sql = "
            SELECT
                d.id,
                d.uuid,
                d.titulo,
                d.descricao,
                d.status,
                d.anonima,
                d.duplicada,
                d.pontuacao_confianca,
                d.origem,
                d.criado_em,
                d.atualizado_em,
                t.nome AS tipo_ocorrencia,
                t.gravidade_padrao,
                l.rua,
                l.numero,
                l.referencia,
                l.latitude,
                l.longitude,
                b.nome AS bairro
            FROM denuncia d
            INNER JOIN tipo_ocorrencia t
                ON t.id = d.tipo_ocorrencia_id
            INNER JOIN localizacao l
                ON l.id = d.localizacao_id
            INNER JOIN bairro b
                ON b.id = l.bairro_id
            WHERE d.id = :denuncia_id
              AND d.utilizador_id = :utilizador_id
              AND d.ativo = 1
              AND d.eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'denuncia_id' => $denunciaId,
            'utilizador_id' => $utilizadorId,
        ]);

        $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

        return $denuncia !== false ? $denuncia : null;
    }


/**
 * Lista os anexos de uma denúncia pertencente ao utilizador.
 */
public function getAnexosByDenunciaAndUser(
    int $denunciaId,
    int $utilizadorId
): array {
    $sql = "
        SELECT
            a.id,
            a.uuid,
            a.denuncia_id,
            a.nome_original,
            a.tipo_mime,
            a.extensao,
            a.tamanho_bytes,
            a.criado_em
        FROM anexo a
        INNER JOIN denuncia d
            ON d.id = a.denuncia_id
        WHERE a.denuncia_id = :denuncia_id
          AND a.utilizador_id = :anexo_utilizador_id
          AND d.utilizador_id = :denuncia_utilizador_id
          AND a.ativo = 1
          AND d.ativo = 1
          AND d.eliminado_em IS NULL
        ORDER BY a.id ASC
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':denuncia_id' => $denunciaId,
        ':anexo_utilizador_id' => $utilizadorId,
        ':denuncia_utilizador_id' => $utilizadorId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    /**
     * Devolve um anexo apenas se pertencer a uma denúncia do utilizador.
     * Inclui o caminho físico para uso interno do servidor.
     */
    public function findAnexoByIdAndUser(
        int $anexoId,
        int $utilizadorId
    ): ?array {
        $sql = "
            SELECT
                a.id,
                a.denuncia_id,
                a.nome_arquivo,
                a.tipo_mime,
                a.extensao,
                a.tamanho_bytes,
                a.caminho
            FROM anexo a
            INNER JOIN denuncia d
                ON d.id = a.denuncia_id
            WHERE a.id = :anexo_id
              AND a.utilizador_id = :utilizador_id
              AND d.utilizador_id = :utilizador_id
              AND a.ativo = 1
              AND d.ativo = 1
              AND d.eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'anexo_id' => $anexoId,
            'utilizador_id' => $utilizadorId,
        ]);

        $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $anexo !== false ? $anexo : null;
    }
}
