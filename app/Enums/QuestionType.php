<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case TEXT = 'TEXT';
    case MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    case FILE = 'FILE';

    public static function toArray(): array
    {
        return collect(QuestionType::cases())->map(fn (QuestionType $type) => [
            'id' => $type->value,
            'name' => $type->label(),
        ])->toArray();
    }

    public static function getIconFromFilename(string $filename): string
    {
        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt', 'md', 'doc', 'docx', 'odt' => 'o-document-text',
            'pdf' => 'o-document',
            'jpg', 'jpeg', 'png', 'svg' => 'o-photo',
            'ppt', 'pptx', 'odp' => 'o-presentation-chart-bar',
            'xls', 'xlsx', 'ods' => 'o-table-cells',
            default => 'o-question-mark-circle',
        };
    }

    public function label(): string
    {
        return __('question_types.'.$this->value);
    }
}
