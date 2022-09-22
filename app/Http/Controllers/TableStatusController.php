<?php
namespace App\Http\Controllers;

use App\Models\Table;
use App\ModelStates\Table\Available;
use App\ModelStates\Table\NonOperational;
use App\ModelStates\Table\Reserved;

class TableStatusController extends Controller {

    private function fetchTable( int $tableId){
        return Table::where('restaurant_id',request('restaurant_id'))
        ->where('id',$tableId)
        ->firstOrFail();
        
    }

    public function markAsAvailable( int $tableId){
           $table = $this->fetchTable($tableId);
            $table = $this->ChangeStatusTo(Available::class);
    }

    public function markAsNonOperational(int $tableId){
            $table = $this->fetchTable($tableId);
            $table = $this->ChangeStatusTo(NonOperational::class);
    }

    public function markAsReserved(int $tableId){
        $table = $this->fetchTable($tableId);
        $table = $this->changeStatusTo(Reserved::class);
        
    }
}