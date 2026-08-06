<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\JudgingSession;

class JudgingSessionController extends Controller
{
    /**
     * แสดงรายการห้องตัดสินของผู้จัดการแข่งขัน
     */
    public function index()
    {
        return view('competition-admin.judging-room.index');
    }

    /**
     * แสดงหน้าควบคุมห้องตัดสิน
     */
    public function show(Competition $competition)
    {
        return view('competition-admin.judging-room.show');
    }
}




