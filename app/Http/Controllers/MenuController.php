<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Request\MenuValidation;
use App\Http\Resources\MenuResources;
use Illuminate\Support\Facades\Cache;
use App\Models\Menu;

class MenuController extends Controller
{
    public function list(): array{
      $menuItems = Cache::rememberForever('menu_items_of'.request('restaurant_id'),function (){
        return Menu::where('restaurant_id',request('restaurant_id'))
        ->get();
      });
    return ['menu_items'=>MenuResources::collection($menuItems),];

    }


    public function store(MenuValidation $request):array {
            $menuItem = Menu::create($request->validated());
            return ['menu_items_id '=>$menuItem->id, ];

            }

    public function update(MenuValidation $request,int $menuItemId):void {
            $menuItemUpdate = Menu::where('restaurant_id',$request->input('restaurant_id'))
            ->where('id',$menuItemId)
            ->update($request->validated()) ;
            if (!$menuItemUpdate){
                abort(404,'Menu Item Not found');

            }
    
    }
    public function archive( int $menuItemId):void{
        $menuItem = Menu::where('restaurant_id',request('restaurant_id'))
        ->where('id',$MenuItemId)
        ->delete();
        if (!$menuItemId){
            abort(404,'Menu Item Not Found');
        }
    }

}
