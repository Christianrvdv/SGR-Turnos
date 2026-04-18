<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418145633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cliente ADD numero_tarjeta VARCHAR(20) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_cliente_numero_tarjeta ON cliente (numero_tarjeta)');
        $this->addSql('ALTER TABLE configuracion_diaria ADD version INT DEFAULT 1 NOT NULL, CHANGE porcentaje_reserva porcentaje_reserva NUMERIC(5, 2) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uk_turno_configuracion_cliente ON turno (configuracion_diaria_id, cliente_id)');
        $this->addSql('ALTER TABLE turno RENAME INDEX uk_turno_fecha_numero TO uk_turno_configuracion_numero');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_cliente_numero_tarjeta ON cliente');
        $this->addSql('ALTER TABLE cliente DROP numero_tarjeta');
        $this->addSql('ALTER TABLE configuracion_diaria DROP version, CHANGE porcentaje_reserva porcentaje_reserva NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('DROP INDEX uk_turno_configuracion_cliente ON turno');
        $this->addSql('ALTER TABLE turno RENAME INDEX uk_turno_configuracion_numero TO uk_turno_fecha_numero');
    }
}
