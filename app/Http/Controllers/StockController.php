<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;

class StockController
{

    public function __construct() {

    }

    public function getData() {
        $data = DB::select('select * from company where active = ?', [1]);
        return $data;
    }
}
