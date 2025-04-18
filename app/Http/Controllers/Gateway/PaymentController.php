<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Appointment;
use App\Models\Deposit;
use App\Models\Doctor;
use App\Models\GatewayCurrency;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposit($appointmentId)
    {
        try {
            $appointmentId = decrypt($appointmentId);
        } catch (\Exception $e) {
            $notify[] = ['success', 'Invalid Request!'];
            return to_route('home')->withNotify($notify);
        };

        $appointment = Appointment::findOrFail($appointmentId);
        $doctor      = $appointment->doctor;
        $fees        = $doctor->fees;
        $doctorId    = $doctor->id;
        $trx         = $appointment->trx;
        $email       = $appointment->email;

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();
        $pageTitle = "Appointment Payment Method";

        return view('Template::user.payment.deposit', compact('pageTitle', 'fees', 'doctorId', 'trx', 'email', 'gatewayCurrency'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'gateway' => 'required',
            'currency' => 'required',
            'doctor_id'   => 'required|exists:doctors,id',
            'trx'         => 'required',
        ]);

        $appointment = Appointment::where('trx', $request->trx)->first();
        if (!$appointment) {
            $notify[] = ['error', 'Invalid appointment!'];
            return back()->withNotify($notify);
        }


        $doctor = Doctor::findOrFail($request->doctor_id);

        if ($doctor->fees != $request->amount) {
            $notify[] = ['error', "Sorry! Didn't permit to customize doctor fees."];
            return back()->withNotify($notify);
        }




        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();
        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $charge = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable = $request->amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data = new Deposit();
        $data->appointment_id  = $appointment->id;
        $data->doctor_id       = $doctor->id;
        $data->method_code = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount = $request->amount;
        $data->charge = $charge;
        $data->rate = $gate->rate;
        $data->final_amount = $finalAmount;
        $data->btc_amount = 0;
        $data->btc_wallet = "";
        $data->trx = getTrx();
        $data->success_url = urlPath('doctors.all');
        $data->failed_url = urlPath('doctors.all');
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('deposit.confirm');
    }



    public function depositConfirm()
    {
        $track = session()->get('Track');

        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }


        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();


            $doctor = Doctor::find($deposit->doctor_id);
            $doctor->balance += $deposit->amount;
            $doctor->save();

            $methodName = $deposit->methodName();

            $transaction = new Transaction();
            $transaction->doctor_id    = $deposit->doctor_id;
            $transaction->amount = $deposit->amount;
            $transaction->post_balance = $doctor->balance;
            $transaction->charge = $deposit->charge;
            $transaction->trx_type = '+';
            $transaction->details = 'Deposit Via ' . $methodName;
            $transaction->trx = $deposit->trx;
            $transaction->remark = 'deposit';
            $transaction->save();



            if (!$isManual) {
                $adminNotification = new AdminNotification();
                $adminNotification->doctor_id = $doctor->id;
                $adminNotification->title = 'Deposit successful via ' . $methodName;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }


            $appointment = Appointment::where('id', $deposit->appointment_id)->first();
            $appointment->payment_status = Status::APPOINTMENT_PAID_PAYMENT;
            $appointment->site = Status::YES;
            $appointment->try  = Status::YES;
            $appointment->save();

            notify($doctor, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name' => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount' => showAmount($deposit->final_amount, currencyFormat: false),
                'amount' => showAmount($deposit->amount, currencyFormat: false),
                'charge' => showAmount($deposit->charge, currencyFormat: false),
                'rate' => showAmount($deposit->rate, currencyFormat: false),
                'trx' => $deposit->trx,
                'post_balance' => showAmount($doctor->balance)
            ]);

            $user = [
                'name'     => $appointment->name,
                'username' => $appointment->email,
                'fullname' => $appointment->name,
                'email'    => $appointment->email,
                'mobileNumber'   => $appointment->mobile,
            ];

            notify($user, 'APPOINTMENT_CONFIRMATION', [
                'booking_date' => $appointment->booking_date,
                'time_serial'  => $appointment->time_serial,
                'doctor_name'  => $doctor->name,
                'doctor_fees'  =>  showAmount($doctor->fees)
            ], ['email', 'sms']);
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method = $data->gatewayCurrency();
            $gateway = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway = $gatewayCurrency->method;
        $formData = $gateway->form->form_data;

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);


        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();


        $adminNotification = new AdminNotification();
        $adminNotification->doctor_id = $data->doctor_id;
        $adminNotification->title = 'Deposit request to ' . $data->doctor->name;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name' => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount' => showAmount($data->final_amount, currencyFormat: false),
            'amount' => showAmount($data->amount, currencyFormat: false),
            'charge' => showAmount($data->charge, currencyFormat: false),
            'rate' => showAmount($data->rate, currencyFormat: false),
            'trx' => $data->trx
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('home')->withNotify($notify);
    }
}
