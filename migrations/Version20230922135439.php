<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230922135439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comprador (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, celular VARCHAR(255) DEFAULT NULL, departamento VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE costo_extra (id INT AUTO_INCREMENT NOT NULL, pasajero_id INT NOT NULL, descripcion VARCHAR(255) NOT NULL, monto DOUBLE PRECISION NOT NULL, fecha DATETIME NOT NULL, INDEX IDX_68520204704716FE (pasajero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposito (id INT AUTO_INCREMENT NOT NULL, pasajero_id INT NOT NULL, monto DOUBLE PRECISION NOT NULL, tipo VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, fecha_procesado DATETIME DEFAULT NULL, csv TINYINT(1) NOT NULL, comentario LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', geopay TINYINT(1) DEFAULT NULL, INDEX IDX_509FC0D1704716FE (pasajero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documento (id INT AUTO_INCREMENT NOT NULL, tipo_documento_id INT DEFAULT NULL, pais_id INT DEFAULT NULL, persona_id INT DEFAULT NULL, numero INT NOT NULL, serie VARCHAR(50) DEFAULT NULL, fecha_expedicion DATE DEFAULT NULL, fecha_vencimiento DATE DEFAULT NULL, imagen_url VARCHAR(100) DEFAULT NULL, INDEX IDX_B6B12EC7F6939175 (tipo_documento_id), INDEX IDX_B6B12EC7C604D5C6 (pais_id), INDEX IDX_B6B12EC7F5F88DB9 (persona_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etiqueta (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etiqueta_pasajero (id INT AUTO_INCREMENT NOT NULL, etiqueta_id INT DEFAULT NULL, pasajero_id INT DEFAULT NULL, comentario VARCHAR(255) DEFAULT NULL, INDEX IDX_BE362F4BD53DA3AB (etiqueta_id), INDEX IDX_BE362F4B704716FE (pasajero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forgot_password (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(300) NOT NULL, expire DATETIME NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2AB9B566A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE foto_persona (id INT AUTO_INCREMENT NOT NULL, persona_id INT DEFAULT NULL, fecha DATE DEFAULT NULL, url VARCHAR(255) NOT NULL, nombre VARCHAR(100) NOT NULL, tipo VARCHAR(50) DEFAULT NULL, INDEX IDX_43618E61F5F88DB9 (persona_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grupo (id INT AUTO_INCREMENT NOT NULL, viaje_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8C0E9BD394E1E648 (viaje_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historial_transferencias (id INT AUTO_INCREMENT NOT NULL, talon_id INT NOT NULL, pasajero_id INT NOT NULL, fecha DATETIME NOT NULL, accion VARCHAR(255) NOT NULL, INDEX IDX_C1D030DC4654B4FD (talon_id), INDEX IDX_C1D030DC704716FE (pasajero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE itinerario (id INT AUTO_INCREMENT NOT NULL, viaje_id INT NOT NULL, grupo_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, precio DOUBLE PRECISION DEFAULT NULL, fecha_inicio DATE DEFAULT NULL, fecha_fin DATE DEFAULT NULL, principal TINYINT(1) NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_11ECF40494E1E648 (viaje_id), INDEX IDX_11ECF4049C833003 (grupo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link_pago_rifa (id INT AUTO_INCREMENT NOT NULL, pasajero_id INT NOT NULL, deposito_id INT DEFAULT NULL, comprador_nombre VARCHAR(255) NOT NULL, comprador_apellido VARCHAR(255) NOT NULL, comprador_email VARCHAR(255) NOT NULL, comprador_celular VARCHAR(255) NOT NULL, comprador_departamento VARCHAR(255) DEFAULT NULL, estado VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', encrypted_link VARCHAR(255) NOT NULL, geocom_token VARCHAR(255) DEFAULT NULL, asumir_recargo TINYINT(1) NOT NULL, INDEX IDX_6A4C3583704716FE (pasajero_id), UNIQUE INDEX UNIQ_6A4C35834140C3FC (deposito_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link_pago_rifa_seleccion (id INT AUTO_INCREMENT NOT NULL, link_pago_rifa_id INT NOT NULL, tarjeta VARCHAR(255) DEFAULT NULL, tipo_tarjeta VARCHAR(255) DEFAULT NULL, cuotas SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2E97317A5869AF8E (link_pago_rifa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link_pago_rifa_talones (id INT AUTO_INCREMENT NOT NULL, link_pago_rifa_id INT NOT NULL, talon_id INT NOT NULL, INDEX IDX_12149FA45869AF8E (link_pago_rifa_id), INDEX IDX_12149FA44654B4FD (talon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lote_rifa (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, tipo VARCHAR(255) NOT NULL, estado VARCHAR(255) NOT NULL, cantidad_sorteos SMALLINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', moneda VARCHAR(255) NOT NULL, anio VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pago_personal (id INT AUTO_INCREMENT NOT NULL, deposito_id INT NOT NULL, fecha DATETIME NOT NULL, monto DOUBLE PRECISION NOT NULL, INDEX IDX_6637FA6D4140C3FC (deposito_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pais (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, nacionalidad VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pasajero (id INT AUTO_INCREMENT NOT NULL, persona_id INT NOT NULL, itinerario_id INT NOT NULL, universidad_id INT NOT NULL, acompanante_id INT DEFAULT NULL, estado VARCHAR(255) NOT NULL, comentarios LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_17D4512BF5F88DB9 (persona_id), INDEX IDX_17D4512BB824E717 (itinerario_id), INDEX IDX_17D4512B271768CD (universidad_id), UNIQUE INDEX UNIQ_17D4512B4C735FDF (acompanante_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE persona (id INT AUTO_INCREMENT NOT NULL, nombres VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, fecha_nacimiento DATE NOT NULL, direccion VARCHAR(255) DEFAULT NULL, cedula VARCHAR(255) NOT NULL, celular VARCHAR(255) DEFAULT NULL, sexo VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', id_externo INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE talon (id INT AUTO_INCREMENT NOT NULL, comprador_id INT DEFAULT NULL, pasajero_id INT DEFAULT NULL, deposito_id INT DEFAULT NULL, solicitante_id INT DEFAULT NULL, lote_rifa_id INT DEFAULT NULL, numero SMALLINT NOT NULL, fecha_sorteo DATE NOT NULL, estado VARCHAR(255) NOT NULL, precio SMALLINT NOT NULL, fecha_registro DATETIME DEFAULT NULL, fecha_entrega DATETIME DEFAULT NULL, sorteo_numero SMALLINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', comentario VARCHAR(255) DEFAULT NULL, recaudacion SMALLINT DEFAULT NULL, valor DOUBLE PRECISION DEFAULT NULL, INDEX IDX_40482807200A5E25 (comprador_id), INDEX IDX_40482807704716FE (pasajero_id), INDEX IDX_404828074140C3FC (deposito_id), INDEX IDX_40482807C680A87 (solicitante_id), INDEX IDX_40482807A8151442 (lote_rifa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarjeta (id INT AUTO_INCREMENT NOT NULL, deposito_id INT NOT NULL, issuer VARCHAR(255) NOT NULL, moneda VARCHAR(255) NOT NULL, cuotas SMALLINT NOT NULL, fecha_transaccion DATETIME NOT NULL, codigo_autorizacion VARCHAR(255) NOT NULL, numero_tarjeta VARCHAR(255) DEFAULT NULL, acquirer VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', nombre_tarjeta VARCHAR(255) DEFAULT NULL, fecha_vencimiento VARCHAR(10) DEFAULT NULL, UNIQUE INDEX UNIQ_AE90B7864140C3FC (deposito_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_documento (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE universidad (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, persona_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F5F88DB9 (persona_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE viaje (id INT AUTO_INCREMENT NOT NULL, viaje_madre_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion VARCHAR(255) DEFAULT NULL, anio SMALLINT NOT NULL, activo TINYINT(1) NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE NOT NULL, titulo VARCHAR(255) DEFAULT NULL, subtitulo VARCHAR(255) DEFAULT NULL, destacado TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1D41ED163279E901 (viaje_madre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE viaje_madre (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, imagen VARCHAR(255) DEFAULT NULL, descripcion VARCHAR(255) DEFAULT NULL, tipo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE costo_extra ADD CONSTRAINT FK_68520204704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE deposito ADD CONSTRAINT FK_509FC0D1704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE documento ADD CONSTRAINT FK_B6B12EC7F6939175 FOREIGN KEY (tipo_documento_id) REFERENCES tipo_documento (id)');
        $this->addSql('ALTER TABLE documento ADD CONSTRAINT FK_B6B12EC7C604D5C6 FOREIGN KEY (pais_id) REFERENCES pais (id)');
        $this->addSql('ALTER TABLE documento ADD CONSTRAINT FK_B6B12EC7F5F88DB9 FOREIGN KEY (persona_id) REFERENCES persona (id)');
        $this->addSql('ALTER TABLE etiqueta_pasajero ADD CONSTRAINT FK_BE362F4BD53DA3AB FOREIGN KEY (etiqueta_id) REFERENCES etiqueta (id)');
        $this->addSql('ALTER TABLE etiqueta_pasajero ADD CONSTRAINT FK_BE362F4B704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE forgot_password ADD CONSTRAINT FK_2AB9B566A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE foto_persona ADD CONSTRAINT FK_43618E61F5F88DB9 FOREIGN KEY (persona_id) REFERENCES persona (id)');
        $this->addSql('ALTER TABLE grupo ADD CONSTRAINT FK_8C0E9BD394E1E648 FOREIGN KEY (viaje_id) REFERENCES viaje (id)');
        $this->addSql('ALTER TABLE historial_transferencias ADD CONSTRAINT FK_C1D030DC4654B4FD FOREIGN KEY (talon_id) REFERENCES talon (id)');
        $this->addSql('ALTER TABLE historial_transferencias ADD CONSTRAINT FK_C1D030DC704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE itinerario ADD CONSTRAINT FK_11ECF40494E1E648 FOREIGN KEY (viaje_id) REFERENCES viaje (id)');
        $this->addSql('ALTER TABLE itinerario ADD CONSTRAINT FK_11ECF4049C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id)');
        $this->addSql('ALTER TABLE link_pago_rifa ADD CONSTRAINT FK_6A4C3583704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE link_pago_rifa ADD CONSTRAINT FK_6A4C35834140C3FC FOREIGN KEY (deposito_id) REFERENCES deposito (id)');
        $this->addSql('ALTER TABLE link_pago_rifa_seleccion ADD CONSTRAINT FK_2E97317A5869AF8E FOREIGN KEY (link_pago_rifa_id) REFERENCES link_pago_rifa (id)');
        $this->addSql('ALTER TABLE link_pago_rifa_talones ADD CONSTRAINT FK_12149FA45869AF8E FOREIGN KEY (link_pago_rifa_id) REFERENCES link_pago_rifa (id)');
        $this->addSql('ALTER TABLE link_pago_rifa_talones ADD CONSTRAINT FK_12149FA44654B4FD FOREIGN KEY (talon_id) REFERENCES talon (id)');
        $this->addSql('ALTER TABLE pago_personal ADD CONSTRAINT FK_6637FA6D4140C3FC FOREIGN KEY (deposito_id) REFERENCES deposito (id)');
        $this->addSql('ALTER TABLE pasajero ADD CONSTRAINT FK_17D4512BF5F88DB9 FOREIGN KEY (persona_id) REFERENCES persona (id)');
        $this->addSql('ALTER TABLE pasajero ADD CONSTRAINT FK_17D4512BB824E717 FOREIGN KEY (itinerario_id) REFERENCES itinerario (id)');
        $this->addSql('ALTER TABLE pasajero ADD CONSTRAINT FK_17D4512B271768CD FOREIGN KEY (universidad_id) REFERENCES universidad (id)');
        $this->addSql('ALTER TABLE pasajero ADD CONSTRAINT FK_17D4512B4C735FDF FOREIGN KEY (acompanante_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE talon ADD CONSTRAINT FK_40482807200A5E25 FOREIGN KEY (comprador_id) REFERENCES comprador (id)');
        $this->addSql('ALTER TABLE talon ADD CONSTRAINT FK_40482807704716FE FOREIGN KEY (pasajero_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE talon ADD CONSTRAINT FK_404828074140C3FC FOREIGN KEY (deposito_id) REFERENCES deposito (id)');
        $this->addSql('ALTER TABLE talon ADD CONSTRAINT FK_40482807C680A87 FOREIGN KEY (solicitante_id) REFERENCES pasajero (id)');
        $this->addSql('ALTER TABLE talon ADD CONSTRAINT FK_40482807A8151442 FOREIGN KEY (lote_rifa_id) REFERENCES lote_rifa (id)');
        $this->addSql('ALTER TABLE tarjeta ADD CONSTRAINT FK_AE90B7864140C3FC FOREIGN KEY (deposito_id) REFERENCES deposito (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F5F88DB9 FOREIGN KEY (persona_id) REFERENCES persona (id)');
        $this->addSql('ALTER TABLE viaje ADD CONSTRAINT FK_1D41ED163279E901 FOREIGN KEY (viaje_madre_id) REFERENCES viaje_madre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE talon DROP FOREIGN KEY FK_40482807200A5E25');
        $this->addSql('ALTER TABLE link_pago_rifa DROP FOREIGN KEY FK_6A4C35834140C3FC');
        $this->addSql('ALTER TABLE pago_personal DROP FOREIGN KEY FK_6637FA6D4140C3FC');
        $this->addSql('ALTER TABLE talon DROP FOREIGN KEY FK_404828074140C3FC');
        $this->addSql('ALTER TABLE tarjeta DROP FOREIGN KEY FK_AE90B7864140C3FC');
        $this->addSql('ALTER TABLE etiqueta_pasajero DROP FOREIGN KEY FK_BE362F4BD53DA3AB');
        $this->addSql('ALTER TABLE itinerario DROP FOREIGN KEY FK_11ECF4049C833003');
        $this->addSql('ALTER TABLE pasajero DROP FOREIGN KEY FK_17D4512BB824E717');
        $this->addSql('ALTER TABLE link_pago_rifa_seleccion DROP FOREIGN KEY FK_2E97317A5869AF8E');
        $this->addSql('ALTER TABLE link_pago_rifa_talones DROP FOREIGN KEY FK_12149FA45869AF8E');
        $this->addSql('ALTER TABLE talon DROP FOREIGN KEY FK_40482807A8151442');
        $this->addSql('ALTER TABLE documento DROP FOREIGN KEY FK_B6B12EC7C604D5C6');
        $this->addSql('ALTER TABLE costo_extra DROP FOREIGN KEY FK_68520204704716FE');
        $this->addSql('ALTER TABLE deposito DROP FOREIGN KEY FK_509FC0D1704716FE');
        $this->addSql('ALTER TABLE etiqueta_pasajero DROP FOREIGN KEY FK_BE362F4B704716FE');
        $this->addSql('ALTER TABLE historial_transferencias DROP FOREIGN KEY FK_C1D030DC704716FE');
        $this->addSql('ALTER TABLE link_pago_rifa DROP FOREIGN KEY FK_6A4C3583704716FE');
        $this->addSql('ALTER TABLE pasajero DROP FOREIGN KEY FK_17D4512B4C735FDF');
        $this->addSql('ALTER TABLE talon DROP FOREIGN KEY FK_40482807704716FE');
        $this->addSql('ALTER TABLE talon DROP FOREIGN KEY FK_40482807C680A87');
        $this->addSql('ALTER TABLE documento DROP FOREIGN KEY FK_B6B12EC7F5F88DB9');
        $this->addSql('ALTER TABLE foto_persona DROP FOREIGN KEY FK_43618E61F5F88DB9');
        $this->addSql('ALTER TABLE pasajero DROP FOREIGN KEY FK_17D4512BF5F88DB9');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F5F88DB9');
        $this->addSql('ALTER TABLE historial_transferencias DROP FOREIGN KEY FK_C1D030DC4654B4FD');
        $this->addSql('ALTER TABLE link_pago_rifa_talones DROP FOREIGN KEY FK_12149FA44654B4FD');
        $this->addSql('ALTER TABLE documento DROP FOREIGN KEY FK_B6B12EC7F6939175');
        $this->addSql('ALTER TABLE pasajero DROP FOREIGN KEY FK_17D4512B271768CD');
        $this->addSql('ALTER TABLE forgot_password DROP FOREIGN KEY FK_2AB9B566A76ED395');
        $this->addSql('ALTER TABLE grupo DROP FOREIGN KEY FK_8C0E9BD394E1E648');
        $this->addSql('ALTER TABLE itinerario DROP FOREIGN KEY FK_11ECF40494E1E648');
        $this->addSql('ALTER TABLE viaje DROP FOREIGN KEY FK_1D41ED163279E901');
        $this->addSql('DROP TABLE comprador');
        $this->addSql('DROP TABLE costo_extra');
        $this->addSql('DROP TABLE deposito');
        $this->addSql('DROP TABLE documento');
        $this->addSql('DROP TABLE etiqueta');
        $this->addSql('DROP TABLE etiqueta_pasajero');
        $this->addSql('DROP TABLE forgot_password');
        $this->addSql('DROP TABLE foto_persona');
        $this->addSql('DROP TABLE grupo');
        $this->addSql('DROP TABLE historial_transferencias');
        $this->addSql('DROP TABLE itinerario');
        $this->addSql('DROP TABLE link_pago_rifa');
        $this->addSql('DROP TABLE link_pago_rifa_seleccion');
        $this->addSql('DROP TABLE link_pago_rifa_talones');
        $this->addSql('DROP TABLE lote_rifa');
        $this->addSql('DROP TABLE pago_personal');
        $this->addSql('DROP TABLE pais');
        $this->addSql('DROP TABLE pasajero');
        $this->addSql('DROP TABLE persona');
        $this->addSql('DROP TABLE talon');
        $this->addSql('DROP TABLE tarjeta');
        $this->addSql('DROP TABLE tipo_documento');
        $this->addSql('DROP TABLE universidad');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE viaje');
        $this->addSql('DROP TABLE viaje_madre');
    }
}
