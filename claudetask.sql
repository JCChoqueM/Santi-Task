CREATE DATABASE IF NOT EXISTS claudetask
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE claudetask;

CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  rol ENUM('tutor','tutelado') NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  alias VARCHAR(60) DEFAULT NULL,
  curso VARCHAR(60) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  token VARCHAR(255) DEFAULT NULL,
  token_expira INT UNSIGNED DEFAULT NULL,
  confirmado TINYINT(1) NOT NULL DEFAULT 0,
  creado_por INT UNSIGNED DEFAULT NULL,
  UNIQUE KEY uq_email (email),
  UNIQUE KEY uq_alias (alias),
  CONSTRAINT fk_usuario_creador FOREIGN KEY (creado_por)
    REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE vinculos (
  tutor_id INT UNSIGNED NOT NULL,
  tutelado_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (tutor_id, tutelado_id),
  FOREIGN KEY (tutor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (tutelado_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tareas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  url VARCHAR(255) NOT NULL,
  tutelado_id INT UNSIGNED NOT NULL,
  creada_por INT UNSIGNED NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT,
  fecha_limite DATE DEFAULT NULL,
  UNIQUE KEY uq_url (url),
  KEY idx_tutelado (tutelado_id),
  FOREIGN KEY (tutelado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (creada_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE partes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tarea_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  estado ENUM('pendiente','hecha','aprobada') NOT NULL DEFAULT 'pendiente',
  comentario VARCHAR(500) NOT NULL DEFAULT '',
  devuelta TINYINT(1) NOT NULL DEFAULT 0,
  revisada_por INT UNSIGNED DEFAULT NULL,
  KEY idx_tarea (tarea_id),
  FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
  FOREIGN KEY (revisada_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE historial (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tarea_id INT UNSIGNED NOT NULL,
  parte VARCHAR(255) NOT NULL,
  accion ENUM('hecha','aprobar','devolver','reabrir') NOT NULL,
  por INT UNSIGNED NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tarea (tarea_id),
  FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
  FOREIGN KEY (por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE intentos_login (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(255) NOT NULL,
  intentos INT NOT NULL DEFAULT 0,
  bloqueado_hasta INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_clave (clave)
) ENGINE=InnoDB;
