<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220411045227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vehicles (id INT AUTO_INCREMENT NOT NULL, date_added DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, type VARCHAR(4) NOT NULL, msrp NUMERIC(20, 2) NOT NULL, year INT NOT NULL, make VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, miles INT NOT NULL, vin VARCHAR(255) NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_1FCE69FAB1085141 (vin), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE vehicles');
    }
}
