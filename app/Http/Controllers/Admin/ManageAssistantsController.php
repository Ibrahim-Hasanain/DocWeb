<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Assistant;
use App\Models\Doctor;
use App\Models\AssistantLogin;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManageAssistantsController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Assistants';
        $assistants = $this->commonQuery()->paginate(getPaginate());
        return view('admin.assistant.index', compact('pageTitle', 'assistants'));
    }
    public function active()
    {
        $pageTitle = 'Active Doctors';
        $assistants = $this->commonQuery()->where('status', Status::ACTIVE)->paginate(getPaginate());
        return view('admin.assistant.index', compact('pageTitle', 'assistants'));
    }
    public function inactive()
    {
        $pageTitle = 'Inactive Doctors';
        $assistants = $this->commonQuery()->where('status', Status::INACTIVE)->paginate(getPaginate());
        return view('admin.assistant.index', compact('pageTitle', 'assistants'));
    }

    protected function commonQuery()
    {
        return Assistant::orderBy('id', 'DESC')->searchable(['name', 'mobile', 'email'])->filter(['status']);
    }

    public function status($id)
    {
        return Assistant::changeStatus($id);
    }

    public function form()
    {
        $pageTitle = 'Add New Assistant';
        $doctors   = Doctor::active()->orderBy('name')->get();
        return view('admin.assistant.form', compact('pageTitle', 'doctors'));
    }

    public function store(Request $request, $id = 0)
    {
        $this->validation($request, $id);
        if ($id) {
            $assistant    = Assistant::findOrFail($id);
            $notification = 'Assistant updated successfully';
        } else {
            $assistant    = new Assistant();
            $notification = 'Assistant added successfully';
        }

        $password = passwordGen();

        $this->assistantSave($assistant, $request,  $password);

        if (!$id) {
            $general = gs();
            notify($assistant, 'PEOPLE_CREDENTIAL', [
                'site_name' => $general->site_name,
                'name'      => $assistant->name,
                'username'  => $assistant->username,
                'password'  =>  $password,
                'guard'     => route('login'),
            ]);
        }
        $notify[] = ['success', $notification];
        return to_route('admin.assistant.detail', $assistant->id)->withNotify($notify);
    }


    protected function validation($request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';
        $request->validate([
            'image'    => ["$imageValidation", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'name'     => 'required|string|max:40',
            'username' => 'required|string|max:40|min:6|unique:assistants,username,' . $id,
            'email'    => 'required|email|string|unique:assistants,email,' . $id,
            'mobile'   => 'required|numeric|unique:assistants,mobile,' . $id,
            'address'  => 'nullable|string|max:255',
        ]);
    }

    protected function assistantSave($assistant, $request,  $password)
    {
        $doctors = Doctor::findOrFail($request->doctor_id);

        if ($request->hasFile('image')) {
            try {
                $old = $assistant->image;
                $assistant->image = fileUploader($request->image, getFilePath('assistantProfile'), getFileSize('assistantProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        $general = gs();
        $mobile = $general->country_code . $request->mobile;

        $assistant->name     = $request->name;
        $assistant->username = $request->username;
        $assistant->email    = strtolower(trim($request->email));
        if (!$assistant->id) {
            $assistant->password = Hash::make($password);
        }
        $assistant->mobile   = $mobile;
        $assistant->address  = $request->address;
        $assistant->save();

        if ($doctors) {
            $assistant->doctors()->sync($doctors->pluck('id'));
        }
    }


    public function detail($id)
    {
        $assistant    = Assistant::findOrFail($id);
        $pageTitle    = 'Assistant Detail - ' . $assistant->name;
        $doctors      = Doctor::orderBy('name')->get();
        $totalDoctors = $assistant->doctors->count();

        $basicQuery          = Appointment::where('try', Status::YES)->where('is_delete', Status::NO)->where('added_assistant_id', $id);
        $totalCount          = clone $basicQuery;
        $completeCount       = clone $basicQuery;
        $newCount            = clone $basicQuery;
        $totalAppointment    = $totalCount->count();
        $completeAppointment = $completeCount->where('is_complete', Status::APPOINTMENT_COMPLETE)->count();
        $newAppointment      = $newCount->where('is_complete', Status::APPOINTMENT_INCOMPLETE)->count();

        return view('admin.assistant.details', compact('pageTitle', 'assistant', 'doctors', 'totalDoctors', 'totalAppointment', 'completeAppointment', 'newAppointment'));
    }


    public function login($id)
    {
        $assistant = Assistant::findOrFail($id);
        Auth::guard('assistant')->login($assistant);
        return redirect()->route('assistant.dashboard');
    }

    public function notificationLog($id)
    {
        $assistant = Assistant::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $assistant->username;
        $logs      = NotificationLog::where('assistant_id', $id)->with('assistant')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.assistant.notification_history', compact('pageTitle', 'logs', 'assistant'));
    }

    public function showNotificationSingleForm($id)
    {
        $assistant = Assistant::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.admin.detail', $assistant->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $assistant->username;
        return view('admin.assistant.notification_single', compact('pageTitle', 'assistant'));
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

        $user = Assistant::findOrFail($id);
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
        $assistants = Assistant::active()->count();

        $notifyToAssistant = Assistant::notifyToAssistant();


        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        $pageTitle = 'Notification to Verified Assistants';
        return view('admin.assistant.notification_all', compact('pageTitle', 'assistants', 'notifyToAssistant'));
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
        if ($request->being_sent_to == 'selectedAssistants') {

            if (session()->get('SEND_NOTIFICATION')) {
                $request->merge(['assistant' => session()->get('SEND_NOTIFICATION')['assistant']]);
            } else {
                if (!$request->assistant || !is_array($request->assistant) || empty($request->assistant)) {
                    $notify[] = ['error', "Ensure that the assistant field is populated when sending an email to the designated user group"];
                    return back()->withNotify($notify);
                }
            }
        }

        $scope          = $request->being_sent_to;
        $userQuery      = Assistant::oldest()->active()->$scope();

        if (session()->has("SEND_NOTIFICATION")) {
            $totalUserCount = session('SEND_NOTIFICATION')['total_assistant'];
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
            $sessionData['total_assistant'] = $totalUserCount;
        }

        $sessionData['start'] = $sessionData['total_sent'] + 1;

        if ($sessionData['total_sent'] >= $totalUserCount) {
            session()->forget("SEND_NOTIFICATION");
            $message = ucfirst($request->via) . " notifications were sent successfully";
            $url     = route("admin.assistant.notification.all");
        } else {
            session()->put('SEND_NOTIFICATION', $sessionData);
            $message = $sessionData['total_sent'] . " " . $sessionData['via'] . "  notifications were sent successfully";
            $url     = route("admin.assistant.notification.all") . "?email_sent=yes";
        }
        $notify[] = ['success', $message];
        return redirect($url)->withNotify($notify);
    }



    public function countBySegment($methodName)
    {
        return Assistant::active()->$methodName()->count();
    }


    public function list()
    {
        $query = Assistant::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $assistants = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'assistants'   => $assistants,
            'more'    => $assistants->hasMorePages()
        ]);
    }



    public function loginHistory($id = 0)
    {
        $logs = AssistantLogin::orderByDesc('id')->searchable(['assistant:username,name'])->with('assistant');
        if ($id) {
            $assistant = Assistant::find($id);
            $pageTitle = $assistant->name . ' ' . 'Login History';
            $loginLogs = $logs->where('assistant_id', $id)->paginate(getPaginate());
        } else {
            $pageTitle = 'Assistant Login History';
            $loginLogs = $logs->paginate(getPaginate());
        }
        return view('admin.assistant.logins', compact('pageTitle', 'loginLogs'));
    }
}
