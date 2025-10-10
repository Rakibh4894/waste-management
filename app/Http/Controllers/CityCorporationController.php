<?php

namespace App\Http\Controllers;

use App\Models\CityCorporation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class CityCorporationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CityCorporation::select('*');
            return DataTables::of($data)
                ->addColumn('action', function ($list) {
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $list->id . '" title="Edit" class="edit btn btn-primary btn-sm editData"><i class="ri-edit-box-line"></i></a>';
                    $btn = auth()->user()->can('000262') // 👈 changed permission code (you may adjust)
                        ? $btn . ' <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="fas fa-trash-alt"></i></a>'
                        : '';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('city-corporations.index');
    }

    public function store(Request $request)
    {
        CityCorporation::create($request->only('title','status'));
        return response()->json(['message' => 'City Corporation created successfully']);
    }

    public function edit($id)
    {
        $data = CityCorporation::findOrFail($id);
        $status = "<option value=''>Select One</option>";
        if ($data->status == 1) {
            $status .= "<option value='1' selected>Active</option><option value='0'>Inactive</option>";
        } else {
            $status .= "<option value='1'>Active</option><option value='0' selected>Inactive</option>";
        }
        return response()->json(['data' => $data, 'status' => $status]);
    }

    public function update(Request $request, $id)
    {
        $cityCorp = CityCorporation::findOrFail($id);
        $cityCorp->update($request->only('title','status'));
        return response()->json(['message' => 'City Corporation updated successfully']);
    }

    public function destroy($id)
    {
        CityCorporation::findOrFail($id)->delete();
        return response()->json(['message' => 'City Corporation deleted successfully']);
    }
}
