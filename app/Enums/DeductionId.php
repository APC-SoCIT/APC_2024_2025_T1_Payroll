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
    case Peraa = 7;
    case Mp2 = 8;
    case Sla = 9;
    case SmCard = 10;
    case SssLoan = 11;
    case SssCalamityLoan = 12;
    case PeraaLoan = 13;
    case HdmfLoan = 14;
    case ArPhone = 15;
    case Hmo = 16;
    case SpecialExam = 17;
    case ArOthers = 18;
    case GradesPenalty = 19;
    case BikeLoan = 20;
}
