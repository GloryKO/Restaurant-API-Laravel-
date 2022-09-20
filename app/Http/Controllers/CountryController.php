<?php
namespace App\Http\Controllers;

use App\Models\Country;

class CountryController{
        public function __invoke(){
            $countries = Country::get(['id','name']);
        
            return ['countries'=> $countries->toArray(),];

        }
}