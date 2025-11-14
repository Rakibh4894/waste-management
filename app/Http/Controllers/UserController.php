<?php

namespace App\Http\Controllers;

use App\Libraries\CommonFunction;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CityCorporation;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Exception;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:000258|000259|000260|000261', ['only' => ['index']]);
        $this->middleware('permission:000258', ['only' => ['create']]);
        $this->middleware('permission:000260', ['only' => ['edit', 'update']]);
        $this->middleware('permission:000261', ['only' => ['delete']]);
        $this->middleware('permission:000261', ['only' => ['delete']]);
        $this->middleware('permission:000262|000263', ['only' => ['manageUserPermission', 'getUsersForPermission', 'assignRevokePermission', 'getUserPermissionsList']]);
        $this->middleware('permission:000263', ['only' => ['assignPermissionToUser', 'revokePermissionFromUser']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('users.admin');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
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
                'name' => 'required|unique:users,name',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|same:confirm-password',
                'roles' => 'required'
            ]);

            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            Session::flash("success", "User Created Successfully!");
            return redirect('users/admin');
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
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
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
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'same:confirm-password',
                'roles' => 'required'
            ]);

            $input = $request->all();
            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = array_except($input, array('password'));
            }

            $user = User::find($id);
            $user->update($input);
            \Illuminate\Support\Facades\DB::table('model_has_roles')->where('model_id', $id)->delete();

            $user->assignRole($request->input('roles'));

            Session::flash("success", "User Updated Successfully!");
            return redirect("users/admin");
        } catch (\Exception $e) {
            Session::flash('error', CommonFunction::showErrorPublic($e->getMessage()));
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
    }

    public function getList()
    {
        $list = User::get();
        return \Yajra\DataTables\Facades\DataTables::of($list)
            ->addColumn('roles', function ($list) {
                $roles = "";
                if (!empty($list->getRoleNames())) {
                    foreach ($list->getRoleNames() as $v) {
                        $roles .= ' <label class="badge badge-success">' . $v . '</label> ';
                    }
                }
                return $roles;
            })
            ->addColumn('action', function ($list) {
                $btn = auth()->user()->can('000260') ? '<a href="' . url('users/edit') . "/" . $list->id . '" style="margin:2px" title="Edit" title="Edit" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>' : '';
                $btn = auth()->user()->can('000261') ? $btn . ' <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="fas fa-trash-alt"></i></a>' : '';
                return $btn;
            })
            ->rawColumns(['roles', 'action'])
            ->make(true);
    }

    public function manageUser(Request $request)
{
    $roleList = Role::where('name', '!=', 'Super Admin')->pluck('name');
    $cityCorporations = CityCorporation::all();

    if ($request->ajax()) {
        $query = DB::table('users')
            ->leftJoin('user_has_role', 'users.id', '=', 'user_has_role.user_id')
            ->leftJoin('city_corporations', 'users.city_corporation_id', '=', 'city_corporations.id')
            ->leftJoin('wards', 'users.ward_id', '=', 'wards.id')
            ->leftJoin('roles', 'user_has_role.role_id', '=', 'roles.id')
            ->select(
                'users.*',
                'city_corporations.title as cityCorporation',
                'wards.number as ward',
                'user_has_role.role_id',
                'roles.name as role_name'
            )
            ->whereNotIn('users.id', [1]); // Exclude superadmin

        $loggedUser = auth()->user();

        // Filter based on admin's city_corporation_id and ward_id
        if ($loggedUser->hasRole('Admin')) {
            if ($loggedUser->city_corporation_id > 0 && is_null($loggedUser->ward_id)) {
                // Admin with city only, list all collectors in that city
                $query->where('users.city_corporation_id', $loggedUser->city_corporation_id);
            } elseif ($loggedUser->city_corporation_id > 0 && $loggedUser->ward_id > 0) {
                // Admin with city + ward, list collectors in that ward
                $query->where('users.city_corporation_id', $loggedUser->city_corporation_id)
                      ->where('users.ward_id', $loggedUser->ward_id);
            }
        }

        $dataGrid = $query->orderBy('users.id', 'desc')->get();

        return DataTables::of($dataGrid)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = "";
                if (auth()->user()->can('000260') && $row->name != 'superadmin') {
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" title="Edit" class="edit btn btn-primary btn-sm editData"><i class="ri-edit-box-line"></i></a>';
                }
                return $btn;
            })
            ->editColumn('users.status', function ($dataGrid) {
                return match($dataGrid->status) {
                    '1' => 'Active',
                    '2' => 'Inactive',
                    default => 'Cancel',
                };
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    return view('users.manage-users', compact('roleList', 'cityCorporations'));
}


    public function storeManageUser(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'city_corporation_id' => 'required|integer',
            'roles_id' => 'required|array',
            'roles_id.*' => 'string',
        ], [
            'name.required' => 'name is required.',
            'name.unique' => 'name is already taken. Please choose a different one.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Provide a valid email address.',
            'email.unique' => 'Email address is already in use.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'roles_id.required' => 'At least one role must be selected.',
            'roles_id.array' => 'Roles must be provided as an array.',
            'roles_id.*.string' => 'Each role ID must be a valid string.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        FacadesDB::beginTransaction();
//        $emp = Employee::where('id',$request->emp_id)->select('manual_id_no')->first();
//        dd($emp->manual_id_no);
        try {
            // Create the user
            $emp_id = $request->input('emp_id');
            $user  = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = FacadesHash::make($request->password);
            $user->employee_id = $emp_id;
            $user->save();
//            $user = User::create([
//                'name' => $request->name,
//                'email' => $request->email,
//                'password' => FacadesHash::make($request->password),
//                'employee_id' => $emp_id,
//            ]);
            $roleNames = $request->input('roles_id', []);
            $rolesString = implode(',', $roleNames);
            foreach ($roleNames as $rolename){
                $user->assignRole($rolename);
            }


            UserHasRole::create([
                'user_id' => $user->id,
                'role_id' => $rolesString,
                'employee_id' => $emp_id,
            ]);
//            $user = User::create($input);
//            $user->assignRole($request->roles);
            FacadesDB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User Created successfully'
            ]);
        } catch (\Exception $e) {
            FacadesDB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function manageUsersEdit($id)
    {
//        $user = UserHasRole::find($id);
//        $userData = $user->user_id;
        $employee = DB::table('users')->select('id','name','email','employee_id','status')->where('id', $id)->first();
        $userDataInfo = $employee->employee_id;
        $employeeData = DB::table('employees')->select('full_name' ?? '')->where('id', $userDataInfo)->first();
        $data = UserHasRole::where('user_id', $id)->first();
        if($data==null){
            $data = [];
        }

        $status = "<option value=''>Select One</option>";
        if ($employee->status == 1) {
            $status .= "<option value='1' selected>Active</option><option value='2'>InActive</option>";
        } else {
            $status .= "<option value='1'>Active</option><option value='2' selected>InActive</option>";
        }
        return response()->json(['data' => $data, 'status' => $status, 'employee' => $employee,'employeeData' => $employeeData,]);
    }

    public function manageUserUpdate(Request $request)
{
    $user = User::find($request->data_id);

    // Base validation (without password rules)
    $rules = [
        'name' => 'required',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'city_corporation_id' => 'required|integer',
        'roles_id' => 'required|array',
        'roles_id.*' => 'string',
        'status' => 'required',
    ];

    // Add password validation ONLY if password field is filled
    if ($request->filled('password')) {
        $rules['password'] = 'required|same:confirm_password';
    }

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    }

    DB::beginTransaction();

    try {

        // Update password only if provided
        if ($request->filled('password')) {
            $newPassword = Hash::make($request->password);
        } else {
            $newPassword = $user->password; // keep existing password
        }

        // Update the user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $newPassword,
            'city_corporation_id' => $request->city_corporation_id,
            'ward_id' => $request->ward_id ?? $user->ward_id,
            'status' => $request->status,
        ]);

        // Update roles
        UserHasRole::where('user_id', $user->id)->delete();
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        foreach ($request->roles_id as $roleName) {
            $user->assignRole($roleName);
        }
        
        UserHasRole::create([
            'user_id' => $user->id,
            'role_id' => implode(',', $request->roles_id),
            'employee_id' => $request->employee_id,
        ]);
        
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'new_status' => $request->status,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'User not updated.',
            'error' => $e->getMessage()
        ]);
    }
}


    public function manageUsersDestroy($id)
    {

        try {
;
           $user = UserHasRole::find($id)->user;

            User::where('id', $user->id)->update([
                'status' => 2,
            ]);
            UserHasRole::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'User role deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user role assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function manageUserPermission()
    {
        return view('users.manage-user-permission');
    }

    public function getUsersForPermission()
    {
        $list = User::where('status','1')->whereNotIn('id',[1])->get();
        return Datatables::of($list)
            ->addIndexColumn()
            ->addColumn('roles', function ($list) {
                $roles = "";
                if (!empty($list->getRoleNames())) {
                    foreach ($list->getRoleNames() as $v) {
                        $roles .= ' <label class="badge badge-success">' . $v . '</label> ';
                    }
                }
                return $roles;
            })
            ->addColumn('action', function ($list) {
                $btn = '';
//                if ($list->name == 'superadmin') {
                    // If the list email belongs to Super Admin

                    if ($list->name != 'superadmin' && auth()->user()->can('000262')) {
                        $btn = '<a href="' . url('users/assign-revoke-permission') . "/" . $list->id . '" style="margin:2px" data-original-title="Assign / Revoke Permission" title="Assign / Revoke Permission" class="btn btn-primary btn-sm"><i class="ri-arrow-right-double-fill"></i></a>';
                    }
//                }
//                else {
//                    // For other users
//                    $btn = '<a href="' . url('users/assign-revoke-permission') . "/" . $list->id . '" style="margin:2px" data-original-title="Assign / Revoke Permission" title="Assign / Revoke Permission" class="btn btn-primary btn-sm"><i class="ri-arrow-right-double-fill"></i></a>';
//                }

                return $btn;
            })
            ->rawColumns(['roles', 'action'])
            ->make(true);
    }

    public function assignRevokePermission($id)
    {
        return view('users.assign-revoke-permission', compact('id'));
    }

    public function getUserPermissionsList()
    {
        $user_id = $_POST['user_id'];
        $user = User::find($user_id);
        $list = Permission::get();
        return Datatables::of($list)
            ->addIndexColumn()
            ->addColumn('assign_revoke', function ($list) use ($user) {
                $html = "";
                if(auth()->user()->can('000263')) {
                    if ($user->hasPermissionTo($list->name)) {
                        $html = '<a href="javascript:void(0)" data-toggle="tooltip" title="Revoke" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm revokePermission">Revoke</a>';
                    } else {
                        $html = '<a href="javascript:void(0)" data-toggle="tooltip" title="Assign" style="margin:2px" data-id="' . $list->id . '" class="btn btn-success btn-sm assignPermission">Assign</a>';
                    }
                }
                return $html;
            })
            ->rawColumns(['assign_revoke'])
            ->make(true);
    }

    public function assignPermissionToUser($permission_id)
    {
        try {
            $permission = Permission::find($permission_id);
            $user_id = $_POST['user_id'];
            $user = User::find($user_id);
            $user->givePermissionTo($permission);
        } catch (\Exception $e) {
            Session::flash('error', CommonFunction::showErrorPublic($e->getMessage()));
            return redirect()->back()->withInput();
        }
    }

    public function revokePermissionFromUser($permission_id)
    {
        try {
            $permission = Permission::find($permission_id);
            $user_id = $_POST['user_id'];
            $user = User::find($user_id);
            $user->revokePermissionTo($permission);
        } catch (\Exception $e) {
            Session::flash('error', CommonFunction::showErrorPublic($e->getMessage()));
            return redirect()->back()->withInput();
        }
    }

    public function changePassword()
    {
        return view('auth.change-password');
    }

    public function storeChangePassword(Request $request)
    {
        // Validate the input

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:8',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required|min:8',
        ], [
                'old_password.required' => 'Old password is required.',
                'old_password.min' => 'Old password must be at least 8 characters.',
                'new_password.required' => 'New password is required.',
                'new_password.min' => 'New password must be at least 8 characters.',
                'new_password.confirmed' => 'New password confirmation does not match.',
                'new_password_confirmation.required' => 'New password confirmation is required.',
                'new_password_confirmation.min' => 'New password confirmation must be at least 8 characters.',
            ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        try {
            // Get the authenticated user
            $user = Auth::user();

            if (!Hash::check($request->old_password, $user->password)) {
                $validator->getMessageBag()->add('old_password', 'Old password is incorrect.');
                return response()->json(['errors' => $validator->errors()]);
            }
            // Check if the new password is the same as the old password
            if (Hash::check($request->new_password, $user->password)) {
                $validator->getMessageBag()->add('new_password', 'New password cannot be the same as the old password.');
                return response()->json(['errors' => $validator->errors()]);
            }
            // Update the user's password
            $user->password = Hash::make($request->new_password);
            $user->is_password_change = 1;
            $user->password_changed_at = Carbon::now();
            $user->save();


            // Clear the intended URL from session
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

//            return redirect('/');

            // Redirect back with a success message
            return response()->json(['success' => true,'message' => 'Password Changed successfully.','logout'=>true]);
        }
        catch (Exception $e) {
            return response()->json(['success' => false,'message' => 'Password Not Changed.','logout'=> false]);
        }

    }
}
