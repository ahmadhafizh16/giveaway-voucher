<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
     /**
     * @OA\Info(
     *     version="1.0",
     *     title="Give Away Voucher to 1000 lucky customers"
     * )
     */
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
