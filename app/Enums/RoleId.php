<?php

namespace App\Enums;

/**
 * Utility enum for easy indexing into Roles table
 *
 * DO NOT reorder, otherwise you must migrate the database
 */
enum RoleId: int
{
    case Admin = 1;
    case Payroll = 2;
    case Hr = 3;
}
