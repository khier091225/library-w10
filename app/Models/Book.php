<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'isbn',
        'title',
        'published_year',
        'is_reference',
        'cover_path',
    ];

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'is_reference' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'book_member')
            ->withPivot(['id', 'borrowed_at', 'returned_at'])
            ->withTimestamps()
            ->using(Loan::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function isAvailable(): bool
    {
        if ($this->is_reference) {
            return false;
        }

        return $this->loans()->whereNull('returned_at')->doesntExist();
    }

    public function activeLoan(): ?Loan
    {
        return $this->loans()->whereNull('returned_at')->latest('borrowed_at')->first();
    }
}
