<?php

namespace App\Common;

class RestResponse
{
    private $status;
    private $message;
    private $data;
    private $traceId;
    private $token;
    private $extra;

    public function __construct(array $data, int $status, string $message = null, string $traceId = null,  $token = false, array $extra = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->traceId = $traceId;
        $this->token = $token;
        $this->extra = $extra;
    }

    public function toArray()
    {
        $responseArray = [
            // 'data' => $this->data,
            'status' => $this->status,
            // 'message' => $this->message,
            'timestamp' => CommonsFunctions::formatarDataTimeZonaAmericaSaoPaulo(now()),
        ];

        if ($this->data || (isset($this->extra['dataEmpty']) && $this->extra['dataEmpty']) === true) {
            $responseArray['data'] = $this->data;
        }

        if ($this->message) {
            $responseArray['message'] = $this->message;
        }

        if ($this->traceId) {
            $responseArray['trace_id'] = $this->traceId;
        }

        if ($this->token == true) {
            $token = csrf_token();
            if ($token) {
                $responseArray['csrf_token'] = $token;
            }
        }

        return $responseArray;
    }

    public function toJson()
    {
        return json_encode($this->toArray(), $this->getStatusCode());
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public static function createErrorResponse(int $status, string $message, string $traceId = null)
    {
        return new self([], $status, $message, $traceId);
    }

    public static function createGenericResponse(array $data, int $status, string $message, string $traceId = null)
    {
        return new self($data, $status, $message, $traceId);
    }

    public static function createSuccessResponse(array $data, int $status = 200, array $options = [])
    {
        $message = '';
        $token = false;
        $extra = ['dataEmpty' => true];

        if (isset($options['message'])) {
            $message = $options['message'];
        }
        if (isset($options['token'])) {
            $token = $options['token'] === true ? true : false;
        }
        if (isset($options['dataEmpty'])) {
            $extra['dataEmpty'] = $options['dataEmpty'] === false ? false : true;
        }

        return new self($data, $status, $message, null, $token, $extra);
    }

    public static function createTestResponse(array $data = [], string $message = 'Retorno teste', array $options = [])
    {
        $status = 422;

        if (isset($options['status'])) {
            $status = $options['status'];
        }

        $response = new self($data, $status, $message);
        return $response->throwResponse();
        // return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
    }

    public function autoResponse(){
        return response()->json($this->toArray(), $this->getStatusCode());
    }

    public function throwResponse(){
        return response()->json($this->toArray(), $this->getStatusCode())->throwResponse();
    }
}
