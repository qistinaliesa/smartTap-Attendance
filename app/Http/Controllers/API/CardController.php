<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'uid' => 'required|string|unique:cards,uid',
            'name' => 'required|string|max:255',
            'matricID' => 'required|string|max:255',
        ]);

        // Create a new card
        $card = Card::create([
            'uid' => $request->uid,
            'name' => $request->name,
            'matric_id' => $request->matricID,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card registered successfully',
            'card' => $card
        ]);
    }

    public function index()
    {
        $cards = Card::all();
        return view('cards.index', compact('cards'));
    }
}
