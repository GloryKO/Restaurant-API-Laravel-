<?php

namespace App\Http\Controllers;
use App\Http\Resources\RestaurantResource;
use App\Http\Requests\RestaurantValidation;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller {

    public function  store(RestaurantValidation $request): array {
        $restaurant = Restaurant::create(array_merge($request->validated(),['user_id'=> Auth::id(),]));

        return ['restaurant_id'=>$restaurant->id, ];

    }

    public function list(): array {
        $restaurant = Restaurant::where('user_id',Auth::id())->get();
        return ['restaurant'=> RestaurantResource::collection($restaurant),];
    }

    public function update(RestaurantValidation $request, int $restaurantId):void {
        $restaurant  = Restaurant::where('user_id',Auth::id())->where('id',$restaurantId)->update($request->validated());
        if (!restaurant){
            abort(404,'Restaurant not found ');
        }
    }
    

    public function archive(int $restaurantId):void{
        $restaurant = Restaurant::where('user_id',Auth::id())->where('restaurant_id',$restaurantId)->delete();
        if (!$restaurant){
            abort(404,'Restaurant not found');
        }
    }


}