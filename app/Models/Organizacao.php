<?php

declare(strict_types=1);

/**
 * Modelo administrativo de organizações do SINC.
 *
 * Responsabilidades:
 * - Consultar e pesquisar organizações;
 * - Carregar províncias e municípios activos;
 * - Registar e actualizar organizações;
 * - Activar e desactivar organizações;
 * - Validar as relações geográficas.
 */
final class Organizacao
{
    private PDO $db;

    private const TIPOS = [
        'PUBLICA',
        'PRIVADA',
        'MISTA',
    ];

    private const NIVEIS = [
        'COMUNAL',
        'MUNICIPAL',
        'PROVINCIAL',
        'NACIONAL',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Gera um UUID versão 4.
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
     * Lista organizações não eliminadas.
     *
     * A pesquisa abrange nome, sigla e código oficial.
     * Organizações inactivas continuam visíveis para administração.
     */
    public function listar(string $pesquisa = ''): array
    {
        $sql = "
            SELECT
                o.id,
                o.uuid,
                o.nome,
                o.sigla,
                o.tipo,
                o.nivel_administrativo,
                o.codigo_oficial,
                o.email,
                o.telefone,
                o.site,
                o.provincia_id,
                o.municipio_id,
                o.endereco,
                o.latitude,
                o.longitude,
                o.descricao,
                o.ativo,
                o.criado_em,
                p.nome AS provincia_nome,
                m.nome AS municipio_nome
            FROM organizacao AS o
            LEFT JOIN provincia AS p
                ON p.id = o.provincia_id
            LEFT JOIN municipio AS m
                ON m.id = o.municipio_id
            WHERE o.eliminado_em IS NULL
        ";

        $parametros = [];

        if (trim($pesquisa) !== '') {
            $sql .= "
                AND (
                    o.nome LIKE :nome
                    OR o.sigla LIKE :sigla
                    OR o.codigo_oficial LIKE :codigo
                )
            ";

            $termo = '%' . trim($pesquisa) . '%';

            $parametros['nome'] = $termo;
            $parametros['sigla'] = $termo;
            $parametros['codigo'] = $termo;
        }

        $sql .= ' ORDER BY o.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Obtém uma organização pelo ID.
     */
    public function encontrarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                o.*,
                p.nome AS provincia_nome,
                m.nome AS municipio_nome
            FROM organizacao AS o
            LEFT JOIN provincia AS p
                ON p.id = o.provincia_id
            LEFT JOIN municipio AS m
                ON m.id = o.municipio_id
            WHERE o.id = :id
              AND o.eliminado_em IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $organizacao = $stmt->fetch();

        return $organizacao ?: null;
    }

    /**
     * Lista províncias activas e não eliminadas.
     */
    public function listarProvincias(): array
    {
        $sql = "
            SELECT id, nome
            FROM provincia
            WHERE ativo = 1
              AND eliminado_em IS NULL
            ORDER BY nome ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lista municípios activos.
     *
     * Quando é fornecido um ID de província,
     * devolve apenas os municípios dessa província.
     */
    public function listarMunicipios(?int $provinciaId = null): array
    {
        $sql = "
            SELECT
                m.id,
                m.nome,
                m.provincia_id,
                p.nome AS provincia_nome
            FROM municipio AS m
            INNER JOIN provincia AS p
                ON p.id = m.provincia_id
            WHERE m.ativo = 1
              AND m.eliminado_em IS NULL
              AND p.ativo = 1
              AND p.eliminado_em IS NULL
        ";

        $parametros = [];

        if ($provinciaId !== null) {
            if ($provinciaId <= 0) {
                return [];
            }

            $sql .= ' AND m.provincia_id = :provincia_id';
            $parametros['provincia_id'] = $provinciaId;
        }

        $sql .= ' ORDER BY p.nome ASC, m.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Regista uma nova organização.
     */
    public function criar(array $dados): int
    {
        $dados = $this->validarDados($dados);

        $sql = "
            INSERT INTO organizacao (
                uuid,
                nome,
                sigla,
                tipo,
                nivel_administrativo,
                codigo_oficial,
                email,
                telefone,
                site,
                provincia_id,
                municipio_id,
                endereco,
                latitude,
                longitude,
                descricao,
                ativo
            ) VALUES (
                :uuid,
                :nome,
                :sigla,
                :tipo,
                :nivel_administrativo,
                :codigo_oficial,
                :email,
                :telefone,
                :site,
                :provincia_id,
                :municipio_id,
                :endereco,
                :latitude,
                :longitude,
                :descricao,
                1
            )
        ";

        $dados['uuid'] = $this->gerarUuid();

        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza uma organização existente.
     */
    public function actualizar(int $id, array $dados): bool
    {
        if ($id <= 0 || $this->encontrarPorId($id) === null) {
            throw new InvalidArgumentException(
                'A organização seleccionada não existe.'
            );
        }

        $dados = $this->validarDados($dados);
        $dados['id'] = $id;

        $sql = "
            UPDATE organizacao
            SET
                nome = :nome,
                sigla = :sigla,
                tipo = :tipo,
                nivel_administrativo = :nivel_administrativo,
                codigo_oficial = :codigo_oficial,
                email = :email,
                telefone = :telefone,
                site = :site,
                provincia_id = :provincia_id,
                municipio_id = :municipio_id,
                endereco = :endereco,
                latitude = :latitude,
                longitude = :longitude,
                descricao = :descricao,
                versao = versao + 1
            WHERE id = :id
              AND eliminado_em IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);

        return $stmt->rowCount() > 0;
    }

    /**
     * Altera o estado activo de uma organização.
     *
     * Não elimina nem remove dados.
     */
    public function definirEstado(int $id, bool $ativo): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Identificador de organização inválido.'
            );
        }

        $sql = "
            UPDATE organizacao
            SET
                ativo = :ativo,
                versao = versao + 1
            WHERE id = :id
              AND eliminado_em IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'ativo' => $ativo ? 1 : 0,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Normaliza e valida os dados do formulário.
     */
    private function validarDados(array $dados): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $sigla = trim((string) ($dados['sigla'] ?? ''));
        $tipo = strtoupper(trim((string) ($dados['tipo'] ?? 'PUBLICA')));
        $nivel = strtoupper(
            trim((string) ($dados['nivel_administrativo'] ?? ''))
        );

        if ($nome === '' || mb_strlen($nome) > 200) {
            throw new InvalidArgumentException(
                'O nome é obrigatório e deve ter no máximo 200 caracteres.'
            );
        }

        if ($sigla !== '' && mb_strlen($sigla) > 30) {
            throw new InvalidArgumentException(
                'A sigla não pode ultrapassar 30 caracteres.'
            );
        }

        if (!in_array($tipo, self::TIPOS, true)) {
            throw new InvalidArgumentException(
                'O tipo de organização seleccionado é inválido.'
            );
        }

        if (!in_array($nivel, self::NIVEIS, true)) {
            throw new InvalidArgumentException(
                'O nível administrativo seleccionado é inválido.'
            );
        }

        $email = $this->textoOpcional($dados, 'email');
        $site = $this->textoOpcional($dados, 'site');
        $codigo = $this->textoOpcional($dados, 'codigo_oficial');
        $telefone = $this->textoOpcional($dados, 'telefone');
        $endereco = $this->textoOpcional($dados, 'endereco');
        $descricao = $this->textoOpcional($dados, 'descricao');

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'O endereço de e-mail não é válido.'
            );
        }

        if ($email !== null && mb_strlen($email) > 150) {
            throw new InvalidArgumentException(
                'O e-mail não pode ultrapassar 150 caracteres.'
            );
        }

        if ($site !== null && mb_strlen($site) > 255) {
            throw new InvalidArgumentException(
                'O site não pode ultrapassar 255 caracteres.'
            );
        }

        if ($codigo !== null && mb_strlen($codigo) > 50) {
            throw new InvalidArgumentException(
                'O código oficial não pode ultrapassar 50 caracteres.'
            );
        }

        if ($telefone !== null && mb_strlen($telefone) > 30) {
            throw new InvalidArgumentException(
                'O telefone não pode ultrapassar 30 caracteres.'
            );
        }

        if ($endereco !== null && mb_strlen($endereco) > 255) {
            throw new InvalidArgumentException(
                'O endereço não pode ultrapassar 255 caracteres.'
            );
        }

        $provinciaId = $this->idOpcional($dados, 'provincia_id');
        $municipioId = $this->idOpcional($dados, 'municipio_id');

        $latitude = $this->coordenada(
            $dados['latitude'] ?? null,
            -90,
            90,
            'Latitude'
        );

        $longitude = $this->coordenada(
            $dados['longitude'] ?? null,
            -180,
            180,
            'Longitude'
        );

        if (($latitude === null) !== ($longitude === null)) {
            throw new InvalidArgumentException(
                'Preenche a latitude e a longitude em conjunto.'
            );
        }

        $this->validarGeografia($provinciaId, $municipioId);

        return [
            'nome' => $nome,
            'sigla' => $sigla !== '' ? $sigla : null,
            'tipo' => $tipo,
            'nivel_administrativo' => $nivel,
            'codigo_oficial' => $codigo,
            'email' => $email,
            'telefone' => $telefone,
            'site' => $site,
            'provincia_id' => $provinciaId,
            'municipio_id' => $municipioId,
            'endereco' => $endereco,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'descricao' => $descricao,
        ];
    }

    private function textoOpcional(
        array $dados,
        string $campo
    ): ?string {
        $valor = trim((string) ($dados[$campo] ?? ''));

        return $valor === '' ? null : $valor;
    }

    private function idOpcional(
        array $dados,
        string $campo
    ): ?int {
        $valor = $dados[$campo] ?? null;

        if ($valor === null || $valor === '') {
            return null;
        }

        $id = filter_var($valor, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            throw new InvalidArgumentException(
                'A selecção geográfica indicada é inválida.'
            );
        }

        return $id;
    }

    private function coordenada(
        mixed $valor,
        float $minimo,
        float $maximo,
        string $campo
    ): ?float {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        if (!is_numeric($valor)) {
            throw new InvalidArgumentException(
                $campo . ' deve ser um número válido.'
            );
        }

        $numero = (float) $valor;

        if (!is_finite($numero) || $numero < $minimo || $numero > $maximo) {
            throw new InvalidArgumentException(
                $campo . ' está fora do intervalo permitido.'
            );
        }

        return $numero;
    }

    /**
     * Confirma que as referências geográficas existem e estão activas.
     */
    private function validarGeografia(
        ?int $provinciaId,
        ?int $municipioId
    ): void {
        if ($provinciaId !== null) {
            $stmt = $this->db->prepare("
                SELECT id
                FROM provincia
                WHERE id = :id
                  AND ativo = 1
                  AND eliminado_em IS NULL
                LIMIT 1
            ");

            $stmt->execute(['id' => $provinciaId]);

            if (!$stmt->fetch()) {
                throw new InvalidArgumentException(
                    'A província seleccionada não está disponível.'
                );
            }
        }

        if ($municipioId !== null) {
            $sql = "
                SELECT id, provincia_id
                FROM municipio
                WHERE id = :id
                  AND ativo = 1
                  AND eliminado_em IS NULL
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $municipioId]);

            $municipio = $stmt->fetch();

            if (!$municipio) {
                throw new InvalidArgumentException(
                    'O município seleccionado não está disponível.'
                );
            }

            if (
                $provinciaId !== null
                && (int) $municipio['provincia_id'] !== $provinciaId
            ) {
                throw new InvalidArgumentException(
                    'O município não pertence à província seleccionada.'
                );
            }
        }
    }
}
