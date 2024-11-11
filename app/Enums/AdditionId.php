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
    case SubstitutionPayShs = 14;
    case Overtime = 15;
    case OvertimeNight = 16;
    case OvertimeRest = 17;
    case OvertimeRestExcess = 18;
    case OvertimeRestNight = 19;
    case OvertimeHoliday = 20;
    case OvertimeHolidayExcess = 21;
    case OvertimeHolidayNight = 22;
}
