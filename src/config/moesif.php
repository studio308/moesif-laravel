<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Moesif Application ID
    |--------------------------------------------------------------------------
    |
    | This is the Moesif application id.
    |
    */

    'applicationId' => env('MOESIF_APPLICATION_ID'),

    /*
    |--------------------------------------------------------------------------
    | Skip
    |--------------------------------------------------------------------------
    |
    | Return true if the event is to be skipped.
    |
    */

    'skip' => function ($request, $response) {
        $host = explode('.', $request->server('HTTP_HOST'));
        return $host[0] != 'api';
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Request Headers
    |--------------------------------------------------------------------------
    |
    | Add or remove request headers.
    |
    */

    'maskRequestHeaders' => function ($headers) {
        return $headers;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Request Body
    |--------------------------------------------------------------------------
    |
    | Remove any fields from body that you don't want sent to Moesif.
    |
    */

    'maskRequestBody' => function ($body) {
        if (isset($body['password'])) {
            $body['password'] = str_repeat('*', 18);
        }
        return $body;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Response Headers
    |--------------------------------------------------------------------------
    |
    | Add or remove response headers.
    |
    */

    'maskResponseHeaders' => function ($headers) {
        return $headers;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Response Body
    |--------------------------------------------------------------------------
    |
    | Remove any fields from body that you don't want sent to Moesif.
    |
    */

    'maskResponseBody' => function ($body) {
        return $body;
    },

    /*
    |--------------------------------------------------------------------------
    | Identify User ID
    |--------------------------------------------------------------------------
    |
    | Identify the user.
    |
    */

    'identifyUserId' => function ($request, $response) {
        return null;
    },

    /*
    |--------------------------------------------------------------------------
    | Identify Company
    |--------------------------------------------------------------------------
    |
    | Identify the company.
    |
    */

    'identifyCompany' => function ($request, $response) {
        return null;
    },

    /*
    |--------------------------------------------------------------------------
    | Identify Session ID
    |--------------------------------------------------------------------------
    |
    | Identify the session.
    |
    */

    'identifySessionId' => function ($request, $response) {
        if ($request->hasSession()) {
            return $request->session()->getId();
        } else {
            return null;
        }
    },

    /*
    |--------------------------------------------------------------------------
    | Meta Data
    |--------------------------------------------------------------------------
    |
    | Add any extra data to be sent to Moesif.
    |
    */

    'getMetaData' => function ($request, $response) {
        return [];
    },

    /*
    |--------------------------------------------------------------------------
    | Tags
    |--------------------------------------------------------------------------
    |
    | Add any tags to be sent to Moesif.
    |
    */

    'addTags' => function ($request, $response) {
        return '';
    },

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Identify the API Version.
    |
    */

    'apiVersion' => function ($request, $response) {
        return null;
    },

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Set to true to see debug information.
    |
    */

    'debug' => env('MOESIF_DEBUG', false),
);
