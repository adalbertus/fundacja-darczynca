<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121064229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bank_history (id INT AUTO_INCREMENT NOT NULL, donor_id INT DEFAULT NULL, bank_history_id INT DEFAULT NULL, date DATE NOT NULL, value NUMERIC(12, 2) NOT NULL, category VARCHAR(32) DEFAULT \'brak\' NOT NULL, sub_category VARCHAR(32) DEFAULT \'brak\' NOT NULL, description VARCHAR(2048) NOT NULL, sender_name VARCHAR(2048) NOT NULL, sender_bank_account VARCHAR(32) NOT NULL, comment VARCHAR(4096) DEFAULT NULL, is_draft TINYINT(1) NOT NULL, md5 VARCHAR(32) DEFAULT NULL, raw VARCHAR(4096) DEFAULT NULL, manual TINYINT(1) DEFAULT 0 NOT NULL, flagged TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_92E18E53DD7B7A7 (donor_id), INDEX IDX_92E18E597A542E4 (bank_history_id), INDEX category_idx (category), INDEX subcategory_idx (sub_category), INDEX date_idx (date), FULLTEXT INDEX description (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE description_regexp (id INT AUTO_INCREMENT NOT NULL, expression VARCHAR(500) NOT NULL, category VARCHAR(32) DEFAULT \'brak\' NOT NULL, sub_category VARCHAR(32) DEFAULT \'brak\' NOT NULL, comment VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donor (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, is_auto TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, comment VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D7F240975E237E06 (name), INDEX IDX_D7F24097A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donor_search_pattern (id INT AUTO_INCREMENT NOT NULL, donor_id INT NOT NULL, search_pattern VARCHAR(255) NOT NULL, INDEX IDX_70AFE93F3DD7B7A7 (donor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(250) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) DEFAULT 1 NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, comment VARCHAR(500) DEFAULT NULL, login_success DATETIME DEFAULT NULL, login_failed DATETIME DEFAULT NULL, new_send_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bank_history ADD CONSTRAINT FK_92E18E53DD7B7A7 FOREIGN KEY (donor_id) REFERENCES donor (id)');
        $this->addSql('ALTER TABLE bank_history ADD CONSTRAINT FK_92E18E597A542E4 FOREIGN KEY (bank_history_id) REFERENCES bank_history (id)');
        $this->addSql('ALTER TABLE donor ADD CONSTRAINT FK_D7F24097A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE donor_search_pattern ADD CONSTRAINT FK_70AFE93F3DD7B7A7 FOREIGN KEY (donor_id) REFERENCES donor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bank_history DROP FOREIGN KEY FK_92E18E53DD7B7A7');
        $this->addSql('ALTER TABLE bank_history DROP FOREIGN KEY FK_92E18E597A542E4');
        $this->addSql('ALTER TABLE donor DROP FOREIGN KEY FK_D7F24097A76ED395');
        $this->addSql('ALTER TABLE donor_search_pattern DROP FOREIGN KEY FK_70AFE93F3DD7B7A7');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE bank_history');
        $this->addSql('DROP TABLE description_regexp');
        $this->addSql('DROP TABLE donor');
        $this->addSql('DROP TABLE donor_search_pattern');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
    }
}
