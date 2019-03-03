<?php
class Response {
    public function ajaxReturn($data, $type='success'){
        switch ($type) {
            case 'success':
                $header_str = 'HTTP/1.1 200 OK';
                break;
            case 'error':
                $header_str = 'HTTP/1.1 400 Bad Request';
                break;
            default:
                $header_str = 'HTTP/1.1 200 OK';
                break;
        }
        header($header_str);
        echo json_encode($data);
        exit;
    }
}