<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418005436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auditoria (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, tipo_accion VARCHAR(50) NOT NULL, entidad_afectada VARCHAR(50) DEFAULT NULL, entidad_id INT UNSIGNED DEFAULT NULL, datos_antes JSON DEFAULT NULL, datos_despues JSON DEFAULT NULL, ip_origen VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, creado_en DATETIME NOT NULL, usuario_id INT UNSIGNED DEFAULT NULL, INDEX IDX_AF4BB49DDB38439E (usuario_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cliente (id INT UNSIGNED AUTO_INCREMENT NOT NULL, numero_identidad VARCHAR(20) NOT NULL, nombre_completo VARCHAR(150) DEFAULT NULL, numero_telefono VARCHAR(20) DEFAULT NULL, creado_en DATETIME NOT NULL, actualizado_en DATETIME DEFAULT NULL, UNIQUE INDEX uk_cliente_numero_identidad (numero_identidad), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE configuracion_diaria (id INT UNSIGNED AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, monto_cargado NUMERIC(12, 2) NOT NULL, limite_por_persona NUMERIC(10, 2) NOT NULL, porcentaje_reserva NUMERIC(5, 2) DEFAULT 0 NOT NULL, tickets_generados INT UNSIGNED NOT NULL, tickets_restantes INT UNSIGNED NOT NULL, estado VARCHAR(20) NOT NULL, creado_en DATETIME NOT NULL, actualizado_en DATETIME DEFAULT NULL, servicio_id INT UNSIGNED NOT NULL, creado_por INT UNSIGNED NOT NULL, INDEX IDX_9D0AA27E71CAA3E7 (servicio_id), INDEX IDX_9D0AA27EA13368D4 (creado_por), UNIQUE INDEX uk_configuracion_diaria_servicio_fecha (servicio_id, fecha), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_tokens (refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rol (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, creado_en DATETIME NOT NULL, UNIQUE INDEX uk_rol_nombre (nombre), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE servicio (id INT UNSIGNED AUTO_INCREMENT NOT NULL, codigo VARCHAR(20) NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, permite_reserva_futura TINYINT DEFAULT 1 NOT NULL, requiere_control_frecuencia TINYINT DEFAULT 1 NOT NULL, dias_bloqueo SMALLINT UNSIGNED DEFAULT NULL, activo TINYINT DEFAULT 1 NOT NULL, creado_en DATETIME NOT NULL, actualizado_en DATETIME DEFAULT NULL, UNIQUE INDEX uk_servicio_codigo (codigo), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE turno (id INT UNSIGNED AUTO_INCREMENT NOT NULL, numero_turno VARCHAR(10) NOT NULL, monto_permitido NUMERIC(10, 2) NOT NULL, estado VARCHAR(20) NOT NULL, fecha_reserva DATETIME NOT NULL, fecha_uso DATETIME DEFAULT NULL, ip_registro VARCHAR(45) DEFAULT NULL, observaciones VARCHAR(255) DEFAULT NULL, configuracion_diaria_id INT UNSIGNED NOT NULL, servicio_id INT UNSIGNED NOT NULL, cliente_id INT UNSIGNED NOT NULL, marcado_por INT UNSIGNED DEFAULT NULL, INDEX IDX_E797676244CAB782 (configuracion_diaria_id), INDEX IDX_E797676271CAA3E7 (servicio_id), INDEX IDX_E7976762DE734E51 (cliente_id), INDEX IDX_E797676270E4B3CB (marcado_por), INDEX idx_turno_cliente_estado_fecha (cliente_id, estado, fecha_uso), UNIQUE INDEX uk_turno_fecha_numero (configuracion_diaria_id, numero_turno), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE usuario (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(50) NOT NULL, email VARCHAR(180) DEFAULT NULL, contrasena_hash VARCHAR(255) NOT NULL, nombre_completo VARCHAR(100) NOT NULL, activo TINYINT DEFAULT 1 NOT NULL, ultimo_acceso DATETIME DEFAULT NULL, creado_en DATETIME NOT NULL, actualizado_en DATETIME DEFAULT NULL, rol_id INT UNSIGNED NOT NULL, INDEX IDX_2265B05D4BAB96C (rol_id), UNIQUE INDEX uk_usuario_nombre_usuario (nombre_usuario), UNIQUE INDEX uk_usuario_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE auditoria ADD CONSTRAINT FK_AF4BB49DDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE configuracion_diaria ADD CONSTRAINT FK_9D0AA27E71CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE configuracion_diaria ADD CONSTRAINT FK_9D0AA27EA13368D4 FOREIGN KEY (creado_por) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E797676244CAB782 FOREIGN KEY (configuracion_diaria_id) REFERENCES configuracion_diaria (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E797676271CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E7976762DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E797676270E4B3CB FOREIGN KEY (marcado_por) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D4BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auditoria DROP FOREIGN KEY FK_AF4BB49DDB38439E');
        $this->addSql('ALTER TABLE configuracion_diaria DROP FOREIGN KEY FK_9D0AA27E71CAA3E7');
        $this->addSql('ALTER TABLE configuracion_diaria DROP FOREIGN KEY FK_9D0AA27EA13368D4');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E797676244CAB782');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E797676271CAA3E7');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E7976762DE734E51');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E797676270E4B3CB');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05D4BAB96C');
        $this->addSql('DROP TABLE auditoria');
        $this->addSql('DROP TABLE cliente');
        $this->addSql('DROP TABLE configuracion_diaria');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE rol');
        $this->addSql('DROP TABLE servicio');
        $this->addSql('DROP TABLE turno');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
