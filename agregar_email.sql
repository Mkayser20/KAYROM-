-- Ejecutar esto en phpMyAdmin si la tabla usuario no tiene columna email
ALTER TABLE `usuario`
ADD COLUMN `email` VARCHAR(150) NULL AFTER `apellido_usuario`;

-- Crear índice único para evitar emails duplicados
ALTER TABLE `usuario`
ADD UNIQUE INDEX `idx_email` (`email`);
