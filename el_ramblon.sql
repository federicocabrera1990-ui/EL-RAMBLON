DROP DATABASE IF EXISTS el_ramblon;
CREATE DATABASE el_ramblon
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE el_ramblon;

CREATE TABLE usuario (
  id_usuario  INT          NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(50)  NOT NULL,
  apellido    VARCHAR(50)  NOT NULL,
  telefono    VARCHAR(20)  NOT NULL,
  email       VARCHAR(100) NOT NULL,
  contrasena  VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB;

CREATE TABLE cliente (
  id_cliente INT NOT NULL,
  PRIMARY KEY (id_cliente),
  CONSTRAINT fk_cliente_usuario
    FOREIGN KEY (id_cliente) REFERENCES usuario (id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE personal (
  id_personal        INT           NOT NULL,
  rol                VARCHAR(20)   NOT NULL,
  activo             BOOLEAN       NOT NULL DEFAULT TRUE,
  fecha_contratacion DATE          NULL,
  sueldo             DECIMAL(10,2) NULL,
  PRIMARY KEY (id_personal),
  CONSTRAINT fk_personal_usuario
    FOREIGN KEY (id_personal) REFERENCES usuario (id_usuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_personal_rol
    CHECK (rol IN ('admin','mozo','cocinero'))
) ENGINE=InnoDB;

CREATE TABLE sector (
  id_sector   INT          NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(50)  NOT NULL,
  descripcion VARCHAR(255) NULL,
  PRIMARY KEY (id_sector)
) ENGINE=InnoDB;

CREATE TABLE mesa (
  id_mesa    INT         NOT NULL AUTO_INCREMENT,
  numero     VARCHAR(10) NOT NULL,
  capacidad  INT         NOT NULL,
  estado     VARCHAR(20) NOT NULL DEFAULT 'DISPONIBLE',
  id_sector  INT         NOT NULL,
  PRIMARY KEY (id_mesa),
  CONSTRAINT chk_mesa_estado
    CHECK (estado IN ('DISPONIBLE','OCUPADA','RESERVADA')),
  CONSTRAINT fk_mesa_sector
    FOREIGN KEY (id_sector) REFERENCES sector (id_sector)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reserva (
  id_reserva      INT          NOT NULL AUTO_INCREMENT,
  fecha           DATE         NOT NULL,
  hora            TIME         NOT NULL,
  cant_personas   INT          NOT NULL,
  comentarios     VARCHAR(255) NULL,
  nombre_invitado VARCHAR(100) NULL,
  tel_invitado    VARCHAR(20)  NULL,
  estado          VARCHAR(20)  NOT NULL DEFAULT 'PENDIENTE',
  id_cliente      INT          NULL,
  id_mesa         INT          NULL,
  PRIMARY KEY (id_reserva),
  CONSTRAINT chk_reserva_estado
    CHECK (estado IN ('PENDIENTE','CONFIRMADA','CANCELADA')),
  CONSTRAINT chk_reserva_cliente_o_invitado
    CHECK (id_cliente IS NOT NULL
           OR (nombre_invitado IS NOT NULL AND tel_invitado IS NOT NULL)),
  CONSTRAINT fk_reserva_cliente
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_reserva_mesa
    FOREIGN KEY (id_mesa) REFERENCES mesa (id_mesa)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plan_membresia (
  id_plan       INT           NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(50)   NOT NULL,
  precio        DECIMAL(10,2) NOT NULL,
  duracion_dias INT           NOT NULL,
  PRIMARY KEY (id_plan)
) ENGINE=InnoDB;

CREATE TABLE membresia (
  id_membresia INT         NOT NULL AUTO_INCREMENT,
  estado       VARCHAR(20) NOT NULL DEFAULT 'activa',
  fecha_inicio DATE        NOT NULL,
  fecha_fin    DATE        NOT NULL,
  id_cliente   INT         NOT NULL,
  id_plan      INT         NOT NULL,
  PRIMARY KEY (id_membresia),
  CONSTRAINT chk_membresia_estado
    CHECK (estado IN ('activa','vencida','cancelada')),
  CONSTRAINT fk_membresia_cliente
    FOREIGN KEY (id_cliente) REFERENCES cliente (id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_membresia_plan
    FOREIGN KEY (id_plan) REFERENCES plan_membresia (id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
