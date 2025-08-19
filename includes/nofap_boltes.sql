-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/08/2025 às 02:39
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `nofap_boltes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$iLA1PATQYhf56cCN3JhmB.dSsaHjSHls8WBGxO13w9Y0aQoljI0vC', '2025-08-18 18:25:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `check_ins`
--

CREATE TABLE `check_ins` (
  `id` int(11) NOT NULL,
  `membro_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `status` enum('falhei','firme') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL DEFAULT 1,
  `dt_inicio` date DEFAULT NULL,
  `dt_fim` date DEFAULT NULL,
  `qtd_dias` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dt_max_inscricao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`id`, `dt_inicio`, `dt_fim`, `qtd_dias`, `created_at`, `updated_at`, `dt_max_inscricao`) VALUES
(1, '2025-08-01', '2025-08-17', 17, '2025-08-18 18:25:14', '2025-08-19 00:18:52', '2025-08-30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `status` enum('ativo','falhou','inativo') DEFAULT 'ativo',
  `ultimo_checkin` date DEFAULT NULL,
  `dias_consecutivos` int(11) DEFAULT 0,
  `data_falha` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `members`
--

INSERT INTO `members` (`id`, `nome`, `status`, `ultimo_checkin`, `dias_consecutivos`, `data_falha`, `created_at`, `updated_at`) VALUES
(12, 'Tonho2', 'ativo', '2025-08-18', 0, NULL, '2025-08-18 23:58:29', '2025-08-19 00:01:32');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Índices de tabela `check_ins`
--
ALTER TABLE `check_ins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_membro_data` (`membro_id`,`data`),
  ADD KEY `idx_checkins_date` (`data`),
  ADD KEY `idx_checkins_membro` (`membro_id`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_members_status` (`status`),
  ADD KEY `idx_members_last_checkin` (`ultimo_checkin`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `check_ins`
--
ALTER TABLE `check_ins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `check_ins`
--
ALTER TABLE `check_ins`
  ADD CONSTRAINT `check_ins_ibfk_1` FOREIGN KEY (`membro_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
