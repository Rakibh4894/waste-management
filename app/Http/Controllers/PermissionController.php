<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:000254|000255|000256|000257', ['only' => ['index', 'getList']]);
        $this->middleware('permission:000254', ['only' => ['create', 'store']]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataGrid = Permission::select('id', 'name')->get();

            return DataTables::of($dataGrid)
                ->addIndexColumn()
                ->addColumn('action', function ($list) {
                    $btn = '<a href="javascript:void(0)" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="ri-delete-bin-2-line"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('permission.permissions.admin');
    }

    public function create()
    {
        return view('permission.permissions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:permissions,name',
        ], [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name must not exceed 50 characters.',
            'name.unique' => 'This permission name is already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        Permission::create([
            'name' => $request->name,
            'slug' => $request->name,
        ]);

        return response()->json(['success' => 'Permission saved successfully.']);
    }

    public function edit($id)
    {
        $data = Permission::find($id);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:permissions,name,' . $request->data_id,
        ], [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name must not exceed 50 characters.',
            'name.unique' => 'This permission name is already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        Permission::where('id', $request->data_id)->update([
            'name' => $request->name,
            'slug' => $request->name,
        ]);

        return response()->json(['success' => 'Permission updated successfully.']);
    }

    public function destroy($id)
    {
        Permission::where('id', $id)->delete();
        return response()->json(['success' => 'Permission deleted successfully.']);
    }

    public function getList()
    {
        $list = Permission::select('id', 'name', 'slug')->get();
        return Datatables::of($list)
            ->addColumn('action', function ($list) {
                $btn = '<a href="javascript:void(0)" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="fas fa-trash-alt"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getAllForRole(Request $request)
{
    $role_id = $request->role_id;

    // Get all permissions
    $permissions = Permission::all();

    // Get already assigned permission IDs for this role
    $assignedPermissions = DB::table('role_has_permissions')
        ->where('role_id', $role_id)
        ->pluck('permission_id')
        ->toArray();

    // Generate checkboxes HTML
    $grid = '';
    foreach ($permissions as $permission) {
        $checked = in_array($permission->id, $assignedPermissions) ? 'checked' : '';
        $grid .= "<div class='col-sm-3 mb-2'>
                    <input type='checkbox' value='{$permission->id}' {$checked}> {$permission->name}
                  </div>";
    }

    return response()->json(['grid' => $grid]);
}

}
