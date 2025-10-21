<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251020120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Create LiePayroll tables";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS payroll_settings (
            id INT AUTO_INCREMENT NOT NULL,
            key_name VARCHAR(191) NOT NULL UNIQUE,
            value LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS payroll_period (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            month VARCHAR(7) NOT NULL,
            total_hours NUMERIC(10,2) NOT NULL,
            hourly_rate NUMERIC(10,2) NOT NULL,
            gross_salary NUMERIC(10,2) NOT NULL,
            deductions JSON NOT NULL,
            net_salary NUMERIC(10,2) NOT NULL,
            status VARCHAR(16) NOT NULL,
            payslip_path VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_USER (user_id),
            UNIQUE KEY uniq_user_month (user_id, month),
            CONSTRAINT FK_PAYROLL_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE IF NOT EXISTS payroll_item (
            id INT AUTO_INCREMENT NOT NULL,
            period_id INT NOT NULL,
            type VARCHAR(32) NOT NULL,
            quantity NUMERIC(10,2) NOT NULL,
            unit VARCHAR(8) NOT NULL,
            amount NUMERIC(10,2) NOT NULL,
            INDEX IDX_PERIOD (period_id),
            CONSTRAINT FK_PAYROLLITEM_PERIOD FOREIGN KEY (period_id) REFERENCES payroll_period (id) ON DELETE CASCADE,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS payroll_item");
        $this->addSql("DROP TABLE IF EXISTS payroll_period");
        $this->addSql("DROP TABLE IF EXISTS payroll_settings");
    }
}
