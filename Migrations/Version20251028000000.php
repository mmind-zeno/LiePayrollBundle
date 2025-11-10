<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251028000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Erweitert LiePayrollBundle mit Mitarbeiterstammdaten';
    }

    public function up(Schema $schema): void
    {
        // 1. Tabelle für Mitarbeiterstammdaten
        $this->addSql("
            CREATE TABLE IF NOT EXISTS lie_payroll_user_profile (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                address VARCHAR(255) DEFAULT NULL,
                postal_code VARCHAR(20) DEFAULT NULL,
                city VARCHAR(100) DEFAULT NULL,
                birthdate DATE DEFAULT NULL,
                ahv_number VARCHAR(20) DEFAULT NULL,
                hire_date DATE DEFAULT NULL,
                termination_date DATE DEFAULT NULL,
                position VARCHAR(100) DEFAULT NULL,
                department VARCHAR(100) DEFAULT NULL,
                marital_status VARCHAR(20) DEFAULT NULL,
                number_of_children INT DEFAULT 0,
                municipality VARCHAR(100) DEFAULT NULL,
                tax_municipality VARCHAR(100) DEFAULT NULL,
                iban VARCHAR(34) DEFAULT NULL,
                employment_level INT DEFAULT 100,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_user (user_id),
                INDEX idx_ahv (ahv_number),
                CONSTRAINT fk_payroll_profile_user FOREIGN KEY (user_id) REFERENCES kimai2_users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 2. Neue Spalten für payroll_period hinzufügen
        $table = $schema->getTable('payroll_period');
        
        if (!$table->hasColumn('target_hours')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN target_hours DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$table->hasColumn('overtime')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN overtime DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('night_shift')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN night_shift DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('sunday_work')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN sunday_work DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('bonus')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN bonus DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('thirteenth_salary')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN thirteenth_salary DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('child_allowance')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN child_allowance DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('vacation_compensation')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN vacation_compensation DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('expense_allowance')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN expense_allowance DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('base_salary')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN base_salary DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$table->hasColumn('other_deductions')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN other_deductions JSON DEFAULT NULL");
        }
        if (!$table->hasColumn('payment_date')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN payment_date DATE DEFAULT NULL");
        }
        if (!$table->hasColumn('vacation_balance')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN vacation_balance DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('vacation_taken')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN vacation_taken DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('vacation_remaining')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN vacation_remaining DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('overtime_balance')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN overtime_balance DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('ytd_gross')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN ytd_gross DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('ytd_net')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN ytd_net DECIMAL(10,2) DEFAULT 0.00");
        }
        if (!$table->hasColumn('ytd_deductions')) {
            $this->addSql("ALTER TABLE payroll_period ADD COLUMN ytd_deductions DECIMAL(10,2) DEFAULT 0.00");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS lie_payroll_user_profile');
    }
}