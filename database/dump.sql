-- MySQL dump gerado do SQLite
-- Data: 2025-08-18 14:26:40

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE "migrations" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "migration" varchar not null, "batch" INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2025_06_26_175451_create_inscricaos_table', '2'),
('5', '2025_06_26_191847_add_etiqueta_to_inscricaos_table', '3'),
('6', '2025_06_26_192640_add_user_types_and_audit_to_users_table', '4'),
('7', '2025_06_26_192646_create_status_histories_table', '4'),
('8', '2025_06_26_193502_add_lead_lock_to_inscricaos_table', '5'),
('9', '2025_06_26_203231_create_system_settings_table', '6'),
('10', '2025_06_26_204048_add_kanban_fields_to_inscricaos_table', '7'),
('11', '2025_06_27_120000_add_photos_to_inscricaos_table', '8');

CREATE TABLE "users" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "tipo_usuario" varchar check ("tipo_usuario" in ('admin', 'vendedor', 'colaborador', 'midia')) not null default 'colaborador', "ativo" tinyint(1) not null default '1', "ultimo_acesso" datetime, "criado_por" varchar) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `tipo_usuario`, `ativo`, `ultimo_acesso`, `criado_por`) VALUES
('1', 'Administrador', 'admin@ensinocerto.com', NULL, '$2y$12$UW2eB/JoMTXLtt/.4db1SOB0dwNYv3zCxQyF.2OcvGDW6XoxgX9My', NULL, '2025-06-26 19:31:10', '2025-06-26 20:26:23', 'admin', '1', '2025-06-26 20:26:23', 'sistema');

CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "sessions" ("id" varchar not null, "user_id" INT, "ip_address" varchar, "user_agent" TEXT, "payload" TEXT not null, "last_activity" INT not null, primary key ("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('fu8QNlx0e9C0neomCHSTK4j5PO4K1Vkf83UxsyF3', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiN0VVVGVxUDdlNFNOTzBjUVpzVlJWenpzaGkwRkRIQlNEQmR6SEhuMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjE1OiJhZG1pbl9sb2dnZWRfaW4iO2I6MTtzOjExOiJhZG1pbl9lbWFpbCI7czoyMToiYWRtaW5AZW5zaW5vY2VydG8uY29tIjtzOjEwOiJhZG1pbl9uYW1lIjtzOjEzOiJBZG1pbmlzdHJhZG9yIjtzOjEwOiJhZG1pbl90aXBvIjtzOjU6ImFkbWluIjtzOjg6ImFkbWluX2lkIjtpOjE7fQ==', '1750972372'),
('XClaNFEE4wHF3csuVZ33ZiMvNqzccoY6UI24mIDE', NULL, '127.0.0.1', 'curl/8.5.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNjFqcFJ2dlNaVmlhZ001Rk9wSEtZSjNFRFpKVzlMZGZwRzlWTlkwTyI7czoxMDoidmlzaXRvcl9pZCI7czozMToidmlzaXRvcl82ODY2ZWJmNTU3ZDRhMS4wNDk1ODMxNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1751575541'),
('9fYCnWncVylRxHamb5OooLZKJiRbf0OtXYPRPpUI', NULL, '127.0.0.1', 'curl/8.5.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoic3QxemxycWtHVzhaaXNoWEtTaGVoZ1JYS1BRRUVXNUZXSmRSWlZKdyI7czoxMDoidmlzaXRvcl9pZCI7czozMToidmlzaXRvcl82ODY2ZWMxOTRkYjFlOS40NDAxMjA0MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8wLjAuMC4wOjgwMDIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', '1751575577'),
('CEAHwQ9SrtWKLBq6hwv1JJ7qhXNRjaOyto0fHD3u', NULL, '127.0.0.1', 'curl/8.5.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib25EQm5XYjJFVlhDdEJYbUN1Q3ZTVTFYWnYzeVFMeEJ2U2I2OERvaCI7czoxMDoidmlzaXRvcl9pZCI7czozMToidmlzaXRvcl82ODY2ZWMzNmIzNDc4MC44Mzg5NTM0NyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8wLjAuMC4wOjgwMDIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', '1751575606'),
('OM7u80RdwtB8mfICdRYBdCVrwzvxpKGlwHDmC2CE', NULL, '127.0.0.1', 'curl/8.5.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoielozRUczbzU0MXYyREpseFg0NFB1Tm5IWGl0ak1qTUFUR25DN3U3SiI7czoxMDoidmlzaXRvcl9pZCI7czozMToidmlzaXRvcl82ODY2ZWU0NjM3Y2MyMi4xNDk5Njg3NCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1751576134'),
('tcUw9y6KAT8wsU56bo0Qr9NDu3Zfuv9TCJuk9VCS', NULL, '127.0.0.1', 'curl/8.5.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVDRGZm1ncTQzYTNGQjB5QUczMXpHVm9DMGRraXVvVW5hV1k4WjZOQyI7czoxMDoidmlzaXRvcl9pZCI7czozMToidmlzaXRvcl82ODY2ZWU1MjQ4M2MxNi44MzgwNjI4MiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1751576146'),
('kHOIUC0GPmsyRe70LNpbLp13a6YjV7sFHIRf2BgS', '1', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:139.0) Gecko/20100101 Firefox/139.0', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6Im5idGZLejhmNlM0djg5UXM0S1JLd2NDa3BQYjBuNFBNTTRiWjJlRmwiO3M6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNToiYWRtaW5fbG9nZ2VkX2luIjtiOjE7czo4OiJhZG1pbl9pZCI7aToxO3M6MTA6ImFkbWluX25hbWUiO3M6MTM6IkFkbWluaXN0cmFkb3IiO3M6MTE6ImFkbWluX2VtYWlsIjtzOjIxOiJhZG1pbkBlbnNpbm9jZXJ0by5jb20iO3M6MTA6ImFkbWluX3RpcG8iO3M6NToiYWRtaW4iO30=', '1751590017');

CREATE TABLE "cache" ("key" varchar not null, "value" TEXT not null, "expiration" INT not null, primary key ("key")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" INT not null, primary key ("key")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "jobs" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "queue" varchar not null, "payload" TEXT not null, "attempts" INT not null, "reserved_at" INT, "available_at" INT not null, "created_at" INT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" INT not null, "pending_jobs" INT not null, "failed_jobs" INT not null, "failed_job_ids" TEXT not null, "options" TEXT, "cancelled_at" INT, "created_at" INT not null, "finished_at" INT, primary key ("id")) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "failed_jobs" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "uuid" varchar not null, "connection" TEXT not null, "queue" TEXT not null, "payload" TEXT not null, "exception" TEXT not null, "failed_at" datetime not null default CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE "status_histories" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "inscricao_id" INT not null, "status_anterior" varchar not null, "status_novo" varchar not null, "alterado_por" varchar not null, "tipo_usuario" varchar not null, "observacoes" TEXT, "data_alteracao" datetime not null, "created_at" datetime, "updated_at" datetime, foreign key("inscricao_id") references "inscricaos"("id") on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `status_histories` (`id`, `inscricao_id`, `status_anterior`, `status_novo`, `alterado_por`, `tipo_usuario`, `observacoes`, `data_alteracao`, `created_at`, `updated_at`) VALUES
('1', '2', 'pendente', 'contatado', 'Administrador', 'admin', NULL, '2025-06-26 19:33:18', '2025-06-26 19:33:18', '2025-06-26 19:33:18'),
('2', '1', 'pendente', 'contatado', 'Administrador', 'admin', NULL, '2025-06-26 19:33:23', '2025-06-26 19:33:23', '2025-06-26 19:33:23'),
('3', '1', 'contatado', 'pendente', 'Administrador', 'admin', NULL, '2025-06-26 19:33:31', '2025-06-26 19:33:31', '2025-06-26 19:33:31'),
('4', '2', 'contatado', 'pendente', 'Administrador', 'admin', NULL, '2025-06-26 19:33:58', '2025-06-26 19:33:58', '2025-06-26 19:33:58'),
('5', '2', 'pendente', 'contatado', 'Administrador', 'admin', NULL, '2025-06-26 20:15:47', '2025-06-26 20:15:47', '2025-06-26 20:15:47'),
('6', '2', 'contatado', 'pendente', 'Administrador', 'admin', NULL, '2025-06-26 20:15:56', '2025-06-26 20:15:56', '2025-06-26 20:15:56'),
('7', '1', 'pendente', 'contatado', 'Administrador', 'admin', 'Movido via Kanban', '2025-06-26 20:45:43', '2025-06-26 20:45:43', '2025-06-26 20:45:43'),
('8', '1', 'contatado', 'pendente', 'Administrador', 'admin', 'Movido via Kanban', '2025-06-26 20:45:43', '2025-06-26 20:45:43', '2025-06-26 20:45:43'),
('9', '1', 'pendente', 'interessado', 'Administrador', 'admin', 'Movido via Kanban', '2025-06-26 20:46:59', '2025-06-26 20:46:59', '2025-06-26 20:46:59'),
('10', '1', 'interessado', 'nao_interessado', 'Administrador', 'admin', 'Movido via Kanban', '2025-06-26 20:48:16', '2025-06-26 20:48:16', '2025-06-26 20:48:16'),
('11', '8', 'pendente', 'matriculado', 'Administrador', 'admin', 'Movido via Kanban', '2025-06-26 20:55:39', '2025-06-26 20:55:39', '2025-06-26 20:55:39');

CREATE TABLE "inscricaos" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "nome" varchar not null, "email" varchar not null, "telefone" varchar not null, "curso" varchar not null, "termos" tinyint(1) not null default ('0'), "ip_address" varchar, "created_at" datetime, "updated_at" datetime, "etiqueta" varchar not null default ('pendente'), "locked_by" INT, "locked_at" datetime, "notas" TEXT, "todolist" TEXT, "prioridade" varchar check ("prioridade" in ('baixa', 'media', 'alta', 'urgente')) not null default 'media', "kanban_order" INT not null default '0', "ultimo_contato" datetime, "proximo_followup" datetime, "photos" TEXT, foreign key("locked_by") references "users"("id") on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inscricaos` (`id`, `nome`, `email`, `telefone`, `curso`, `termos`, `ip_address`, `created_at`, `updated_at`, `etiqueta`, `locked_by`, `locked_at`, `notas`, `todolist`, `prioridade`, `kanban_order`, `ultimo_contato`, `proximo_followup`, `photos`) VALUES
('1', 'Douglas Rodrigues', 'douglaseps@gmail.com', '11 99295 0897', 'excel', '1', '127.0.0.1', '2025-06-26 18:16:49', '2025-06-26 20:57:34', 'nao_interessado', '1', '2025-06-26 20:48:16', NULL, NULL, 'media', '3', NULL, NULL, NULL),
('2', 'Douglas Robert', 'admin@nicedesigns.com.br', '11 99295 0897', 'ingles', '1', '127.0.0.1', '2025-06-26 19:12:22', '2025-06-26 21:11:12', 'pendente', NULL, NULL, NULL, NULL, 'media', '0', '2025-06-26 21:11:12', NULL, NULL),
('3', 'Maria Silva', 'maria.silva@email.com', '(11) 99999-1001', 'excel', '1', '127.0.0.1', '2025-06-19 20:46:34', '2025-06-26 20:46:34', 'pendente', NULL, NULL, '[26/06/2025 20:46 - Admin]
Lead interessante, demonstrou muito interesse no curso de Excel.

Precisa entrar em contato urgente.', '[{\"id\":\"685db1aa70dd2\",\"text\":\"Ligar para apresentar o curso\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.462295Z\"},{\"id\":\"685db1aa70f65\",\"text\":\"Enviar material informativo\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.462704Z\"},{\"id\":\"685db1aa70f99\",\"text\":\"Verificar disponibilidade financeira\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.462751Z\"}]', 'alta', '1', NULL, '2025-06-27 20:46:34', NULL),
('4', 'João Santos', 'joao.santos@email.com', '(11) 99999-1002', 'ingles', '1', '127.0.0.1', '2025-06-23 20:46:34', '2025-06-26 21:04:09', 'contatado', NULL, NULL, '[26/06/2025 18:46 - Admin]
Primeiro contato realizado. Cliente interessado mas precisa consultar a esposa.

[25/06/2025 20:46 - Admin]
Enviado WhatsApp com informações do curso.', '[{\"id\":\"685db1aa71060\",\"text\":\"Aguardar retorno do cliente\",\"completed\":true,\"created_at\":\"2025-06-26T20:46:34.462949Z\"},{\"id\":\"685db1aa7108c\",\"text\":\"Enviar proposta personalizada\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.462992Z\"},{\"id\":\"685db1aa710b1\",\"text\":\"Agendar reuni\\u00e3o com casal\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.463029Z\"}]', 'media', '2', '2025-06-26 18:46:34', '2025-06-28 20:46:34', NULL),
('5', 'Ana Costa', 'ana.costa@email.com', '(11) 99999-1003', 'marketing', '1', '127.0.0.1', '2025-06-23 20:46:34', '2025-06-26 20:54:25', 'interessado', NULL, NULL, '[26/06/2025 16:46 - Admin]
Cliente muito interessado! Quer começar na próxima turma.
Possui experiência prévia em redes sociais.

[24/06/2025 20:46 - Admin]
Enviou várias perguntas específicas sobre o curso.', '[{\"id\":\"685db3817c88f\",\"text\":\"Enviar cronograma detalhado\",\"completed\":true,\"created_at\":\"2025-06-26T20:54:25.510120Z\"},{\"id\":\"685db3817c905\",\"text\":\"Explicar formas de pagamento\",\"completed\":true,\"created_at\":\"2025-06-26T20:54:25.510220Z\"},{\"id\":\"685db3817c92e\",\"text\":\"Fechar matr\\u00edcula hoje\",\"completed\":false,\"created_at\":\"2025-06-26T20:54:25.510259Z\"},{\"id\":\"685db3817c952\",\"text\":\"Enviar contrato por email\",\"completed\":false,\"created_at\":\"2025-06-26T20:54:25.510294Z\"},{\"id\":\"685db3817c974\",\"text\":\"aaaaaaa\",\"completed\":false,\"created_at\":\"2025-06-26T20:54:25.510327Z\"}]', 'urgente', '1', '2025-06-26 16:46:34', '2025-06-26 00:00:00', NULL),
('6', 'Carlos Oliveira', 'carlos.oliveira@email.com', '(11) 99999-1004', 'excel', '1', '127.0.0.1', '2025-06-26 20:46:34', '2025-06-26 20:57:34', 'nao_interessado', NULL, NULL, '[23/06/2025 20:46 - Admin]
Cliente não tem interesse no momento. Disse que talvez no próximo ano.
Manter na base para campanhas futuras.', '[{\"id\":\"685db1aa711d6\",\"text\":\"Adicionar na lista de newsletter\",\"completed\":true,\"created_at\":\"2025-06-26T20:46:34.463322Z\"},{\"id\":\"685db1aa711fa\",\"text\":\"Marcar para recontato em 6 meses\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.463358Z\"}]', 'baixa', '4', '2025-06-23 20:46:34', NULL, NULL),
('7', 'Fernanda Lima', 'fernanda.lima@email.com', '(11) 99999-1005', 'ingles', '1', '127.0.0.1', '2025-06-20 20:46:34', '2025-06-26 20:55:39', 'matriculado', NULL, NULL, '[25/06/2025 20:46 - Admin]
Matrícula confirmada! Pagamento realizado via PIX.
Cliente muito animada para começar as aulas.

[24/06/2025 20:46 - Admin]
Enviado dados para matrícula.', '[{\"id\":\"685db1aa7124b\",\"text\":\"Confirmar pagamento\",\"completed\":true,\"created_at\":\"2025-06-26T20:46:34.463439Z\"},{\"id\":\"685db1aa7126f\",\"text\":\"Enviar dados de acesso ao curso\",\"completed\":true,\"created_at\":\"2025-06-26T20:46:34.463474Z\"},{\"id\":\"685db1aa71290\",\"text\":\"Agendar primeira aula\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.463508Z\"},{\"id\":\"685db1aa712b1\",\"text\":\"Adicionar no grupo do WhatsApp\",\"completed\":false,\"created_at\":\"2025-06-26T20:46:34.463541Z\"}]', 'baixa', '2', '2025-06-26 20:51:00', NULL, NULL),
('8', 'Roberto Mendes', 'roberto.mendes@email.com', '(11) 99999-1006', 'marketing', '1', '127.0.0.1', '2025-06-23 20:46:34', '2025-06-26 20:55:46', 'matriculado', '1', '2025-06-26 20:55:39', '[26/06/2025 20:16 - Admin]
Lead recém chegado. Preencheu formulário completo.
Demonstrou interesse em horários noturnos.', '[{\"id\":\"685db3d2deadb\",\"text\":\"Fazer primeiro contato\",\"completed\":false,\"created_at\":\"2025-06-26T20:55:46.912178Z\"},{\"id\":\"685db3d2debf7\",\"text\":\"Verificar disponibilidade de hor\\u00e1rios\",\"completed\":false,\"created_at\":\"2025-06-26T20:55:46.912383Z\"}]', 'baixa', '1', NULL, '2025-06-27 00:00:00', NULL),
('9', 'Maria Silva', 'maria.silva@email.com', '(11) 99999-1001', 'excel', '1', '127.0.0.1', '2025-06-22 20:53:14', '2025-06-26 20:53:14', 'pendente', NULL, NULL, '[26/06/2025 20:53 - Admin]
Lead interessante, demonstrou muito interesse no curso de Excel.

Precisa entrar em contato urgente.', '[{\"id\":\"685db33a0aa12\",\"text\":\"Ligar para apresentar o curso\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.043544Z\"},{\"id\":\"685db33a0d116\",\"text\":\"Enviar material informativo\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.053562Z\"},{\"id\":\"685db33a0d17d\",\"text\":\"Verificar disponibilidade financeira\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.053635Z\"}]', 'alta', '1', NULL, '2025-06-27 20:53:14', NULL),
('10', 'João Santos', 'joao.santos@email.com', '(11) 99999-1002', 'ingles', '1', '127.0.0.1', '2025-06-26 20:53:14', '2025-06-26 21:04:09', 'contatado', NULL, NULL, '[26/06/2025 18:53 - Admin]
Primeiro contato realizado. Cliente interessado mas precisa consultar a esposa.

[25/06/2025 20:53 - Admin]
Enviado WhatsApp com informações do curso.', '[{\"id\":\"685db52019316\",\"text\":\"Aguardar retorno do cliente\",\"completed\":true,\"created_at\":\"2025-06-26T21:01:20.103203Z\"},{\"id\":\"685db5201936a\",\"text\":\"Enviar proposta personalizada\",\"completed\":true,\"created_at\":\"2025-06-26T21:01:20.103280Z\"},{\"id\":\"685db52019394\",\"text\":\"Agendar reuni\\u00e3o com casal\",\"completed\":false,\"created_at\":\"2025-06-26T21:01:20.103321Z\"}]', 'media', '0', '2025-06-26 18:53:14', '2025-06-28 00:00:00', '[]'),
('11', 'Ana Costa', 'ana.costa@email.com', '(11) 99999-1003', 'marketing', '1', '127.0.0.1', '2025-06-21 20:53:14', '2025-06-26 20:53:36', 'interessado', NULL, NULL, '[26/06/2025 16:53 - Admin]
Cliente muito interessado! Quer começar na próxima turma.
Possui experiência prévia em redes sociais.

[24/06/2025 20:53 - Admin]
Enviou várias perguntas específicas sobre o curso.', '[{\"id\":\"685db33a0d374\",\"text\":\"Enviar cronograma detalhado\",\"completed\":true,\"created_at\":\"2025-06-26T20:53:14.054136Z\"},{\"id\":\"685db33a0d39a\",\"text\":\"Explicar formas de pagamento\",\"completed\":true,\"created_at\":\"2025-06-26T20:53:14.054174Z\"},{\"id\":\"685db33a0d3be\",\"text\":\"Fechar matr\\u00edcula hoje\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054211Z\"},{\"id\":\"685db33a0d3e3\",\"text\":\"Enviar contrato por email\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054247Z\"}]', 'urgente', '3', '2025-06-26 16:53:14', '2025-06-26 22:53:14', NULL),
('12', 'Carlos Oliveira', 'carlos.oliveira@email.com', '(11) 99999-1004', 'excel', '1', '127.0.0.1', '2025-06-20 20:53:14', '2025-06-26 20:53:14', 'nao_interessado', NULL, NULL, '[23/06/2025 20:53 - Admin]
Cliente não tem interesse no momento. Disse que talvez no próximo ano.
Manter na base para campanhas futuras.', '[{\"id\":\"685db33a0d439\",\"text\":\"Adicionar na lista de newsletter\",\"completed\":true,\"created_at\":\"2025-06-26T20:53:14.054332Z\"},{\"id\":\"685db33a0d45e\",\"text\":\"Marcar para recontato em 6 meses\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054370Z\"}]', 'baixa', '1', '2025-06-23 20:53:14', NULL, NULL),
('13', 'Fernanda Lima', 'fernanda.lima@email.com', '(11) 99999-1005', 'ingles', '1', '127.0.0.1', '2025-06-21 20:53:14', '2025-06-26 20:55:39', 'matriculado', NULL, NULL, '[25/06/2025 20:53 - Admin]
Matrícula confirmada! Pagamento realizado via PIX.
Cliente muito animada para começar as aulas.

[24/06/2025 20:53 - Admin]
Enviado dados para matrícula.', '[{\"id\":\"685db33a0d4b2\",\"text\":\"Confirmar pagamento\",\"completed\":true,\"created_at\":\"2025-06-26T20:53:14.054454Z\"},{\"id\":\"685db33a0d4d7\",\"text\":\"Enviar dados de acesso ao curso\",\"completed\":true,\"created_at\":\"2025-06-26T20:53:14.054491Z\"},{\"id\":\"685db33a0d4f9\",\"text\":\"Agendar primeira aula\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054525Z\"},{\"id\":\"685db33a0d51d\",\"text\":\"Adicionar no grupo do WhatsApp\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054560Z\"}]', 'baixa', '2', '2025-06-25 20:53:14', NULL, NULL),
('14', 'Roberto Mendes', 'roberto.mendes@email.com', '(11) 99999-1006', 'marketing', '1', '127.0.0.1', '2025-06-23 20:53:14', '2025-06-26 20:53:14', 'pendente', NULL, NULL, '[26/06/2025 20:23 - Admin]
Lead recém chegado. Preencheu formulário completo.
Demonstrou interesse em horários noturnos.', '[{\"id\":\"685db33a0d5ac\",\"text\":\"Fazer primeiro contato\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054704Z\"},{\"id\":\"685db33a0d5d4\",\"text\":\"Verificar disponibilidade de hor\\u00e1rios\",\"completed\":false,\"created_at\":\"2025-06-26T20:53:14.054745Z\"}]', 'media', '2', NULL, '2025-06-27 00:53:14', NULL);

CREATE TABLE "system_settings" ("id" INT AUTO_INCREMENT PRIMARY KEY not null, "key" varchar not null, "value" TEXT not null, "type" varchar not null default 'string', "category" varchar not null default 'general', "description" varchar, "created_at" datetime, "updated_at" datetime) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`id`, `key`, `value`, `type`, `category`, `description`, `created_at`, `updated_at`) VALUES
('1', 'lead_cooldown_minutes', '2', 'integer', 'leads', 'Tempo em minutos que um usuário deve aguardar após pegar um lead para pegar outro', '2025-06-26 20:36:31', '2025-06-26 20:36:31'),
('2', 'auto_unlock_hours', '24', 'integer', 'leads', 'Tempo em horas para destravar automaticamente leads inativos', '2025-06-26 20:36:31', '2025-06-26 20:36:31'),
('3', 'max_leads_per_user', '10', 'integer', 'leads', 'Número máximo de leads que um usuário pode ter travados simultaneamente', '2025-06-26 20:36:31', '2025-06-26 20:36:31'),
('4', 'enable_lead_notifications', 'true', 'boolean', 'notifications', 'Ativar notificações de novos leads', '2025-06-26 20:36:31', '2025-06-26 20:36:31');

SET FOREIGN_KEY_CHECKS=1;
