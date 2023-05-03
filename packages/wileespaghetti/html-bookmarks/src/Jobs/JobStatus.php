<?php

declare(strict_types=1);

namespace HtmlBookmarks\Jobs;

enum JobStatus: string
{
    case TO_DO = '';
    case QUEUED = 'QUEUED';
    case INITIALIZING = 'INITIALIZING';
    case IN_PROGRESS = 'IN PROGRESS';
    case FINISHED = 'FINISHED';
    case FAILED = 'FAILED';

    public static function getValues(): array
    {
        return array_column(JobStatus::cases(), 'value');
    }
}
