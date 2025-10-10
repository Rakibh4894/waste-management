<?php

namespace App\Http\Controllers;

use App\Libraries\CommonFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

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
            $dataGrid = DB::table('permissions')
                ->leftJoin('permission_module', 'permissions.module_id', '=', 'permission_module.id')
                ->leftJoin('permission_menu', 'permissions.menu_id', '=', 'permission_menu.id')
                ->leftJoin('permission_sub_menu', 'permissions.sub_menu_id', '=', 'permission_sub_menu.id')
                ->select(
                    'permissions.*',
                    'permission_module.name as module_id',
                    'permission_menu.name as menu_id',
                    'permission_sub_menu.name as sub_menu_id',
                )
                ->get();
            return DataTables::of($dataGrid)
                ->addIndexColumn()
                ->addColumn('action', function ($list) {
                    // $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $list->id . '" title="Edit" class="edit btn btn-primary btn-sm editData">Edit</a>';
                    $btn = ' <a href="javascript:void(0)" data-toggle="tooltip" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData" id="sa-warning"><i class="ri-delete-bin-2-line"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $modules = DB::table('permission_module')->get();
        return view('permission.permissions.admin', compact('modules'));
    }
    public function create()
    {
        return view('permission.permissions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:7|unique:permissions,name',
            'name' => 'required|string|max:50',
            'module_id' => 'required|integer',
            'menu_id' => 'required|integer',
            'sub_menu_id' => 'required|integer',
            'description' => 'nullable|string',
        ], [
            'code.required' => 'Code is required.',
            'code.max' => 'Code must not exceed 7 characters.',
            'code.unique' => 'Code must be unique in the permissions table.',
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 50 characters.',
            'name.string' => 'Name must be a valid string.',
            'module_id.required' => 'Module is required.',
            'module_id.integer' => 'Module is required..',
            'menu_id.required' => 'Menu is required.',
            'menu_id.integer' => 'Menu is required..',
            'sub_menu_id.required' => 'Sub Menu is required.',
            'sub_menu_id.integer' => 'Sub Menu is required..',
            'description.string' => 'Description must be a valid string.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        $code = $request->input('code');
        $slug = $request->input('name');
        $description = $request->input('description');
        $module_id = $request->input('module_id');
        $menu_id = $request->input('menu_id');
        $sub_menu_id = $request->input('sub_menu_id');
        Permission::create(
            [
                'name' => $code,
                'slug' => $slug,
                'module_id' => $module_id,
                'menu_id' => $menu_id,
                'sub_menu_id' => $sub_menu_id,
                'description' => $description
            ]
        );
        return response()->json(['success' => 'Date saved successfully.']);
    }
    public function show($id)
    {
        $permission = Permission::find($id);
        return view('permission.permissions.show', compact('permission'));
    }

    public function edit($id)
    {
        $data  = Permission::find($id);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:7|unique:permissions,name',
            'name' => 'required|string',
            'module_id' => 'required|integer',
            'menu_id' => 'required|integer',
            'sub_menu_id' => 'required|integer',
            'description' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        $slug =  $request->input('name');
        $code =  $request->input('code');
        $module_id = $request->input('module_id');
        $menu_id = $request->input('menu_id');
        $sub_menu_id = $request->input('sub_menu_id');
        $description = $request->input('description');
        Permission::where('id', $request->data_id)->update([
            'name' => $code,
            'slug' => $slug,
            'module_id' => $module_id,
            'menu_id' => $menu_id,
            'sub_menu_id' => $sub_menu_id,
            'description' => $description
        ]);
        return response()->json(['success' => 'Data Update successfully.']);
    }

    public function destroy($id)
    {
        DB::table("permissions")->where('id', $id)->delete();
    }

    public function getList()
    {
        $list = Permission::get();
        return Datatables::of($list)
            ->addColumn('action', function ($list) {
                $btn = '<a href="' . url('permissions/edit') . "/" . $list->id . '" style="margin:2px" title="Edit" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>';
                $btn = $btn . ' <a href="javascript:void(0)" data-toggle="tooltip" style="margin:2px" data-id="' . $list->id . '" class="btn btn-danger btn-sm deleteData"><i class="fas fa-trash-alt"></i></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function generatePermission()
    {
        return view('permission.permissions.generate-permissions');
    }

    public function getUrlsList()
    {
        $list = CommonFunction::getUrslList();
        return Datatables::of($list)
            ->addColumn('singleChk', function ($list) {
                return '<input style="text-align: center;" type="checkbox" class="chkSingle">';
            })
            ->addColumn('url', function ($list) {
                return $list;
            })
            ->addColumn('action', function ($list) {
                return '<a href="javascript:void(0)" data-id="' . $list . '">Generate</a>';
            })
            ->rawColumns(['singleChk', 'action'])
            ->make(true);
    }


    public function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function get_string_last($string, $start)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, '', $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function permissionGeneration()
    {
        $controllersWithName = [];
        $controllers = [];
        $methods = [];
        foreach (Route::getRoutes()->getRoutes() as $key => $route) {
            $action = $route->getAction();
            if (array_key_exists('controller', $action)) {
                $classNameA = $this->get_string_between($action['controller'], "Controllers\\", 'Controller@');
                $className = substr($classNameA, strpos($classNameA, "\\") + 1);
                $method = substr($action['controller'], strpos($action['controller'], "@") + 1);
                if (!in_array($className, $controllersWithName)) {
                    $controllers[] = $className;
                }
                $methods[$className][] = $method;
                $methods[$className] = array_unique($methods[$className]);
            }
        }
        $controllers = array_unique($controllers);
        $controllers = array_filter($controllers, function ($var) {
            return (strpos($var, '\\') === false);
        });
        //$this->setPageTitle('Permission Generate', 'Generate new Permission');
        return view('permission.permissions.generate', compact('controllers', 'methods'));
    }

    public function storeGenerated(Request $request)
    {
        //validate Form Data
        $request->validate([
            'permissions' => "required",
            'groups' => "required",
        ]);
        $i = 0;
        foreach ($request->permissions as $name) {
            $permission = Permission::create(['name' => $name, 'group_name' => $request->groups[$i]]);
            $i++;
        }

        if ($permission) {
            return redirect('/permissions/admin')->with('success', 'Permissions added successfully!');
            //return $this->responseRedirect('admins.permissions.index', 'Permissions added successfully', 'success', false, false);
        } else {
            //redirect to create customer page with previous input
            return redirect('/permissions/permission-generation')->with('error', 'Error occurred while creating Permissions.');
            //return $this->responseRedirectBack('Error occurred while creating Permissions.', 'error', true, true);
        }
    }


    public function getMenu(Request $request)
    {
        $module_id = $request->module_id;
        $role_id = $request->role_id;

        $assignedpermission = DB::table('role_has_permissions')->where('role_id', $role_id)->pluck('permission_id')->all();


        $PermissionData = Permission::where('module_id', $module_id)->get();
        $grid = "";
        foreach ($PermissionData as $Permission) {
            $name = $Permission->slug;
            $code = $Permission->name;
            $Permission_id = $Permission->id;

            $checked = in_array($Permission_id, $assignedpermission) ? 'checked' : '';

            $grid .= "<div class='col-sm-2'><input type='checkbox' value='$Permission_id' $checked onclick='showAlert(this)'> $name ($code) </div>";
        }
        $data = DB::table('permission_menu')->where('module_id', $module_id)->get();
        return response()->json(['data' => $data, 'grid' => $grid]);
    }
    public function getMenubyId($id)
    {
        $module_id = $id;
        $data = DB::table('permission_menu')->where('module_id', $module_id)->get();
        return response()->json(['data' => $data,]);
    }
    public function getSubMenu(Request $request)
    {
        $module_id = $request->module_id;
        $role_id = $request->role_id;

        $assignedpermission = DB::table('role_has_permissions')->where('role_id', $role_id)->pluck('permission_id')->all();

        $PermissionData = Permission::where('menu_id', $module_id)->get();
        $grid = "";
        foreach ($PermissionData as $Permission) {
            $name = $Permission->slug;
            $code = $Permission->name;
            $Permission_id = $Permission->id;
            $checked = in_array($Permission_id, $assignedpermission) ? 'checked' : '';
            $grid .= "<div class='col-sm-2'><input type='checkbox' value='$Permission_id' $checked> $name ($code) </div>";
        }
        $data = DB::table('permission_sub_menu')->where('menu_id', $module_id)->get();
        return response()->json(['data' => $data, 'grid' => $grid]);
    }

    public function getSubMenuById($id)
    {
        $data = DB::table('permission_sub_menu')->where('menu_id', $id)->get();
        return response()->json(['data' => $data]);
    }

    public function getSubMenuPermission(Request $request)
    {
        $module_id = $request->module_id;
        $role_id = $request->role_id;

        $assignedpermission = DB::table('role_has_permissions')->where('role_id', $role_id)->pluck('permission_id')->all();
        $PermissionData = Permission::where('sub_menu_id', $module_id)->get();
        $grid = "";
        foreach ($PermissionData as $Permission) {
            $name = $Permission->slug;
            $code = $Permission->name;
            $Permission_id = $Permission->id;
            $checked = in_array($Permission_id, $assignedpermission) ? 'checked' : '';
            $grid .= "<div class='col-sm-2'><input type='checkbox' value='$Permission_id' $checked> $name ($code) </div>";
        }
        return response()->json(['grid' => $grid]);
    }
}
