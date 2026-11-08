<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends Model
{
    protected $fillable = [
        'hostel',
        'room_number',
        'capacity',
        'fee',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'fee' => 'decimal:2',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function label(): string
    {
        return $this->hostel.' - Room '.$this->room_number;
    }
}
