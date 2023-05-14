<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
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
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Login
    public function showLoginForm(){
        if(\auth()->check()){
            return redirect('/home');
        }

        return view('/auth/login');
    }
    public function login(Request $request)
    {
        $rules = [
            'email'       => 'required|string|email|min:3',
            'password'    => 'required|string|min:3|max:50',
            'remember_me' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput($request->only('email'))->with([
                'status'  => 'warning',
                'message' => $validator->errors()->first(),
            ]);
        }

        try{
            $credentials = request(['email', 'password', 'status' => 1]);

            if ( ! Auth::attempt($credentials, $request->remember)) {
                return redirect()->back()->withInput($request->only('email'))->with([
                    'status'  => 'error',
                    'message' => __('locale.auth.failed'),
                ]);
            }

            if ( ! Auth::user()->status) {
                Auth::logout();
                return redirect()->back()->withInput($request->only('email'))->with([
                    'status'  => 'error',
                    'message' => __('locale.auth.disabled'),
                ]);
            }

            $user = Auth::user();

            if (Gate::allows('access admin')) {
                return redirect()->route('admin.home');
            }else{
                return redirect('/home')->with([
                    'status'  => 'success',
                    'message' => __('locale.auth.welcome_come_back', ['name' => $user->name]),
                ]);
            }

        }catch(\Exception $exception){
            return redirect()->back()->with([
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function logout(Request $request){
        if ($admin_id = Session::get('admin_user_id')) {
            // Impersonate mode, back to original User
            session()->forget('admin_user_id');
            session()->forget('admin_user_name');
            session()->forget('temp_user_id');
            session()->forget('permissions');
            auth()->loginUsingId((int) $admin_id);
//            session(['permissions' => auth()->user()->getPermissions()]);
            return redirect()->route('admin.home');
        }
        $this->guard()->logout();

        $request->session()->invalidate();

        if ($this->loggedOut($request)) {

            return $this->loggedOut($request)->with([
                'status'  => 'success',
                'message' => 'Logout was successfully done',
            ]);
        } else {
            return redirect('/login');
        }
    }
}
