<?php

namespace App\Http\Controllers;

use DB;

class ExampleController extends Controller {

    public function index () {
//        var_dump($_SERVER);
        $result = DB::select('select 1;');
        var_dump($result);
        return "";
    }

}
