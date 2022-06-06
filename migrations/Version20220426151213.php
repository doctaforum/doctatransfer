<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220426151213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE download DROP FOREIGN KEY FK_781A8270FD89DA79');
        $this->addSql('DROP INDEX IDX_781A8270FD89DA79 ON download');
        $this->addSql('ALTER TABLE download CHANGE user_property_id file_id INT NOT NULL');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A827093CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('CREATE INDEX IDX_781A827093CB796C ON download (file_id)');
        $this->addSql('ALTER TABLE file CHANGE description description VARCHAR(65532) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE download DROP FOREIGN KEY FK_781A827093CB796C');
        $this->addSql('DROP INDEX IDX_781A827093CB796C ON download');
        $this->addSql('ALTER TABLE download CHANGE file_id user_property_id INT NOT NULL');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A8270FD89DA79 FOREIGN KEY (user_property_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_781A8270FD89DA79 ON download (user_property_id)');
        $this->addSql('ALTER TABLE file CHANGE description description MEDIUMTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
