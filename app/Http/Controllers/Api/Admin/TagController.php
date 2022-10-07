<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductTab;
use App\Models\ProductTabContent;
use App\Models\ProductTags;
use App\Models\Tag;
use App\Models\TextContent;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Nette\Schema\ValidationException;

class TagController extends Controller
{

    public function addTag(Request $request)
    {

        try {

            $request->validate([
                'name'=>'required'
            ]);
            $tag_id = Tag::query()->insertGetId([
                'name' => null
            ]);

            $name_id = TextContent::query()->insertGetId([
                'original_text' => $request->name
            ]);
            Tag::query()->where('id',$tag_id)->update([
                'name' => $name_id
            ]);

            return response(['message' => 'Etiket ekleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'er' => $throwable->getMessage()]);
        }
    }

    public function updateTag(Request $request,$id){
        try {
            $tag = Tag::query()->where('id',$id)->first();
            Tag::query()->where('id',$id)->update([
                'name' => $tag->name
            ]);

            TextContent::query()->where('id',$tag->name)->update([
                'original_text' => $request->name
            ]);

            return response(['message' => 'Etiket güncelleme işlemi başarılı.','status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','ar' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

    public function deleteTag($id){
        try {
            Tag::query()->where('id',$id)->update([
                'active'=>0
            ]);
            $tag = Tag::query()->where('id',$id)->first();
            $text_contents = TextContent::query()->where('active',1)->get();
            foreach ($text_contents as $text_content){
                if ($tag->name == $text_content->id){
                    TextContent::query()->where('id',$tag->name)->update([
                        'active' => 0
                    ]);
                }
            }
            return response(['message' => 'Etiket silme işlemi başarılı.','status' => 'success']);
        } catch (ValidationException $validationException) {
            return  response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.','status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','ar' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return  response(['message' => 'Hatalı işlem.','status' => 'error-001','ar' => $throwable->getMessage()]);
        }
    }

}
