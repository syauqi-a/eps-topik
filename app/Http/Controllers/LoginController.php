<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request): RedirectResponse {
        return redirect(route('filament.app.auth.login'));
    }
}
