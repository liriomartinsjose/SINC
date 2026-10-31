-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2026 at 04:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sinc`
--

-- --------------------------------------------------------

--
-- Table structure for table `anexo`
--

CREATE TABLE `anexo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `denuncia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `intervencao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nome_original` varchar(255) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `extensao` varchar(20) DEFAULT NULL,
  `tamanho_bytes` bigint(20) DEFAULT NULL,
  `caminho` varchar(500) NOT NULL,
  `hash_sha256` char(64) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bairro`
--

CREATE TABLE `bairro` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `comuna_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comentario`
--

CREATE TABLE `comentario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `comentario_pai_id` bigint(20) UNSIGNED DEFAULT NULL,
  `texto` text NOT NULL,
  `editado` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comuna`
--

CREATE TABLE `comuna` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `municipio_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `confirmacao`
--

CREATE TABLE `confirmacao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `observacao` text DEFAULT NULL,
  `distancia_local` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `denuncia`
--

CREATE TABLE `denuncia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `localizacao_id` bigint(20) UNSIGNED NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `anonima` tinyint(1) DEFAULT 0,
  `status` enum('PENDENTE','VALIDADA','REJEITADA','DUPLICADA') DEFAULT 'PENDENTE',
  `duplicada` tinyint(1) DEFAULT 0,
  `pontuacao_confianca` decimal(5,2) DEFAULT 0.00,
  `origem` enum('WEB','ANDROID','API') DEFAULT 'WEB',
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `estado`
--

CREATE TABLE `estado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `cor` varchar(20) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `estado_final` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historico`
--

CREATE TABLE `historico` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `processo_id` bigint(20) UNSIGNED NOT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `modulo` varchar(80) NOT NULL,
  `acao` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `ip_origem` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `data_evento` datetime NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indicador`
--

CREATE TABLE `indicador` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `nome` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `formula` text DEFAULT NULL,
  `periodo` varchar(50) DEFAULT NULL,
  `valor` decimal(20,4) DEFAULT NULL,
  `ultima_atualizacao` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intervencao`
--

CREATE TABLE `intervencao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `processo_id` bigint(20) UNSIGNED NOT NULL,
  `organizacao_id` bigint(20) UNSIGNED NOT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` enum('INSPECAO','MANUTENCAO','REPARACAO','LIMPEZA','FISCALIZACAO','OUTRO') NOT NULL,
  `status` enum('PLANEADA','EM_EXECUCAO','PAUSADA','CONCLUIDA','CANCELADA') DEFAULT 'PLANEADA',
  `percentual_execucao` decimal(5,2) DEFAULT 0.00,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `custo_estimado` decimal(15,2) DEFAULT NULL,
  `resultado` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `localizacao`
--

CREATE TABLE `localizacao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `bairro_id` bigint(20) UNSIGNED NOT NULL,
  `rua` varchar(200) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `tipo_geometria` enum('PONTO','LINHA','AREA') DEFAULT 'PONTO',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `municipio`
--

CREATE TABLE `municipio` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `provincia_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificacao`
--

CREATE TABLE `notificacao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `tipo` enum('INFORMATIVA','ALERTA','URGENTE','SUCESSO') DEFAULT NULL,
  `canal` enum('APP','EMAIL','SMS','WEB') DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_leitura` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocorrencia`
--

CREATE TABLE `ocorrencia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `localizacao_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `gravidade` enum('BAIXA','MEDIA','ALTA','CRITICA') DEFAULT 'MEDIA',
  `prioridade` enum('BAIXA','MEDIA','ALTA','URGENTE') DEFAULT 'MEDIA',
  `anonima` tinyint(1) DEFAULT 0,
  `data_ocorrencia` datetime DEFAULT NULL,
  `data_validacao` datetime DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `criado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `atualizado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocorrencia_tag`
--

CREATE TABLE `ocorrencia_tag` (
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizacao`
--

CREATE TABLE `organizacao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `sigla` varchar(30) DEFAULT NULL,
  `tipo` enum('PUBLICA','PRIVADA','MISTA') NOT NULL DEFAULT 'PUBLICA',
  `nivel_administrativo` enum('COMUNAL','MUNICIPAL','PROVINCIAL','NACIONAL') NOT NULL,
  `codigo_oficial` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `provincia_id` bigint(20) UNSIGNED DEFAULT NULL,
  `municipio_id` bigint(20) UNSIGNED DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pais`
--

CREATE TABLE `pais` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo_iso` char(3) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `papel`
--

CREATE TABLE `papel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `nivel` int(11) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `papel_permissao`
--

CREATE TABLE `papel_permissao` (
  `papel_id` bigint(20) UNSIGNED NOT NULL,
  `permissao_id` bigint(20) UNSIGNED NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissao`
--

CREATE TABLE `permissao` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `modulo` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processo`
--

CREATE TABLE `processo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `responsavel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `organizacao_id` bigint(20) UNSIGNED DEFAULT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_conclusao` datetime DEFAULT NULL,
  `prazo_estimado` date DEFAULT NULL,
  `percentual_conclusao` int(11) DEFAULT 0,
  `observacao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `versao` int(11) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provincia`
--

CREATE TABLE `provincia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `pais_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `cor` varchar(20) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipo_ocorrencia`
--

CREATE TABLE `tipo_ocorrencia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `subcategoria_id` bigint(20) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `gravidade_padrao` enum('BAIXA','MEDIA','ALTA','CRITICA') DEFAULT 'MEDIA',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `papel_id` bigint(20) UNSIGNED NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo_documento` enum('BI','PASSAPORTE','OUTRO') DEFAULT 'BI',
  `numero_documento` varchar(50) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `versao` int(11) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `eliminado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voto_prioridade`
--

CREATE TABLE `voto_prioridade` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `utilizador_id` bigint(20) UNSIGNED NOT NULL,
  `ocorrencia_id` bigint(20) UNSIGNED NOT NULL,
  `peso` decimal(5,2) DEFAULT 1.00,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp()
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anexo`
--
ALTER TABLE `anexo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `anexo_ibfk_2` (`denuncia_id`),
  ADD KEY `anexo_ibfk_3` (`ocorrencia_id`),
  ADD KEY `anexo_ibfk_4` (`intervencao_id`);

--
-- Indexes for table `bairro`
--
ALTER TABLE `bairro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `comuna_id` (`comuna_id`,`nome`);

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `ocorrencia_id` (`ocorrencia_id`),
  ADD KEY `comentario_ibfk_3` (`comentario_pai_id`);

--
-- Indexes for table `comuna`
--
ALTER TABLE `comuna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `municipio_id` (`municipio_id`,`nome`);

--
-- Indexes for table `confirmacao`
--
ALTER TABLE `confirmacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_confirmacao` (`utilizador_id`,`ocorrencia_id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `confirmacao_ibfk_2` (`ocorrencia_id`);

--
-- Indexes for table `denuncia`
--
ALTER TABLE `denuncia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `tipo_ocorrencia_id` (`tipo_ocorrencia_id`),
  ADD KEY `idx_denuncia_status` (`status`),
  ADD KEY `idx_denuncia_utilizador` (`utilizador_id`),
  ADD KEY `idx_denuncia_localizacao` (`localizacao_id`),
  ADD KEY `denuncia_ibfk_4` (`ocorrencia_id`);

--
-- Indexes for table `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_historico_processo` (`processo_id`),
  ADD KEY `idx_historico_data` (`data_evento`),
  ADD KEY `idx_historico_utilizador` (`utilizador_id`),
  ADD KEY `idx_historico_estado` (`estado_id`),
  ADD KEY `idx_historico_modulo` (`modulo`);

--
-- Indexes for table `indicador`
--
ALTER TABLE `indicador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`);

--
-- Indexes for table `intervencao`
--
ALTER TABLE `intervencao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `fk_intervencao_utilizador` (`utilizador_id`),
  ADD KEY `idx_intervencao_processo` (`processo_id`),
  ADD KEY `idx_intervencao_status` (`status`),
  ADD KEY `idx_intervencao_data` (`data_inicio`),
  ADD KEY `idx_intervencao_org` (`organizacao_id`);

--
-- Indexes for table `localizacao`
--
ALTER TABLE `localizacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `fk_localizacao_bairro` (`bairro_id`);

--
-- Indexes for table `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `provincia_id` (`provincia_id`,`nome`);

--
-- Indexes for table `notificacao`
--
ALTER TABLE `notificacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `notificacao_ibfk_2` (`ocorrencia_id`);

--
-- Indexes for table `ocorrencia`
--
ALTER TABLE `ocorrencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `fk_ocorrencia_utilizador` (`utilizador_id`),
  ADD KEY `idx_ocorrencia_estado` (`estado_id`),
  ADD KEY `idx_ocorrencia_tipo` (`tipo_ocorrencia_id`),
  ADD KEY `idx_ocorrencia_localizacao` (`localizacao_id`),
  ADD KEY `idx_ocorrencia_data` (`criado_em`),
  ADD KEY `fk_ocorrencia_criado_por` (`criado_por`),
  ADD KEY `fk_ocorrencia_atualizado_por` (`atualizado_por`);

--
-- Indexes for table `ocorrencia_tag`
--
ALTER TABLE `ocorrencia_tag`
  ADD PRIMARY KEY (`ocorrencia_id`,`tag_id`),
  ADD KEY `ocorrencia_tag_ibfk_2` (`tag_id`);

--
-- Indexes for table `organizacao`
--
ALTER TABLE `organizacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `uk_organizacao_codigo` (`codigo_oficial`),
  ADD KEY `fk_org_provincia` (`provincia_id`),
  ADD KEY `fk_org_municipio` (`municipio_id`),
  ADD KEY `idx_organizacao_nivel` (`nivel_administrativo`);

--
-- Indexes for table `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `codigo_iso` (`codigo_iso`);

--
-- Indexes for table `papel`
--
ALTER TABLE `papel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `papel_permissao`
--
ALTER TABLE `papel_permissao`
  ADD PRIMARY KEY (`papel_id`,`permissao_id`),
  ADD KEY `fk_pp_permissao` (`permissao_id`);

--
-- Indexes for table `permissao`
--
ALTER TABLE `permissao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `processo`
--
ALTER TABLE `processo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `ocorrencia_id` (`ocorrencia_id`),
  ADD KEY `fk_processo_responsavel` (`responsavel_id`),
  ADD KEY `idx_processo_org` (`organizacao_id`),
  ADD KEY `idx_processo_estado` (`estado_id`);

--
-- Indexes for table `provincia`
--
ALTER TABLE `provincia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `pais_id` (`pais_id`,`nome`);

--
-- Indexes for table `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `categoria_id` (`categoria_id`,`nome`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `tipo_ocorrencia`
--
ALTER TABLE `tipo_ocorrencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `subcategoria_id` (`subcategoria_id`,`nome`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telefone` (`telefone`),
  ADD KEY `fk_utilizador_papel` (`papel_id`),
  ADD KEY `idx_utilizador_email` (`email`),
  ADD KEY `idx_utilizador_telefone` (`telefone`);

--
-- Indexes for table `voto_prioridade`
--
ALTER TABLE `voto_prioridade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilizador_id` (`utilizador_id`,`ocorrencia_id`),
  ADD UNIQUE KEY `uk_voto` (`utilizador_id`,`ocorrencia_id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `voto_prioridade_ibfk_2` (`ocorrencia_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anexo`
--
ALTER TABLE `anexo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bairro`
--
ALTER TABLE `bairro`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comuna`
--
ALTER TABLE `comuna`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `confirmacao`
--
ALTER TABLE `confirmacao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `denuncia`
--
ALTER TABLE `denuncia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estado`
--
ALTER TABLE `estado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historico`
--
ALTER TABLE `historico`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indicador`
--
ALTER TABLE `indicador`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intervencao`
--
ALTER TABLE `intervencao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `municipio`
--
ALTER TABLE `municipio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificacao`
--
ALTER TABLE `notificacao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ocorrencia`
--
ALTER TABLE `ocorrencia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizacao`
--
ALTER TABLE `organizacao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pais`
--
ALTER TABLE `pais`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `papel`
--
ALTER TABLE `papel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissao`
--
ALTER TABLE `permissao`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `processo`
--
ALTER TABLE `processo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provincia`
--
ALTER TABLE `provincia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_ocorrencia`
--
ALTER TABLE `tipo_ocorrencia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voto_prioridade`
--
ALTER TABLE `voto_prioridade`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anexo`
--
ALTER TABLE `anexo`
  ADD CONSTRAINT `anexo_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `anexo_ibfk_2` FOREIGN KEY (`denuncia_id`) REFERENCES `denuncia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `anexo_ibfk_3` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `anexo_ibfk_4` FOREIGN KEY (`intervencao_id`) REFERENCES `intervencao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bairro`
--
ALTER TABLE `bairro`
  ADD CONSTRAINT `fk_bairro_comuna` FOREIGN KEY (`comuna_id`) REFERENCES `comuna` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `comentario_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `comentario_ibfk_2` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`),
  ADD CONSTRAINT `comentario_ibfk_3` FOREIGN KEY (`comentario_pai_id`) REFERENCES `comentario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comuna`
--
ALTER TABLE `comuna`
  ADD CONSTRAINT `fk_comuna_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipio` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `confirmacao`
--
ALTER TABLE `confirmacao`
  ADD CONSTRAINT `confirmacao_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `confirmacao_ibfk_2` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `denuncia`
--
ALTER TABLE `denuncia`
  ADD CONSTRAINT `denuncia_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `denuncia_ibfk_2` FOREIGN KEY (`tipo_ocorrencia_id`) REFERENCES `tipo_ocorrencia` (`id`),
  ADD CONSTRAINT `denuncia_ibfk_3` FOREIGN KEY (`localizacao_id`) REFERENCES `localizacao` (`id`),
  ADD CONSTRAINT `denuncia_ibfk_4` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `historico`
--
ALTER TABLE `historico`
  ADD CONSTRAINT `fk_hist_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_processo` FOREIGN KEY (`processo_id`) REFERENCES `processo` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `intervencao`
--
ALTER TABLE `intervencao`
  ADD CONSTRAINT `fk_intervencao_org` FOREIGN KEY (`organizacao_id`) REFERENCES `organizacao` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_intervencao_processo` FOREIGN KEY (`processo_id`) REFERENCES `processo` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_intervencao_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `localizacao`
--
ALTER TABLE `localizacao`
  ADD CONSTRAINT `fk_localizacao_bairro` FOREIGN KEY (`bairro_id`) REFERENCES `bairro` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `municipio`
--
ALTER TABLE `municipio`
  ADD CONSTRAINT `fk_municipio_provincia` FOREIGN KEY (`provincia_id`) REFERENCES `provincia` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notificacao`
--
ALTER TABLE `notificacao`
  ADD CONSTRAINT `notificacao_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `notificacao_ibfk_2` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ocorrencia`
--
ALTER TABLE `ocorrencia`
  ADD CONSTRAINT `fk_ocorrencia_atualizado_por` FOREIGN KEY (`atualizado_por`) REFERENCES `utilizador` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `utilizador` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_localizacao` FOREIGN KEY (`localizacao_id`) REFERENCES `localizacao` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_tipo` FOREIGN KEY (`tipo_ocorrencia_id`) REFERENCES `tipo_ocorrencia` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ocorrencia_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ocorrencia_tag`
--
ALTER TABLE `ocorrencia_tag`
  ADD CONSTRAINT `ocorrencia_tag_ibfk_1` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ocorrencia_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organizacao`
--
ALTER TABLE `organizacao`
  ADD CONSTRAINT `fk_org_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipio` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_org_provincia` FOREIGN KEY (`provincia_id`) REFERENCES `provincia` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `papel_permissao`
--
ALTER TABLE `papel_permissao`
  ADD CONSTRAINT `fk_pp_papel` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `processo`
--
ALTER TABLE `processo`
  ADD CONSTRAINT `fk_processo_estado` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_processo_ocorrencia` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_processo_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `utilizador` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `provincia`
--
ALTER TABLE `provincia`
  ADD CONSTRAINT `fk_provincia_pais` FOREIGN KEY (`pais_id`) REFERENCES `pais` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_subcategoria_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `tipo_ocorrencia`
--
ALTER TABLE `tipo_ocorrencia`
  ADD CONSTRAINT `fk_tipo_subcategoria` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategoria` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `fk_utilizador_papel` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `voto_prioridade`
--
ALTER TABLE `voto_prioridade`
  ADD CONSTRAINT `voto_prioridade_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`),
  ADD CONSTRAINT `voto_prioridade_ibfk_2` FOREIGN KEY (`ocorrencia_id`) REFERENCES `ocorrencia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
