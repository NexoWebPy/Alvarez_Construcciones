-- Ejecutar este SQL en phpMyAdmin o consola MySQL de constructora_db
CREATE TABLE IF NOT EXISTS mensajes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    remitente_id INT(11) NOT NULL,
    destinatario_id INT(11) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    respuesta TEXT DEFAULT NULL,
    respondido_por INT(11) DEFAULT NULL,
    respondido_en TIMESTAMP NULL DEFAULT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_destinatario_leido (destinatario_id, leido),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
