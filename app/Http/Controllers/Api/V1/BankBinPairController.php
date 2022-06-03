<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankBinPair;
use App\Models\CreditCard;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class BankBinPairController extends Controller
{
    public function getBankBinPairMemberNo($prefix_no)
    {
        try {
            $member_no = BankBinPair::query()->where('prefix_no',$prefix_no)->first()->member_no;
            if (!isset($member_no)){
                $member_no = 000000;
            }
            $card_member = CreditCard::query()->where('member_no',$member_no)->first();
            if (!isset($card_member)){
                $member_no = 000000;
            }
            return response(['message' => 'Sipariş ekleme işlemi başarılı.', 'status' => 'success','object' => ['member_no' => $member_no]]);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
    }

}
