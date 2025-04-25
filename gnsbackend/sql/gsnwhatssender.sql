-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 24/04/2025 às 21:43
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
-- Banco de dados: `gsnwhatssender`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_1d97e022c6459e9e22a2395e10536bd7', 'i:1;', 1745503761),
('laravel_cache_1d97e022c6459e9e22a2395e10536bd7:timer', 'i:1745503761;', 1745503761),
('laravel_cache_28b04261f131f61721f2034c50deda34', 'i:1;', 1745502000),
('laravel_cache_28b04261f131f61721f2034c50deda34:timer', 'i:1745502000;', 1745502000),
('laravel_cache_a89fe688d331e63feaa82ee6d8f318e6', 'i:2;', 1745501917),
('laravel_cache_a89fe688d331e63feaa82ee6d8f318e6:timer', 'i:1745501917;', 1745501917),
('laravel_cache_jonasjaldesigner@gmail.com|127.0.0.1', 'i:2;', 1745501917),
('laravel_cache_jonasjaldesigner@gmail.com|127.0.0.1:timer', 'i:1745501917;', 1745501917);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, 'c3bd640b-7e1e-4fdc-bbba-ecaea51d6a41', 'database', 'default', '{\"uuid\":\"c3bd640b-7e1e-4fdc-bbba-ecaea51d6a41\",\"displayName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendWhatsAppMessageJob\\\":2:{s:10:\\\"\\u0000*\\u0000contact\\\";s:11:\\\"21966950079\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-04-23 20:04:49.180654\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}\"}}', 'TypeError: App\\Services\\WhatsAppService::sendMessage(): Argument #2 ($message) must be of type string, null given, called in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php on line 35 and defined in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Services/WhatsAppService.php:69\nStack trace:\n#0 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php(35): App\\Services\\WhatsAppService->sendMessage(\'21966950079\', NULL, NULL, NULL)\n#1 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SendWhatsAppMessageJob->handle(Object(App\\Services\\WhatsAppService))\n#2 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#3 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#4 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#5 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#6 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(125): Illuminate\\Container\\Container->call(Array)\n#7 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#8 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#9 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#10 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(125): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendWhatsAppMessageJob), false)\n#11 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#12 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#13 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(120): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#14 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendWhatsAppMessageJob))\n#15 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#16 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(441): Illuminate\\Queue\\Jobs\\Job->fire()\n#17 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(391): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#18 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(177): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#21 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#22 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#23 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#24 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#25 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#26 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(211): Illuminate\\Container\\Container->call(Array)\n#27 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Command/Command.php(279): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(1094): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(342): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(193): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1234): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#35 {main}', '2025-04-23 23:04:51'),
(2, '199fbc1d-9f87-400e-a0f0-4ec18720018b', 'database', 'default', '{\"uuid\":\"199fbc1d-9f87-400e-a0f0-4ec18720018b\",\"displayName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendWhatsAppMessageJob\\\":2:{s:10:\\\"\\u0000*\\u0000contact\\\";s:11:\\\"21991099205\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-04-23 20:04:55.199074\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}\"}}', 'TypeError: App\\Services\\WhatsAppService::sendMessage(): Argument #2 ($message) must be of type string, null given, called in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php on line 35 and defined in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Services/WhatsAppService.php:69\nStack trace:\n#0 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php(35): App\\Services\\WhatsAppService->sendMessage(\'21991099205\', NULL, NULL, NULL)\n#1 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SendWhatsAppMessageJob->handle(Object(App\\Services\\WhatsAppService))\n#2 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#3 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#4 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#5 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#6 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(125): Illuminate\\Container\\Container->call(Array)\n#7 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#8 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#9 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#10 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(125): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendWhatsAppMessageJob), false)\n#11 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#12 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#13 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(120): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#14 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendWhatsAppMessageJob))\n#15 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#16 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(441): Illuminate\\Queue\\Jobs\\Job->fire()\n#17 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(391): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#18 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(177): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#21 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#22 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#23 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#24 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#25 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#26 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(211): Illuminate\\Container\\Container->call(Array)\n#27 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Command/Command.php(279): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(1094): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(342): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(193): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1234): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#35 {main}', '2025-04-23 23:04:57'),
(3, 'e2bfe162-6d7e-49f3-a7f0-8875be32b1fe', 'database', 'default', '{\"uuid\":\"e2bfe162-6d7e-49f3-a7f0-8875be32b1fe\",\"displayName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendWhatsAppMessageJob\\\":2:{s:10:\\\"\\u0000*\\u0000contact\\\";s:11:\\\"21960997008\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-04-23 20:04:59.206800\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}\"}}', 'TypeError: App\\Services\\WhatsAppService::sendMessage(): Argument #2 ($message) must be of type string, null given, called in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php on line 35 and defined in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Services/WhatsAppService.php:69\nStack trace:\n#0 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php(35): App\\Services\\WhatsAppService->sendMessage(\'21960997008\', NULL, NULL, NULL)\n#1 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SendWhatsAppMessageJob->handle(Object(App\\Services\\WhatsAppService))\n#2 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#3 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#4 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#5 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#6 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(125): Illuminate\\Container\\Container->call(Array)\n#7 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#8 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#9 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#10 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(125): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendWhatsAppMessageJob), false)\n#11 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#12 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#13 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(120): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#14 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendWhatsAppMessageJob))\n#15 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#16 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(441): Illuminate\\Queue\\Jobs\\Job->fire()\n#17 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(391): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#18 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(177): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#21 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#22 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#23 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#24 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#25 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#26 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(211): Illuminate\\Container\\Container->call(Array)\n#27 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Command/Command.php(279): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(1094): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(342): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(193): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1234): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#35 {main}', '2025-04-23 23:05:00'),
(4, 'ec295f68-a4ce-4099-a82c-f2057f247b90', 'database', 'default', '{\"uuid\":\"ec295f68-a4ce-4099-a82c-f2057f247b90\",\"displayName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendWhatsAppMessageJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendWhatsAppMessageJob\\\":2:{s:10:\\\"\\u0000*\\u0000contact\\\";s:11:\\\"19981189047\\\";s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-04-23 20:05:13.219201\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}\"}}', 'TypeError: App\\Services\\WhatsAppService::sendMessage(): Argument #2 ($message) must be of type string, null given, called in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php on line 35 and defined in /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Services/WhatsAppService.php:69\nStack trace:\n#0 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/app/Jobs/SendWhatsAppMessageJob.php(35): App\\Services\\WhatsAppService->sendMessage(\'19981189047\', NULL, NULL, NULL)\n#1 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SendWhatsAppMessageJob->handle(Object(App\\Services\\WhatsAppService))\n#2 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#3 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#4 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#5 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#6 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(125): Illuminate\\Container\\Container->call(Array)\n#7 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#8 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#9 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(129): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#10 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(125): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendWhatsAppMessageJob), false)\n#11 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(169): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#12 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(126): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendWhatsAppMessageJob))\n#13 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(120): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#14 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(68): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendWhatsAppMessageJob))\n#15 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#16 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(441): Illuminate\\Queue\\Jobs\\Job->fire()\n#17 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(391): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#18 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(177): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#21 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#22 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#23 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#24 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#25 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Container/Container.php(754): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#26 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(211): Illuminate\\Container\\Container->call(Array)\n#27 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Command/Command.php(279): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Console/Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(1094): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(342): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/symfony/console/Application.php(193): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(197): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1234): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 /home/jalvivi/Documentos/projetos/Jean_trabalhos/ZAP/migracao_zap/gnsbackend/artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#35 {main}', '2025-04-23 23:05:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historic`
--

CREATE TABLE `historic` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `contact` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `errorType` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `historic`
--

INSERT INTO `historic` (`id`, `user_id`, `contact`, `status`, `name`, `errorType`, `created_at`, `updated_at`) VALUES
(33, 4, '21966950079', 'enviado', 'Jonas Alves', NULL, '2025-04-24 17:09:50', '2025-04-24 17:09:50'),
(34, 4, '21991099205', 'enviado', 'Vivi', NULL, '2025-04-24 17:09:50', '2025-04-24 17:09:50'),
(35, 4, '21960997008', 'enviado', 'Clayton', NULL, '2025-04-24 17:09:50', '2025-04-24 17:09:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_16_171930_add_two_factor_columns_to_users_table', 1),
(5, '2025_04_17_124936_add_column_message_table', 1),
(6, '2025_04_17_153201_create_historic_table', 1),
(7, '2025_04_19_153540_update_users_table_to_use_increments', 2),
(8, '2025_04_21_032321_rename_column_in_table', 3),
(9, '2025_04_21_034839_create_historic_table', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5qbCngFiiu5L6Sq1K4wSaOrdUcIhEdsaC5CQKI6h', 4, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVlk0V3l1UzJnV3RvNWs4SElqbnE4c3JqREc3TDhOcGJxMjVGS1Y2byI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvd2hhdHNhcHAtc3RhdHVzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDt9', 1745523819);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `msgLimit` int(11) NOT NULL DEFAULT 0,
  `sendedMsg` int(11) NOT NULL DEFAULT 0,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `rightNumber` tinyint(1) NOT NULL DEFAULT 0,
  `lastMessage` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `number`, `msgLimit`, `sendedMsg`, `role`, `enabled`, `rightNumber`, `lastMessage`) VALUES
(2, 'Jonas', 'jaldesigner@gmail.com', NULL, '$2y$12$Fum5RRZ21P5OHn0prhBiWuUPvX7BfbpTXazjXZduuwqAVi0cF.C3S', NULL, '2025-04-19 12:47:20', '2025-04-24 16:28:47', '21966950079', 1, 0, 'nu', 0, 0, NULL),
(4, 'Jal', 'adm@adm.com', NULL, '$2y$12$MmiX6G2mu4tU4vor9rabZeRPDVPyiJmusC8iQQCG1t24QJ55j7iAS', NULL, '2025-04-19 18:48:59', '2025-04-24 22:35:59', '21966950079', 40, 1, 'admin', 1, 0, '2025-04-24 22:35:58');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `historic`
--
ALTER TABLE `historic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `historic_user_id_foreign` (`user_id`);

--
-- Índices de tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices de tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`),
  ADD KEY `sessions_user_id_index` (`user_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_new_email_unique` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `historic`
--
ALTER TABLE `historic`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `historic`
--
ALTER TABLE `historic`
  ADD CONSTRAINT `historic_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
