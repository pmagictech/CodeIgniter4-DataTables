<?php

namespace Pmagictech\DataTables;

use \Config\Services;

class Request
{

    /**
     * Get DataTable Request
     *
     * @param  String $requestName
     * @return String|Array
     */


    public static function get($requestName = NULL)
    {
        $request = Services::request();
        if ($requestName !== NULL)
            return $request->getGetPost($requestName);

        return (object) (($request->getMethod() == 'GET') ? $request->getGet() : $request->getPost());
    }
}
