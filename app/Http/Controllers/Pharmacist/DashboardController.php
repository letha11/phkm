<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the pharmacist dashboard with list of prescriptions
     */
    public function index(Request $request): Response
    {
        // Get filter parameters
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $dateRange = $request->get('date_range', 'all');
        $doctor = $request->get('doctor', 'all');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 12);

        // Validate per_page options
        if (!in_array($perPage, [8, 12, 16, 24])) {
            $perPage = 12;
        }

        // Build the query
        $query = Prescription::with(['patient', 'doctor', 'prescriptionItems.medicine'])
            ->orderBy('submitted_at', 'desc');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if ($status !== 'all') {
            switch ($status) {
                case 'accepted':
                    $query->where('prescription_status', 'accepted');
                    break;
                case 'preparing':
                    $query->where('prescription_status', 'preparing');
                    break;
                case 'completed':
                    $query->where('prescription_status', 'completed');
                    break;
            }
        }

        // Apply date range filter
        if ($dateRange !== 'all') {
            $now = Carbon::now();
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('submitted_at', $now->toDateString());
                    break;
                case 'week':
                    $query->where('submitted_at', '>=', $now->subDays(7));
                    break;
                case 'month':
                    $query->where('submitted_at', '>=', $now->subDays(30));
                    break;
            }
        }

        // Apply doctor filter
        if ($doctor !== 'all') {
            $query->whereHas('doctor', function ($q) use ($doctor) {
                $q->where('name', $doctor);
            });
        }

        // Get paginated results
        $prescriptions = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform the data
        $transformedPrescriptions = $prescriptions->getCollection()->map(function ($prescription) {
            return $this->transformPrescriptionData($prescription);
        });

        // Get unique doctors for filter dropdown
        $uniqueDoctors = Prescription::with('doctor')
            ->join('users', 'prescriptions.doctor_id', '=', 'users.id')
            ->distinct()
            ->pluck('users.name')
            ->sort()
            ->values();

        // Get statistics for all data (not filtered)
        $totalStats = $this->getPrescriptionStats();

        // Get filtered statistics
        $filteredStats = $this->getFilteredPrescriptionStats($request);

        return Inertia::render('pharmacist/Dashboard', [
            'patients' => $transformedPrescriptions,
            'pagination' => [
                'current_page' => $prescriptions->currentPage(),
                'last_page' => $prescriptions->lastPage(),
                'per_page' => $prescriptions->perPage(),
                'total' => $prescriptions->total(),
                'from' => $prescriptions->firstItem(),
                'to' => $prescriptions->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_range' => $dateRange,
                'doctor' => $doctor,
                'per_page' => $perPage,
            ],
            'unique_doctors' => $uniqueDoctors,
            'stats' => [
                'total' => $totalStats,
                'filtered' => $filteredStats,
            ]
        ]);
    }

    /**
     * Get prescription statistics for all data
     */
    private function getPrescriptionStats(): array
    {
        $total = Prescription::count();
        $completed = Prescription::where('prescription_status', 'completed')
                                ->count();
        $pending = Prescription::where('prescription_status', 'accepted')
                             ->count();
        $preparing = Prescription::where('prescription_status', 'preparing')
                               ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'preparing' => $preparing,
        ];
    }

    /**
     * Get filtered prescription statistics
     */
    private function getFilteredPrescriptionStats(Request $request): array
    {
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $dateRange = $request->get('date_range', 'all');
        $doctor = $request->get('doctor', 'all');

        // Build base query for filtered stats
        $baseQuery = Prescription::query();

        // Apply search filter
        if (!empty($search)) {
            $baseQuery->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Apply date range filter
        if ($dateRange !== 'all') {
            $now = Carbon::now();
            switch ($dateRange) {
                case 'today':
                    $baseQuery->whereDate('submitted_at', $now->toDateString());
                    break;
                case 'week':
                    $baseQuery->where('submitted_at', '>=', $now->subDays(7));
                    break;
                case 'month':
                    $baseQuery->where('submitted_at', '>=', $now->subDays(30));
                    break;
            }
        }

        // Apply doctor filter
        if ($doctor !== 'all') {
            $baseQuery->whereHas('doctor', function ($q) use ($doctor) {
                $q->where('name', $doctor);
            });
        }

        // Calculate filtered statistics
        $total = (clone $baseQuery)->count();
        
        $completed = (clone $baseQuery)->where('prescription_status', 'completed')
                                      ->count();
        
        $pending = (clone $baseQuery)->where('prescription_status', 'accepted')
                                    ->count();
        
        $preparing = (clone $baseQuery)->where('prescription_status', 'preparing')
                                      ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'preparing' => $preparing,
        ];
    }

    /**
     * Transform prescription data for frontend
     */
    private function transformPrescriptionData(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'patient_id' => $prescription->patient_id,
            'name' => $prescription->patient->name,
            'birthDate' => $prescription->patient->date_of_birth->format('d-m-Y'),
            'timeAgo' => $prescription->submitted_at->diffForHumans(),
            'complaint' => $prescription->symptom,
            'medications' => $prescription->prescriptionItems->map(function ($item) {
                return $item->medicine->name . ', ' . $item->medicine_dosage_prescribed . ', ' . $item->medicine_amount_prescribed;
            })->join("\n"),
            'status' => $prescription->prescription_status,
            'prescription_status' => $prescription->prescription_status,
            'payment_status' => $prescription->payment_status,
            'doctor_name' => $prescription->doctor->name,
            'submitted_at' => $prescription->submitted_at,
        ];
    }
}
