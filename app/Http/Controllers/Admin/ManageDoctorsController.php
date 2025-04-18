<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Deposit;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Department;
use App\Models\DoctorLogin;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageDoctorsController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Doctors';
        $doctors = $this->commonQuery()->paginate(getPaginate());
        return view('admin.doctor.index', compact('pageTitle', 'doctors'));
    }

    public function active()
    {
        $pageTitle = 'Active Doctors';
        $doctors = $this->commonQuery()->where('status', Status::ACTIVE)->paginate(getPaginate());
        return view('admin.doctor.index', compact('pageTitle', 'doctors'));
    }

    public function inactive()
    {
        $pageTitle = 'Inactive Doctors';
        $doctors = $this->commonQuery()->where('status', Status::INACTIVE)->paginate(getPaginate());
        return view('admin.doctor.index', compact('pageTitle', 'doctors'));
    }

    protected function commonQuery()
    {
        return Doctor::orderBy('id', 'DESC')->searchable(['name', 'mobile', 'email', 'department:name', 'location:name'])->with('department', 'location')->filter(['status']);
    }

    public function status($id)
    {
        return Doctor::changeStatus($id);
    }

    public function featured($id)
    {
        return Doctor::changeStatus($id, 'featured');
    }

    public function form()
    {
        $pageTitle   = 'Add New Doctor';
        $departments = Department::orderBy('name')->get();
        $locations   = Location::orderBy('name')->get();
        return view('admin.doctor.form', compact('pageTitle', 'departments', 'locations'));
    }

    public function store(Request $request, $id = 0)
    {
        $this->validation($request, $id);

        if ($id) {
            $doctor         = Doctor::findOrFail($id);
            $notification   = 'Doctor updated successfully';
        } else {
            $doctor         = new Doctor();
            $notification   = 'Doctor added successfully';
        }

        $password = passwordGen();

        $this->doctorSave($doctor, $request, $password);

        if (!$id) {
            $general = gs();
            notify($doctor, 'PEOPLE_CREDENTIAL', [
                'site_name' => $general->site_name,
                'name'      => $doctor->name,
                'username'  => $doctor->username,
                'password'  =>  $password,
                'guard'     => route('login'),
            ]);
        }

        $notify[] = ['success', $notification];
        return to_route('admin.doctor.detail', $doctor->id)->withNotify($notify);
    }

    protected function validation($request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';
        $request->validate([
            'image'         => ["$imageValidation", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'name'          => 'required|string|max:40',
            'username'      => 'required|string|max:40|min:6|unique:doctors,username,' . $id,
            'email'         => 'required|email|string|unique:doctors,email,' . $id,
            'mobile'        => 'required|numeric|unique:doctors,mobile,' . $id,
            'department'    => 'required||numeric|gt:0',
            'location'      => 'required||numeric|gt:0',
            'fees'          => 'required|numeric|gt:0',
            'qualification' => 'required|string|max:255',
            'address'       => 'required|string|max:255',
            'about'         => 'required|string|max:500',
        ]);
    }

    protected function doctorSave($doctor, $request, $password)
    {


        if ($request->hasFile('image')) {
            try {
                $old = $doctor->image;
                $doctor->image = fileUploader($request->image, getFilePath('doctorProfile'), getFileSize('doctorProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $general = gs();
        $mobile = $general->country_code . $request->mobile;

        $doctor->name               = $request->name;
        $doctor->username           = $request->username;
        $doctor->email              = strtolower(trim($request->email));
        if (!$doctor->id) {
            $doctor->password       = Hash::make($password);
        }
        $doctor->mobile             = $mobile;
        $doctor->department_id      = $request->department;
        $doctor->location_id        = $request->location;
        $doctor->qualification      = $request->qualification;
        $doctor->fees               = $request->fees;
        $doctor->address            = $request->address;
        $doctor->about              = $request->about;
        $doctor->save();
    }

    public function detail($id)
    {
        $doctor            = Doctor::findOrFail($id);
        $pageTitle         = 'Doctor Detail - ' . $doctor->name;
        $departments       = Department::latest()->get();
        $locations         = Location::latest()->get();
        $totalOnlineEarn   = Deposit::where('doctor_id', $doctor->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
        $totalCashEarn     = $doctor->balance - $totalOnlineEarn;
        $totalAppointments = Appointment::where('doctor_id', $doctor->id)->where('try', 1)->count();

        $completeAppointments = Appointment::where('doctor_id', $doctor->id)->where('try', 1)->where('is_complete', Status::YES)->count();
        $trashedAppointments  = Appointment::where('doctor_id', $doctor->id)->where('is_delete', Status::YES)->count();
        return view('admin.doctor.details', compact('pageTitle', 'doctor', 'departments', 'locations', 'totalOnlineEarn', 'totalCashEarn', 'completeAppointments', 'trashedAppointments', 'totalAppointments'));
    }


    public function login($id)
    {
        $doctor = Doctor::findOrFail($id);
        Auth::guard('doctor')->login($doctor);
        return to_route('doctor.dashboard');
    }

    public function notificationLog($id)
    {

        $doctor    = Doctor::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $doctor->username;
        $logs      = NotificationLog::where('doctor_id', $id)->with('doctor')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.doctor.notification_history', compact('pageTitle', 'logs', 'doctor'));
    }

    public function showNotificationSingleForm($id)
    {
        $doctor = Doctor::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.admin.detail', $doctor->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $doctor->username;
        return view('admin.doctor.notification_single', compact('pageTitle', 'doctor'));
    }


    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email,sms,push',
            'subject' => 'required_if:via,email,push',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $imageUrl = null;
        if ($request->via == 'push' && $request->hasFile('image')) {
            $imageUrl = fileUploader($request->image, getFilePath('push'));
        }

        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via . '_status', Status::ENABLE)->exists();
        if (!$template) {
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }

        $user = Doctor::findOrFail($id);
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ], [$request->via], pushImage: $imageUrl);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {

        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $notifyToDoctor = Doctor::notifyToDoctor();
        $doctors = Doctor::active()->count();
        $pageTitle = 'Notification to Verified Doctors';

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }
        return view('admin.doctor.notification_all', compact('pageTitle', 'doctors', 'notifyToDoctor'));
    }


    public function countBySegment($methodName)
    {
        return Doctor::active()->$methodName()->count();
    }


    public function list()
    {
        $query = Doctor::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $doctors = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'doctors'   => $doctors,
            'more'    => $doctors->hasMorePages()
        ]);
    }


    public function sendNotificationAll(Request $request)
    {
        $request->validate([
            'via'                          => 'required|in:email,sms,push',
            'message'                      => 'required',
            'subject'                      => 'required_if:via,email,push',
            'start'                        => 'required|integer|gte:1',
            'batch'                        => 'required|integer|gte:1',
            'being_sent_to'                => 'required',
            'cooling_time'                 => 'required|integer|gte:1',
            'image'                        => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via . '_status', Status::ENABLE)->exists();
        if (!$template) {
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }
        if ($request->being_sent_to == 'selectedDoctors') {
            if (session()->get('SEND_NOTIFICATION')) {
                $request->merge(['doctor' => session()->get('SEND_NOTIFICATION')['doctor']]);
            } else {
                if (!$request->doctor || !is_array($request->doctor) || empty($request->doctor)) {
                    $notify[] = ['error', "Ensure that the doctor field is populated when sending an email to the designated user group"];
                    return back()->withNotify($notify);
                }
            }
        }

        $scope          = $request->being_sent_to;
        $userQuery      = Doctor::oldest()->active()->$scope();

        if (session()->has("SEND_NOTIFICATION")) {
            $totalUserCount = session('SEND_NOTIFICATION')['total_doctor'];
        } else {
            $totalUserCount = (clone $userQuery)->count() - ($request->start - 1);
        }

        if ($totalUserCount <= 0) {
            $notify[] = ['error', "Notification recipients were not found among the selected user base."];
            return back()->withNotify($notify);
        }

        $imageUrl = null;

        if ($request->via == 'push' && $request->hasFile('image')) {
            if (session()->has("SEND_NOTIFICATION")) {
                $request->merge(['image' => session()->get('SEND_NOTIFICATION')['image']]);
            }
            if ($request->hasFile("image")) {
                $imageUrl = fileUploader($request->image, getFilePath('push'));
            }
        }

        $doctors = (clone $userQuery)->skip($request->start - 1)->limit($request->batch)->get();

        foreach ($doctors as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ], [$request->via], pushImage: $imageUrl);
        }

        return $this->sessionForNotification($totalUserCount, $request);
    }


    private function sessionForNotification($totalUserCount, $request)
    {
        if (session()->has('SEND_NOTIFICATION')) {
            $sessionData                = session("SEND_NOTIFICATION");
            $sessionData['total_sent'] += $sessionData['batch'];
        } else {
            $sessionData               = $request->except('_token');
            $sessionData['total_sent'] = $request->batch;
            $sessionData['total_doctor'] = $totalUserCount;
        }

        $sessionData['start'] = $sessionData['total_sent'] + 1;

        if ($sessionData['total_sent'] >= $totalUserCount) {
            session()->forget("SEND_NOTIFICATION");
            $message = ucfirst($request->via) . " notifications were sent successfully";
            $url     = route("admin.doctor.notification.all");
        } else {
            session()->put('SEND_NOTIFICATION', $sessionData);
            $message = $sessionData['total_sent'] . " " . $sessionData['via'] . "  notifications were sent successfully";
            $url     = route("admin.doctor.notification.all") . "?email_sent=yes";
        }
        $notify[] = ['success', $message];
        return redirect($url)->withNotify($notify);
    }

    public function loginHistory($id = 0)
    {
        $logs      = DoctorLogin::orderByDesc('id')->searchable(['doctor:username, name'])->with('doctor');
        if ($id) {
            $doctor = Doctor::find($id);
            $pageTitle = $doctor->name . ' ' . 'Login History';
            $loginLogs = $logs->where('doctor_id', $id)->paginate(getPaginate());
        } else {
            $pageTitle = 'Doctor Login History';
            $loginLogs = $logs->paginate(getPaginate());
        }
        return view('admin.doctor.logins', compact('pageTitle', 'loginLogs'));
    }
}
