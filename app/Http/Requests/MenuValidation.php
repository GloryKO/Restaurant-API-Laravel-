<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class MenuValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueRule = Rule::unique('menu')->where(function ($query) {
            return $query->where('restaurant_id', $this->input('restaurant_id'));
        });
        if (Route::currentRouteName() === 'update_menu_item') {
            $uniqueRule->ignore(Route::current()->originalParameter('menuItemId'));
        }

        return [
            // Existence of the restaurant record already checked by the `RequireRestaurant` middleware
            'restaurant_id' => ['required'],
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
        ];
    }
}