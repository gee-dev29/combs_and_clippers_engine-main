<?php

namespace App\Http\Controllers\App;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceProviderStatController extends Controller
{
    /**
     * Convert a duration string to minutes.
     * Handles durations in hours, minutes, and seconds.
  
     */
    private function convertToMinutes($duration)
    {
        // Normalize and trim the input string
        $duration = strtolower(trim($duration));

        // Use regex to extract the numeric value and its unit (e.g., "1 hour", "30 min")
        preg_match('/(\d+)\s*(hour|hr|hours|min|minutes|sec|seconds)?/', $duration, $matches);

        // Default values if parsing fails
        $value = isset($matches[1]) ? (int) $matches[1] : 0;
        $unit = $matches[2] ?? 'min'; // Default to 'min' (minutes) if no unit is provided

        // Convert the value to minutes based on the unit
        switch ($unit) {
            case 'hour':
            case 'hr':
            case 'hours':
                return $value * 60;
            case 'sec':
            case 'seconds':
                return ceil($value / 60);
            case 'min':
            case 'minutes':
            default:
                return $value;
        }
    }


    public function getTodayAppointmentsForServiceProvider(Request $request)
    {
        $today = now()->toDateString();
        $currentTime = now();

        $serviceProviderId = $this->getAuthID($request);

        // Fetch all appointments for today
        $appointments = Appointment::where('merchant_id', $serviceProviderId)
            ->whereDate('date', $today)
            ->with(['appointmentService.service'])
            ->get();

        // Initialize status groups
        $statusCounts = [
            'Cancelled' => 0,
            'No-Show' => 0,
            'Requested' => 0,
            'In Progress' => 0,
            'Completed' => 0,
        ];

        foreach ($appointments as $appointment) {
            $appointmentStartTime = Carbon::parse($appointment->time);
            $totalDuration = $appointment->appointmentService->sum(function ($appointmentService) {
                $serviceDurationRaw = $appointmentService->service->duration ?? '0min';
                return $this->convertToMinutes($serviceDurationRaw) * ($appointmentService->quantity ?? 1);
            });
            $appointmentEndTime = $appointmentStartTime->copy()->addMinutes($totalDuration);

            // Normalize status for comparison
            $normalizedStatus = strtolower($appointment->status);

            if (in_array($normalizedStatus, ['cancelled', 'canceled'])) {
                $statusCounts['Cancelled']++;
            } elseif ($normalizedStatus === 'no-show') {
                $statusCounts['No-Show']++;
            } elseif ($appointment->payment_status == 1 && $currentTime->lt($appointmentStartTime)) {
                $statusCounts['Requested']++;
            } elseif ($currentTime->between($appointmentStartTime, $appointmentEndTime)) {
                $statusCounts['In Progress']++;
            } elseif ($currentTime->gt($appointmentEndTime)) {
                $statusCounts['Completed']++;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $statusCounts,
        ]);
    }

    public function stats(Request $request)
    {
        $currentDate = Carbon::now();
        $startOfWeek = $currentDate->startOfWeek()->toDateString();
        $endOfWeek = $currentDate->endOfWeek()->toDateString();

        $serviceProviderId = $this->getAuthID($request);

        // Fetch all appointments for the current week
        $appointments = Appointment::where('merchant_id', $serviceProviderId)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with(['appointmentService.service'])
            ->get();

        // Initialize weekly stats
        $completedAppointments = [];
        $totalHoursBooked = [];
        $estimatedRevenue = [];

        // Populate stats for all days of the week
        foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day) {
            $completedAppointments[$day] = 0;
            $totalHoursBooked[$day] = 0;
            $estimatedRevenue[$day] = 0;
        }

        // Group appointments by date and process
        $appointments->groupBy('date')->each(function ($appointmentsForDay, $date) use (&$completedAppointments, &$totalHoursBooked, &$estimatedRevenue) {
            $dayOfWeek = Carbon::parse($date)->format('D');

            foreach ($appointmentsForDay as $appointment) {
                $appointmentStartTime = Carbon::parse($appointment->time);
                $currentTime = Carbon::now();

                // Normalize status for comparison
                $normalizedStatus = strtolower($appointment->status);

                if ($currentTime->gt($appointmentStartTime) && !in_array($normalizedStatus, ['cancelled', 'canceled'])) {
                    $completedAppointments[$dayOfWeek]++;
                    $totalDurationMinutes = $appointment->appointmentService->sum(function ($appointmentService) {
                        $serviceDuration = $this->convertToMinutes($appointmentService->service->duration ?? 0);
                        return $serviceDuration * ($appointmentService->quantity ?? 1);
                    });
                    $totalHoursBooked[$dayOfWeek] += round($totalDurationMinutes / 60, 2);
                    $estimatedRevenue[$dayOfWeek] += $appointment->total_amount ?? 0;
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'completed_appointments' => $completedAppointments,
                'total_hours_booked' => $totalHoursBooked,
                'estimated_revenue' => $estimatedRevenue,
            ],
        ]);
    }


    public function overview(Request $request)
    {

        $todayAppointments = $this->getTodayAppointmentsForServiceProvider($request)->getData()->data;


        $weeklyStats = $this->stats($request)->getData()->data;

        return response()->json([
            'status' => 'success',
            'data' => [
                'today_appointments' => $todayAppointments,
                'weekly_stats' => $weeklyStats
            ]
        ]);
    }
}