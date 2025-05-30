<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function recordAttendance(Request $request)
    {
        // Validate the request
        $request->validate([
            'uid' => 'required|string',
        ]);

        // Find the card
        $card = Card::where('uid', $request->uid)->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not registered',
            ], 404);
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        // Check if attendance record exists for today
        $attendance = Attendance::where('card_id', $card->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            // Create time-in record
            $attendance = Attendance::create([
                'card_id' => $card->id,
                'date' => $today,
                'time_in' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time-in recorded successfully',
                'name' => $card->name,
                'matricID' => $card->matric_id,
                'date' => $today,
                'time_in' => $now,
            ]);
        } else {
            // Update time-out record if time-in exists and time-out doesn't
            if ($attendance->time_in && !$attendance->time_out) {
                $attendance->update([
                    'time_out' => $now,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Time-out recorded successfully',
                    'name' => $card->name,
                    'matricID' => $card->matric_id,
                    'date' => $today,
                    'time_in' => $attendance->time_in,
                    'time_out' => $now,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already completed for today',
                ], 400);
            }
        }
    }

    public function index()
    {
        $attendances = Attendance::with('card')->get();
        $formattedAttendances = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'name' => $attendance->card->name,
                'matric_id' => $attendance->card->matric_id,
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out,
            ];
        });

        return view('attendance.index', compact('formattedAttendances'));
    }
}
