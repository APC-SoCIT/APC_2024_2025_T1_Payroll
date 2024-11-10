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
    case PreviousTaxable = 8;
    case HonorariumOthers = 9;
    case ProfessionalFeeOthers = 10;
    case SalaryAdjustment = 11;
    case SickLeave = 12;
    case SubstitutionPay = 13;
    case Overtime = 14;
    case OvertimeNight = 15;
    case OvertimeRest = 16;
    case OvertimeRestExcess = 17;
    case OvertimeRestNight = 18;
    case OvertimeHoliday = 19;
    case OvertimeHolidayExcess = 20;
    case OvertimeHolidayNight = 21;
}
