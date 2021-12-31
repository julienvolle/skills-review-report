<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220101000000 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create database schema';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql(/** @lang text */"
            CREATE TABLE `framework` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `name` VARCHAR(100) NOT NULL,
                `description` VARCHAR(500) NULL DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_FRAMEWORK_GUID` (`guid`)
            )
            COLLATE='utf8mb4_unicode_ci'
            ENGINE=InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `interview` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `framework_id` INT(11) NULL DEFAULT NULL,
                `title` VARCHAR(500) NOT NULL,
                `lastname` VARCHAR(500) NOT NULL,
                `firstname` VARCHAR(500) NOT NULL,
                `result` JSON NOT NULL,
                `secured` TINYINT(1) DEFAULT 0 NOT NULL,
                `secured_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_INTERVIEW_GUID` (`guid`),
                INDEX `IDX_FRAMEWORK_ID_ON_INTERVIEW` (`framework_id`),
                CONSTRAINT `FK_FRAMEWORK_ID_ON_INTERVIEW`
                    FOREIGN KEY (`framework_id`)
                    REFERENCES `framework` (`id`)
                    ON DELETE CASCADE
            )
            COLLATE='utf8mb4_unicode_ci'
            ENGINE=InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `level` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `framework_id` INT DEFAULT NULL,
                `name` VARCHAR(100) NOT NULL,
                `priority` INT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_LEVEL_GUID` (`guid`),
                INDEX `IDX_FRAMEWORK_ID_ON_LEVEL` (`framework_id`),
                CONSTRAINT `FK_FRAMEWORK_ID_ON_LEVEL`
                    FOREIGN KEY (`framework_id`)
                    REFERENCES `framework` (`id`)
                    ON DELETE CASCADE
            )
            DEFAULT CHARACTER SET `utf8mb4`
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `framework_id` INT DEFAULT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` VARCHAR(500) DEFAULT NULL,
                `priority` INT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_CATEGORY_GUID` (`guid`),
                INDEX `IDX_FRAMEWORK_ID_ON_CATEGORY` (`framework_id`),
                CONSTRAINT `FK_FRAMEWORK_ID_ON_CATEGORY`
                    FOREIGN KEY (`framework_id`)
                    REFERENCES `framework` (`id`)
                    ON DELETE CASCADE
            )
            DEFAULT CHARACTER SET `utf8mb4`
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `skill` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `category_id` INT DEFAULT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` VARCHAR(500) DEFAULT NULL,
                `priority` INT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `UNIQ_SKILL_GUID` (`guid`),
                INDEX `IDX_CATEGORY_ID_ON_SKILL` (`category_id`),
                CONSTRAINT `FK_CATEGORY_ID_ON_SKILL`
                    FOREIGN KEY (`category_id`)
                    REFERENCES `category` (`id`)
                    ON DELETE CASCADE
            )
            DEFAULT CHARACTER SET `utf8mb4`
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `user` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `guid` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                `email` VARCHAR(500) NOT NULL,
                `roles` JSON NOT NULL,
                `password` VARCHAR(500) NOT NULL,
                PRIMARY KEY(`id`),
                UNIQUE INDEX `UNIQ_USER_GUID` (`guid`),
                UNIQUE INDEX `UNIQ_USER_EMAIL` (`email`)
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `user_framework` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `user_id` INT DEFAULT NULL,
                `framework_id` INT DEFAULT NULL,
                `roles` JSON NOT NULL,
                PRIMARY KEY(`id`),
                INDEX `IDX_USER_ID_ON_UF` (`user_id`),
                INDEX `IDX_FRAMEWORK_ID_ON_UF` (`framework_id`),
                UNIQUE INDEX `UNIQ_UF_IDS` (`user_id`, `framework_id`),
                CONSTRAINT `FK_UF_ON_USER_ID`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `user` (`id`),
                CONSTRAINT `FK_UF_ON_FRAMEWORK_ID`
                    FOREIGN KEY (`framework_id`)
                    REFERENCES `framework` (`id`)
                    ON DELETE CASCADE
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");

        $this->addSql(/** @lang text */"
            CREATE TABLE `user_interview` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `user_id` INT DEFAULT NULL,
                `interview_id` INT DEFAULT NULL,
                `roles` JSON NOT NULL,
                PRIMARY KEY(`id`),
                INDEX `IDX_USER_ID_ON_UI` (`user_id`),
                INDEX `IDX_INTERVIEW_ID_ON_UI` (`interview_id`),
                UNIQUE INDEX `UNIQ_UI_IDS` (`user_id`, `interview_id`),
                CONSTRAINT `FK_UI_ON_USER_ID`
                    FOREIGN KEY (`user_id`)
                    REFERENCES `user` (`id`),
                CONSTRAINT `FK_UI_ON_INTERVIEW_ID`
                    FOREIGN KEY (`interview_id`)
                    REFERENCES `interview` (`id`)
                    ON DELETE CASCADE
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->addSql(/** @lang text */"DROP TABLE `user_interview`;");
        $this->addSql(/** @lang text */"DROP TABLE `user_framework`;");
        $this->addSql(/** @lang text */"DROP TABLE `user`;");
        $this->addSql(/** @lang text */"DROP TABLE `interview`;");
        $this->addSql(/** @lang text */"DROP TABLE `skill`;");
        $this->addSql(/** @lang text */"DROP TABLE `category`;");
        $this->addSql(/** @lang text */"DROP TABLE `level`;");
        $this->addSql(/** @lang text */"DROP TABLE `framework`;");
    }
}
