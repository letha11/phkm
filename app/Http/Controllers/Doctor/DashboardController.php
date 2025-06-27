<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the doctor dashboard with form for creating prescriptions
     */
    public function index(): Response
    {
        // Get available medicines for the form
        $medicines = Medicine::select('id', 'name', 'stock', 'dosages', 'price', 'type', 'description')
            ->orderBy('name')
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'stock' => $medicine->stock,
                    'dosages' => $medicine->dosages,
                    'price' => $medicine->price,
                    'type' => $medicine->type,
                    'description' => $medicine->description,
                    'is_available' => $medicine->stock > 0,
                ];
            });

        return Inertia::render('doctor/Dashboard', [
            'medicines' => $medicines,
        ]);
    }

    /**
     * Search for existing patients
     */
    public function searchPatients(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->get('search', '');
        
        if (empty($search) || strlen($search) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where('name', 'like', '%' . $search . '%')
            ->select('id', 'name', 'date_of_birth')
            ->limit(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'formatted_date' => $patient->date_of_birth->format('d-m-Y'),
                ];
            });

        return response()->json($patients);
    }

    /**
     * Search for medicines
     */
    public function searchMedicines(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->get('search', '');
        
        // if (empty($search) || strlen($search) < 2) {
        //     return response()->json([]);
        // }

        $medicines = Medicine::where('name', 'like', '%' . $search . '%')
            ->select('id', 'name', 'stock', 'dosages', 'price', 'type', 'description')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'stock' => $medicine->stock,
                    'dosages' => $medicine->dosages,
                    'price' => $medicine->price,
                    'type' => $medicine->type,
                    'description' => $medicine->description,
                    'is_available' => $medicine->stock > 0,
                ];
            });

        return response()->json($medicines);
    }

    /**
     * Submit consultation and prescription
     */
    public function submitPrescription(Request $request): RedirectResponse
    {
        $request->validate([
            'patient.name' => 'required|string|max:255',
            'patient.date_of_birth' => 'required|date|before:today',
            'patient.existing_id' => 'nullable|exists:patients,id',
            'symptom' => 'required|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.medicine_id' => 'required|exists:medicines,id',
            'medicines.*.dosage' => 'required|string',
            'medicines.*.amount' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Create or use existing patient
                if ($request->input('patient.existing_id')) {
                    $patient = Patient::findOrFail($request->input('patient.existing_id'));
                } else {
                    $patient = Patient::create([
                        'name' => $request->input('patient.name'),
                        'date_of_birth' => $request->input('patient.date_of_birth'),
                    ]);
                }

                // Create prescription
                $prescription = Prescription::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => Auth::id(),
                    'symptom' => $request->input('symptom'),
                    'prescription_status' => 'accepted',
                    'payment_status' => 'waiting',
                    'submitted_at' => now(),
                ]);

                // Add prescription items and update medicine stock
                foreach ($request->input('medicines') as $medicineData) {
                    $medicine = Medicine::findOrFail($medicineData['medicine_id']);
                    
                    // Check stock availability
                    if ($medicine->stock < $medicineData['amount']) {
                        throw new \Exception("Stok {$medicine->name} tidak mencukupi. Stok tersedia: {$medicine->stock}");
                    }

                    // Create prescription item
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $medicine->id,
                        'medicine_dosage_prescribed' => $medicineData['dosage'],
                        'medicine_amount_prescribed' => $medicineData['amount'],
                        'medicine_price_at_prescription' => $medicine->price,
                    ]);

                    // Update medicine stock
                    $medicine->decrement('stock', $medicineData['amount']);
                }
            });

            return redirect()->back()->with([
                'message' => 'Resep berhasil dikirim ke apoteker!',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Gagal mengirim resep: ' . $e->getMessage(),
                'type' => 'error',
            ])->withInput();
        }
    }
} 