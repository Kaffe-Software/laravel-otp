<?php

namespace Ichtrojan\Otp;

use Carbon\Carbon;
use Ichtrojan\Otp\Models\Otp as Model;
use Illuminate\Support\Facades\Facade;

class Otp extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Otp';
    }

    /**
     * @param string $identifier
     * @param int $digits
     * @param int $validity
     * @return mixed
     */
    public function generate(string $identifier, int $digits = 4, int $validity = 10, int $user_id = 0) : object
    {

        $token = str_pad($this->generatePin($digits), $digits, '0', STR_PAD_LEFT);

        Model::create([
            'identifier' => $identifier,
            'token' => $token,
            'validity' => $validity,
            'user_id' => $user_id,
        ]);

        return (object)[
            'status' => true,
            'token' => $token,
            'message' => 'OTP generated'
        ];
    }

    /**
     * @param string $identifier
     * @param string $token
     * @return mixed
     */
    public function validate(string $identifier, string $token) : object
    {
        $otp = Model::where('identifier', $identifier)->where('token', $token)->first();

        if ($otp == null) {
            return (object)[
                'status' => false,
                'message' => 'OTP does not exist'
            ];
        } else {
            if ($otp->valid == true) {
                $carbon = new Carbon;
                $now = $carbon->now();
                $validity = $otp->created_at->addMinutes($otp->validity);

                if (strtotime($validity) < strtotime($now)) {
                    $otp->valid = false;
                    $otp->save();

                    return (object)[
                        'status' => false,
                        'message' => 'OTP Expired'
                    ];
                } else {
                    $otp->valid = false;
                    $otp->save();

                    return (object)[
                        'status' => true,
                        'message' => 'OTP is valid'
                    ];
                }
            } else {
                return (object)[
                    'status' => false,
                    'message' => 'OTP is not valid'
                ];
            }
        }
    }

    /**
     * @param int $digits
     * @return string
     */
    private function generatePin($digits = 4)
    {
        $i = 0;
        $pin = "";

        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return $pin;
    }
}
