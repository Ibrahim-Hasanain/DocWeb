<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\StaffLogin;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManageStaffsController extends Controller
{

    public function index()
    {
        $pageTitle = 'All Staff';
        $staff     = $this->commonQuery()->paginate(getPaginate());
        return view('admin.staff.index', compact('pageTitle', 'staff'));
    }

    public function active()
    {
        $pageTitle = 'Active Staff';
        $staff     = $this->commonQuery()->where('status', Status::ACTIVE)->paginate(getPaginate());
        return view('admin.staff.index', compact('pageTitle', 'staff'));
    }

    public function inactive()
    {
        $pageTitle = 'Inactive Staff';
        $staff     = $this->commonQuery()->where('status', Status::INACTIVE)->paginate(getPaginate());
        return view('admin.staff.index', compact('pageTitle', 'staff'));
    }

    protected function commonQuery()
    {
        return Staff::orderBy('id', 'DESC')->searchable(['name', 'mobile', 'email'])->filter(['status']);
    }

    public function status($id)
    {
        return Staff::changeStatus($id);
    }

    public function featured($id)
    {
        return Staff::changeStatus($id, 'Featured');
    }

    public function form()
    {
        $pageTitle   = 'Add New Staff';
        return view('admin.staff.form', compact('pageTitle'));
    }

    public function store(Request $request, $id = 0)
    {
        $this->validation($request, $id);
        if ($id) {
            $staff        = Staff::findOrFail($id);
            $notification = 'Staff updated successfully';
        } else {
            $staff        = new Staff();
            $notification = 'Staff added successfully';
        }


        $password = passwordGen();
        $this->staffSave($staff, $request, $password);

        if (!$id) {
            $general = gs();
            notify($staff, 'PEOPLE_CREDENTIAL', [
                'site_name' => $general->site_name,
                'name'      => $staff->name,
                'username'  => $staff->username,
                'password'  => $password,
                'guard'     => route('login'),
            ]);
        }
        $notify[] = ['success', $notification];
        return to_route('admin.staff.detail', $staff->id)->withNotify($notify);
    }



    protected function validation($request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';
        $request->validate([
            'image'         => ["$imageValidation", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'name'          => 'required|string|max:40',
            'username'      => 'required|string|max:40|min:6|unique:staff,username,' . $id,
            'email'         => 'required|email|string|unique:staff,email,' . $id,
            'mobile'        => 'required|numeric|unique:staff,mobile,' . $id,
            'address'       => 'nullable|string|max:255',
            'about'         => 'nullable|string|max:500',
        ]);
    }

    protected function staffSave($staff, $request,     $password)
    {
        if ($request->hasFile('image')) {
            try {
                $old = $staff->image;
                $staff->image = fileUploader($request->image, getFilePath('staffProfile'), getFileSize('staffProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        $general = gs();
        $mobile = $general->country_code . $request->mobile;

        $staff->name               = $request->name;
        $staff->username           = $request->username;
        $staff->email              = strtolower(trim($request->email));
        if (!$staff->id) {
            $staff->password       = Hash::make($password);
        }
        $staff->mobile             = $mobile;
        $staff->save();
    }


    public function detail($id)
    {
        $staff       = Staff::findOrFail($id);
        $pageTitle   = 'Staff Detail - ' . $staff->name;
        $appointment  = Appointment::where('added_staff_id', $staff->id);
        $new   = clone  $appointment;
        $done  = clone  $appointment;
        $total = clone $appointment;
        $trash = clone $appointment;
        $newAppointments     = $new->newAppointment()->count();
        $doneAppointments    = $done->completeAppointment()->count();
        $totalAppointments   = $total->where('try', Status::YES)->count();
        $trashedAppointments = $trash->where('delete_by_staff', $staff->id)->where('is_delete', Status::YES)->count();
        return view('admin.staff.details', compact('pageTitle', 'staff', 'doneAppointments', 'newAppointments', 'trashedAppointments', 'totalAppointments'));
    }


    public function login($id)
    {
        $staff = Staff::findOrFail($id);
        Auth::guard('staff')->login($staff);
        return redirect()->route('staff.dashboard');
    }

    public function notificationLog($id)
    {

        $staff    = Staff::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $staff->username;
        $logs      = NotificationLog::where('staff_id', $id)->with('staff')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.staff.notification_history', compact('pageTitle', 'logs', 'staff'));
    }

    public function showNotificationSingleForm($id)
    {
        $staff = Staff::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.admin.detail', $staff->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $staff->username;
        return view('admin.staff.notification_single', compact('pageTitle', 'staff'));
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

        $user = Staff::findOrFail($id);
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
        $staffs = Staff::active()->count();
        $pageTitle = 'Notification to Verified Staffs';
        $notifyToStaff = Staff::notifyToStaff();


        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view('admin.staff.notification_all', compact('pageTitle', 'staffs', 'notifyToStaff'));
    }

    public function countBySegment($methodName)
    {
        return Staff::active()->$methodName()->count();
    }


    public function list()
    {
        $query = Staff::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $staffs = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'staffs'   => $staffs,
            'more'    => $staffs->hasMorePages()
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
        if ($request->being_sent_to == 'selectedStaffs') {
            if (session()->get('SEND_NOTIFICATION')) {
                $request->merge(['staff' => session()->get('SEND_NOTIFICATION')['staff']]);
            } else {
                if (!$request->staff || !is_array($request->staff) || empty($request->staff)) {
                    $notify[] = ['error', "Ensure that the staff field is populated when sending an email to the designated staff group"];
                    return back()->withNotify($notify);
                }
            }
        }

        $scope          = $request->being_sent_to;
        $userQuery      = Staff::oldest()->active()->$scope();

        if (session()->has("SEND_NOTIFICATION")) {
            $totalUserCount = session('SEND_NOTIFICATION')['total_staff'];
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
            $sessionData['total_staff'] = $totalUserCount;
        }

        $sessionData['start'] = $sessionData['total_sent'] + 1;

        if ($sessionData['total_sent'] >= $totalUserCount) {
            session()->forget("SEND_NOTIFICATION");
            $message = ucfirst($request->via) . " notifications were sent successfully";
            $url     = route("admin.staff.notification.all");
        } else {
            session()->put('SEND_NOTIFICATION', $sessionData);
            $message = $sessionData['total_sent'] . " " . $sessionData['via'] . "  notifications were sent successfully";
            $url     = route("admin.staff.notification.all") . "?email_sent=yes";
        }
        $notify[] = ['success', $message];
        return redirect($url)->withNotify($notify);
    }

    public function loginHistory($id = 0)
    {
        $logs      = StaffLogin::orderByDesc('id')->searchable(['staff:username,name'])->with('staff');
        if ($id) {
            $staff = Staff::find($id);
            $pageTitle = $staff->name . ' ' . 'Login History';
            $loginLogs = $logs->where('staff_id', $id)->paginate(getPaginate());
        } else {
            $pageTitle = 'Staff Login History';
            $loginLogs = $logs->paginate(getPaginate());
        }
        return view('admin.staff.logins', compact('pageTitle', 'loginLogs'));
    }
}
