<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\{UserAttendance,User};

class HrController extends Controller
{
      public function attendance_report(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $userQuery = User::where('type', 2);

        if ($request->filled('user_id')) {
            $userQuery->where('id', $request->user_id);
        }

        // Fetch all employees
        $users = $userQuery->get();

        // Preload all attendances for the month once
        $attendances = UserAttendance::whereMonth('start_date', $month)
            ->whereYear('start_date', $year)
            ->get()
            ->groupBy([
                'user_id',
                fn ($item) => Carbon::parse($item->start_date)->day
            ]);

        $attendanceSheet = [];

        foreach ($users as $user) {
                $dailyStatus = [];
                $present = 0;
                $absent = 0;
                $weekoff = 0;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = Carbon::create($year, $month, $day);
                    $today = now()->startOfDay();

                    if (isset($attendances[$user->id][$day][0])) {
                        // Logged in
                        $status = 'P';
                        $present++;
                    }
                    elseif ($date->isWeekend()) {
                        // Weekend
                        $status = 'W';
                        $weekoff++;
                    }
                    elseif ($date->gt($today)) {
                        $status = '-';
                    }
                    else {
                        // Past or today & not logged in
                        $status = 'A';
                        $absent++;
                    }

                    $dailyStatus[$day] = $status;
                }

                $attendanceSheet[] = [
                    'user' => $user,
                    'attendance' => $dailyStatus,
                     'summary' => [
                        'present' => $present,
                        'absent'  => $absent,
                        'weekoff' => $weekoff,
                    ]
                ];
            }

        // dd($attendanceSheet);

        return view('admin.employee_attendance.index', compact(
            'attendanceSheet',
            'month',
            'year',
            'daysInMonth',
            'users'
        ));
    }
}
