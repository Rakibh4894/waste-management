<?php

namespace App\Http\Controllers\Hrm;

use App\Exports\EmployeeReportExport;
use App\Models\Config\Lookup;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Section;
use App\Models\Hrm\ShiftHead;
use App\Models\Hrm\SubSection;
use App\Http\Controllers\Controller;
use App\Models\Hrm\EmpShiftAssign;
use App\Models\Hrm\RecruitmentTempEmployee;
use App\Models\Hrm\SalaryBreakdown;
use App\Models\Hrm\SalarySetup;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use function Yajra\DataTables\Html\Editor\Fields\id;
use function Yajra\DataTables\Services\filename;
use Barryvdh\DomPDF\Facade\Pdf;
use FontLib\Table\Type\post;
use Session;



class EmployeeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:000099|000100|000101|000102', ['only' => ['admin']]);
        $this->middleware('permission:000099', ['only' => ['create']]);
        $this->middleware('permission:000100', ['only' => ['view']]);
        $this->middleware('permission:000101', ['only' => ['edit', 'update']]);
        $this->middleware('permission:000102', ['only' => ['delete']]);
        $this->middleware('permission:000240', ['only' => ['employeeReport', 'generateReport']]);
        $this->middleware('permission:000241', ['only' => ['salaryReport', 'salaryReportGenerate']]);
    }

    public function admin(Request $request)
    {
        if ($request->ajax()) {
            $dataGrid = DB::table('hrm_emp_basic_official as emp')
                ->select('emp.id', 'emp.manual_id_no as manual_id_no', 'emp.full_name as full_name', 'dl.title as designation_level_id', 'deg.title as designation_id', 'br.title as branch_id', 'dp.title as dept_id', 'sec.title as section_id', 'ss.title as sub_section_id', 'emp.status','ds.emp_file_path as photo')
                ->leftjoin('hrm_designation_level as dl', 'dl.id', '=', 'emp.designation_level_id')
                ->leftJoin('hrm_designation as deg', 'deg.id', '=', 'emp.designation_id')
                ->leftJoin('hrm_branch as br', 'br.id', '=', 'emp.branch_id')
                ->LeftJoin('hrm_dept as dp', 'dp.id', '=', 'emp.dept_id')
                ->LeftJoin('hrm_section as sec', 'sec.id', '=', 'emp.section_id')
                ->LeftJoin('hrm_sub_section as ss', 'ss.id', '=', 'emp.sub_section_id')
                ->leftJoin('hrm_emp_document as ds', function($join) {
                    $join->on('ds.emp_id', '=', 'emp.id')
                        ->where('ds.document_name', '=', 'profile picture');
                });


            return DataTables::of($dataGrid)
                ->addIndexColumn()
                ->editColumn('emp.status', function ($dataGrid) {
                    if ($dataGrid->status == '1')
                        return 'Active';
                    if ($dataGrid->status == '2')
                        return 'Inactive';
                    return 'Cancel';
                })

                ->addColumn('photo', function ($row) {
                    if (!empty($row->photo)) {
                        $photoUrl = url($row->photo);
                    } else {
                        $photoUrl = url('app_assets/images/profile.jpg');
                    }
                    $photo = '<img src="' . $photoUrl . '" alt="Image" class="img-fluid">';
                    return $photo;
                })

                // ->addColumn('action', function ($row) {
                //     $btn = '<a href="' . url('employee/view') . "/" . $row->id . '" data-toggle="tooltip"  data-id="' . $row->id . '" title="View" class="edit btn btn-primary btn-sm PViewData">Profile View</a>';
                //     $btn = $btn . '<a href="' . url('employee/view') . "/" . $row->id . '" data-toggle="tooltip"  data-id="' . $row->id . '" class="btn btn-danger btn-sm DViewData">Document View</a>';
                //     return $btn;
                // })
                ->addColumn('operations', function ($row) {
                    $btn1 =auth()->user()->can('000101') ? '<a href="javascript:void(0)" data-toggle="tooltip" data-id ="' . $row->id . '" class="approved btn btn-primary btn-sm statusData" title="Update status"><i class="ri-user-follow-fill"></i></i></a>' : '';

                    $btn1 .= auth()->user()->can('000101') ? '<a href="' . url('employee/edit') . "/" . $row->id . '" data-toggle="tooltip" data-id="' . $row->id . '" title="Edit" class="edit btn btn-primary btn-sm editData" style="margin-right: 0px !important;"><i class="ri-edit-box-line"></i></a>' : '';
//                    $btn1 .= auth()->user()->can('000102') ? ' <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '"title="Delete" class="btn btn-danger btn-sm deleteData"><i class="ri-delete-bin-2-line"></i></a>' : '';
                    $btn1 .= auth()->user()->can('000100') ? ' <a href="' . url('employee/view') . "/" . $row->id . '" data-toggle="tooltip" data-id="' . $row->id . '" title="View" class="edit btn btn-primary btn-sm PViewData"><i class="fa fa-eye" aria-hidden="true"></i></a>' : '';
                    // $btn1 .= ' <a href="' . url('employee/document') . "/" . $row->id . '" data-toggle="tooltip" data-id="' . $row->id . '" class="btn btn-danger btn-sm DViewData">Document View</a>';
                    return $btn1;
                })
                ->filter(function ($query) {
                    $search = request('search')['value'];
                    // Map "active" and "inactive" to their corresponding database values

                    if ($search) {
                        $query->where(function ($query) use ($search) {
                            $statusMapping = [
                                'active' => 1,
                                'inactive' => 2,
                            ];
                            $query->whereRaw("LOWER(emp.manual_id_no) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(emp.full_name) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(dl.title) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(deg.title) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(br.title) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(dp.title) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(sec.title) LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("LOWER(ss.title) LIKE ?", ["%{$search}%"]);
                            // Add condition for emp.status
                            if (array_key_exists(strtolower($search), $statusMapping)) {
                                $query->orWhere('emp.status', $statusMapping[strtolower($search)]);
                            }
                        });
                    }
                })
                ->rawColumns(['photo', 'operations'])
                ->order(function ($query){ $query->orderBy('emp.id', 'desc')->groupBy('emp.id'); })
                ->make(true);
        }
        return view('hrm.employee.admin');
    }
    public function create()
    {
        $employee = '';
        $empAddress = null;
        $empNominee = null;
        $entry_type = 1;
        return view('hrm.employee.create2', compact('employee', 'empAddress', 'empNominee', 'entry_type'));
//        return view('hrm.employee.create', compact('employee', 'empAddress', 'empNominee', 'entry_type'));
    }

    public function save(Request $request)
    {

        $rules = [
            'full_name' => 'required|string|max:255',
            'nick_name' => 'nullable|string|max:255',
            'gender' => 'required|integer',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|integer',
            'religion' => 'nullable|integer',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|integer',
            'spouse_address' => 'nullable|string|max:255',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_occupation' => 'nullable|integer',
            'emergency_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'marital_status' => 'nullable|integer',
            'offical_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'personal_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'secondary_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'payment_mobile_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'personal_email' => 'nullable|email|max:255',
            'official_email' => 'nullable|email|max:255',
            'nationality' => 'nullable|integer',
            'blood_group' => 'nullable|integer',
            'salary_payment_mode' => 'nullable|integer',
            'bank_ac_no' => 'nullable',
            'nid' => 'nullable|string|max:25',
            'passport_no' => 'nullable|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'driving_license_no' => 'nullable|string|max:255',
            'birth_certificate_no' => 'nullable|string|max:255',
            'entry_type' => 'required|integer',
            'company_id' => 'required|integer|exists:hrm_company,id',
            'branch_id' => 'required|integer|exists:hrm_branch,id',
            'dept_id' => 'required|integer|exists:hrm_dept,id',
            'section_id' => 'nullable|integer',
            'sub_section_id' => 'nullable|integer',
            'staff_cat_id' => 'nullable|integer',
            'nominee_upazilla' => 'nullable|integer',
            'designation_level_id' => 'required|integer',
            'reporting_officer_id' => 'nullable|integer',
            'employee_type' => 'required|integer',
            'observation_days' => 'nullable|integer',
            'joinning_date' => 'required|date_format:Y-m-d',
            'confirmation_date' => 'nullable|date',
            'designation_id' => 'required|integer|exists:hrm_designation,id',
            'gross_salary' => 'required|numeric',
            'present_division_id' => 'required|integer',
            'present_district_id' => 'required|integer',
            'present_upazilla_id' => 'required|integer',
            'present_police_station' => 'required|integer',
            'present_post_office' => 'required|integer',
            'bank_payment' => 'nullable|integer',
            'cash_payment' => 'nullable|integer',
            'facilities' => 'nullable',
            'temp_education_level.*' => 'required',
            'temp_education_institude.*' => 'nullable|string|max:255',
            'temp_education_passing_year.*' => 'nullable|numeric',
            'temp_trainning_start_date.*' => 'nullable|date_format:Y-m-d',
            'temp_trainning_end_date.*' => 'nullable|date_format:Y-m-d',
            'temp_family_dob.*' => 'nullable|date_format:Y-m-d',
        ];

        $messages = [
            'full_name.required' => 'Full name is required.',
            'gender.required' => 'Gender is required.',
            'entry_type.required' => 'Entry type is required.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'The selected company is invalid.',
            'branch_id.required' => 'Branch is required.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'dept_id.required' => 'Department is required.',
            'dept_id.exists' => 'The selected department is invalid.',
            'designation_level_id.required' => 'Designation level is required.',
            'offical_contact_no.regex' => 'The official contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'birth_date.date_format' => 'The birth date must be in the format YYYY-MM-DD.',
            'personal_contact_no.regex' => 'The personal contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'secondary_contact_no.regex' => 'The secondary contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'employee_type.required' => 'Employee type is required.',
            'joinning_date.required' => 'Joining date is required.',
            'joinning_date.*.date_format' => 'Joining date must be in the format Y-m-d.',
            'designation_id.required' => 'Designation is required.',
            'designation_id.exists' => 'The selected designation is invalid.',
            'gross_salary.required' => 'Gross salary is required.',
            'present_division_id.required' => 'Present division is required.',
            'present_district_id.required' => 'Present district is required.',
            'present_upazilla_id.required' => 'Present upazilla is required.',
            'present_police_station.required' => 'Present police station is required.',
            'present_post_office.required' => 'Present post office is required.',
            'temp_education_level.*.required' => 'Education level is required.',
            'temp_education_institude.*.max' => 'Education institute must not exceed 255 characters.',
            'temp_education_passing_year.*.numeric' => 'Passing year must be a number.',
            'temp_trainning_start_date.*.date_format' => 'Training start date must be in the format Y-m-d.',
            'temp_trainning_end_date.*.date_format' => 'Training end date must be in the format Y-m-d.',
            'temp_family_dob.*.date_format' => 'Family date of birth must be in the format Y-m-d.',
        ];

        $validatedData =$request->validate($rules, $messages);

        $branch = Branch::where('id', $request->branch_id)->first();
        $joiningDate = $request->joinning_date;

//        if ($joiningDate) {
//            $year = date('y', strtotime($joiningDate)); // Extract 2-digit year
//            $month = date('m', strtotime($joiningDate)); // Extract 2-digit month
//
//            // Fetch the last manual ID for the same year and month
//            $existingMaxManualId = DB::table('hrm_emp_basic_official')
//                ->where('manual_id_no', 'LIKE', "{$year}{$month}%")
//                ->orderBy('manual_id_no', 'desc')
//                ->value('manual_id_no');
//
//            if ($existingMaxManualId) {
//                $lastNumber = (int) substr($existingMaxManualId, -3); // Extract the last 3 digits
//                $newNumber = $lastNumber + 1; // Increment the sequence
//            } else {
//                $newNumber = 1; // Start from 001 for the first employee of the month
//            }
//
//            $manualIdNo = $year . $month . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
//        }
        // $empID = $branch->code . '-' . IdGenerator::generate(['table' => 'hrm_emp_basic_official', 'length' => 7, 'prefix' => date('y')]);


        $result = Employee::processJoiningDateAndGenerateManualId($joiningDate);
        $manualIdNo = $result['manual_id_no'];

        try {
            // employee basicInfo
            DB::beginTransaction();
            $id = Employee::insertGetId([
                'full_name' => $validatedData['full_name'],
                'nick_name' => $validatedData['nick_name'] ?? null,
                'manual_id_no' =>$manualIdNo,
                'gender' => $validatedData['gender'],
                'father_name' => $validatedData['father_name'] ?? null,
                'mother_name' => $validatedData['mother_name'] ?? null,
                'spouse_name' => $validatedData['spouse_name'] ?? null,
                'spouse_occupation' => $validatedData['spouse_occupation'] ?? null,
                'father_occupation' => $validatedData['father_occupation'] ?? null,
                'mother_occupation' => $validatedData['mother_occupation'] ?? null,
                'spouse_address' => $validatedData['spouse_address'] ?? null,
                'personal_contact_no' => $validatedData['personal_contact_no'],
                'secondary_contact_no' => $validatedData['secondary_contact_no'] ?? null,
                'offical_contact_no' => $validatedData['offical_contact_no'] ?? null,
                'emergency_contact_no' => $validatedData['emergency_contact_no'] ?? null,
                'personal_email' => $validatedData['personal_email'] ?? null,
                'official_email' => $validatedData['official_email'] ?? null,
                'blood_group' => $validatedData['blood_group'] ?? null,
                'tin_no' => $validatedData['tin_no'] ?? null,
                'passport_no' => $validatedData['passport_no'] ?? null,
                'religion' => $validatedData['religion'] ?? null,
                'nid' => $validatedData['nid'] ?? null,
                'birth_date' => $validatedData['birth_date'] ?? null,
                'birth_certificate_no' => $validatedData['birth_certificate_no'] ?? null,
                'marital_status' => $validatedData['marital_status'] ?? null,
                'dob' => $validatedData['dob'] ?? null,
                'nationality' => $validatedData['nationality'] ?? null,
                'driving_license_no' => $validatedData['driving_license_no'] ?? null,
                'company_id' => $validatedData['company_id'],
                'branch_id' => $validatedData['branch_id'],
                'dept_id' => $validatedData['dept_id'],
                'section_id' => $validatedData['section_id'] ?? null,
                'sub_section_id' => $validatedData['sub_section_id'] ?? null,
                'employee_type' => $validatedData['employee_type'],
                'reporting_officer_id' => $validatedData['reporting_officer_id'] ?? null,
                'designation_level_id' => $validatedData['designation_level_id'] ?? null,
                'staff_cat_id' => $validatedData['staff_cat_id'] ?? null,
                'designation_id' => $validatedData['designation_id'],
                'joinning_date' => $validatedData['joinning_date'],
                'confirmation_date' => $validatedData['confirmation_date'] ?? null,
                'observation_days' => $validatedData['observation_days'] ?? null,
                'salary_payment_mode' => $validatedData['salary_payment_mode'],
                'bank_ac_no' => $validatedData['bank_ac_no'] ?? null,
                'payment_mobile_no' => $validatedData['payment_mobile_no'] ?? null,
                'cash_payment' => $validatedData['cash_payment'] ?? null,
                'bank_payment' => $validatedData['bank_payment'] ?? null,
                'gross_salary' => $validatedData['gross_salary'],
                'facilities' => $validatedData['facilities'] ?? null,
                'status' => 1,
                'entry_type' => $validatedData['entry_type'] ?? null,
                'created_at' => Carbon::now(),
                'created_by' => Auth::user()->id,
            ]);

            if($validatedData['entry_type'] == 3){
                $recruit_temp_employee_id = $_POST['recruit_temp_employee_id'];
                RecruitmentTempEmployee::where('id', $recruit_temp_employee_id)->update([
                    'is_emp' => 1
                ]);
            }


            if ($id && $validatedData['designation_id'] > 0 && $validatedData['gross_salary']  > 0) {
                $designationData = Designation::where('id', $validatedData['designation_id'])->first();
                if ($designationData) {
                    $salaryHead = "";
                    if ($designationData->grade_id > 0) {
                        $salaryHead = SalaryBreakdown::where('grade_id', $designationData->grade_id)->get();
                        if ($salaryHead) {
                            if ($designationData->id) {
                                $SalaryHead = SalaryBreakdown::where('designation_id', $designationData->id)->get();
                            }
                        }
                    }
                    if ($salaryHead) {
                        foreach ($salaryHead as $item) {
                            $amount = ($item->percentage * $validatedData['gross_salary']) / 100;
                            SalarySetup::insert([
                                'emp_id' => $id,
                                'emp_name' =>  $validatedData['full_name'] . $validatedData['nick_name'],
                                'branch_id' =>  $validatedData['branch_id'],
                                'designation_id' => $validatedData['designation_id'],
                                'break_down_type_id' => $item->break_down_type,
                                'salary_head_id' => $item->salary_head_id,
                                'percentage' => $item->percentage,
                                'amount' => $amount,
                                'basic' => $validatedData['gross_salary'],
                                'effective_date' => Carbon::now(),
                                'created_by' => auth()->user()->id,
                                'created_at' => Carbon::now(),
                            ]);
                        }
                    }
                }
            }
            //employee Address
            $result1 = DB::table('hrm_emp_address')->insert([
                'emp_id' => $id,
                'present_division_id' => $request->present_division_id,
                'present_district_id' => $request->present_district_id,
                'present_upazilla_id' => $request->present_upazilla_id,
                'present_post_office' => $request->present_post_office,
                'present_police_station' => $request->present_police_station,
                'present_union_id' => $request->present_union_id,
                'present_village' => $request->present_village,
                'present_ward_no' => $request->present_ward_no,
                'present_road_no' => $request->present_road_no,
                'present_house_no' => $request->present_house_no,
                'permanant_division_id' => $request->presentaspermanent ==1?$request->present_division_id:$request->permanent_division_id,
                'permanent_district_id' => $request->presentaspermanent ==1?$request->present_district_id: $request->permanent_district_id,
                'permanent_upazilla_id' => $request->presentaspermanent ==1?$request->present_upazilla_id: $request->permanent_upazilla_id,
                'permanent_union_id' => $request->presentaspermanent ==1?$request->present_union_id: $request->permanent_union_id,
                'permanent_village' => $request->presentaspermanent ==1?$request->present_village: $request->permanent_village,
                'permanent_post_office' => $request->presentaspermanent ==1?$request->present_post_office: $request->permanent_post_office,
                'permanent_police_station' => $request->presentaspermanent ==1?$request->present_police_station: $request->permanent_police_station,
                'permanent_ward_no' => $request->presentaspermanent ==1?$request->present_ward_no: $request->permanent_ward_no,
                'permanent_road_no' => $request->presentaspermanent ==1?$request->present_road_no: $request->permanent_road_no,
                'permanent_house_no' => $request->presentaspermanent ==1?$request->present_house_no: $request->permanent_house_no,
                'care_of' => $request->co_id,
                'alternate_village' => $request->alternate_village,
                'alternate_house' => $request->alternate_house,
                'alternate_road_no' => $request->alternate_road_no,
                'alternate_division_id' => $request->alternate_division,
                'alternate_district_id' => $request->alternate_district,
                'alternate_details' => $request->alternate_details,
                'land_phone' => $request->alternate_phone,
                'alternate_mobile_no' => $request->alternate_mobile,


            ]);


            //employee education
            if (!is_null($request->temp_education_level)) {
                foreach ($request->temp_education_level as $i => $value) {
                    $result = DB::table('hrm_emp_education')->insert([
                        'emp_id' => $id,
                        'level' => $request->temp_education_level[$i],
                        'degree' => $request->temp_education_degree[$i],
                        'board' => $request->temp_education_board[$i],
                        'institude' => $request->temp_education_institude[$i],
                        'group' => $request->temp_education_group[$i],
                        'passing_year' => $request->temp_education_passing_year[$i],
                        'result' => $request->temp_education_result[$i],
                        'grade' => $request->temp_education_grading[$i],
                        'duration' => $request->temp_education_duration[$i],
                    ]);
                }
            }



            //employee training.
            if (!is_null($request->temp_trainning_title)) {
                foreach ($request->temp_trainning_title as $i => $value) {
                    $result = DB::table('hrm_emp_trainning')->insert([
                        'emp_id' => $id,
                        'title' => $request->temp_trainning_title[$i],
                        'topics' => $request->temp_trainning_topics_covered[$i],
                        'institude' => $request->temp_trainning_institude[$i],
                        'start_date' => $request->temp_trainning_start_date[$i],
                        'end_date' => $request->temp_trainning_end_date[$i],
                        'duration' => $request->temp_trainning_duration[$i],
                        'location' => $request->temp_trainning_location[$i],
                        'course_type' => $request->temp_trainning_course_type[$i],
                        'trainning_name' => $request->temp_trainning_name[$i],
                    ]);
                }
            }

            //employee Experience
            if (!is_null($request->temp_experience_company)) {
                foreach ($request->temp_experience_company as $i => $value) {
                    $result = DB::table('hrm_emp_experience')->insert([
                        'emp_id' => $id,
                        'company' => $request->temp_experience_company[$i],
                        'start_date' => $request->temp_experience_start_date[$i],
                        'end_date' => $request->temp_experience_end_date[$i],
                        'duration' => $request->temp_experience_duration[$i],
                        'responsibility' => $request->temp_experience_responsibility[$i],
                        'pre_salary' => $request->temp_experience_pre_salary[$i],
                        'designation' => $request->tempdesignation[$i],
                        'resign_reason' => $request->temp_experience_resign_reason[$i],
                    ]);
                }
            }


            //employee Skill
            if (!is_null($request->temp_skills)) {
                foreach ($request->temp_skills as $i => $value) {
                    $result = DB::table('hrm_emp_skill')->insert([
                        'emp_id' => $id,
                        'skill' => $request->temp_skills[$i],
                    ]);
                }
            }


            // employee family
            if (!is_null($request->temp_family_name)) {
                foreach ($request->temp_family_name as $i => $value) {
                    $result = DB::table('hrm_emp_family')->insert([
                        'emp_id' => $id,
                        'name' => $request->temp_family_name[$i],
                        'gender' => $request->temp_family_gender[$i],
                        'relation' => $request->temp_family_relation[$i],
                        'nid' => $request->temp_family_nid[$i],
                        'bc' => $request->temp_family_bc[$i],
                        'dob' => $request->temp_family_dob[$i],
                        'contact_number' => $request->temp_family_contact_number[$i],
                        'emergency_contact' => $request->temp_family_emergency_contact[$i],
                    ]);
                }
            }


            //employee nominee
            if (!is_null($request->nominee_name)) {
                $result = DB::table('hrm_emp_nominee')->insert([
                    'emp_id' => $id,
                    'name' => $request->nominee_name,
                    'gender' => $request->nominee_gender,
                    'dob' => $request->nominee_dob,
                    'district' => $request->nominee_district,
                    'police_station' => $request->nominee_police_station,
                    'post_office' => $request->nominee_post_office,
                    'village' => $request->nominee_village,
                    'nid' => $request->nominee_nid,
                    'relationship' => $request->nominee_relationship,
                    'division' => $request->nominee_division,
                    'upazila' => $request->nominee_upazilla,
                    'house_no' => $request->nominee_house,
                    'contact_no' => $request->nominee_contact,
                    'bc' => $request->nominee_birth_certificate,
                ]);
            }

            //employee documentation
            if (!is_null($request->temp_emp_file)) {
                foreach ($request->temp_emp_file as $i => $value) {
                    $result = DB::table('hrm_emp_document')->insert([
                        'emp_id' => $id,
                        'document_name' => $request->temp_document_name[$i],
                        'emp_file_path' => 'upload/employee/' . $request->temp_emp_file[$i],
                    ]);
                }
            }

            if ($id && $manualIdNo) {
                // Create the user
                $user_name = strtolower(str_replace(" ", "", $validatedData['full_name']));
                $user = new User();
                $user->username = $user_name;
                $user->email = $validatedData['official_email'] ?? null;
                $user->password = FacadesHash::make('12345678');
                $user->employee_id = $id;
                $user->pin = $manualIdNo;
                $user->save();
            }

            // Step 3: Commit the transaction
            DB::commit();
//            if ($result1) {
//                Session::flash('message', "Employee Information Save Successfully");
////                return redirect('/employee/admin');
//            }
            return response()->json([
                'success' => true,
                'message' => 'Employee Information Save Successfully.'
            ]);
//            return redirect('/employee/admin')->with('message', 'Employee information saved successfully.');
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Employee Information not  Saved.'
            ]);
//            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $ex->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $employee = Employee::leftJoin('hrm_emp_document', 'hrm_emp_document.emp_id', '=', 'hrm_emp_basic_official.id',)
            ->leftJoin('hrm_emp_skill', 'hrm_emp_basic_official.id', '=', 'hrm_emp_skill.emp_id')
            ->where('hrm_emp_basic_official.id', $id)
            ->select(
                'hrm_emp_basic_official.*',
                'hrm_emp_document.*',
                'hrm_emp_skill.*'
            )
            ->first();
        $empEdu = DB::table('hrm_emp_education')->where('emp_id', $id)->get();
        $empTrain = DB::table('hrm_emp_trainning')->where('emp_id', $id)->get();
//        $empExp = DB::table('hrm_emp_experience')->where('emp_id', $id)->get();
        $empExp =  DB::table('hrm_emp_experience')
            ->leftJoin('lookups as l1', function($join) {
                $join->on('l1.code', '=', 'hrm_emp_experience.designation')
                    ->where('l1.type', '=', 'emp_exp'); // Filter by type 'emp_exp'
            })
            ->where('emp_id', $id)
            ->select('hrm_emp_experience.*', 'l1.name as designation_name')
            ->get();
        $empFamily = DB::table('hrm_emp_family')->where('emp_id', $id)->get();
        $emmSkill = DB::table('hrm_emp_skill')->where('emp_id', $id)->get();
        $empAddress = DB::table('hrm_emp_address')->where('emp_id', $id)->first();
        $empNominee = DB::table('hrm_emp_nominee')->where('emp_id', $id)->first();
        $empDocument = DB::table('hrm_emp_document')->where('emp_id', $id)->get();
        $employeeId = $id;
        $entry_type = $employee->entry_type;

//        return view('hrm.employee.create', compact('employee', 'empEdu', 'empTrain', 'empExp', 'empFamily','emmSkill','empAddress','empNominee','entry_type','employeeId','empDocument'));
        return view('hrm.employee.create2', compact('employee', 'empEdu', 'empTrain', 'empExp', 'empFamily','emmSkill','empAddress','empNominee','entry_type','employeeId','empDocument'));
    }


    public function update(Request $request)
    {

        $rules = [
            'full_name' => 'required|string|max:255',
            'nick_name' => 'nullable|string|max:255',
            'gender' => 'required|integer',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|integer',
            'religion' => 'nullable|integer',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|integer',
            'spouse_address' => 'nullable|string|max:255',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_occupation' => 'nullable|integer',
            'emergency_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'marital_status' => 'nullable|integer',
            'offical_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'personal_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'secondary_contact_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'payment_mobile_no' => 'nullable|regex:/^(\+?[0-9]{1,15})$/',
            'personal_email' => 'nullable|email|max:255',
            'official_email' => 'nullable|email|max:255',
            'nationality' => 'nullable|integer',
            'blood_group' => 'nullable|integer',
            'salary_payment_mode' => 'nullable|integer',
            'bank_ac_no' => 'nullable',
            'nid' => 'nullable|string|max:25',
            'passport_no' => 'nullable|string|max:255',
            'tin_no' => 'nullable|string|max:255',
            'driving_license_no' => 'nullable|string|max:255',
            'birth_certificate_no' => 'nullable|string|max:255',
            'entry_type' => 'required|integer',
            'company_id' => 'required|integer|exists:hrm_company,id',
            'branch_id' => 'required|integer|exists:hrm_branch,id',
            'dept_id' => 'required|integer|exists:hrm_dept,id',
            'section_id' => 'nullable|integer',
            'sub_section_id' => 'nullable|integer',
            'staff_cat_id' => 'nullable|integer',
            'nominee_upazilla' => 'nullable|integer',
            'designation_level_id' => 'required|integer',
            'reporting_officer_id' => 'nullable|integer',
            'employee_type' => 'required|integer',
            'observation_days' => 'nullable|integer',
            'joinning_date' => 'required|date_format:Y-m-d',
            'confirmation_date' => 'nullable|date',
            'designation_id' => 'required|integer|exists:hrm_designation,id',
            'gross_salary' => 'required|numeric',
            'present_division_id' => 'required|integer',
            'present_district_id' => 'required|integer',
            'present_upazilla_id' => 'required|integer',
            'present_police_station' => 'required|integer',
            'present_post_office' => 'required|integer',
            'bank_payment' => 'nullable|integer',
            'cash_payment' => 'nullable|integer',
            'facilities' => 'nullable',
            'temp_education_level.*' => 'required',
            'temp_education_institude.*' => 'nullable|string|max:255',
            'temp_education_passing_year.*' => 'nullable|numeric',
            'temp_trainning_start_date.*' => 'nullable|date_format:Y-m-d',
            'temp_trainning_end_date.*' => 'nullable|date_format:Y-m-d',
            'temp_family_dob.*' => 'nullable|date_format:Y-m-d',
        ];

        $messages = [
            'full_name.required' => 'Full name is required.',
            'gender.required' => 'Gender is required.',
            'entry_type.required' => 'Entry type is required.',
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'The selected company is invalid.',
            'branch_id.required' => 'Branch is required.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'dept_id.required' => 'Department is required.',
            'dept_id.exists' => 'The selected department is invalid.',
            'offical_contact_no.regex' => 'The official contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'birth_date.date_format' => 'The birth date must be in the format YYYY-MM-DD.',
            'personal_contact_no.regex' => 'The personal contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'secondary_contact_no.regex' => 'The secondary contact number must be a valid phone number with up to 15 digits and may include a leading +.',
            'designation_level_id.required' => 'Designation level is required.',
            'employee_type.required' => 'Employee type is required.',
            'joinning_date.required' => 'Joining date is required.',
            'joinning_date.*.date_format' => 'Joining date must be in the format Y-m-d.',
            'designation_id.required' => 'Designation is required.',
            'designation_id.exists' => 'The selected designation is invalid.',
            'gross_salary.required' => 'Gross salary is required.',
            'present_division_id.required' => 'Present division is required.',
            'present_district_id.required' => 'Present district is required.',
            'present_upazilla_id.required' => 'Present upazilla is required.',
            'present_police_station.required' => 'Present police station is required.',
            'present_post_office.required' => 'Present post office is required.',
            'temp_education_level.*.required' => 'Education level is required.',
            'temp_education_institude.*.max' => 'Education institute must not exceed 255 characters.',
            'temp_education_passing_year.*.numeric' => 'Passing year must be a number.',
            'temp_trainning_start_date.*.date_format' => 'Training start date must be in the format Y-m-d.',
            'temp_trainning_end_date.*.date_format' => 'Training end date must be in the format Y-m-d.',
            'temp_family_dob.*.date_format' => 'Family date of birth must be in the format Y-m-d.',
        ];

        $validatedData =$request->validate($rules, $messages);
//        dd($request);

        try {
            DB::beginTransaction();
            // employee basicInfo
            $id = Employee::where('id', $request->id)->update([
                'full_name' => $validatedData['full_name'],
                'nick_name' => $validatedData['nick_name'] ?? null,
                'gender' => $validatedData['gender'],
                'father_name' => $validatedData['father_name'] ?? null,
                'mother_name' => $validatedData['mother_name'] ?? null,
                'spouse_name' => $validatedData['spouse_name'] ?? null,
                'spouse_occupation' => $validatedData['spouse_occupation'] ?? null,
                'father_occupation' => $validatedData['father_occupation'] ?? null,
                'mother_occupation' => $validatedData['mother_occupation'] ?? null,
                'spouse_address' => $validatedData['spouse_address'] ?? null,
                'personal_contact_no' => $validatedData['personal_contact_no'],
                'secondary_contact_no' => $validatedData['secondary_contact_no'] ?? null,
                'offical_contact_no' => $validatedData['offical_contact_no'] ?? null,
                'emergency_contact_no' => $validatedData['emergency_contact_no'] ?? null,
                'personal_email' => $validatedData['personal_email'] ?? null,
                'official_email' => $validatedData['official_email'] ?? null,
                'blood_group' => $validatedData['blood_group'] ?? null,
                'tin_no' => $validatedData['tin_no'] ?? null,
                'passport_no' => $validatedData['passport_no'] ?? null,
                'religion' => $validatedData['religion'] ?? null,
                'nid' => $validatedData['nid'] ?? null,
                'birth_date' => $validatedData['birth_date'] ?? null,
                'birth_certificate_no' => $validatedData['birth_certificate_no'] ?? null,
                'marital_status' => $validatedData['marital_status'] ?? null,
                'dob' => $validatedData['dob'] ?? null,
                'nationality' => $validatedData['nationality'] ?? null,
                'driving_license_no' => $validatedData['driving_license_no'] ?? null,
                'company_id' => $validatedData['company_id'],
                'branch_id' => $validatedData['branch_id'],
                'dept_id' => $validatedData['dept_id'],
                'section_id' => $validatedData['section_id'] ?? null,
                'sub_section_id' => $validatedData['sub_section_id'] ?? null,
                'employee_type' => $validatedData['employee_type'],
                'reporting_officer_id' => $validatedData['reporting_officer_id'] ?? null,
                'designation_level_id' => $validatedData['designation_level_id'] ?? null,
                'staff_cat_id' => $validatedData['staff_cat_id'] ?? null,
                'designation_id' => $validatedData['designation_id'],
                'joinning_date' => $validatedData['joinning_date'],
                'confirmation_date' => $validatedData['confirmation_date'] ?? null,
                'observation_days' => $validatedData['observation_days'] ?? null,
                'salary_payment_mode' => $validatedData['salary_payment_mode'],
                'bank_ac_no' => $validatedData['bank_ac_no'] ?? null,
                'payment_mobile_no' => $validatedData['payment_mobile_no'] ?? null,
                'cash_payment' => $validatedData['cash_payment'] ?? null,
                'bank_payment' => $validatedData['bank_payment'] ?? null,
                'gross_salary' => $validatedData['gross_salary'],
                'facilities' => $validatedData['facilities'] ?? null,
                // 'status' => $validatedData['status'] ?? null,
                'updated_at' => Carbon::now(),
                'updated_by' => Auth::user()->id,
                'entry_type' => $validatedData['entry_type'] ?? null,


            ]);

            if ($id && $request->designation_id > 0 && $request->gross_salary > 0) {
                $designationData = Designation::where('id', $request->designation_id)->first();
                if ($designationData) {
                    $salaryHead = SalaryBreakdown::where('designation_id', $designationData->id)->where('status', 1)->get();
//                    dd(count($salaryHead));
                    if (count($salaryHead)==0) {
                        if ($designationData->grade_id > 0) {
                            $salaryHead = SalaryBreakdown::where('grade_id', $designationData->grade_id)->where('status', 1)->get();
                        }
                    }
                    if ($salaryHead) {
                        SalarySetup::where('emp_id', $request->id)->update([
                            'status' => 2
                        ]);
                        foreach ($salaryHead as $item) {
                            $amount = ($item->percentage * $request->gross_salary) / 100;
                            SalarySetup::insert([
                                'emp_id' => $request->id,
                                'emp_name' =>  $validatedData['full_name'] . $validatedData['nick_name'] ,
                                'branch_id' =>  $validatedData['branch_id'] ,
                                'designation_id' =>$validatedData['designation_id'],
                                'break_down_type_id' => $item->break_down_type,
                                'salary_head_id' => $item->salary_head_id,
                                'percentage' => $item->percentage,
                                'amount' => $amount,
                                'basic' => $validatedData['gross_salary'],
                                'effective_date' => Carbon::now(),
                                'created_by' => auth()->user()->id,
                                'created_at' => Carbon::now(),
                            ]);
                        }

                    }
                }
            }
            //employee Address

            $result1 = DB::table('hrm_emp_address')->updateOrInsert(
            // The condition to check for existing record
                ['emp_id' => $request->id],
                // The data to update or insert
                [
                    'emp_id' => $request->id,
                    'present_division_id' => $request->present_division_id,
                    'present_district_id' => $request->present_district_id,
                    'present_upazilla_id' => $request->present_upazilla_id,
                    'present_post_office' => $request->present_post_office,
                    'present_police_station' => $request->present_police_station,
                    'present_union_id' => $request->present_union_id,
                    'present_village' => $request->present_village,
                    'present_ward_no' => $request->present_ward_no,
                    'present_road_no' => $request->present_road_no,
                    'present_house_no' => $request->present_house_no,
                    'permanant_division_id' => $request->presentaspermanent == 1 ? $request->present_division_id : $request->permanent_division_id,
                    'permanent_district_id' => $request->presentaspermanent == 1 ? $request->present_district_id : $request->permanent_district_id,
                    'permanent_upazilla_id' => $request->presentaspermanent == 1 ? $request->present_upazilla_id : $request->permanent_upazilla_id,
                    'permanent_union_id' => $request->presentaspermanent == 1 ? $request->present_union_id : $request->permanent_union_id,
                    'permanent_village' => $request->presentaspermanent == 1 ? $request->present_village : $request->permanent_village,
                    'permanent_post_office' => $request->presentaspermanent == 1 ? $request->present_post_office : $request->permanent_post_office,
                    'permanent_police_station' => $request->presentaspermanent == 1 ? $request->present_police_station : $request->permanent_police_station,
                    'permanent_ward_no' => $request->presentaspermanent == 1 ? $request->present_ward_no : $request->permanent_ward_no,
                    'permanent_road_no' => $request->presentaspermanent == 1 ? $request->present_road_no : $request->permanent_road_no,
                    'permanent_house_no' => $request->presentaspermanent == 1 ? $request->present_house_no : $request->permanent_house_no,
                    'care_of' => $request->co_id,
                    'alternate_village' => $request->alternate_village,
                    'alternate_house' => $request->alternate_house,
                    'alternate_road_no' => $request->alternate_road_no,
                    'alternate_division_id' => $request->alternate_division,
                    'alternate_district_id' => $request->alternate_district,
                    'alternate_details' => $request->alternate_details,
                    'land_phone' => $request->alternate_phone,
                    'alternate_mobile_no' => $request->alternate_mobile,
                ]
            );


            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_education')
                    ->where('emp_id', $request->id)
                    ->delete();

                if (!is_null($request->temp_education_level)) {
                    foreach ($request->temp_education_level as $i => $value) {
                        if (!is_null($value)) {
                            DB::table('hrm_emp_education')->insert([
                                'emp_id' => $request->id,
                                'level' => $value,
                                'degree' => $request->temp_education_degree[$i] ?? null,
                                'board' => $request->temp_education_board[$i] ?? null,
                                'institude' => $request->temp_education_institude[$i] ?? null,
                                'group' => $request->temp_education_group[$i] ?? null,
                                'passing_year' => $request->temp_education_passing_year[$i] ?? null,
                                'result' => $request->temp_education_result[$i] ?? null,
                                'grade' => $request->temp_education_grading[$i] ?? null,
                                'duration' => $request->temp_education_duration[$i] ?? null,
                            ]);
                        }
                    }
                }
            });

            // //employee training.
            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_trainning')
                    ->where('emp_id', $request->id)
                    ->delete();
                if (!is_null($request->temp_trainning_title)) {
                    foreach ($request->temp_trainning_title as $i => $value) {
                        if (!is_null($value)) {
                            DB::table('hrm_emp_trainning')->insert([
                                'emp_id' => $request->id,
                                'title' => $value,
                                'topics' => $request->temp_trainning_topics_covered[$i] ?? null,
                                'institude' => $request->temp_trainning_institude[$i] ?? null,
                                'start_date' => $request->temp_trainning_start_date[$i] ?? null,
                                'end_date' => $request->temp_trainning_end_date[$i] ?? null,
                                'duration' => $request->temp_trainning_duration[$i] ?? null,
                                'location' => $request->temp_trainning_location[$i] ?? null,
                                'course_type' => $request->temp_trainning_course_type[$i] ?? null,
                                'trainning_name' => $request->temp_trainning_name[$i] ?? null,
                            ]);
                        }
                    }
                }
            });

            // //employee Experience
            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_experience')
                    ->where('emp_id', $request->id)
                    ->delete();
                if (!is_null($request->temp_experience_company)) {
                    foreach ($request->temp_experience_company as $i => $value) {
                        if (!is_null($value)) {
                            DB::table('hrm_emp_experience')->insert([
                                'emp_id' => $request->id,
                                'company' => $value,
                                'start_date' => $request->temp_experience_start_date[$i] ?? null,
                                'end_date' => $request->temp_experience_end_date[$i] ?? null,
                                'duration' => $request->temp_experience_duration[$i] ?? null,
                                'responsibility' => $request->temp_experience_responsibility[$i] ?? null,
                                'pre_salary' => $request->temp_experience_pre_salary[$i] ?? null,
                                'designation' => $request->tempdesignation[$i] ?? null,
                                'resign_reason' => $request->temp_experience_resign_reason[$i] ?? null,
                            ]);
                        }
                    }
                }
            });
            // employee skill
            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_skill')
                    ->where('emp_id', $request->id)
                    ->delete();
                if (!is_null($request->temp_skills)) {
                    foreach ($request->temp_skills as $i => $skill) {
                        if (!is_null($skill)) {
                            DB::table('hrm_emp_skill')->insert([
                                'emp_id' => $request->id,
                                'skill' => $skill,
                            ]);
                        }
                    }
                }
            });

            // employee family
            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_family')
                    ->where('emp_id', $request->id)
                    ->delete();
                if (!is_null($request->temp_family_name)) {
                    foreach ($request->temp_family_name as $i => $value) {
                        if (!is_null($value)) {
                            DB::table('hrm_emp_family')->insert([
                                'emp_id' => $request->id,
                                'name' => $value,
                                'gender' => $request->temp_family_gender[$i] ?? null,
                                'relation' => $request->temp_family_relation[$i] ?? null,
                                'nid' => $request->temp_family_nid[$i] ?? null,
                                'bc' => $request->temp_family_bc[$i] ?? null,
                                'dob' => $request->temp_family_dob[$i] ?? null,
                                'contact_number' => $request->temp_family_contact_number[$i] ?? null,
                                'emergency_contact' => $request->temp_family_emergency_contact[$i] ?? null,
                            ]);
                        }
                    }
                }
            });

            // //employee nominee
            DB::transaction(function () use ($request) {
                DB::table('hrm_emp_nominee')
                    ->where('emp_id', $request->id)
                    ->delete();
                if (!is_null($request->nominee_name)) {
                    DB::table('hrm_emp_nominee')->updateOrInsert(
                    // Condition to check for an existing record
                        [
                            'emp_id' => $request->id,
                            'nid' => $request->nominee_nid, // Assuming 'nid' is unique for nominees
                        ],
                        // Data to insert or update
                        [
                            'name' => $request->nominee_name,
                            'gender' => $request->nominee_gender,
                            'dob' => $request->nominee_dob,
                            'district' => $request->nominee_district,
                            'police_station' => $request->nominee_police_station,
                            'post_office' => $request->nominee_post_office,
                            'village' => $request->nominee_village,
                            'relationship' => $request->nominee_relationship,
                            'division' => $request->nominee_division,
                            'upazila' => $request->nominee_upazilla,
                            'house_no' => $request->nominee_house,
                            'contact_no' => $request->nominee_contact,
                            'bc' => $request->nominee_birth_certificate,
                        ]
                    );
                }

            });

            // //employee documentation
            DB::transaction(function () use ($request) {

                $empDocuments = DB::table('hrm_emp_document')->where('emp_id', $request->id)->get();

                // Loop through each employee document from the database

                foreach ($empDocuments as $empDocument) {
                    // Check if the current database file exists in the request file list
                    if(isset($request->temp_emp_file_old)){
                        if (!in_array(basename($empDocument->emp_file_path), $request->temp_emp_file_old)) {
                            if (file_exists($empDocument->emp_file_path)) {
                                unlink($empDocument->emp_file_path);
                            }
                            DB::table('hrm_emp_document')->where('id', $empDocument->id)->delete();
//                        array_push($stack, $empDocument->emp_file_path);
                        }
                    }
                    else{
                        if (file_exists($empDocument->emp_file_path)) {
                            unlink($empDocument->emp_file_path);
                        }
                        DB::table('hrm_emp_document')->where('id', $empDocument->id)->delete();
                    }

                }


                if (!is_null($request->temp_emp_file)) {
                    foreach ($request->temp_emp_file as $i => $file) {
                        if (!is_null($file)) {

                            DB::table('hrm_emp_document')->insert([
                                'emp_id' => $request->id,
                                'document_name' => $request->temp_document_name[$i] ?? null,
                                'emp_file_path' => 'upload/employee/' . $file,
                            ]);
                        }
                    }
                }
            });

            DB::commit();

//            Session::flash('message', "Employee Information Updated Successfully'");
//
//            return redirect('/employee/admin');
            return response()->json([
                'success' => true,
                'message' => 'Employee Information Updated Successfully.'
            ]);
        } catch (Exception $ex) {
            DB::rollback();
//            Toastr::success($ex, 'Success');
//            return redirect('/employee/admin');
            return response()->json([
                'success' => false,
                'message' => 'Employee Information not  Updated.'
            ]);
        }
    }

    public function delete($id)
    {

        try {
            Employee::where('id', $id)->delete();
            DB::table('hrm_emp_address')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_document')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_family')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_nominee')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_skill')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_education')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_experience')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_nominee')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_trainning')->where('emp_id', $id)->delete();
            DB::table('hrm_emp_document')->where('emp_id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Employee Deleted successfully.'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not Deleted'
            ]);
        }

    }

    public function deleteFile($id)
    {
        $fileData = DB::table('hrm_emp_document')
            ->find($id);

        if (isset($fileData)){
            if (file_exists($fileData->emp_file_path)){
                unlink($fileData->emp_file_path);
                DB::table('hrm_emp_document')->where('id',$id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'File Deleted.'
                ]);
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'File not  Deleted.'
                ]);
            }
        }
    }

    public function upload(Request $req)
    {
        if ($req->hasFile('emp_file')) {
            $path = url('upload/employee/');
            $file = $req->file('emp_file');
            $currentDate = \Carbon\Carbon::now();
            $year = $currentDate->year;
            $month = str_pad($currentDate->month, 2, '0', STR_PAD_LEFT);
            $day = str_pad($currentDate->day, 2, '0', STR_PAD_LEFT);
            $milliseconds = str_pad((int)($currentDate->micro / 1000), 3, '0', STR_PAD_LEFT);
            $originalFileName = $file->getClientOriginalName();
            $fileName = $year . $month . $day . '_' . $milliseconds . '_' . $originalFileName;
            $filePath = $file->move('public/upload/employee/', $fileName);
            return $fileName;
        }
    }
    public function searchEmployeeByNameOrID(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('hrm_emp_basic_official')
                ->where('full_name', 'LIKE', $request->emp . "%")
                ->orWhere('manual_id_no', 'LIKE', $request->emp . "%")
                ->limit(10)->get(['hrm_emp_basic_official.id','hrm_emp_basic_official.full_name','hrm_emp_basic_official.manual_id_no']);
            $output = '';
            if (count($data) > 0) {
                $output = '<ul style="position:absolute;top:5px;left:-24px;z-index: 1;width:101%;" class="custom-list" id="employee">';
                foreach ($data as $row) {
                    $output .= '<li style="" class="searchEmployee" value='.$row->id.'>' . $row->full_name . '(' . $row->manual_id_no . ')' . '</li>';
                }
                $output .= '</ul>';
            } else {
                $output .= null;
            }

            return $output;



        }
    }
    public function searchEmployeeByNameOrIDs(Request $request)
    {
        $request->validate([
            'sub_section_id' => 'required|array',
            'sub_section_id.*' => 'integer'
        ]);
        $sub_section_id  = $request->input('sub_section_id');
        $data = Employee::where('status', 1)->whereIn('sub_section_id', $sub_section_id)->get();
        return response()->json($data);
    }

    public function searchEmployeeByType(Request $request)
    {
        $data = '';
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        if ($request->type=='branch'){
            $branch_id  = $request->input('ids');
            $data = Employee::where('status', 1)->whereIn('branch_id', $branch_id)->get();
        }
        if ($request->type=='department'){
            $department_id  = $request->input('ids');
            $data = Employee::where('status', 1)->whereIn('dept_id', $department_id)->get();
        }
        if ($request->type=='section'){
            $section_id  = $request->input('ids');
            $data = Employee::where('status', 1)->whereIn('section_id', $section_id)->get();
        }
        if ($request->type=='department'){
            $sub_section_id  = $request->input('ids');
            $data = Employee::where('status', 1)->whereIn('sub_section_id', $sub_section_id)->get();
        }
        return response()->json($data);

    }

    public function getEmployeeByNameOrID(Request $request, $empID)
    {
        if ($request->ajax()) {
            $string = $request->textInput;
            $parts = explode("(", $string);
            $parts = explode(")", $parts[1]);
            $idNo = $parts[0];
//            $data = DB::table('hrm_emp_basic_official')->where('manual_id_no', $idNo)->first();
            $data = DB::table('hrm_emp_basic_official')->where('id', $empID)->first();
            if ($data) {
                $grade = '';
                $designationData = DB::table('hrm_designation')->where('id', $data->designation_id)->first();
                if ($designationData) {
                    $grade = $designationData->grade_id;
                }
                $dataEmpPromotion = DB::table('hrm_emp_promotion')->where('emp_id', $data->id)->latest('id')->first();
                $last_promotion_date = '';
                if ($dataEmpPromotion) {
                    $last_promotion_date = $dataEmpPromotion->current_promotion_date;
                }
                return response()->json(['last_promotion_date' => $last_promotion_date, 'confirmation_date' => $data->confirmation_date, 'photo' => $data->photo, 'joinning_date' => $data->joinning_date, 'emp_name' => $data->full_name,  'emp_id_no' => $data->manual_id_no, 'designation_id' => $data->designation_id, 'branch_id' => $data->branch_id, 'department_id' => $data->dept_id, 'section_id' => $data->section_id, 'sub_section_id' => $data->sub_section_id, 'grade_id' => $grade, 'emp_id' => $data->id]);
            }
        }
    }

    public function findEmployeeByAllFilterForEmpAttendence(Request $request)
    {
        $branchId = $request->branch_id;
        $dept_id = $request->dept_id;
        $section_id = $request->section_id;
        $sub_section_id = $request->sub_section_id;
        $entry_date = $request->entry_date;
        $in_time = $request->in_time;
        $out_time = $request->out_time;
        $data = '';
        if ($sub_section_id > 0) {
            $data = Employee::whereIn('sub_section_id', $sub_section_id)->get();
        } else if ($section_id > 0) {
            $data = Employee::whereIn('section_id', $section_id)->get();
        } else if ($dept_id > 0) {
            $data = Employee::whereIn('dept_id', $dept_id)->get();
        } else if ($branchId > 0) {
            $data = Employee::whereIn('branch_id', $branchId)->get();
        }else{
            $data = Employee::get();
        }
        $str = '';
        $i = 1;
        if (count($data) > 0) {
            foreach ($data as $dt) {

                $str .= '<tr>';
                $str .= '<td class="sno" style="text-align:center;width:4%">' . $i . '</td>';
                $str .= '<td style="width:5%"><input class="isChecked" checked type="checkbox" name="chkBox[]" ></td>';
                $str .= '<td style="width:11%">' . Branch::title($dt->branch_id) . '<input type="hidden" name="temp_branch_id[]" value=' . $dt->branch_id . '></td>';
                $str .= '<td style="width:11%">' . Department::title($dt->dept_id) . '<input type="hidden" name="temp_dept_id[]" value=' . $dt->dept_id . '></td>';
                $str .= '<td style="width:11%">' . Section::title($dt->section_id) . '<input type="hidden" name="temp_section_id[]" value=' . $dt->section_id . '></td>';
                $str .= '<td style="width:11%">' . SubSection::title($dt->sub_section_id) . '<input type="hidden" name="temp_sub_section_id[]" value=' . $dt->sub_section_id . '></td>';
                $str .= '<td style="width:11%">' . $dt->full_name . '<input type="hidden" name="temp_emp_id[]" value=' . $dt->id . '><input type="hidden" name="temp_device_id[]" value=' . $dt->device_id . '><input type="hidden" name="temp_designation_id[]" value=' . $dt->designation_id . '></td>';
                $str .= '<td style="width:11%">' . $entry_date . '<input type="hidden" name="temp_entry_date[]" value=' . $entry_date . '></td>';
                $str .= '<td style="width:11%">' . $in_time . '<input type="hidden" name="temp_in_time[]" value=' . $in_time . '></td>';
                $str .= '<td style="width:11%">' . $out_time . '<input type="hidden" name="temp_out_time[]" value=' . $out_time . '></td>';
                $str .= '</tr>';
                $i++;
            }
            return response()->json(['success' => true, 'html' => $str]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function findEmployeeByAllFilterForEmpShiftAssign(Request $request)
    {

        $branchId = $request->branch_id;
        $dept_id = $request->dept_id;
        $section_id = $request->section_id;
        $sub_section_id = $request->sub_section_id;
        $shift_head_id = $request->shift_head_id;
        $date_from = $request->date_from;
        $date_to = $request->date_to;
        $week = $request->week;
        $emp_id = $request->emp_id;
        $data_row = $i = $j = $request->data_row;

        $data = "";
        if ($emp_id > 0) {
            $data = Employee::where('id', $emp_id)->get();
        } else if (isset($sub_section_id) && count($sub_section_id) > 0) {
            $data = Employee::whereIn('sub_section_id', $sub_section_id)->get();
        } else if (isset($section_id) && count($section_id) > 0) {
            $data = Employee::whereIn('section_id', $section_id)->get();
        } else if (isset($dept_id) && count($dept_id) > 0) {
            $data = Employee::whereIn('dept_id', $dept_id)->get();
        } else if (isset($branchId) &&  count($branchId) > 0) {
            $data = Employee::whereIn('branch_id', $branchId)->get();
        }
        $firstGrid = '';
        $secondGrid = '';


        if ($data) {
            if ($data_row > 0) {
                $firstGrid .= '<tr>';
                $firstGrid .= '<td class="sno">' . ++$j . '</td>';
                $firstGrid .= '<td>' . EmpShiftAssign::weekTitle($week) . '<input type="hidden" id="week" name="temp_week[]" value=' . $week . '></td>';
                $firstGrid .= '<td>' . ShiftHead::title($shift_head_id) . '<input type="hidden" id="shift_head_id" name="temp_shift_head_id[]" value=' . $shift_head_id . '></td>';
                $firstGrid .= '</tr>';
            } else {
                $firstGrid .= '<tr>';
                $firstGrid .= '<td class="sno">' . ++$j . '</td>';
                $firstGrid .= '<td>' . EmpShiftAssign::weekTitle($week) . '<input type="hidden" id="week" name="temp_week[]" value=' . $week . '></td>';
                $firstGrid .= '<td>' . ShiftHead::title($shift_head_id) . '<input type="hidden" id="shift_head_id" name="temp_shift_head_id[]" value=' . $shift_head_id . '></td>';
                $firstGrid .= '</tr>';
                foreach ($data as $dt) {
                    $secondGrid .= '<tr>';
                    $secondGrid .= '<td class="sno">' . ++$i . '</td>'; // Keeping the same Sl No
                    $secondGrid .= '<td><input class="isChecked" checked type="checkbox" name="chkBox[]"></td>';
                    $secondGrid .= '<td>' . $dt->full_name . '<input type="hidden" name="temp_emp_id[]" id="emp_id" value=' . $dt->id . '></td>';
                    $secondGrid .= '<td>' . Branch::title($dt->branch_id) . '<input type="hidden" id="branch_id" name="temp_branch_id[]" value=' . $dt->branch_id . '></td>';
                    $secondGrid .= '<td>' . Department::title($dt->dept_id) . '<input type="hidden" id="dept_id" name="temp_dept_id[]" value=' . $dt->dept_id . '></td>';
                    $secondGrid .= '<td>' . Section::title($dt->section_id) . '<input type="hidden" id="section_id" name="temp_section_id[]" value=' . $dt->section_id . '></td>';
                    $secondGrid .= '<td>' . SubSection::title($dt->sub_section_id) . '<input type="hidden" id="sub_section_id" name="temp_sub_section_id[]" value=' . $dt->sub_section_id . '></td>';
                    $secondGrid .= '</tr>';
                }
            }
        }

        return response()->json(['firstGrid' => $firstGrid, 'secondGrid' => $secondGrid]);
    }

    public function findEmployeeByAllFilterForEmpIncrement(Request $request)
    {
        $emp_id = $request->emp_id;
        $branchId = $request->branch_id;
        $dept_id = $request->dept_id;
        $section_id = $request->section_id;
        $sub_section_id = $request->sub_section_id;
        $effective_month = $request->effective_month;
        $data = '';
        if ($sub_section_id > 0) {
            $data = Employee::whereIn('sub_section_id', $sub_section_id)->get();
        } else if ($section_id > 0) {
            $data = Employee::whereIn('section_id', $section_id)->get();
        } else if ($dept_id > 0) {
            $data = Employee::whereIn('dept_id', $dept_id)->get();
        } else if ($branchId > 0) {
            $data = Employee::whereIn('branch_id', $branchId)->get();
        }

        $str = '';
        $i = 1;
        if ($data) {
            foreach ($data as $dt) {
                $str .= '<tr>';
                $str .= '<td class="sno">' . $i . '</td>';
//                $str .= '<td><input class="isChecked" checked type="checkbox" name="chkBox' . $i . '[]" value="1"></td>';
                $str .= '<td><label for="chkBox'. $i .'" style="display:block;cursor:pointer"><input class="isChecked" checked type="checkbox" name="chkBox[]" onclick="chkBoxChange('. $i .')" id="chkBox'. $i .'" value="1"></label></td>';
                // $str .= '<td>' . Employee::title($emp_id) . '<input type="hidden" name="temp_recommanded_by[]" value=' . $emp_id . '></td>';
                $str .= '<td>' . $dt->full_name . '<input type="hidden" name="temp_recommanded_by[]" value=' . $emp_id . '><input type="hidden" name="temp_emp_id[]" value=' . $dt->id . '></td>';
                $str .= '<td>' . Designation::title($dt->designation_id) . '<input type="hidden" name="temp_designation_id[]" value=' . $dt->designation_id . '></td>';
                $str .= '<td>' . Branch::title($dt->branch_id) . '<input type="hidden" name="temp_branch_id[]" value=' . $dt->branch_id . '></td>';
                $str .= '<td>' . Department::title($dt->dept_id) . '<input type="hidden" name="temp_dept_id[]" value=' . $dt->dept_id . '></td>';
                $str .= '<td>' . Section::title($dt->section_id) . '<input type="hidden" name="temp_joinning_date[]" value=' . $dt->joinning_date . '><input type="hidden" name="temp_section_id[]" value=' . $dt->section_id . '></td>';
                $str .= '<td>' . SubSection::title($dt->sub_section_id) . '<input type="hidden" name="temp_sub_section_id[]" value=' . $dt->sub_section_id . '></td>';
                $str .= '<td>' . $effective_month . '<input type="hidden" name="temp_effective_month[]" value=' . $effective_month . '></td>';
                $str .= '<td><input class="w-100 form-control increment_amount" type="number" name="temp_increment_amount[]" onchange="incrementAmountChange('. $i .')" id="increment_amount' . $i . '"></td>';
                $str .= '<td><input class="w-100 form-control increment_percentage" type="number" name="temp_increment_percentage[]" onchange="incrementPercentageChange('. $i .')" id="increment_percentage' . $i . '"></td>';
                $str .= '</tr>';
                $i++;
            }
            return $str;
        } else {
            $str .= '<tr>';
            $str .= '<td> No data available </td>';
            $str .= '</tr>';
            return $str;
        }
    }

    public function view($id)
    {
        $employee = Employee::leftJoin('hrm_emp_document', 'hrm_emp_document.emp_id', '=', 'hrm_emp_basic_official.id',)
            ->leftJoin('hrm_emp_skill', 'hrm_emp_basic_official.id', '=', 'hrm_emp_skill.emp_id')
            ->where('hrm_emp_basic_official.id', $id)
            ->select(
                'hrm_emp_basic_official.*',
                'hrm_emp_document.*',
                'hrm_emp_skill.*'
            )
            ->first();
        $empEdu = DB::table('hrm_emp_education')->where('emp_id', $id)->get();
        $empTrain = DB::table('hrm_emp_trainning')->where('emp_id', $id)->get();
        $empExp = DB::table('hrm_emp_experience')->where('emp_id', $id)->get();
        $empFamily = DB::table('hrm_emp_family')->where('emp_id', $id)->get();
        $emmSkill = DB::table('hrm_emp_skill')->where('emp_id', $id)->get();
        $empAddress = DB::table('hrm_emp_address')->where('emp_id', $id)->first();
        $empNominee = DB::table('hrm_emp_nominee')->where('emp_id', $id)->first();
        $empDocument = DB::table('hrm_emp_document')->where('emp_id', $id)->get();
        $employeeId = $id;
        $entry_type = $employee->entry_type;
//        return view('hrm.employee.view',  compact('employee', 'empEdu', 'empTrain', 'empExp', 'empFamily','emmSkill','empAddress','empNominee','entry_type','employeeId','empDocument'));
        return view('hrm.employee.view2',  compact('employee', 'empEdu', 'empTrain', 'empExp', 'empFamily','emmSkill','empAddress','empNominee','entry_type','employeeId','empDocument'));
    }

    public function statusEdit($id)
    {
        $data = Employee::where('id', $id)->first();

        $status = "<option value=''>Select One</option>";
        if ($data->status == 1) {
            $status .= "<option value='1' selected>Active</option><option value='2'>InActive</option>";
        } else {
            $status .= "<option value='1'>Active</option><option value='2' selected>InActive</option>";
        }
        return response()->json(['data' => $data, 'status' => $status]);
    }

    public function statusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|int',
        ], [
            'status.required' => 'Status is required.',
            'status.int' => 'Status is required.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        try {
            DB::beginTransaction();
            Employee::where('id', $request->data_id)->update([
                'status' => $request->status,
                'updated_at' => Carbon::now(),
                'updated_by' => Auth::user()->id,
            ]);
            User::where('employee_id', $request->data_id)->update([
                'status' => $request->status,
                'updated_at' => Carbon::now(),
                'updated_by' => Auth::user()->id,
            ]);
            Db::commit();
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Status Update successfully.',
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Status not Update.',
                ]
            );
        }
    }

    public function employeeReport()
    {
        return view('hrm.report.employeeRreport');
    }

    public function salaryReport()
    {
        return view('hrm.report.salaryReport');
    }

    public function generateReport(Request $request)
    {
        $branches = $request->input('branchs', []);
        $departments = $request->input('departments', []);
        $sections = $request->input('sections', []);

        $employees = DB::table('hrm_emp_basic_official as emp')
            ->leftJoin('hrm_branch as br', 'br.id', '=', 'emp.branch_id')
            ->leftJoin('hrm_dept as dp', 'dp.id', '=', 'emp.dept_id')
            ->leftJoin('hrm_section as sec', 'sec.id', '=', 'emp.section_id')
            ->when($branches, function ($query, $branches) {
                return $query->whereIn('emp.branch_id', $branches);
            })->when($departments, function ($query, $departments) {
                return $query->whereIn('emp.dept_id', $departments);
            })->when($sections, function ($query, $sections) {
                return $query->whereIn('emp.section_id', $sections);
            })->select(
                'emp.id',
                'emp.full_name as full_name',
                'br.title as branch_id',
                'dp.title as dept_id',
                'sec.title as section_id',
                DB::raw("CASE WHEN emp.status = 1 THEN 'Active' WHEN emp.status = 2 THEN 'Inactive' ELSE 'Cancle' END as status")
            )->orderBy('emp.id', 'desc')
            ->get();

        return response()->json(['employees' => $employees]);
    }

    public function salaryReportGenerate()
    {
        $branch = isset($_GET['branch']) ? $_GET['branch'] : null;
        $department = isset($_GET['department']) ? $_GET['department'] : null;
        $section = isset($_GET['section']) ? $_GET['section'] : null;
        $monthYear = isset($_GET['month']) ? $_GET['month'] : null;
        if ($monthYear!=null){
            list($month, $year) = explode("/", $monthYear);
            $first_date = new \DateTime("$year-$month-01");
            $last_date = new \DateTime("$year-$month-01");
            $last_date->modify('last day of this month');
            $formattedDate = $first_date->format('F Y');
            $total_days = $first_date->diff($last_date)->days +1;
        }

        $working_days = DB::table('hrm_workingday_setup')->where('month',$monthYear)->where('is_deleted',1)->first();

        $holidays=DB::table('hrm_holiday_setup')
            ->select(DB::raw("
        SUM(
            DATEDIFF(
            LEAST(leave_to, '" . $last_date->format('Y-m-d') . "'),
                GREATEST(leave_from, '" . $first_date->format('Y-m-d') . "')
            ) + 1
        ) as total_holidays
    "))
            ->where('is_approved', 1)
            ->where(function($query) use ($first_date, $last_date) {
                // Ensure that either leave_from or leave_to overlaps with the given month
                $query->whereBetween('leave_from', [$first_date, $last_date])
                    ->orWhereBetween('leave_to', [$first_date, $last_date])
                    // Also handle cases where the entire leave period engulfs the given month
                    ->orWhere(function($query) use ($first_date, $last_date) {
                        $query->where('leave_from', '<', $first_date)
                            ->where('leave_to', '>', $last_date);
                    });
            })
            ->value('total_holidays');





        $employees = DB::table('hrm_emp_basic_official as emp')
            ->leftJoin('hrm_branch as br', 'br.id', '=', 'emp.branch_id')
            ->leftJoin('hrm_dept as dp', 'dp.id', '=', 'emp.dept_id')
            ->leftJoin('hrm_section as sec', 'sec.id', '=', 'emp.section_id')
            ->leftJoin('hrm_designation as designation', 'designation.id', '=', 'emp.designation_id')
            ->leftJoin('hrm_grade as grade', 'designation.grade_id', '=', 'grade.id')
            ->when($branch, function ($query, $branch) {
                return $query->where('emp.branch_id', $branch);
            })->when($department, function ($query, $department) {
                return $query->where('emp.dept_id', $department);
            })->when($section, function ($query, $section) {
                return $query->where('emp.section_id', $section);
            })->select(
                'emp.*',
//                'emp.full_name as full_name',
                'br.title as branch_id',
                'dp.title as dept_id',
                'sec.title as section_id',
                'designation.title as designation_id',
                'grade.title as grade_id',
                DB::raw("CASE WHEN emp.status = 1 THEN 'Active' WHEN emp.status = 2 THEN 'Inactive' ELSE 'Cancle' END as status")
            )->orderBy('emp.id', 'desc')
            ->get();



        return view('hrm.report.report_page',compact('employees','branch','department','section','formattedDate','first_date','last_date','total_days','working_days','holidays'));
    }

    public function downloadPDF(Request $request)
    {
//        $branches = isset($_GET['branches']) ? $_GET['branches'] : null;
//        $departments = isset($_GET['departments']) ? $_GET['departments'] : null;
//        $sections = isset($_GET['sections']) ? $_GET['sections'] : null;
//        dd($request->get('branches'));

        // Retrieve array inputs (with default empty arrays if not provided)
        $branches = $request->input('branches', []);
        $departments = $request->input('departments', []);
        $sections = $request->input('sections', []);
        $sections = $request->input('sections', []);


        $employees = DB::table('hrm_emp_basic_official as emp')
            ->leftJoin('hrm_branch as br', 'br.id', '=', 'emp.branch_id')
            ->leftJoin('hrm_dept as dp', 'dp.id', '=', 'emp.dept_id')
            ->leftJoin('hrm_section as sec', 'sec.id', '=', 'emp.section_id')
            ->leftJoin('hrm_designation as designation', 'designation.id', '=', 'emp.designation_id')
            ->leftJoin('hrm_grade as grade', 'designation.grade_id', '=', 'grade.id')
            ->when($branches, function ($query, $branches) {
                return $query->whereIn('emp.branch_id', $branches);
            })->when($departments, function ($query, $departments) {
                return $query->whereIn('emp.dept_id', $departments);
            })->when($sections, function ($query, $sections) {
                return $query->whereIn('emp.section_id', $sections);
            })->select(
                'emp.*',
//                'emp.full_name as full_name',
                'br.title as branch_id',
                'dp.title as dept_id',
                'sec.title as section_id',
                'designation.title as designation_id',
                'grade.title as grade_id',
                DB::raw("CASE WHEN emp.status = 1 THEN 'Active' WHEN emp.status = 2 THEN 'Inactive' ELSE 'Cancle' END as status")
            )->orderBy('emp.id', 'desc')
            ->get();
//


        // Load a view for the PDF generation
        $pdf = Pdf::loadView('hrm.report.report_page',compact('employees','branches','departments','sections'))
            ->setPaper('A1', 'landscape')  // Set landscape mode for more horizontal space
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Download the PDF
        return $pdf->download('salary_report.pdf');

    }

    function empUpdate(){
        $emp = Employee::orderBy('joinning_date', 'DESC')->get();
        $prev_year = $prev_month = 0;
        $maxNo = 1;
        foreach($emp as $d){
            $joinning_date = $d['joinning_date'];
            $id = $d['id'];
            if($joinning_date != '0000-00-00'){
                $year = date('y', strtotime($joinning_date));
                $month = date('m', strtotime($joinning_date));
                if($prev_year > 0 && $prev_month > 0 && $prev_year == $year && $prev_month == $month){
                    $maxNo = str_pad($maxNo, 3, "0", STR_PAD_LEFT);
                    $manualIdNo = $year."".$month."".$maxNo;
                }else{
                    $maxNo = 1;
                    $prev_year = $year;
                    $prev_month = $month;

                    $maxNo = str_pad($maxNo, 3, "0", STR_PAD_LEFT);
                    $manualIdNo = $year."".$month."".$maxNo;
                }
                Employee::where('id',$id)
                    ->update([
                        'manual_id_no' => $manualIdNo
                    ]);
                $maxNo++;
                echo $joinning_date."--".$manualIdNo."<br>";
            }else{
                continue;
            }
        }
    }


    public function createUser()
    {
        $employees = Employee::get(['id','full_name','manual_id_no','status','official_email']);
        foreach ($employees as $key => $employee){
            $user_name = strtolower(str_replace(" ", "", $employee->full_name));
//            $user = new User();
//            $user->username = $user_name;
//            $user->email = $employee->official_email ?? null;
//            $user->password = FacadesHash::make('12345678');
//            $user->employee_id = $employee->id;
//            $user->pin = $employee->manual_id_no;
//            $user->save();
            User::updateOrInsert(
            // Matching condition (to find an existing record)
                ['employee_id' => $employee->id],

                // Values to update or insert
                [
                    'username' => $user_name,
                    'email' => $employee->official_email ?? null,
                    'password' => FacadesHash::make('12345678'), // Always hash passwords!
                    'pin' => $employee->manual_id_no,
                ]
            );
        }
    }
}
