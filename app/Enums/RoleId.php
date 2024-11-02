<?php

namespace App\Enums;

/**
 * Utility enum for easy indexing into Roles table
 *
 * DO NOT reorder, otherwise you must migrate the database
 */
enum RoleId: int
{
    case Payroll = 1;
    case Hr = 2;
}
