<?php

namespace App\Enums;

/**
 * Utility enum for easy indexing into Addition table
 *
 * DO NOT reorder, otherwise you must migrate the database
 */
enum AdditionId: int
{
    case Salary = 1;
    case Deminimis = 2;
    case ProfessionalFee = 3;
    case Allowance = 4;
    case Honorarium = 5;
    case Merit = 6;
    case AllowanceAdjustment = 7;
    case HonorariumOthers = 8;
    case ProfessionalFeeOthers = 9;
    case SalaryAdjustment = 10;
    case SickLeave = 11;
    case OvertimePay = 12;
    case SubstitutionPay = 13;
}
