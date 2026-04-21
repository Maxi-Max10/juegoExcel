-- Migración: Índices de rendimiento
-- Ejecutar una sola vez para acelerar las consultas más frecuentes

-- user_level_status: acelera fetch de todos los niveles completados por usuario
-- (user_id ya tiene índice implícito por UNIQUE KEY uniq_user_level, pero lo hacemos explícito)
ALTER TABLE user_level_status
    ADD INDEX IF NOT EXISTS idx_uls_user_completed (user_id, completed_at);

-- progress: acelera ORDER BY puntos en leaderboard
ALTER TABLE progress
    ADD INDEX IF NOT EXISTS idx_progress_puntos (puntos DESC);

-- users: acelera búsqueda de usuarios online por last_seen
ALTER TABLE users
    ADD INDEX IF NOT EXISTS idx_users_last_seen (last_seen DESC);

-- duels: acelera búsqueda de duelos pendientes por challenged_id + status
ALTER TABLE duels
    ADD INDEX IF NOT EXISTS idx_duels_challenged_status (challenged_id, status);

-- duels: acelera búsqueda de duelos del challenger
ALTER TABLE duels
    ADD INDEX IF NOT EXISTS idx_duels_challenger_status (challenger_id, status);
