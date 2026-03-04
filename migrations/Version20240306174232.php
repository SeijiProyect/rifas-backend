<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240306174232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ciudad CHANGE descripcion descripcion LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pasajero_servicio CHANGE booking booking VARCHAR(255) DEFAULT NULL, CHANGE estado estado VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE piques CHANGE descripcion descripcion LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE punto_interes CHANGE maps_me maps_me LONGTEXT DEFAULT NULL, CHANGE google_maps google_maps LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A97138D8A');
        $this->addSql('DROP INDEX IDX_CB86F22A97138D8A ON servicio');
        $this->addSql('ALTER TABLE servicio CHANGE subgrupo_id grupo_id INT NOT NULL');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A9C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id)');
        $this->addSql('CREATE INDEX IDX_CB86F22A9C833003 ON servicio (grupo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pasajero_servicio CHANGE booking booking VARCHAR(255) NOT NULL, CHANGE estado estado VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE punto_interes CHANGE maps_me maps_me VARCHAR(255) DEFAULT NULL, CHANGE google_maps google_maps VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A9C833003');
        $this->addSql('DROP INDEX IDX_CB86F22A9C833003 ON servicio');
        $this->addSql('ALTER TABLE servicio CHANGE grupo_id subgrupo_id INT NOT NULL');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A97138D8A FOREIGN KEY (subgrupo_id) REFERENCES subgrupo (id)');
        $this->addSql('CREATE INDEX IDX_CB86F22A97138D8A ON servicio (subgrupo_id)');
        $this->addSql('ALTER TABLE piques CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ciudad CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL');
    }
}
