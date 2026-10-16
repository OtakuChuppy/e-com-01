<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PolicyController extends Controller
{
    public function show(string $page): View
    {
        abort_unless(in_array($page, ['shipping', 'returns', 'privacy'], true), 404);

        return view('policies.show', ['page' => $page]);
    }
}
