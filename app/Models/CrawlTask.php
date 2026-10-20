<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawlTask extends Model
{
    protected $fillable = [
        'user_id', 'name', 'start_urls', 'url_pattern', 'fields',
        'max_pages', 'max_depth', 'delay_ms', 'respect_robots',
        'status', 'crawled', 'failed', 'output_path', 'error',
    ];

    protected $casts = [
        'start_urls'     => 'array',
        'fields'         => 'array',
        'respect_robots' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['done', 'failed', 'cancelled'], true);
    }

    public function progressPercent(): int
    {
        if ($this->max_pages <= 0) {
            return 0;
        }
        return $this->isFinished() && $this->status === 'done'
            ? 100
            : (int) min(100, round($this->crawled / $this->max_pages * 100));
    }
}
