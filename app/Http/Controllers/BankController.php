<?php

namespace App\Http\Controllers;

use DB;
use PaymentGateway;
use Illuminate\Http\Request;
use App\Contracts\BankServiceInterface as BankService;

class BankController extends Controller
{
    /**
     * @var $bankService
     */
    private $bankService, $paymentGateway;

    /**
     * Inject Dependencies
     */
    public function __construct( BankService $bankService, PaymentGateway $paymentGateway )
    {
        $this->bankService = $bankService;
        $this->paymentGateway = $paymentGateway;
    }


    /**
     * @OA\Get(
     * path="/banks",
     *   tags={"Bank Service"},
     *   summary="List Bank",
     *   operationId="list-banks",
     *
     *   @OA\Parameter(
     *      name="nuban",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     * @param ListBankRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     * @throws \Throwable
     */
    public function listBanks(Request $request)
    {
        DB::beginTransaction();
        try{
            $req = $this->bankService->listBanks($request->nuban);
            if( is_object($req) ) $req = $req->toArray();

            DB::commit();
            return success( 'success', $req );
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     * path="/users/banks",
     *   tags={"Bank Service"},
     *   summary="Add Bank Account",
     *   operationId="add-bank-account",
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *         required={"nuban", "code"},
     *         @OA\Property(property="nuban", type="string", format="text", example="0043610610"),
     *         @OA\Property(property="code", type="string", format="text", example="041"),
     *      ),
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     * @param AddBankRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     * @throws \Throwable
     */
    public function addBankAccount(Request $request)
    {
        DB::beginTransaction();
        try{
            $req = $this->bankService->addBankAccount($request->all());
            if( is_object($req) ) $req = $req->toArray();

            DB::commit();
            return success( 'success', $req );
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @OA\Get(
     * path="/users/banks",
     *   tags={"Bank Service"},
     *   summary="List Bank Accounts",
     *   operationId="list-bank-accounts",
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    public function listBankAccounts(Request $request)
    {
        DB::beginTransaction();
        try{
            $req = $this->bankService->listBankAccounts();
            if( is_object($req) ) $req = $req->toArray();

            DB::commit();
            return success( 'success', $req );
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @OA\Get(
     * path="/users/banks",
     *   tags={"Bank Service"},
     *   summary="Find Bank Account",
     *   operationId="find-bank-account",
     *
     *   @OA\Parameter(
     *      name="bankAccountId",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     **/
    public function findBankAccount($id)
    {
        DB::beginTransaction();
        try{
            $req = $this->bankService->findBankAccount($id);
            if( is_object($req) ) $req = $req->toArray();

            DB::commit();
            return success( 'success', $req );
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @OA\Delete(
     * path="/users/banks",
     *   tags={"Bank Service"},
     *   summary="Delete Bank Account",
     *   operationId="delete-bank-account",
     *
     *   @OA\Parameter(
     *      name="bankId",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response( response=401, description="Unauthenticated" ),
     *   @OA\Response( response=400, description="Bad Request" ),
     *   @OA\Response( response=404, description="Not found" ),
     *   @OA\Response( response=403, description="Forbidden" ),
     *   @OA\Response( response=405, description="Method not allowed" ),
     *   @OA\Response( response=422, description="Failed validation" ),
     *   @OA\Response( response=429, description="Too many request" ),
     *   @OA\Response( response=500, description="Internal server error" ),
     *   @OA\Response( response=503, description="Service uavailable" )
     *)
     * @param $bank_id
     * @param PinRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\AppException
     * @throws \Throwable
     */
    public function deleteBankAccount($bank_id, Request $request)
    {
        DB::beginTransaction();
        try{
            $req = $this->bankService->deleteBankAccount($bank_id, $request->all());
            if( is_object($req) ) $req = $req->toArray();

            DB::commit();
            return success( 'success', $req );
        }
        catch(\Throwable $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get basic info tied to a bank account
     */
    public function bankAccountEnquiry(Request $request)
    {
            $req = $this->bankService->recipientEnquiry($request->nuban, $request->code);
            
            return success($req['message'] ?? 'success', $req['data'] ?? $req);
    }
}
