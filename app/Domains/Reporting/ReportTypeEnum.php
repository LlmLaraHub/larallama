<?php

namespace App\Domains\Reporting;

enum ReportTypeEnum: string
{
    case RFP = 'rfp';
    case GatherInfo = 'gather_info';
}
