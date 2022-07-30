<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Jobs\ResetVoucherOwnershipJob;
use App\Http\Requests\EligibleCheckRequest;
use App\Http\Requests\ValidatePhotoRequest;
use App\Services\PurchaseTransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherGiftController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/customer-check",
     *      tags={"customer check"},
     *      summary="check if customer eligible for the event",
     *      description="Return unclaimed voucher code",
     *      operationId="checkCustomer",
     *      @OA\Parameter(
     *          name="customer_id",
     *          description="Customer unique identifier",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success booked voucher code or already have voucher code"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="All voucher has been claimed or fully booked"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="customer not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error (customer_id needed) "
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      ) 
     * )
     */
    public function eligibleCheck(
            EligibleCheckRequest $request,
            PurchaseTransactionService $PTService,
            VoucherService $VoucherService,
    )
    {
        try{
            $customerId = $request->customer_id;

            $customer = Customer::select(['id','first_name','last_name','email'])->findOrFail($customerId);
            $customerTransaction = $PTService->getPurchaseCountAndTotalSpent($customer);
            $voucher = $VoucherService->customerHasVoucher($customer);

            if($voucher){
                return response()->json([
                    'message' => 'This customer can participate! Please upload your photo before '.$voucher->updated_at->addMinutes(10),
                    'data' => [
                        'isEligible' => true,
                        'transaction' => $customerTransaction,
                        'customer' => $customer,
                        'voucher' => $voucher
                    ]
                ], 200);
            }

            if(!$VoucherService->isVoucherAvailable()){
                return response()->json([
                    'message' => 'No voucher available !'
                ], 403);
            }

            if($customerTransaction['totalSpent'] < 300 || $customerTransaction['purchaseCount'] < 3)
            {
                return response()->json([
                    'message' => 'This customer is not eligible!',
                    'data' => [
                        'isEligible' => false,
                        'transaction' => $customerTransaction
                    ]
                ], 403);
            }

            DB::beginTransaction();
            $voucher = $VoucherService->bindVoucherToCustomer($customer);
            DB::commit();

            $delay = now()->addMinutes(10);

            ResetVoucherOwnershipJob::dispatch($voucher)->delay($delay); 
        }
        catch(ModelNotFoundException $ex){
            return response()->json([
                'message' => 'Customer not found !'
            ], 404);
        }
        catch(\Throwable $ex){
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
        
        return response()->json([
            'message' => 'This customer can participate! Please upload your photo before '.$delay,
            'data' => [
                'isEligible' => true,
                'transaction' => $customerTransaction,
                'customer' => $customer,
                'voucher' => $voucher
            ]
        ], 200);
    }

     /**
     * @OA\Post(
     *      path="/api/validate-photo",
     *      tags={"validate photo"},
     *      summary="Claim voucher by uploading product's photo",
     *      description="This API will return claimed voucher code with customer data if success",
     *      operationId="validatePhoto",
     *      @OA\MediaType(mediaType="multipart/form-data"),
     *      @OA\Parameter(
     *          name="customer_id",
     *          description="Customer unique identifier",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="photo",
     *                     nullable=false,
     *                     type="file"
     *                 ),
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success claimed voucher code or already claimed voucher code"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Customer dont have voucher to claim or Image recognizition API fail"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="customer not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error (customer_id needed) "
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      ) 
     * )
     */
    public function validatePhoto(
        ValidatePhotoRequest $request,
        VoucherService $VoucherService,
    )
    {
        Http::fake([
            'image-recognize.com' => Http::response(['isRecognized' => true], 200)
        ]);

        try{
            $customer = Customer::select(['id','first_name','last_name','email'])->findOrFail($request->customer_id);
            $voucher = $VoucherService->customerHasVoucher($customer);

            if(!$voucher){
                return response()->json([
                    'message' => 'You dont have any booked voucher or maybe your voucher has expired!'
                ], 403);
            }

            if($voucher->is_claimed){
                return response()->json([
                    'message' => 'Your voucher has been claimed before!',
                    'data' => [
                        'customer' => $customer,
                        'voucher' => $voucher
                    ]
                ], 200);
            }

            $response = Http::withBody(
                base64_encode($request->file('photo')), 'image/*'
            )->post("http://image-recognize.com");
            
            if(!$response->json()['isRecognized']){
                return response()->json([
                    'message' => 'Cannot recognize uploaded photo'
                ], 403);
            }

            $VoucherService->claimVoucher($voucher);
        }
        catch(ModelNotFoundException $ex){
            return response()->json([
                'message' => 'Customer not found !'
            ], 404);
        }
        catch(\Throwable $ex){
            return response()->json([
                'message' => 'Something went wrong!'.$ex
            ], 500);
        }
        
        return response()->json([
            'message' => 'Claim voucher success!',
            'data' => [
                'customer' => $customer,
                'voucher' => $voucher
            ]
        ], 200);
    }
}
