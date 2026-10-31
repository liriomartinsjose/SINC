<?php

declare(strict_types=1);

/**
 * Modelo de ocorrências do SINC.
 * Utiliza a tabela ocorrencia e as relações do esquema atual.
 */
final class Ocorrencia
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lista ocorrências de um cidadão.
     */
    public function getByUserId(int $userId): array
    {
        $sql = '
            SELECT
                o.*,
                e.nome AS estado_nome,
                t.nome AS tipo_ocorrencia_nome,
                l.id AS localizacao_registo
            FROM ocorrencia o
            INNER JOIN estado e ON e.id = o.estado_id
            INNER JOIN tipo_ocorrencia t ON t.id = o.tipo_ocorrencia_id
            INNER JOIN localizacao l ON l.id = o.localizacao_id
            WHERE o.utilizador_id = :utilizador_id
              AND o.eliminado_em IS NULL
              AND o.ativo = 1
            ORDER BY o.criado_em DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilizador_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Lista ocorrências para gestão administrativa.
     */
    public function getAll(): array
    {
        $sql = '
            SELECT
                o.*,
                u.nome_completo AS autor_nome,
                e.nome AS estado_nome,
                t.nome AS tipo_ocorrencia_nome,
                l.id AS localizacao_registo
            FROM ocorrencia o
            LEFT JOIN utilizador u ON u.id = o.utilizador_id
            INNER JOIN estado e ON e.id = o.estado_id
            INNER JOIN tipo_ocorrencia t ON t.id = o.tipo_ocorrencia_id
            INNER JOIN localizacao l ON l.id = o.localizacao_id
            WHERE o.ativo = 1
            AND o.eliminado_em IS NULL
            ORDER BY o.criado_em DESC
        ';

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Pesquisa ocorrências por título, descrição ou autor.
     */
    public function getAllFiltered(
        ?string $pesquisa,
        ?string $estado,
        ?string $tipoOcorrencia
    ): array {
        $sql = '
            SELECT
                o.*,
                u.nome_completo AS autor_nome,
                e.nome AS estado_nome,
                t.nome AS tipo_ocorrencia_nome
            FROM ocorrencia o
            LEFT JOIN utilizador u ON u.id = o.utilizador_id
            INNER JOIN estado e ON e.id = o.estado_id
            INNER JOIN tipo_ocorrencia t ON t.id = o.tipo_ocorrencia_id
            WHERE id = :id
            AND utilizador_id = :utilizador_id
            AND ativo = 1
            AND eliminado_em IS NULL
            LIMIT 1
        ';

        $params = [];

        if ($pesquisa !== null && trim($pesquisa) !== '') {
            $sql .= '
                AND (
                    o.titulo LIKE :titulo
                    OR o.descricao LIKE :descricao
                    OR u.nome_completo LIKE :autor
                )
            ';

            $termo = '%' . trim($pesquisa) . '%';

            $params['titulo'] = $termo;
            $params['descricao'] = $termo;
            $params['autor'] = $termo;
        }

        if ($estado !== null && $estado !== '') {
            $sql .= ' AND e.nome = :estado';
            $params['estado'] = $estado;
        }

        if ($tipoOcorrencia !== null && $tipoOcorrencia !== '') {
            $sql .= ' AND t.nome = :tipo';
            $params['tipo'] = $tipoOcorrencia;
        }

        $sql .= ' ORDER BY o.criado_em DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Obtém uma ocorrência pelo identificador.
     */
    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                o.*,
                u.nome_completo AS autor_nome,
                e.nome AS estado_nome,
                t.nome AS tipo_ocorrencia_nome,
                l.id AS localizacao_registo
            FROM ocorrencia o
            LEFT JOIN utilizador u ON u.id = o.utilizador_id
            INNER JOIN estado e ON e.id = o.estado_id
            INNER JOIN tipo_ocorrencia t ON t.id = o.tipo_ocorrencia_id
            INNER JOIN localizacao l ON l.id = o.localizacao_id
            WHERE id = :id
            AND utilizador_id = :utilizador_id
            AND ativo = 1
            AND eliminado_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    /**
     * Obtém uma ocorrência pertencente a um cidadão.
     */

    public function findByIdAndUserId(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ocorrencia
            WHERE id = :id
            AND utilizador_id = :utilizador_id
            AND ativo = 1
            AND eliminado_em IS NULL
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
            'utilizador_id' => $userId,
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }


    /**
     * Atualiza os campos descritivos de uma ocorrência.
     * Não altera o estado nem a localização.
     */
    public function update(
        int $id,
        string $titulo,
        string $descricao
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE ocorrencia
             SET titulo = :titulo,
                 descricao = :descricao,
                 versao = versao + 1,
                 atualizado_em = CURRENT_TIMESTAMP
             WHERE id = :id
               AND eliminado_em IS NULL'
        );

        return $stmt->execute([
            'titulo' => trim($titulo),
            'descricao' => trim($descricao),
            'id' => $id,
        ]);
    }

    /**
     * Eliminação lógica: preserva o registo e o histórico.
     */
    public function delete(int $id, ?int $alteradoPor = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ocorrencia
             SET eliminado_em = CURRENT_TIMESTAMP,
                 atualizado_em = CURRENT_TIMESTAMP,
                 atualizado_por = :utilizador_id,
                 versao = versao + 1
             WHERE id = :id
               AND eliminado_em IS NULL'
        );

        $stmt->execute([
            'utilizador_id' => $alteradoPor,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Altera o estado e regista o histórico na mesma transação.
     *
     * Os identificadores dos estados devem existir na tabela estado.
     */
    
/**
 * Altera o estado da ocorrência e regista o histórico
 * na mesma transacção.
 *
 * A autorização do utilizador deve ser validada no controlador.
 */

    public function updateEstado(int $id, int $estadoId, int $alteradoPor): bool
    {
        if ($id <= 0 || $estadoId <= 0 || $alteradoPor <= 0) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Bloquear a ocorrência para evitar alterações concorrentes.
            $stmt = $this->db->prepare(
                'SELECT id, estado_id
                FROM ocorrencia
                WHERE id = :id
                AND ativo = 1
                AND eliminado_em IS NULL
                FOR UPDATE'
            );
            $stmt->execute(['id' => $id]);
            $ocorrencia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ocorrencia) {
                $this->db->rollBack();
                return false;
            }

            $estadoAnterior = (int) $ocorrencia['estado_id'];

            // Não criar processos nem histórico para alterações inexistentes.
            if ($estadoAnterior === $estadoId) {
                $this->db->commit();
                return true;
            }

            
            // Validar a transição de estado dentro da transacção.
            $transicoesPermitidas = [
                1 => [2],       // Criada -> Em análise
                2 => [3, 7],    // Em análise -> Validada ou Arquivada
                3 => [4, 7],    // Validada -> Encaminhada ou Arquivada
                4 => [5, 7],    // Encaminhada -> Em execução ou Arquivada
                5 => [6, 7],    // Em execução -> Resolvida ou Arquivada
            ];

            $destinosPermitidos =
                $transicoesPermitidas[$estadoAnterior] ?? [];

            if (!in_array($estadoId, $destinosPermitidos, true)) {
                $this->db->rollBack();
                return false;
            }


            // 2. Validar o novo estado.
            $stmt = $this->db->prepare(
                'SELECT id
                FROM estado
                WHERE id = :id
                AND ativo = 1
                AND eliminado_em IS NULL
                LIMIT 1'
            );
            $stmt->execute(['id' => $estadoId]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->rollBack();
                return false;
            }

            // 3. Confirmar que o utilizador responsável existe.
            $stmt = $this->db->prepare(
                'SELECT id
                FROM utilizador
                WHERE id = :id
                AND eliminado_em IS NULL
                LIMIT 1'
            );
            $stmt->execute(['id' => $alteradoPor]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->db->rollBack();
                return false;
            }

            // 4. Procurar o processo existente.
            $stmt = $this->db->prepare(
                'SELECT id, estado_id
                FROM processo
                WHERE ocorrencia_id = :ocorrencia_id
                AND ativo = 1
                AND eliminado_em IS NULL
                FOR UPDATE'
            );
            $stmt->execute(['ocorrencia_id' => $id]);
            $processo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($processo) {
                $processoId = (int) $processo['id'];
            } else {
                // 5. Criar automaticamente o processo na primeira alteração.
                $uuid = $this->gerarUuidV4();

                $stmt = $this->db->prepare(
                    'INSERT INTO processo (
                        uuid,
                        ocorrencia_id,
                        estado_id,
                        data_inicio,
                        ativo,
                        versao
                    ) VALUES (
                        :uuid,
                        :ocorrencia_id,
                        :estado_id,
                        CURRENT_TIMESTAMP,
                        1,
                        1
                    )'
                );

                $stmt->execute([
                    'uuid' => $uuid,
                    'ocorrencia_id' => $id,
                    'estado_id' => $estadoAnterior
                ]);

                $processoId = (int) $this->db->lastInsertId();
            }

            // 6. Actualizar a ocorrência.
            $stmt = $this->db->prepare(
                'UPDATE ocorrencia
                SET estado_id = :estado_id,
                    atualizado_por = :utilizador_id,
                    atualizado_em = CURRENT_TIMESTAMP,
                    versao = versao + 1
                WHERE id = :id'
            );

            $stmt->execute([
                'estado_id' => $estadoId,
                'utilizador_id' => $alteradoPor,
                'id' => $id
            ]);

            // 7. Actualizar o processo.
            $stmt = $this->db->prepare(
                'UPDATE processo
                SET estado_id = :estado_id,
                    atualizado_em = CURRENT_TIMESTAMP,
                    versao = versao + 1
                WHERE id = :id'
            );

            $stmt->execute([
                'estado_id' => $estadoId,
                'id' => $processoId
            ]);

            // 8. Registar a alteração no histórico.
            $uuidHistorico = $this->gerarUuidV4();

            $dadosAnteriores = json_encode(
                ['estado_id' => $estadoAnterior],
                JSON_THROW_ON_ERROR
            );

            $dadosNovos = json_encode(
                ['estado_id' => $estadoId],
                JSON_THROW_ON_ERROR
            );

            $stmt = $this->db->prepare(
                'INSERT INTO historico (
                    uuid,
                    processo_id,
                    utilizador_id,
                    estado_id,
                    modulo,
                    acao,
                    descricao,
                    dados_anteriores,
                    dados_novos,
                    ip_origem,
                    user_agent,
                    data_evento,
                    ativo,
                    versao
                ) VALUES (
                    :uuid,
                    :processo_id,
                    :utilizador_id,
                    :estado_id,
                    :modulo,
                    :acao,
                    :descricao,
                    :dados_anteriores,
                    :dados_novos,
                    :ip_origem,
                    :user_agent,
                    CURRENT_TIMESTAMP,
                    1,
                    1
                )'
            );

            $stmt->execute([
                'uuid' => $uuidHistorico,
                'processo_id' => $processoId,
                'utilizador_id' => $alteradoPor,
                'estado_id' => $estadoId,
                'modulo' => 'OCORRENCIA',
                'acao' => 'ALTERACAO_ESTADO',
                'descricao' => "Estado alterado de ID {$estadoAnterior} para ID {$estadoId}.",
                'dados_anteriores' => $dadosAnteriores,
                'dados_novos' => $dadosNovos,
                'ip_origem' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            // 9. Confirmar todas as operações em conjunto.
            $this->db->commit();

            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }


    // ... o teu método updateEstado() termina aqui.

    /**
     * Gera um UUID v4 válido.
     */
    private function gerarUuidV4(): string
    {
        $dados = random_bytes(16);

        // Versão 4.
        $dados[6] = chr((ord($dados[6]) & 0x0f) | 0x40);

        // Variante RFC 4122.
        $dados[8] = chr((ord($dados[8]) & 0x3f) | 0x80);

        $hex = bin2hex($dados);

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


