<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminHolidayController extends Controller
{
    public function index(int $year = null)
    {
        $year     = $year ?? now()->year;
        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return view('admin.holidays', compact('year', 'holidays'));
    }

    public function sync(int $year)
    {
        Artisan::call('holidays:sync', ['year' => $year]);
        $output = trim(Artisan::output());

        return redirect()->route('admin.holidays', $year)
            ->with('success', $output ?: "Feriados de {$year} sincronizados.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d|unique:holidays,date',
            'name' => 'required|string|max:120',
        ]);

        $data['year'] = (int) substr($data['date'], 0, 4);
        Holiday::create($data);

        return redirect()->route('admin.holidays', $data['year'])
            ->with('success', "Feriado \"{$data['name']}\" adicionado.");
    }

    public function update(Request $request, Holiday $holiday)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $holiday->update($data);

        return redirect()->route('admin.holidays', $holiday->date->year)
            ->with('success', "Feriado atualizado.");
    }

    public function destroy(Holiday $holiday)
    {
        $year = $holiday->date->year;
        $holiday->delete();

        return redirect()->route('admin.holidays', $year)
            ->with('success', "Feriado removido.");
    }
}
