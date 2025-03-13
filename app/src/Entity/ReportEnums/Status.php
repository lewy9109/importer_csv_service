<?php

namespace App\Entity\ReportEnums;

enum Status: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case PARTIAL = 'partial';
    case IN_PROGRESS = 'in-progress';
}