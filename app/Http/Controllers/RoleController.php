<?php

namespace App\Http\Controllers;

use App\Libraries\CommonFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:000250|000251|000252|000253', ['only' => ['index', 'getList']]);
        $this->middleware('permission:000250', ['only' => ['create', 'store']]);
        $this->middleware('permission:000252', ['only' => ['edit', 'update']]);
        $this->middleware('permission:000253', ['only' => ['delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        CommonFunction::getUrslList();
        return view('permission.roles.admin');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();
        $modules = DB::table('permission_module')->get();
        return view('permission.roles.create', compact('permission', 'modules'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:roles,name',
                'permission' => 'required',
                'module_id' => 'required',
            ]);

            $role = Role::create(['name' => $request->name]);
            $permissions = Permission::whereIn('id', $request->permission)->get(['name'])->toArray();

            $role->syncPermissions($permissions);

            Session::flash("success", "Role Created Successfully!");
            return redirect('roles/admin');
        } catch (\Exception $e) {
            Session::flash('error', CommonFunction::showErrorPublic($e->getMessage()));
            return redirect()->back()->withInput();
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();

        return view('permission.roles.show', compact('role', 'rolePermissions'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30|unique:roles,name'
        ], [
            'name.required' => 'Role name is required.',
            'name.max' => 'Role name may not be greater than 30 characters.',
            'name.unique' => 'Role name must be unique.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        try {
            Role::create(['name' => $request->name]);
            return response()->json([
                'success' => true,
                'message' => 'User Role saved successfully.'
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'User Role not  Saved.'
            ]);
        }

    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30|unique:roles,name,' . $request->data_id . ',',
            'data_id' => 'required|integer'
        ], [
            'name.required' => 'Role name is required.',
            'name.max' => 'Role name may not be greater than 30 characters.',
            'name.unique' => 'Role name must be unique.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $role = Role::find($request->data_id);
            $role->name = $request->name;
            $role->save();
            return response()->json([
                'success' => true,
                'message' => 'User Role saved successfully.'
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'User Role not  Updated.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data  = Role::find($id);
        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assign($id)
    {

        $role = Role::find($id);

        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();
        $modules = DB::table('permission_module')->get();

        return view('permission.roles.edit', compact('role', 'permission', 'rolePermissions', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // return $request->permission;
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->name;
        $role->save();
        $permissions = Permission::whereIn('id', $request->permission)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        Session::flash("success", "Role Updated Successfully!");
        return redirect('roles/admin');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id', $id)->delete();
    }

    public function getList()
    {
        $list = Role::get();
        return DataTables::of($list)
            ->addColumn('action', function ($list) {
                $btn = null;
                if ($list->name !== 'Super Admin') {
                    if (auth()->user()->can('000252')) {
                        $btn .= '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $list->id . '" title="Edit" class="edit btn btn-primary btn-sm editData"><i class="ri-edit-box-line"></i></a>';
                    }
                    if (auth()->user()->can('000252')) {
                        $btn .= '<a href="' . url('roles/permission-assign') . "/" . $list->id . '" style="" title="Assign" class="btn btn-primary btn-sm assignData"><i class="ri-add-fill"></i></a>';
                    }
                    if (auth()->user()->can('000253')) {
                        $btn .= ' <a href="javascript:void(0)" data-toggle="tooltip" style="" data-id="' . $list->id . '" title="Delete" class="btn btn-danger btn-sm deleteData"><i class="ri-delete-bin-2-line"></i></a>';
                    }
                }

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function assignPermission()
    {
        $roles = Role::orderBy('name', 'asc')->get();
        $permissions = Permission::get();
        return view('permission.roles.assign-permission', compact('roles', 'permissions'));
    }

    public function assignBatchPermissions(Request $request)
    {
        try {
            $this->validate($request, [

                'permissions' => 'required',
                'role_id' => 'required',
            ]);

            $role = Role::find($request->role_id);
            $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();
//            $role->syncPermissions($permissions);
            $role->givePermissionTo($permissions);

            return response()->json(['success' => true, 'responseCode' => 200]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'responseCode' => 500]);
        }
    }
    public function revokeBatchPermissions(Request $request)
    {
        try {
            $this->validate($request, [

                'permissions' => 'required',
                'role_id' => 'required',
            ]);

            $role = Role::find($request->role_id);
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
            $role->revokePermissionTo($permissions);

            return response()->json(['success' => true, 'responseCode' => 200]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'responseCode' => 500]);
        }
    }

    public function assignPermissionToRole($permission_id)
    {
        $role_id = $_POST['role_id'];
        $role = Role::find($role_id);
        $permission = Permission::find($permission_id);
        $role->givePermissionTo($permission->name);
        return response()->json(['success' => true, 'responseCode' => 200]);
    }


    public function revokePermissionFromRole($permission_id)
    {
        $role_id = $_POST['role_id'];
        $role = Role::find($role_id);
        $permission = Permission::find($permission_id);
        $role->revokePermissionTo($permission);
        return response()->json(['success' => 'Permission Revoked Successfully.']);
    }

    public function assignRevokeMultiplePermission(Request $request)
    {
        try {
            $role_id = $request->role_id;
            $permission_id = $request->permission_id;
            $assign_revoke = $request->assign_revoke;
            $permission_ids = explode(',', $permission_id);
            // 1 = Assign
            if ($assign_revoke == "1") {
                if ($role_id > 0 && count($permission_ids)) {
                    $role = Role::find($role_id);
                    foreach ($permission_ids as $id) {
                        $permission = Permission::find($id);
                        $role->givePermissionTo($permission);
                    }
                    return response()->json(['success' => true, 'message' => 'Permission Assigned Successfully!']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Something went wrong! Permission Not Assigned!']);
                }
            } else {
                if ($role_id > 0 && count($permission_ids)) {
                    $role = Role::find($role_id);
                    foreach ($permission_ids as $id) {
                        $permission = Permission::find($id);
                        $role->revokePermissionTo($permission);
                    }
                    return response()->json(['success' => true, 'message' => 'Permission Revoked Successfully!']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Something went wrong! Permission Not Revoked!']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => CommonFunction::showErrorPublic($e->getMessage())]);
        }
    }
}
