<?php
namespace App\Http\Middleware;
use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireRestaurant {
    public function handle(Request $request,Closure $next){
        if (! $request->has('restaurant_id')){
            abort(417,'Specifying the restaurant is required');
        
        }

        $ownedByUser = Restaurant::query()
        ->where('user_id',Auth::id())
        ->where('id', $request->input('restaurant_id'))
        ->exists();

        if(!$ownedByUser){
            abort(404,'Restaurant not found');
        }

        return $next($request);
    }

}