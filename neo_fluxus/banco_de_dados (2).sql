-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 14-Nov-2025 às 10:36
-- Versão do servidor: 10.4.10-MariaDB
-- versão do PHP: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `fluxusdb`
--
CREATE DATABASE IF NOT EXISTS `fluxusdb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `fluxusdb`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `atividade`
--

DROP TABLE IF EXISTS `atividade`;
CREATE TABLE IF NOT EXISTS `atividade` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `disciplina_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_atividade` date NOT NULL,
  `tipo` varchar(50) DEFAULT 'atividade',
  `criado_por` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `atividade`
--

INSERT INTO `atividade` (`id`, `disciplina_id`, `titulo`, `descricao`, `data_atividade`, `tipo`, `criado_por`, `criado_em`) VALUES
(1, 1, 'Redação sobre Machado de Assis', 'Escrever uma redação de 30 linhas sobre a obra Dom Casmurro', '2024-12-15', 'redacao', 3, '2025-11-14 10:31:37'),
(2, 1, 'Prova de Literatura', 'Avaliação sobre Romantismo e Realismo', '2024-12-18', 'prova', 3, '2025-11-14 10:31:37'),
(3, 2, 'Lista de Exercícios - Equações', 'Resolver exercícios do capítulo 5', '2024-12-12', 'exercicio', 2, '2025-11-14 10:31:37'),
(4, 2, 'Prova de Matemática', 'Avaliação sobre funções e geometria', '2024-12-20', 'prova', 2, '2025-11-14 10:31:37'),
(5, 3, 'Trabalho sobre Brasil Colonial', 'Pesquisa em grupo sobre período colonial', '2024-12-16', 'trabalho', 3, '2025-11-14 10:31:37'),
(6, 4, 'Mapa do Brasil', 'Desenhar mapa com relevos e rios', '2024-12-14', 'atividade', 2, '2025-11-14 10:31:37'),
(7, 5, 'Relatório de Experimento', 'Relatório sobre observação de células', '2024-12-17', 'relatorio', 3, '2025-11-14 10:31:37'),
(8, 5, 'prova', 'a', '2025-11-15', 'prova', 3, '2025-11-14 10:34:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `aula`
--

DROP TABLE IF EXISTS `aula`;
CREATE TABLE IF NOT EXISTS `aula` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `disciplina_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `horario` time NOT NULL,
  `conteudo` text DEFAULT NULL,
  `criado_por` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `aula`
--

INSERT INTO `aula` (`id`, `disciplina_id`, `professor_id`, `data`, `horario`, `conteudo`, `criado_por`) VALUES
(1, 1, 3, '2024-12-01', '08:00:00', 'Introdução à Literatura Brasileira', 3),
(2, 1, 3, '2024-12-03', '08:00:00', 'Análise de Texto - Machado de Assis', 3),
(3, 1, 3, '2024-12-05', '08:00:00', 'Gramática: Classes de Palavras', 3),
(4, 1, 3, '2024-12-08', '08:00:00', 'Redação: Texto Dissertativo', 3),
(5, 1, 3, '2024-12-10', '08:00:00', 'Literatura: Romantismo', 3),
(6, 2, 2, '2024-12-02', '10:00:00', 'Equações do 1º Grau', 2),
(7, 2, 2, '2024-12-04', '10:00:00', 'Equações do 2º Grau', 2),
(8, 2, 2, '2024-12-06', '10:00:00', 'Funções Lineares', 2),
(9, 2, 2, '2024-12-09', '10:00:00', 'Geometria Plana', 2),
(10, 2, 2, '2024-12-11', '10:00:00', 'Trigonometria Básica', 2),
(11, 3, 3, '2024-12-02', '14:00:00', 'Brasil Colonial', 3),
(12, 3, 3, '2024-12-04', '14:00:00', 'Independência do Brasil', 3),
(13, 3, 3, '2024-12-06', '14:00:00', 'República Velha', 3),
(14, 3, 3, '2024-12-09', '14:00:00', 'Era Vargas', 3),
(15, 3, 3, '2024-12-11', '14:00:00', 'Ditadura Militar', 3),
(16, 4, 2, '2024-12-03', '08:00:00', 'Relevo Brasileiro', 2),
(17, 4, 2, '2024-12-05', '08:00:00', 'Hidrografia', 2),
(18, 4, 2, '2024-12-10', '08:00:00', 'Clima e Vegetação', 2),
(19, 5, 3, '2024-12-01', '10:00:00', 'Sistema Solar', 3),
(20, 5, 3, '2024-12-03', '10:00:00', 'Células e Tecidos', 3),
(21, 5, 3, '2024-12-08', '10:00:00', 'Ecossistemas', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contato_suporte`
--

DROP TABLE IF EXISTS `contato_suporte`;
CREATE TABLE IF NOT EXISTS `contato_suporte` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `secao_exibicao` varchar(50) NOT NULL DEFAULT 'institucionais',
  `titulo` varchar(100) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `icone` varchar(50) DEFAULT 'fas fa-info-circle',
  `cor` varchar(20) DEFAULT '#d32f2f',
  `ordem` int(11) DEFAULT 0,
  `ativo` smallint(6) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo` (`tipo`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `contato_suporte`
--

INSERT INTO `contato_suporte` (`id`, `tipo`, `secao_exibicao`, `titulo`, `valor`, `icone`, `cor`, `ordem`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(1, 'endereco_escola', 'institucionais', 'Endereço da Escola', 'Rua Paulo Frontin, 365, Mogi das Cruzes 08710-050, BR', 'fas fa-map-marker-alt', '#d32f2f', 10, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(2, 'email_geral', 'institucionais', 'E-mail Geral', 'maio68coletivo@gmail.com', 'fas fa-envelope', '#1976d2', 20, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(3, 'telefone_geral', 'institucionais', 'Telefone Geral', '(11) 99999-9999', 'fas fa-phone', '#388e3c', 30, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(4, 'instagram_social', 'sociais', 'Instagram', 'https://www.instagram.com/cursinhopopularmaio68/', 'fab fa-instagram', '#e91e63', 40, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(5, 'facebook_social', 'sociais', 'Facebook', 'https://www.facebook.com/cursinhopopularmaio68', 'fab fa-facebook', '#3f51b5', 50, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(6, 'youtube_social', 'sociais', 'YouTube', 'https://www.youtube.com/@cursinhopopularmaio68', 'fab fa-youtube', '#f44336', 60, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(7, 'prof_matematica', 'professores', 'Prof. Matemática', 'matematica@fluxus.edu', 'fas fa-calculator', '#ff9800', 100, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(8, 'prof_portugues', 'professores', 'Prof. Português', 'portugues@fluxus.edu', 'fas fa-book-open', '#9c27b0', 110, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(9, 'prof_historia', 'professores', 'Prof. História', 'historia@fluxus.edu', 'fas fa-landmark', '#795548', 120, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38'),
(10, 'coordenacao', 'professores', 'Coordenação Pedagógica', 'coordenacao@fluxus.edu', 'fas fa-user-tie', '#607d8b', 130, 1, '2025-11-14 10:31:38', '2025-11-14 10:31:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cronograma_semanal`
--

DROP TABLE IF EXISTS `cronograma_semanal`;
CREATE TABLE IF NOT EXISTS `cronograma_semanal` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dia_semana` varchar(20) NOT NULL,
  `horario` time NOT NULL,
  `disciplina_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `conteudo` text DEFAULT NULL,
  `criado_por` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dia_semana` (`dia_semana`,`horario`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `cronograma_semanal`
--

INSERT INTO `cronograma_semanal` (`id`, `dia_semana`, `horario`, `disciplina_id`, `professor_id`, `conteudo`, `criado_por`) VALUES
(1, 'segunda', '08:00:00', 1, 3, 'Língua Portuguesa - Gramática', 3),
(2, 'segunda', '10:00:00', 2, 2, 'Matemática - Álgebra', 2),
(3, 'segunda', '14:00:00', 3, 3, 'História - Brasil República', 3),
(4, 'terca', '08:00:00', 4, 2, 'Geografia - Relevo Brasileiro', 2),
(5, 'terca', '10:00:00', 5, 3, 'Ciências - Sistema Solar', 3),
(6, 'terca', '14:00:00', 1, 3, 'Língua Portuguesa - Literatura', 3),
(7, 'quarta', '08:00:00', 2, 2, 'Matemática - Geometria', 2),
(8, 'quarta', '10:00:00', 3, 3, 'História - Era Vargas', 3),
(9, 'quarta', '14:00:00', 4, 2, 'Geografia - Hidrografia', 2),
(10, 'quinta', '08:00:00', 5, 3, 'Ciências - Ecossistemas', 3),
(11, 'quinta', '10:00:00', 1, 3, 'Língua Portuguesa - Redação', 3),
(12, 'quinta', '14:00:00', 2, 2, 'Matemática - Funções', 2),
(13, 'sexta', '08:00:00', 3, 3, 'História - Ditadura Militar', 3),
(14, 'sexta', '10:00:00', 4, 2, 'Geografia - Clima e Vegetação', 2),
(15, 'sexta', '14:00:00', 5, 3, 'Ciências - Células e Tecidos', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `disciplina`
--

DROP TABLE IF EXISTS `disciplina`;
CREATE TABLE IF NOT EXISTS `disciplina` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `ativo` smallint(6) DEFAULT 1,
  `primeiro_login` timestamp NULL DEFAULT NULL,
  `senha_alterada` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `disciplina`
--

INSERT INTO `disciplina` (`id`, `nome`, `professor_id`, `ativo`, `primeiro_login`, `senha_alterada`) VALUES
(1, 'Língua Portuguesa', 3, 1, NULL, 0),
(2, 'Matemática', 2, 1, NULL, 0),
(3, 'História', 3, 1, NULL, 0),
(4, 'Geografia', 2, 1, NULL, 0),
(5, 'Ciências', 3, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `frequencia`
--

DROP TABLE IF EXISTS `frequencia`;
CREATE TABLE IF NOT EXISTS `frequencia` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `aula_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `presente` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aula_id` (`aula_id`,`aluno_id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `frequencia`
--

INSERT INTO `frequencia` (`id`, `aula_id`, `aluno_id`, `presente`) VALUES
(1, 1, 4, 1),
(2, 1, 5, 1),
(3, 1, 6, 1),
(4, 1, 7, 1),
(5, 1, 8, 1),
(6, 2, 4, 1),
(7, 2, 5, 1),
(8, 2, 6, 1),
(9, 2, 7, 0),
(10, 2, 8, 0),
(11, 3, 4, 0),
(12, 3, 5, 1),
(13, 3, 6, 1),
(14, 3, 7, 1),
(15, 3, 8, 1),
(16, 4, 4, 1),
(17, 4, 5, 0),
(18, 4, 6, 1),
(19, 4, 7, 1),
(20, 4, 8, 1),
(21, 5, 4, 1),
(22, 5, 5, 1),
(23, 5, 6, 0),
(24, 5, 7, 1),
(25, 5, 8, 1),
(26, 6, 4, 1),
(27, 6, 5, 1),
(28, 6, 6, 1),
(29, 6, 7, 1),
(30, 6, 8, 1),
(31, 7, 4, 0),
(32, 7, 5, 1),
(33, 7, 6, 1),
(34, 7, 7, 1),
(35, 7, 8, 1),
(36, 8, 4, 1),
(37, 8, 5, 1),
(38, 8, 6, 1),
(39, 8, 7, 1),
(40, 8, 8, 0),
(41, 9, 4, 1),
(42, 9, 5, 1),
(43, 9, 6, 1),
(44, 9, 7, 0),
(45, 9, 8, 1),
(46, 10, 4, 1),
(47, 10, 5, 0),
(48, 10, 6, 1),
(49, 10, 7, 1),
(50, 10, 8, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `matricula`
--

DROP TABLE IF EXISTS `matricula`;
CREATE TABLE IF NOT EXISTS `matricula` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) NOT NULL,
  `disciplina_id` int(11) NOT NULL,
  `ativo` smallint(6) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aluno_id` (`aluno_id`,`disciplina_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `matricula`
--

INSERT INTO `matricula` (`id`, `aluno_id`, `disciplina_id`, `ativo`) VALUES
(1, 4, 1, 1),
(2, 4, 2, 1),
(3, 4, 3, 1),
(4, 4, 4, 1),
(5, 4, 5, 1),
(6, 5, 1, 1),
(7, 5, 2, 1),
(8, 5, 3, 1),
(9, 5, 4, 1),
(10, 5, 5, 1),
(11, 6, 1, 1),
(12, 6, 2, 1),
(13, 6, 3, 1),
(14, 6, 4, 1),
(15, 6, 5, 1),
(16, 7, 1, 1),
(17, 7, 2, 1),
(18, 7, 3, 1),
(19, 7, 4, 1),
(20, 7, 5, 1),
(21, 8, 1, 1),
(22, 8, 2, 1),
(23, 8, 3, 1),
(24, 8, 4, 1),
(25, 8, 5, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tipo` varchar(20) NOT NULL CHECK (`tipo` in ('aluno','professor','coordenador')),
  `login` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` smallint(6) DEFAULT 1,
  `primeiro_login` timestamp NULL DEFAULT NULL,
  `senha_alterada` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `email`, `tipo`, `login`, `senha`, `ativo`, `primeiro_login`, `senha_alterada`) VALUES
(1, 'Ana Souza', 'ana.souza@fluxus.edu', 'coordenador', 'ana.souza@fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(2, 'Bruno Almeida', 'bruno.almeida@fluxus.edu', 'professor', 'bruno.almeida@fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(3, 'Carla Ribeiro', 'carla.ribeiro@fluxus.edu', 'professor', 'carla.ribeiro@fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(4, 'Diego Martins', 'diego.martins@estudante.fluxus.edu', 'aluno', 'diego.martins@estudante.fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(5, 'Fernanda Lopes', 'fernanda.lopes@estudante.fluxus.edu', 'aluno', 'fernanda.lopes@estudante.fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(6, 'Gabriela Costa', 'gabriela.costa@estudante.fluxus.edu', 'aluno', 'gabriela.costa@estudante.fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(7, 'Henrique Silva', 'henrique.silva@estudante.fluxus.edu', 'aluno', 'henrique.silva@estudante.fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0),
(8, 'Rodrigo Silva', 'rodrigo.silva@estudante.fluxus.edu', 'aluno', 'rodrigo.silva@estudante.fluxus.edu', '123456', 1, '2025-11-14 10:31:37', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
