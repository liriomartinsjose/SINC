# SINC — Plataforma de Participação e Inteligência Cívica

## Descrição

O SINC é uma plataforma web de participação e inteligência cívica, desenvolvida com HTML5, CSS3, JavaScript puro, PHP orientado a objectos e MySQL (PDO), compatível com XAMPP.

## Funcionalidades Implementadas (v2)

### Cidadão
- **Registo e login** com palavras-passe protegidas por `password_hash()` / `password_verify()`
- **Sessões PHP seguras** (HttpOnly, SameSite, regeneração de ID)
- **Criar denúncias** com título, descrição, categoria e localização
- **Consultar as próprias denúncias** com estados e datas
- **Abrir o detalhe de uma denúncia** com informações completas
- **Consultar o histórico de alterações de estado** (timeline com responsável e data)
- **Editar uma denúncia** apenas quando o estado for "Pendente" ou "Em análise"
- **Eliminar uma denúncia** apenas quando o estado for "Pendente" ou "Em análise"
- **Bloqueio automático** de edição/eliminação quando o estado é "Resolvido" ou "Rejeitado"

### Gestão de Denúncias
- **Listagem com pesquisa** por título, descrição, localização ou autor
- **Filtros por estado e categoria** na área administrativa
- **Consulta de detalhes completos** pelo administrador
- **Alteração de estado com registo** de data e responsável na tabela `ocorrencia_historico`
- **Isolamento de dados**: cidadãos só acedem às suas próprias denúncias (`findByIdAndUserId`)
- **Controlo de permissões**: cidadãos nunca executam operações administrativas

### Área Administrativa
- **Dashboard** com totais calculados a partir da base de dados (total, pendentes, em análise, resolvidas, rejeitadas)
- **Listagem de denúncias** com filtros e pesquisa
- **Consulta de detalhes** de qualquer denúncia
- **Alteração de estado** com registo automático de responsável
- **Gestão de utilizadores**: ativar ou desativar contas
- **Proteção**: um administrador não pode desativar a sua própria conta
- **Proteção**: não existe nenhum mecanismo que permita a um cidadão tornar-se administrador

### Interface
- Páginas consistentes, modernas e responsivas em português
- Navegação dinâmica (links diferentes consoante autenticação e papel)
- Tabelas com hover, badges de estado coloridos
- Timeline visual para o histórico de alterações
- Cartões de estatística no dashboard
- Mensagens de sucesso e erro claras
- Confirmáveis antes de eliminar ou desativar
- Identidade visual do SINC mantida (cores, tipografia, layout)

## Estrutura de Pastas

```
sinc/
├── config/
│   ├── app.php             Configuração geral
│   └── database.php        Parâmetros de ligação MySQL (XAMPP)
├── core/
│   ├── Controller.php      Classe base com render, requireAuth, requireAdmin
│   ├── Router.php          Encaminhamento de URLs
│   └── Database.php        Ligação PDO singleton
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php           Páginas públicas
│   │   ├── AuthController.php           Registo, login, logout
│   │   ├── OcorrenciaController.php     CRUD de ocorrências do cidadão
│   │   └── AdminController.php          Dashboard, ocorrências, detalhe, utilizadores
│   ├── Models/
│   │   ├── User.php                     Operações sobre users
│   │   ├── Ocorrencia.php              Operações sobre ocorrencias + filtros
│   │   └── OcorrenciaHistorico.php      Consulta de histórico de estados
│   ├── Services/
│   │   └── Auth.php                     Gestão de sessões
│   └── Helpers/
│       └── helpers.php                  Escape, redirect, base_url, estadoLabel
├── views/
│   ├── layouts/
│   │   ├── header.php                   Cabeçalho com navegação dinâmica
│   │   └── footer.php                   Rodapé
│   ├── pages/
│   │   ├── home.php                     Página inicial
│   │   └── sobre.php                    Página "Sobre"
│   ├── auth/
│   │   ├── registo.php                  Formulário de registo
│   │   ├── login.php                    Formulário de login
│   │   ├── nova_ocorrencia.php          Formulário de nova ocorrência
│   │   ├── minhas_ocorrencias.php       Lista + detalhe/editar/eliminar
│   │   ├── detalhe.php                  Detalhe da ocorrência + histórico
│   │   └── editar.php                   Formulário de edição
│   └── admin/
│       ├── dashboard.php               Estatísticas totais
│       ├── ocorrencias.php              Lista com filtros e pesquisa
│       ├── detalhe.php                  Detalhe + alteração de estado + histórico
│       └── utilizadores.php             Ativar/desativar contas
├── public/
│   ├── css/
│   │   ├── base.css                     Reset, variáveis, tipografia
│   │   ├── layout.css                   Cabeçalho, rodapé, contentor
│   │   └── components.css              Botões, formulários, tabelas, badges, dashboard, timeline, filtros
│   └── js/
│       └── main.js
├── sql/
│   └── sinc_v1.sql                      Esquema completo (v2) + admin inicial
├── .htaccess
├── index.php                            Ponto de entrada
└── README.md
```

## Configuração da Base de Dados no XAMPP

### Instalação nova

1. **Copiar o projecto**: Colocar a pasta `sinc/` dentro de `htdocs/` do XAMPP.
2. **Iniciar XAMPP**: Abrir o painel de controlo e iniciar **Apache** e **MySQL**.
3. **Criar a base de dados**:
   - Abrir `http://localhost/phpmyadmin` no navegador.
   - Separador **Importar** → selecionar o ficheiro `sql/sinc_v1.sql` → **Executar**.

### Actualização a partir da v1

Se já tinha a base de dados v1, execute apenas estas instruções no phpMyAdmin (separador SQL):

```sql
USE sinc;

-- Adicionar coluna "ativo" à tabela users (se não existir)
ALTER TABLE users ADD COLUMN IF NOT EXISTS ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER papel;

-- Criar tabela de histórico (se não existir)
CREATE TABLE IF NOT EXISTS ocorrencia_historico (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ocorrencia_id   INT             NOT NULL,
    estado_anterior ENUM('pendente','em_analise','resolvido','rejeitado') NOT NULL,
    estado_novo     ENUM('pendente','em_analise','resolvido','rejeitado') NOT NULL,
    alterado_por    INT             NOT NULL,
    alterado_em     TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_ocorrencia
        FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hist_user
        FOREIGN KEY (alterado_por) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_hist_ocorrencia_id (ocorrencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Administrador inicial

| Campo | Valor |
|-------|-------|
| Email | `admin@sinc.pt` |
| Palavra-passe | `admin123` |

**Importante**: Altere a palavra-passe do administrador o mais rapidamente possível. Para gerar um novo hash:

```php
<?php echo password_hash('nova_palavra_passe', PASSWORD_DEFAULT);
```

Depois atualize o registo:

```sql
UPDATE users SET password = 'HASH_GERADO' WHERE email = 'admin@sinc.pt';
```

### Configuração da ligação

Os parâmetros estão em `config/database.php` (pré-configurados para XAMPP):

- Host: `localhost`
- Base de dados: `sinc`
- Utilizador: `root`
- Palavra-passe: (vazia)

## Como Executar

1. Certifique-se de que Apache e MySQL estão a correr no XAMPP.
2. Aceda a `http://localhost/sinc/` no navegador.

## Rotas Disponíveis

| URL | Descrição | Acesso |
|-----|-----------|--------|
| `/sinc/` | Página inicial | Público |
| `/sinc/sobre` | Página "Sobre" | Público |
| `/sinc/registo` | Registo de novo cidadão | Público |
| `/sinc/login` | Início de sessão | Público |
| `/sinc/logout` | Terminar sessão | Autenticado |
| `/sinc/nova-ocorrencia` | Registar nova ocorrência | Cidadão autenticado |
| `/sinc/minhas-ocorrencias` | Listar as suas ocorrências | Cidadão autenticado |
| `/sinc/detalhe-ocorrencia?id=N` | Detalhe de uma ocorrência própria | Cidadão autenticado |
| `/sinc/editar-ocorrencia?id=N` | Editar uma ocorrência própria | Cidadão autenticado |
| `/sinc/eliminar-ocorrencia` | Eliminar ocorrência (POST) | Cidadão autenticado |
| `/sinc/admin` | Dashboard administrativo | Administrador |
| `/sinc/admin/ocorrencias` | Listar todas as ocorrências com filtros | Administrador |
| `/sinc/admin/detalhe?id=N` | Detalhe de qualquer ocorrência | Administrador |
| `/sinc/admin/alterar-estado` | Alterar estado (POST) | Administrador |
| `/sinc/admin/utilizadores` | Gerir utilizadores | Administrador |
| `/sinc/admin/toggle-utilizador` | Ativar/desativar utilizador (POST) | Administrador |

## Estados de Ocorrência

| Estado | Etiqueta | Cor | Editável pelo cidadão? |
|--------|----------|-----|----------------------|
| `pendente` | Pendente | Amarelo | Sim |
| `em_analise` | Em análise | Azul | Sim |
| `resolvido` | Resolvido | Verde | Não |
| `rejeitado` | Rejeitado | Vermelho | Não |

## Categorias Disponíveis

- Infraestrutura
- Saneamento
- Iluminação
- Ambiente
- Segurança
- Transportes
- Outro

## Esquema da Base de Dados (v2)

### Tabela `users`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| nome | VARCHAR(100) | Nome do utilizador |
| email | VARCHAR(255) UNIQUE | Email único |
| password | VARCHAR(255) | Hash bcrypt |
| papel | ENUM('cidadao','admin') | Papel no sistema |
| ativo | TINYINT(1) | 1=ativa, 0=desativada |
| created_at | TIMESTAMP | Data de registo |
| updated_at | TIMESTAMP | Última atualização |

### Tabela `ocorrencias`
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| user_id | INT FK→users.id | Autor |
| titulo | VARCHAR(200) | Título |
| descricao | TEXT | Descrição detalhada |
| categoria | ENUM(7 valores) | Categoria |
| localizacao | VARCHAR(255) | Localização |
| estado | ENUM(4 valores) | Estado atual |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Última atualização |

### Tabela `ocorrencia_historico` (nova em v2)
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| ocorrencia_id | INT FK→ocorrencias.id | Ocorrência alterada |
| estado_anterior | ENUM(4 valores) | Estado anterior |
| estado_novo | ENUM(4 valores) | Novo estado |
| alterado_por | INT FK→users.id | Quem alterou |
| alterado_em | TIMESTAMP | Quando alterou |

## Segurança

- **SQL injection**: Todas as consultas usam PDO com prepared statements.
- **Saída HTML**: Todas as saídas usam `htmlspecialchars()` com `ENT_QUOTES` e `UTF-8`.
- **Palavras-passe**: bcrypt via `password_hash()` / `password_verify()`.
- **Sessões**: Cookies HttpOnly, SameSite=Lax, regeneração de ID no login.
- **Controlo de acesso**: Páginas de cidadão exigem autenticação; páginas de admin exigem papel admin.
- **Isolamento de dados**: Cidadãos só acedem às suas próprias ocorrências.
- **Bloqueio de auto-desativação**: Admin não pode desativar a sua própria conta.
- **Sem escalonamento de privilégios**: Não existe nenhuma rota que permita a um cidadão alterar o seu papel.
- **Directorias sensíveis**: `.htaccess` bloqueia acesso directo a `config/`, `core/`, `app/`, `views/`, `sql/`, `modules/`.
- **Listagem de directorias**: Desativada com `Options -Indexes`.
- **Erros de login**: Mensagem genérica "Email ou palavra-passe incorretos." (não revela qual campo está errado).

## Preparação para o Futuro

O código está organizado para permitir a integração posterior de:
- Comentários em ocorrências
- Confirmações cidadãs
- Votos de prioridade
- Organizações
- Encaminhamentos
- Intervenções
- Expansão de categorias e estados

Estes módulos não estão implementados nesta versão.

## Tecnologias

- HTML5, CSS3, JavaScript puro (sem frameworks)
- PHP 8+ orientado a objectos
- MySQL (via PDO)
- Apache (XAMPP)
