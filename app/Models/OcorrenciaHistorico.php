<?php
declare(strict_types=1);

/**
 * Modelo de consulta do histórico de uma ocorrência.
 *
 * A tabela historico está ligada à ocorrência através de processo.
 */
final class OcorrenciaHistorico
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtém o histórico de uma ocorrência por ordem cronológica.
     */
    public function getByOcorrenciaId(int $ocorrenciaId): array
    {
        if ($ocorrenciaId <= 0) {
            return [];
        }

        $sql = '
            SELECT
                h.id,
                h.uuid,
                h.processo_id,
                h.utilizador_id,
                h.estado_id,
                h.modulo,
                h.acao,
                h.descricao,
                h.dados_anteriores,
                h.dados_novos,
                h.ip_origem,
                h.data_evento,
                u.nome_completo AS responsavel_nome,
                e.nome AS estado_nome
            FROM historico h
            INNER JOIN processo p
                ON p.id = h.processo_id
            LEFT JOIN utilizador u
                ON u.id = h.utilizador_id
            LEFT JOIN estado e
                ON e.id = h.estado_id
            WHERE p.ocorrencia_id = :ocorrencia_id
              AND p.eliminado_em IS NULL
              AND p.ativo = 1
              AND h.ativo = 1
            ORDER BY h.data_evento ASC, h.id ASC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ocorrencia_id' => $ocorrenciaId,
        ]);

        return $stmt->fetchAll();
    }
}

