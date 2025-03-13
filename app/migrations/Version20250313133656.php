<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250313133656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Init DB';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE error (id INT AUTO_INCREMENT NOT NULL, report_id INT DEFAULT NULL, message LONGTEXT NOT NULL, row_id INT DEFAULT NULL, INDEX IDX_5DDDBC714BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report_import (id INT AUTO_INCREMENT NOT NULL, file_path LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_rows INT DEFAULT NULL, business_id LONGTEXT NOT NULL, INDEX IDX_B4BD1B82A89DB457 (business_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, full_name LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE error ADD CONSTRAINT FK_5DDDBC714BD2A4C0 FOREIGN KEY (report_id) REFERENCES report_import (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE error DROP FOREIGN KEY FK_5DDDBC714BD2A4C0');
        $this->addSql('DROP TABLE error');
        $this->addSql('DROP TABLE report_import');
        $this->addSql('DROP TABLE user');
    }
}
