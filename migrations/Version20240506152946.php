<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240506152946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archivo (id INT AUTO_INCREMENT NOT NULL, documento_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, tipo VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, fecha_creado DATE NOT NULL, INDEX IDX_3529B48245C0CF75 (documento_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pasajero_documento (id INT AUTO_INCREMENT NOT NULL, pasajero_id INT DEFAULT NULL, documento_id INT DEFAULT NULL, fecha_creado DATETIME NOT NULL, INDEX IDX_997F86E704716FE (pasajero_id), INDEX IDX_997F86E45C0CF75 (documento_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archivo ADD CONSTRAINT FK_3529B48245C0CF75 FOREIGN KEY (documento_id) REFERENCES documento (id)');
        $this->addSql('ALTER TABLE pasajero_documento ADD CONSTRAINT FK_997F86E704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE pasajero_documento ADD CONSTRAINT FK_997F86E45C0CF75 FOREIGN KEY (documento_id) REFERENCES documento (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE archivo');
        $this->addSql('DROP TABLE pasajero_documento');
    }
}
