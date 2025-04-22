<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case TEXT = 'TEXT';
    case MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    case FILE = 'FILE';
}
