<?php

namespace App\Http\Controllers\Api\V1;

use App\Mail\UserWelcome;
use App\Models\ContactRule;
use App\Models\User;
use App\Models\UserContactRule;
use App\Models\UserDocumentCheck;
use App\Models\UserProfile;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Nette\Schema\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'user_name' => 'nullable',
                'email' => 'required|email',
                'phone_number' => 'required',
                'password' => 'required'
            ]);

            //Önce Kullanıcıyı oluşturuyor
            $userId = User::query()->insertGetId([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'token' => Str::random(60)
            ]);

            //İletişim Kurallarını oluşturuyor
            $user_contact_rules = $request->user_contact_rules;
            foreach ($user_contact_rules as $user_contact_rule){
                UserContactRule::query()->insert([
                    'user_id' => $userId,
                    'contact_rule_id' => $user_contact_rule['contact_rule_id'],
                    'value' => $user_contact_rule['value']
                ]);
            }

            //Kullanıcının dökümanlarını ekliyor
            $user_document_checks = $request->user_document_checks;
            foreach ($user_document_checks as $user_document_check){
                UserDocumentCheck::query()->insert([
                    'user_id' => $userId,
                    'document_id' => $user_document_check['document_id'],
                    'value' => $user_document_check['value']
                ]);
            }
            //Kullanıcı profilini oluşturuyor
            $name = $request->name;
            $surname = $request->surname;
            UserProfile::query()->insert([
                'user_id' => $userId,
                'name' => $name,
                'surname' => $surname
            ]);

            // Oluşturulan kullanıcıyı çekiyor
            $user = User::query()->whereId($userId)->first();

            //Oluşturulan Kullanıcıyı mail yolluyor
            $user->sendApiConfirmAccount($user);

            return response(['message' => __('Kullanıcı başarıyla oluşturuldu sisteme giriş için epostanızı kontrol ediniz.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001','error' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001','ero' => $throwable->getMessage()]);
        }

    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::query()->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new \Exception('auth-001');
            }

            $userToken = $user->createToken('api-token', ['role:user'])->plainTextToken;
            User::query()->where('id', $user->id)->update([
                'token' => $userToken
            ]);

            $user->token = $userToken;

            return  response(['message' => __('Başarılıs.'),'status' => 'success', 'object' => ['user'=>$user]]);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Exception $exception){
            if ($exception->getMessage() == 'auth-001'){
                return response(__('Eposta veya şifre hatalı.'));
            }
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001']);
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();
            return response(['message' => __('Çıkış başarılı.'),'status' => 'success']);
        } catch (\Exception $exception){
            return response(['message' => __('Hatalı işlem.'),'status' => 'error-001']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */

    protected function verify(Request $request)
    {
        try {
            $user = User::query()->where('token', $request['token'])->first();
            if ($user->verified == 1 && $user->email_verified_at != null) {
                throw new \Exception('validation-002');
            }
            $user->email_verified_at = now();
            $user->verified = true;
            $user->active = true;
            $user->token = null;
            $user->save();
            /*
               $setDelay = Carbon::parse($user->email_verified_at)->addSeconds(10);
               Bu kısımda isterseniz Kullanıcıya Hoşgeldinizi Maili İçin Gecikme Verebilirsiniz.
               Mail::queue(new \App\Mail\UserWelcome($user->name, $user->email))->delay($setDelay);
              */
            Mail::queue(new UserWelcome($user->name, $user->email));
            return response(['message' => __('Kullanıcı epostası doğrulandı.'),'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Exception $exception){
            if ($exception->getMessage() == 'validation-002'){
                return response(__('Eposta adresi daha önceden doğrulanmış.'));
            }
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001']);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */

    protected function resend(Request $request)
    {

        try {
            $user = User::query()->where('email', $request['email'])->first();
            if ($user->hasVerifiedEmail()) {
                throw new \Exception('validation-002');
            }
            $user->sendApiConfirmAccount($user);
            return response(['message' => __('Yeniden eposta gönderildi.'), 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => __('Lütfen girdiğiniz bilgileri kontrol ediniz.'),'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => __('Hatalı sorgu.'),'status' => 'query-001']);
        } catch (\Exception $exception){
            if ($exception->getMessage() == 'validation-002'){
                return response(__('Eposta adresi daha önceden doğrulanmış.'));
            }
            return  response(['message' => __('Hatalı işlem.'),'status' => 'error-001']);
        }
    }
}
