<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;

class AdminController extends Controller
{
    public function dashboard()
    {
        $lessons = Lesson::all();
        return view('admin.dashboard', [
            'lessons' => $lessons,
        ]);
    }
}
