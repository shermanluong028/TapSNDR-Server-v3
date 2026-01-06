<?php
namespace App\Http\Controllers;

use App\Models\FormGameOption;
use Illuminate\Routing\Controller;

class GamesController extends Controller
{
    public function get()
    {
        $games = FormGameOption::select('game_name')->groupBy('game_name')->get();
        return response()->json([
            'status' => 1,
            'data'   => $games,
        ]);
    }
}
