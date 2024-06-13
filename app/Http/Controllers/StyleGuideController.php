<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use App\Models\Setting;
use Illuminate\Http\Request;

class StyleGuideController extends Controller
{
    public function show()
    {
        $setting = Setting::createNewSetting();

        return inertia('StyleGuide/Show', [
            'personas' => PersonaResource::collection(Persona::all()),
        ]);
    }

    public function updatePersona(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'name' => 'string|required',
            'content' => 'string|required',
        ]);

        $persona->update($validated);

        request()->session()->flash('flash.banner', 'Persona updated');

        return back();
    }

    public function createPersona(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|required',
            'content' => 'string|required',
        ]);

        Persona::create($validated);

        request()->session()->flash('flash.banner', 'Persona Created');

        return back();
    }
}
