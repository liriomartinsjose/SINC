<?php

declare(strict_types=1);

final class DashboardCidadaoController extends Controller
{
    /**
     * Estados que permitem a apresentação pública de uma ocorrência.
     *
     * A comparação é feita sem distinguir maiúsculas/minúsculas.
     * Ajustar estes nomes caso a tabela estado use designações diferentes.
     */
    private const ESTADOS_PUBLICOS = [
        'validada',
        'validado',
        'encaminhada',
        'encaminhado',
        'em execução',
        'em execucao',
        'resolvida',
        'resolvido',
        'concluída',
        'concluida',
        'concluído',
        'concluido',
    ];

    public function index(): void
    {
        Auth::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $pdo = Database::getConnection();
        $utilizadorId = (int) ($user['id'] ?? 0);

        $resumo = [
            'total' => 0,
            'em_aberto' => 0,
            'em_execucao' => 0,
            'resolvidas' => 0,
        ];

        $estatisticasPublicas = [
            'total' => 0,
            'em_execucao' => 0,
            'resolvidas' => 0,
            'provincias' => 0,
        ];

        $denunciasRecentes = [];
        $ocorrenciasPublicas = [];
        $provincias = [];
        $categorias = [];
        $estadosPublicos = [];
        $erroConsulta = false;

        try {
            /*
             * 1. Resumo pessoal do cidadão autenticado.
             */
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(LOWER(e.nome) IN (
                        'criada', 'criado',
                        'pendente', 'em análise', 'em analise'
                    )), 0) AS em_aberto,
                    COALESCE(SUM(LOWER(e.nome) IN (
                        'encaminhada', 'encaminhado',
                        'em execução', 'em execucao'
                    )), 0) AS em_execucao,
                    COALESCE(SUM(LOWER(e.nome) IN (
                        'resolvida', 'resolvido',
                        'concluída', 'concluida',
                        'concluído', 'concluido'
                    )), 0) AS resolvidas
                FROM ocorrencia o
                INNER JOIN estado e ON e.id = o.estado_id
                WHERE o.utilizador_id = :utilizador_id
                  AND o.eliminado_em IS NULL
                  AND o.ativo = 1
            ");

            $stmt->execute(['utilizador_id' => $utilizadorId]);
            $dadosResumo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($dadosResumo)) {
                $resumo = array_merge($resumo, $dadosResumo);
            }

            /*
             * 2. Ocorrências pessoais mais recentes.
             */
            $stmtRecentes = $pdo->prepare("
                SELECT
                    o.id,
                    o.uuid,
                    o.titulo,
                    o.gravidade,
                    o.prioridade,
                    o.estado_id,
                    e.nome AS estado_nome,
                    e.cor AS estado_cor,
                    t.nome AS tipo_nome,
                    o.criado_em
                FROM ocorrencia o
                INNER JOIN estado e
                    ON e.id = o.estado_id
                INNER JOIN tipo_ocorrencia t
                    ON t.id = o.tipo_ocorrencia_id
                WHERE o.utilizador_id = :utilizador_id
                  AND o.eliminado_em IS NULL
                  AND o.ativo = 1
                ORDER BY o.criado_em DESC
                LIMIT 5
            ");

            $stmtRecentes->execute([
                'utilizador_id' => $utilizadorId,
            ]);

            $denunciasRecentes = $stmtRecentes->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * 3. Feed público e dados do mapa.
             *
             * Só são seleccionados estados aprovados para publicação.
             * Não seleccionamos o nome, email ou outros dados do denunciante.
             */
            $marcadoresPublicos = implode(
                ', ',
                array_fill(0, count(self::ESTADOS_PUBLICOS), '?')
            );

            $sqlPublicas = "
                SELECT
                    o.id,
                    o.uuid,
                    o.titulo,
                    o.descricao,
                    o.gravidade,
                    o.prioridade,
                    o.criado_em,
                    e.nome AS estado_nome,
                    e.cor AS estado_cor,
                    e.icone AS estado_icone,
                    t.nome AS tipo_nome,
                    c.id AS categoria_id,
                    c.nome AS categoria_nome,
                    l.latitude,
                    l.longitude,
                    l.rua,
                    b.nome AS bairro_nome,
                    co.nome AS comuna_nome,
                    m.nome AS municipio_nome,
                    p.id AS provincia_id,
                    p.nome AS provincia_nome
                FROM ocorrencia o
                INNER JOIN estado e
                    ON e.id = o.estado_id
                INNER JOIN tipo_ocorrencia t
                    ON t.id = o.tipo_ocorrencia_id
                INNER JOIN subcategoria sc
                    ON sc.id = t.subcategoria_id
                INNER JOIN categoria c
                    ON c.id = sc.categoria_id
                INNER JOIN localizacao l
                    ON l.id = o.localizacao_id
                INNER JOIN bairro b
                    ON b.id = l.bairro_id
                INNER JOIN comuna co
                    ON co.id = b.comuna_id
                INNER JOIN municipio m
                    ON m.id = co.municipio_id
                INNER JOIN provincia p
                    ON p.id = m.provincia_id
                WHERE o.ativo = 1
                  AND o.eliminado_em IS NULL
                  AND e.ativo = 1
                  AND l.ativo = 1
                  AND b.ativo = 1
                  AND co.ativo = 1
                  AND m.ativo = 1
                  AND p.ativo = 1
                  AND t.ativo = 1
                  AND sc.ativo = 1
                  AND c.ativo = 1
                  AND LOWER(e.nome) IN ($marcadoresPublicos)
                ORDER BY o.criado_em DESC
                LIMIT 200
            ";

            $stmtPublicas = $pdo->prepare($sqlPublicas);
            $stmtPublicas->execute(self::ESTADOS_PUBLICOS);

            $ocorrenciasPublicas = $stmtPublicas->fetchAll(
                PDO::FETCH_ASSOC
            );

            /*
             * 4. Validar coordenadas e preparar os dados para o frontend.
             */
            $ocorrenciasPublicas = array_values(array_filter(
                $ocorrenciasPublicas,
                static function (array $item): bool {
                    if (
                        !isset($item['latitude'], $item['longitude'])
                        || !is_numeric($item['latitude'])
                        || !is_numeric($item['longitude'])
                    ) {
                        return false;
                    }

                    $latitude = (float) $item['latitude'];
                    $longitude = (float) $item['longitude'];

                    return $latitude >= -90
                        && $latitude <= 90
                        && $longitude >= -180
                        && $longitude <= 180;
                }
            ));

            /*
             * 5. Indicadores calculados a partir do conjunto público
             * carregado. O feed e os indicadores referem-se, portanto,
             * às últimas 200 ocorrências públicas elegíveis.
             */
            $estatisticasPublicas['total'] = count(
                $ocorrenciasPublicas
            );

            $provinciasComOcorrencias = [];

            foreach ($ocorrenciasPublicas as $ocorrencia) {
                $estado = mb_strtolower(
                    trim((string) ($ocorrencia['estado_nome'] ?? '')),
                    'UTF-8'
                );

                if (in_array($estado, [
                    'encaminhada',
                    'encaminhado',
                    'em execução',
                    'em execucao',
                ], true)) {
                    $estatisticasPublicas['em_execucao']++;
                }

                if (in_array($estado, [
                    'resolvida',
                    'resolvido',
                    'concluída',
                    'concluida',
                    'concluído',
                    'concluido',
                ], true)) {
                    $estatisticasPublicas['resolvidas']++;
                }

                if (!empty($ocorrencia['provincia_id'])) {
                    $provinciasComOcorrencias[
                        (string) $ocorrencia['provincia_id']
                    ] = true;
                }
            }

            $estatisticasPublicas['provincias'] = count(
                $provinciasComOcorrencias
            );

            /*
             * 6. Opções para os filtros.
             */
            $stmtProvincias = $pdo->query("
                SELECT id, nome
                FROM provincia
                WHERE ativo = 1
                ORDER BY nome
            ");

            $provincias = $stmtProvincias->fetchAll(
                PDO::FETCH_ASSOC
            );

            $stmtCategorias = $pdo->query("
                SELECT id, nome
                FROM categoria
                WHERE ativo = 1
                ORDER BY nome
            ");

            $categorias = $stmtCategorias->fetchAll(
                PDO::FETCH_ASSOC
            );

            $estadosPublicos = array_values(array_unique(
                array_map(
                    static fn(array $item): string =>
                        (string) ($item['estado_nome'] ?? ''),
                    $ocorrenciasPublicas
                )
            ));
        } catch (Throwable $e) {
            error_log(
                'SINC dashboard do cidadão: ' . $e->getMessage()
            );

            $erroConsulta = true;
        }

        $this->render(
            'cidadao/dashboard',
            [
                'user' => $user,
                'pageTitle' => 'O SINC é de todos',
                'resumo' => $resumo,
                'estatisticasPublicas' => $estatisticasPublicas,
                'denunciasRecentes' => $denunciasRecentes,
                'ocorrenciasPublicas' => $ocorrenciasPublicas,
                'provincias' => $provincias,
                'categorias' => $categorias,
                'estadosPublicos' => $estadosPublicos,
                'erroConsulta' => $erroConsulta,
                'pageStyles' => [
                    'public/assets/css/cidadao/dashboard.css',
                ],
                'pageScripts' => [
                    'public/assets/js/cidadao/dashboard.js',
                ],
            ],
            'app'
        );
    }
}
