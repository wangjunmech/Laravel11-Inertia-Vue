<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawlTask extends Model
{
    protected $fillable = [
        'user_id', 'name', 'start_urls', 'url_pattern', 'follow_pattern', 'fields',
        'max_pages', 'max_depth', 'delay_ms', 'respect_robots',
        'status', 'crawled', 'failed', 'fetched', 'output_path', 'error',
        'started_at', 'finished_at', 'current_url',
    ];

    protected $casts = [
        'start_urls'     => 'array',
        'fields'         => 'array',
        'respect_robots' => 'boolean',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'running', 'cancelling'], true);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['done', 'failed', 'cancelled'], true);
    }

    public function progressPercent(): int
    {
        if ($this->status === 'done') {
            return 100;
        }
        if ($this->max_pages <= 0) {
            return 0;
        }
        return (int) min(100, round($this->crawled / $this->max_pages * 100));
    }
}
