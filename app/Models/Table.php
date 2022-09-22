<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ModelStates\Table\TableState;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Table extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasStates;

    protected $fillable = ['restaurant_id', 'number', 'extra_details'];

    protected $casts = [
        'state' => TableState::class,
    ];

    
    public function changeStatusTo(string $newStatus)
    {
        if (! $this->state->canTransitionTo($newStatus)) {
            $newStatusLabel = (new $newStatus($this))->label();

            abort(
                417,
                'The status of the table cannot be changed to ' . $newStatusLabel . ' from the current status of ' . $this->state->label() . '.'
            );
        }
    }
}