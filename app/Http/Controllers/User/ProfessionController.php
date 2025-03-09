<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Illuminate\Support\Facades\Validator;

class ProfessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['index', 'single']]);
        $this->middleware('check.payment');
    }

    public function index()
    {
        try {
            $professions = Profession::all();
            return response()->json($professions);
        } catch (\Exception $exception) {
            return response()->json([], 500);
        }
    }

    public function single($id)
    {
        $profession = Profession::find($id);
        return response()->json($profession);
    }

    public function indexAdmin()
    {
        $professions = Profession::paginate(10);
        return response()->json($professions);
    }

    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'name_am' => 'required',
            'name_ru' => 'required',
            'name_en' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['message'=> $validator->errors()->first()],400);
        }

        if($profession = Profession::create($validator->validate())){
            return response()->json(['message' => 'Profession Created','data'=> $profession],200);
        }
        return response()->json(['message'=> 'Profession not created.'],400);
    }

    public function delete($id)
    {
        $deleted = Profession::destroy($id);
        return response()->json(['message' => $deleted ? 'Profession deleted successfully': 'Something went wrong!']);
    }
}
