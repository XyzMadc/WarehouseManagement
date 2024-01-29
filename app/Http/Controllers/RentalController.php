<?php

namespace App\Http\Controllers;

use App\Exports\ExportLogs;
use App\Models\Item;
use App\Models\Rental;
use App\Models\Returning;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RentalController extends Controller
{
    //for admin
    public function indexAdmin()
    {
        return Inertia::render("Dashboard/Request/index", [
            'logs' => Log::with(['item', 'user'])->paginate(5),
            'rental_count' => Rental::where('status', '!=', 1)->count(),
            'return_count' => Returning::where('status', '!=', 1)->where('photo', '!=', null)->count(),
        ]);
    }

    public function exportExcel(){
        //return Excel::download(new ExportLogs, 'logs.xlsx');
        return (new ExportLogs)->download('logs.xlsx');
    }

    public function rentalAdmin()
    {
        return Inertia::render("Dashboard/Request/Rental/index", [
            'rentals' => Rental::where('status', false)->with(['item', 'user'])->get(),
            'users' => User::all(),
        ]);
    }

    public function acceptRental(Request $request)
    {
        $request->validate([
            'reason' => 'required',
        ]);
        $rental = Rental::find($request->id);
        $rental->status = true;
        $rental->save();
        Log::create([
            'user_id' => $request->user_id,
            'item_id' => $request->item_id,
            'reason' => $request->reason,
            'rent_date' => $request->rent_date,
            'return_date' => $request->return_date,
        ]);
        Returning::create([
            'user_id' => $request->user_id,
            'item_id' => $request->item_id,
            'rent_date' => $request->rent_date,
        ]);
        return redirect('/request/rental');
    }

    public function rejectRental(Request $request)
    {
        $rental = Rental::find($request->id);
        $rental->delete();
        return redirect('/request/rental');
    }

    //for user
    public function indexUser(Request $request)
    {
        return Inertia::render("Peminjaman/index", [
            'items' => Item::all(),
            'rentals' => Rental::with(['item', 'user'])->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'reason' => 'required',
        ]);
        $request['rent_date'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        $request['return_date'] = Carbon::now()->addDays(7)->toDateString();
        Rental::create([
            'user_id' => auth()->id(),
            'item_id' => $request->item_id,
            'reason' => $request->reason,
            'rent_date' => $request->rent_date,
            'return_date' => $request->return_date,
        ]);
        // $item = Item::find($request->item_id);
        // $item->amount -= $request->amount;
        // $item->save();

        return redirect('/peminjaman');
    }
}
