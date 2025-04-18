<?php
namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use App\Models\StaffLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laramin\Utility\Onumoti;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = 'staff';



    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
      
        $pageTitle = "Staff Login";
        return view('staff.auth.login', compact('pageTitle'));
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return auth()->guard('staff');
    }

    public function username()
    {
        return 'username';
    }

    public function login(Request $request)
    {


        $this->validateLogin($request);

        $request->session()->regenerateToken();



        Onumoti::getData();

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
        $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }



        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->status == 0) {
            $this->guard()->logout();
            $notify[] = ['error','Your account has been deactivated.'];
            return redirect()->route('staff.login')->withNotify($notify);
        }

        
        $user->save();
        $ip = getRealIP();
        $exist = StaffLogin::where('staff_ip', $ip)->first();

        $staffLogin = new StaffLogin();
        if ($exist) {
            $staffLogin->longitude    = $exist->longitude;
            $staffLogin->latitude     = $exist->latitude;
            $staffLogin->city         = $exist->city;
            $staffLogin->country_code = $exist->country_code;
            $staffLogin->country      = $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $staffLogin->country      = @implode(',', $info['country']);
            $staffLogin->country_code = @implode(',', $info['code']);
            $staffLogin->city         = @implode(',', $info['city']);
            $staffLogin->longitude    = @implode(',', $info['long']);
            $staffLogin->latitude     = @implode(',', $info['lat']);
        }

        $userAgent              = osBrowser();
        $staffLogin->staff_id = $user->id;
        $staffLogin->staff_ip = $ip;

        $staffLogin->browser = @$userAgent['browser'];
        $staffLogin->os      = @$userAgent['os_platform'];
        $staffLogin->save();

        return to_route('staff.dashboard');

    }






    public function logout(Request $request)
    {
        $this->guard('staff')->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect('/staff');
    }
}
