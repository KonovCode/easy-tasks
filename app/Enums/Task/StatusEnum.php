<?php

namespace App\Enums\Task;

enum StatusEnum: string
{
    case Pending = 'pending';
    case Done = 'done';
    case Cancel = 'cancel';
}
