<?php

namespace App\Http\Controllers;

use App\Models\CityCorporation;
use App\Models\MonthlyBillAmount;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyBillAmountController extends Controller
{
    public function index()
    {
        // Order by city -> ward -> active (active first) so the active amount appears on top per ward
        $items = MonthlyBillAmount::with('cityCorporation','ward')
            ->orderBy('city_corporation_id')
            ->orderBy('ward_id')
            ->orderByDesc('is_active')
            ->get();
        return view('monthly_bill_amounts.index', compact('items'));
    }

    public function create()
    {
        $cityCorporations = CityCorporation::all();
        return view('monthly_bill_amounts.create', compact('cityCorporations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'city_corporation_id' => 'required|exists:city_corporations,id',
            'ward_id' => 'required|exists:wards,id',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['created_by'] = Auth::id();
        $data['is_active'] = !empty($data['is_active']);

        if ($data['is_active']) {
            // deactivate others for the same ward
            MonthlyBillAmount::where('city_corporation_id', $data['city_corporation_id'])
                ->where('ward_id', $data['ward_id'])
                ->update(['is_active' => false]);
        }

        MonthlyBillAmount::create($data);

        return redirect()->route('monthly-bill.index')->with('success', 'Monthly bill amount saved.');
    }

    public function edit(MonthlyBillAmount $monthlyBill)
    {
        $cityCorporations = CityCorporation::all();
        return view('monthly_bill_amounts.edit', ['item' => $monthlyBill, 'cityCorporations' => $cityCorporations]);
    }

    public function update(Request $request, MonthlyBillAmount $monthlyBill)
    {
        // If request is AJAX and only updating active flag, handle minimal payload
        if ($request->ajax() && $request->has('is_active')) {
            $isActive = $request->boolean('is_active');

            if ($isActive) {
                // deactivate others for same city+ward
                MonthlyBillAmount::where('city_corporation_id', $monthlyBill->city_corporation_id)
                    ->where('ward_id', $monthlyBill->ward_id)
                    ->where('id', '!=', $monthlyBill->id)
                    ->update(['is_active' => false]);
            }

            $monthlyBill->update(['is_active' => $isActive]);

            return response()->json(['success' => true, 'message' => 'Updated successfully']);
        }

        // Full update (from edit page)
        $data = $request->validate([
            'city_corporation_id' => 'required|exists:city_corporations,id',
            'ward_id' => 'required|exists:wards,id',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = !empty($data['is_active']);

        if ($data['is_active']) {
            MonthlyBillAmount::where('city_corporation_id', $data['city_corporation_id'])
                ->where('ward_id', $data['ward_id'])
                ->where('id', '!=', $monthlyBill->id)
                ->update(['is_active' => false]);
        }

        $monthlyBill->update($data);

        return redirect()->route('monthly-bill.index')->with('success', 'Monthly bill amount updated.');
    }

    public function destroy(MonthlyBillAmount $monthlyBill)
    {
        $monthlyBill->delete();
        return redirect()->route('monthly-bill.index')->with('success', 'Monthly bill amount deleted.');
    }

    // AJAX: get active amount for city + ward
    public function getActiveAmount(Request $request)
    {
        $city = $request->query('city_id');
        $ward = $request->query('ward_id');

        if (!$city || !$ward) {
            return response()->json(['success' => false, 'amount' => null]);
        }

        $record = MonthlyBillAmount::where('city_corporation_id', $city)
            ->where('ward_id', $ward)
            ->where('is_active', true)
            ->first();

        if ($record) {
            return response()->json(['success' => true, 'amount' => number_format($record->amount, 2), 'raw' => $record->amount]);
        }

        return response()->json(['success' => false, 'amount' => null]);
    }
}
