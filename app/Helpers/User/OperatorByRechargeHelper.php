<?php

namespace App\Helpers\User;

class OperatorByRechargeHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // Recharge Helper Initialized
    }

    /**
     * Send Robi Recharge Request.
     * 
     * @param string $customerMsisdn
     * @param string $offerAmount
     * @param string $rechargeAmount
     * @param string $offerType
     * @param string $robiPin
     * @return array
     */
    public function robiRecharge($customerMsisdn, $offerAmount, $rechargeAmount, $offerType = "direct_dial", $robiPin = "8711")
    {
        // API URL
        $url = "https://rdms-api.robi.com.bd/api-gw-ext/recharge-service/api/app/v2/purchase/recharge";

        // Headers
        $headers = [
            "Accept: application/json;versions=1",
            "Device-ID: 72231aaccaa730dd",
            "App-Origin: RED_CUBE_APP",
            "Device-IMEI: 72231aaccaa730dd",
            "Device-MAC-Address: ",
            "Accept-Language: en",
            "App-Version-Code: 5021",
            "Device-Vendor: Xiaomi",
            "Device-Model: 23129RAA4G",
            "Device-API-Version: 35",
            "Device-Latitude: 0.0",
            "Device-Longitude: 0.0",
            "X-Secret: CURRENT",
            "Authorization: Bearer eyJhbGciOiJIUzUxMiJ9.eyJVU0VSX05BTUUiOiI4ODAxODQ4NzMwMTE5IiwiUk9MRVMiOlsiUkVUQUlMRVIiXSwiQVBQX09SSUdJTiI6IlJFRF9DVUJFX0FQUCIsIkFDQ0VTU19KVEkiOiIxZjI1Zjg0Zi01NjU3LTRlMmEtYjk5MS1iZDNhNDIxOTdjNjAiLCJzdWIiOiI4ODAxODQ4NzMwMTE5IiwiaWF0IjoxNzg4NzY3NTA5LCJleHAiOjE3OTEzNTk1MDksImlzcyI6IkFVVEhfU0VSVklDRSJ9.3c_LE1AT8DH23bMvpuW6rEmZS7SoIY-dsf6MNbpRDHPkGy79w7xzW2Ek_rXOP08D1R2UwFB6vMtub_5sMM2LcA",
            "token: eyJhbGciOiJIUzUxMiJ9.eyJVU0VSX05BTUUiOiI4ODAxODQ4NzMwMTE5IiwiUk9MRVMiOlsiUkVUQUlMRVIiXSwiQVBQX09SSUdJTiI6IlJFRF9DVUJFX0FQUCIsIkFDQ0VTU19KVEkiOiIxZjI1Zjg0Zi01NjU3LTRlMmEtYjk5MS1iZDNhNDIxOTdjNjAiLCJzdWIiOiI4ODAxODQ4NzMwMTE5IiwiaWF0IjoxNzg4NzY3NTA5LCJleHAiOjE3OTEzNTk1MDksImlzcyI6IkFVVEhfU0VSVklDRSJ9.3c_LE1AT8DH23bMvpuW6rEmZS7SoIY-dsf6MNbpRDHPkGy79w7xzW2Ek_rXOP08D1R2UwFB6vMtub_5sMM2LcA",
            "App-Name: rsa",
            "Content-Type: application/json; charset=UTF-8",
            "Host: rdms-api.robi.com.bd",
            "Connection: Keep-Alive",
            "Accept-Encoding: gzip",
            "User-Agent: okhttp/3.12.12"
        ];

        // JSON Body with dynamic parameters
        $postData = json_encode([
            "latitude" => 0.0,
            "longitude" => 0.0,
            "rechargeDetails" => [
                [
                    "customerMsisdn" => $customerMsisdn,
                    "offerAmount" => (string) $offerAmount,
                    "offerType" => $offerType,
                    "rechargeAmount" => (string) $rechargeAmount,
                    "rechargeType" => "REGULAR_RECHARGE"
                ]
            ],
            "robiPin" => (string) $robiPin
        ]);

        // cURL Initialization
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, "");

        // লোকালহোস্টে এসএসএল ভেরিফিকেশন বাইপাস করার জন্য
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlError = null;
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
        }

        curl_close($ch);

        // JSON ডিকোড করা
        $decodedResponse = json_decode($response, true);

        // সঠিক পাথ থেকে পেমেন্ট রেজাল্ট বের করা (paymentResults একটি অ্যারে হওয়ায় [0] দিতে হবে)
        $paymentResult = $decodedResponse['result']['paymentResults'][0] ?? [];

        $status = $paymentResult['status'] ?? false;
        $message = $paymentResult['message'] ?? 'No message returned from API';
        $transactionNo = $paymentResult['transactionNo'] ?? null;

        // ফাইনাল রিটার্ন অ্যারে সাজানো
        $result = [
            "success" => $status,
            "message" => $message,
            "transactionNo" => $transactionNo,
            "http_code" => $httpCode,
            // "raw_response" => $decodedResponse
        ];

        if ($curlError) {
            $result["curl_error"] = $curlError;
        }

        return $result;
    }

    /**
     * Send Banglalink Recharge Request.
     * 
     * @param string $customerMsisdn
     * @param string $rechargeAmount
     * @param string $blPin
     * @return array
     */
    public function blRecharge($customerMsisdn, $rechargeAmount, $blPin = "8711")
    {
        $url = "https://retailerselfapp.banglalink.net/RechargeApi/EvRecharge";

        $headers = [
            "User-Agent: RetApp/7.1.0",
            "Accept-Language: en-US",
            "Content-Type: application/json; charset=UTF-8",
            "Host: retailerselfapp.banglalink.net",
            "Connection: Keep-Alive",
            "Accept-Encoding: gzip"
        ];

        $postData = json_encode([
            "amount" => (string) $rechargeAmount,
            "denoValidity" => "0",
            "deviceId" => "b6bad17109b83be5",
            "email" => "",
            "iTopUpNumber" => "01912887021",
            "lan" => "en",
            "lat" => "0",
            "lng" => "0",
            "paymentType" => 1,
            "retailerCode" => "R227432",
            "sessionToken" => "eyJhbGciOiJBMjU2S1ciLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwidHlwIjoiSldUIiwiY3R5IjoiSldUIn0.OHu1Fm6B1u-LhiXhZ0KjszSTh4wnvBMbjyefAl5b7OyyY7lFxeGNdnigrbms-mK-t5BD0jEMlTgpBpxv4WOmTXm_35ZpG7VR.MeTPxKImif7H1Fi7G-7UoA.vo6a3fuaxUAEb0uyZjABZj03RNCiGLUUwZ-W3_L97t3FuZ-2tdy_666iS_p6qvNnSS6tkK34qdUvUm9cODu_2s9mvzPRtLjOEDz-lqRLpjutIOuKShu30ZfbiKKokoTg8FTmpxbyf0qt9oUObRI9pi65eMm9ufWmGBFAo8YSBxHZBGE4HOZIiC3-FxYsissfzDivzNFAZX3cQIXEeOWgjdul2FKvRICWkTF2mBnllrAG_mvbyLaGiZ1KTHPWff0Y7tGfn4J8cxDHiqP-t-kHYTSTqqNUFbrYTWIm2hgRk01a-2DodKJeYuQPTRlk012x_K47vW8od6FXZFKUj1gMKGG43zi48s_LO5Ac1gf--ZecvJjBf4zxMTdxTWHZFL-vBPDag3qvsdhmzTPgT5D1nbyE7fYGmHB-_Xz9mAeAJar7a_PjzsDWjJaOVSWAwLcnQXlqMMrIBGAAziUUyrs2h_VJCtaBepKB8SluBNHKH7U.sdfVqKs3t1-3SxkY0mVTDV8N0mEr3qubceXFXL7Mi_o",
            "subscriberNo" => (string) $customerMsisdn,
            "userPin" => (string) $blPin
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlError = null;
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
        }
        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        // Banglalink API রেসপন্স স্ট্রাকচার অনুযায়ী ফিল্ড সেট করা
        $isSuccess = $decodedResponse['isError'] ? false : true;
        $message = $decodedResponse['message'] ?? ($decodedResponse['errorDetails'] ?? 'No message returned from API');

        $result = [
            "success" => $isSuccess,
            "message" => $message,
            "http_code" => $httpCode,
            "raw_response" => $decodedResponse
        ];

        if ($curlError) {
            $result["curl_error"] = $curlError;
        }

        return $result;
    }


}
