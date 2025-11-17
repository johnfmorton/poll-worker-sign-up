<?php

declare(strict_types=1);

namespace App\Enums;

enum PartyAffiliation: string
{
    case DEMOCRAT = 'democrat';
    case REPUBLICAN = 'republican';
    case INDEPENDENT = 'independent';
    case UNAFFILIATED = 'unaffiliated';
}
