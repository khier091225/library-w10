<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Loan extends Pivot
{
    use HasFactory;

    protected $table = 'book_member';

    public $incrementing = true;

    protected $fillable = [
        'book_id',
        'member_id',
        'borrowed_at',
        'due_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->returned_at && $this->due_at && now()->gt($this->due_at);
    }

    public function daysOverdue(): int
    {
        return $this->isOverdue() ? (int) $this->due_at->diffInDays(now()) : 0;
    }
}
