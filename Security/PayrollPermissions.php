<?php
declare(strict_types=1);

namespace KimaiPlugin\LiePayrollBundle\Security;

final class PayrollPermissions
{
    public const ROLE_PAYROLL_ADMIN = "ROLE_PAYROLL_ADMIN";
    public const ROLE_PAYROLL_VIEW  = "ROLE_PAYROLL_VIEW";
    
    // Zusätzliche Permissions für User Management
    public const VIEW = 'view_payroll';
    public const EDIT = 'edit_payroll';
    public const APPROVE = 'approve_payroll';
    public const MANAGE_USERS = 'manage_payroll_users';
}