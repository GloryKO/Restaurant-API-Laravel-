<?php 

namespace App\ModelStates\Table;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class TableState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Available::class)
            ->allowTransition(NonOperational::class, Available::class)
            ->allowTransition(Available::class, NonOperational::class)
            ->allowTransition(Available::class, Occupied::class)
            ->allowTransition(Available::class, Reserved::class)
            ->allowTransition(Occupied::class, Available::class)
            ->allowTransition(Reserved::class, Available::class)
            ->allowTransition(Reserved::class, Occupied::class);
    }
}