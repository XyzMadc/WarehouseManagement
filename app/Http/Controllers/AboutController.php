<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Returning;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AboutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $username = $user->username;
        $words = explode(' ', $username);
        $initial = '';
        foreach ($words as $word) {
            $initial .= Str::upper(Str::substr($word, 0, 1));
        }
        return Inertia::render("About/index", [
            'rental_count' => Rental::where('status', '!=', 1)->count(),
            'return_count' => Returning::where('status', '!=', 1)->whereNotNull('photo')->count(),
            'initial' => $initial,
        ]);
    }
}
