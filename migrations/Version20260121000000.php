<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260121000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema with User and Article entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS "user" (
            id SERIAL PRIMARY KEY,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT \'reader\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_email ON "user" (email)');

        $this->addSql('CREATE TABLE IF NOT EXISTS article (
            id SERIAL PRIMARY KEY,
            author_id INT NOT NULL,
            updated_by_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $this->addSql('DO $$ BEGIN
            ALTER TABLE article ADD CONSTRAINT fk_article_author FOREIGN KEY (author_id) REFERENCES "user"(id) ON DELETE CASCADE;
        EXCEPTION WHEN duplicate_object THEN NULL; END $$');

        $this->addSql('DO $$ BEGIN
            ALTER TABLE article ADD CONSTRAINT fk_article_updated_by FOREIGN KEY (updated_by_id) REFERENCES "user"(id) ON DELETE SET NULL;
        EXCEPTION WHEN duplicate_object THEN NULL; END $$');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_article_author ON article (author_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_article_created_at ON article (created_at)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_article_updated_by ON article (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE "user"');
    }
}
