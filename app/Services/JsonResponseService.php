<?php

namespace App\Services;

class JsonResponseService
{
    /**
     * JSON response method.
     *
     * @param  boolean  $status
     * @param  null|array  $result
     * @param  null|string  $message
     * @param  integer  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($status = true, $result = null, $message = null, $code = 200, $string = 'default')
    {
        $response = [
            'status' => $status,
            'message' => $message === null ? trans('message.DEFAULT_SUCCESS_MESSAGE') : $message,
        ];

        if ($result !== null) {
            foreach ($result as $key => $_result) {
                $response[$key] = $_result;
            }
        }

        return response()->json($response, $code);
    }
}
