<?php

namespace App\Enums;

/**
 * Utility enum for easy indexing into Deduction table
 *
 * DO NOT reorder, otherwise you must migrate the database
 */
enum DeductionId: int
{
    case Tax = 1;
    case Sss = 2;
    case Philhealth = 3;
    case Pagibig = 4;
    case PreviousTaxWithheld = 5;
    case Absences = 6;
    case ClassAbsences = 7;
    case ClassAbsencesShs = 8;
    case Peraa = 9;
    case Mp2 = 10;
    case Sla = 11;
    case SmCard = 12;
    case SssLoan = 13;
    case SssCalamityLoan = 14;
    case PeraaLoan = 15;
    case HdmfLoan = 16;
    case ArPhone = 17;
    case Hmo = 18;
    case SpecialExam = 19;
    case ArOthers = 20;
    case GradesPenalty = 21;
    case BikeLoan = 22;

    public static function toDictionary(): array
    {
        $cases = [];
        foreach (self::cases() as $case) {
            $cases[$case->name] = $case->value;
        }
        return $cases;
    }
}
