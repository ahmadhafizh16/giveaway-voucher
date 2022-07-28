<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoucherGiftController extends Controller
{
    /**
     * @OA\Get(path="/customer-check",
     *     tags={"customer check"},
     *     summary="check if customer eligible",
     *     description="return link to claim voucher",
     *     operationId="checkCustomer",
     *     parameters={},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             additionalProperties={
     *                 "type": "integer",
     *                 "format": "int32"
     *             }
     *         )
     *     ),
     *     security={{
     *         "api_key": {}
     *     }}
     * )
     */
}
