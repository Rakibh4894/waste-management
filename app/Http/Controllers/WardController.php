<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use App\Models\CityCorporation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class WardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Ward::with('cityCorporation')->select('wards.*');
            return DataTables::of($data)
                ->addColumn('city_corporation', function ($row) {
                    return $row->cityCorporation ? $row->cityCorporation->title : '-';
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($list) {
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $list->id . '" title="Edit" class="edit btn btn-primary btn-sm editData"><i class="ri-edit-box-line"></i></a>';
                    $btn .= auth()->user()->can('000261')
                        ? ' <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="fas fa-trash-alt"></i></a>'
                        : '';
                    return $btn;
                })
                ->rawColumns(['status','action'])
                ->make(true);
        }

        // <- This is the important line to fix "Undefined variable $city_corporations"
        $city_corporations = CityCorporation::orderBy('title')->get();

        return view('wards.wards', compact('city_corporations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'city_corporation_id' => 'required|exists:city_corporations,id',
        ]);

        Ward::create($request->only('number','status','city_corporation_id'));
        return response()->json(['message' => 'Ward created successfully']);
    }

    public function edit($id)
    {
        $data = Ward::findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'status' => 'required|in:0,1',
            'city_corporation_id' => 'required|exists:city_corporations,id',
        ]);

        $ward = Ward::findOrFail($id);
        $ward->update($request->only('number','status','city_corporation_id'));
        return response()->json(['message' => 'Ward updated successfully']);
    }

    public function destroy($id)
    {
        Ward::findOrFail($id)->delete();
        return response()->json(['message' => 'Ward deleted successfully']);
    }
}
