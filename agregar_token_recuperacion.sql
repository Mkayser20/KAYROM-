-- ============================================================
-- KAYROM — Migración: recuperación de contraseña
-- Ejecutar UNA sola vez contra la base de datos soft_kayrom
-- ============================================================

ALTER TABLE usuario
    ADD COLUMN IF NOT EXISTS token_recuperacion VARCHAR(64)  NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS token_expiracion   DATETIME     NULL DEFAULT NULL;
