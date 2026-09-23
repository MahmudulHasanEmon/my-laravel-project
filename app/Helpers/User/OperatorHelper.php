<?php

namespace App\Helpers\User;

class OperatorHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // Robi Helper Initialized
    }

    /**
     * Get segregated offers for a specific customer MSISDN.
     * 
     * @param string $customerMsisdn
     * @return mixed
     */
    public function getSegregatedOffers($customerMsisdn)
    {
        // API URL with dynamic customer MSISDN
        $url = "https://rdms-api.robi.com.bd/api-gw-ext/recharge-service/api/app/v2/offer/segregated-offers?customerMsisdn=" . urlencode($customerMsisdn);

        // Headers
        $headers = [
            "Accept: application/json;versions=1",
            "Content-Type: application/json",
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
            "Host: rdms-api.robi.com.bd",
            "Accept-Encoding: gzip",
            "User-Agent: okhttp/3.12.12"
        ];
        
        // cURL Initialization
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, "");

        // লোকালহোস্টে এসএসএল ভেরিফিকেশন বাইপাস
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        curl_close($ch);

        // JSON রেসপন্সটিকে অ্যারেতে রূপান্তর করে রিটার্ন করা
        return json_decode($response, true);
    }
}