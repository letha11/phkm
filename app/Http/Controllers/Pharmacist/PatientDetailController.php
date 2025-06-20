<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PatientDetailController extends Controller
{
    /**
     * Show prescription details
     */
    public function show(string $id): Response
    {
        // Always load fresh data from database
        $prescription = Prescription::with(['patient', 'doctor', 'prescriptionItems.medicine'])
            ->findOrFail($id);

        $prescriptionData = $this->formatPrescriptionData($prescription);

        return Inertia::render('pharmacist/PatientDetail', [
            'patient' => $prescriptionData
        ]);
    }


    /**
     * Process payment
     */
    public function processPayment(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|string',
            'notes_pharmacist' => 'nullable|string'
        ]);

        $prescription = Prescription::findOrFail($id);

        // Calculate totals if not already calculated
        if (!$prescription->total_amount) {
            $this->calculatePrescriptionTotal($prescription);
        }

        DB::transaction(function () use ($prescription, $request) {
            $prescription->payment_status = 'success';
            $prescription->payment_method = $request->payment_method;
            $prescription->paid_amount = $prescription->total_amount;
            $prescription->paid_at = now();
            $prescription->notes_pharmacist = $request->notes_pharmacist;

            $prescription->save();
        });

        return redirect()->back()->with([
            'message' => 'Pembayaran berhasil diproses',
            'type' => 'success',
        ]);
    }

    public function getInvoiceData(Request $request, string $id): RedirectResponse
    {
        $prescription = Prescription::with(['patient', 'doctor', 'prescriptionItems.medicine'])
            ->where('payment_status', 'success')
            ->findOrFail($id);

        $invoiceData = [
            'invoice_number' => 'INV-' . str_pad((string)$prescription->id, 6, '0', STR_PAD_LEFT),
            'date' => $prescription->paid_at->format('d/m/Y H:i'),
            'patient' => [
                'name' => $prescription->patient->name,
                'date_of_birth' => $prescription->patient->date_of_birth->format('d/m/Y'),
            ],
            'doctor' => $prescription->doctor->name,
            'items' => $prescription->prescriptionItems->map(function ($item) {
                return [
                    'medicine_name' => $item->medicine->name,
                    'dosage' => $item->medicine_dosage_prescribed,
                    'quantity' => $item->medicine_amount_prescribed,
                    'price_per_unit' => $item->medicine_price_at_prescription,
                    'total_price' => $item->medicine_price_at_prescription * $item->medicine_amount_prescribed,
                ];
            }),
            'consultation_fee' => $prescription->consultation_fee,
            'medicines_subtotal' => $prescription->prescriptionItems->sum(function ($item) {
                return $item->medicine_price_at_prescription * $item->medicine_amount_prescribed;
            }),
            'ppn_rate' => $prescription->ppn_rate_applied * 100, // Convert to percentage
            'ppn_amount' => ($prescription->total_amount - $prescription->consultation_fee - $prescription->prescriptionItems->sum(function ($item) {
                return $item->medicine_price_at_prescription * $item->medicine_amount_prescribed;
            })),
            'total_amount' => $prescription->total_amount,
            'paid_amount' => $prescription->paid_amount,
            'payment_method' => $prescription->payment_method,
        ];

        return redirect()->back()->with([
            'invoice' => $invoiceData,
            'showInvoiceModal' => true,
        ]);
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'prescription_status' => 'required|string|in:accepted,preparing,completed',
            'notes_pharmacist' => 'nullable|string'
        ]);

        $prescription = Prescription::findOrFail($id);

        DB::transaction(function () use ($prescription, $request) {
            $prescription->prescription_status = $request->prescription_status;
            $prescription->notes_pharmacist = $request->notes_pharmacist;

            // If status is completed, update payment status to waiting
            if ($request->prescription_status === 'completed') {
                $prescription->payment_status = 'waiting';
            }

            $prescription->save();
        });

        return redirect()->back()->with([
            'message' => 'Status resep berhasil diupdate',
            'type' => 'success',
        ]);
    }

    /**
     * Format prescription data consistently
     */
    private function formatPrescriptionData(Prescription $prescription): array
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
            'total_amount' => $prescription->total_amount,
            'consultation_fee' => $prescription->consultation_fee,
            'ppn_rate_applied' => $prescription->ppn_rate_applied,
            'paid_amount' => $prescription->paid_amount,
            'payment_method' => $prescription->payment_method,
            'notes_pharmacist' => $prescription->notes_pharmacist,
            'prescription_items' => $prescription->prescriptionItems->map(function ($item) {
                return [
                    'medicine_name' => $item->medicine->name,
                    'dosage' => $item->medicine_dosage_prescribed,
                    'amount' => $item->medicine_amount_prescribed,
                    'price' => $item->medicine_price_at_prescription,
                    'total_price' => $item->medicine_price_at_prescription * $item->medicine_amount_prescribed,
                ];
            }),
        ];
    }

    /**
     * Calculate prescription total amount
     */
    private function calculatePrescriptionTotal(Prescription $prescription): void
    {
        $medicinesTotal = $prescription->prescriptionItems->sum(function ($item) {
            return $item->medicine_price_at_prescription * $item->medicine_amount_prescribed;
        });

        $consultationFee = $prescription->consultation_fee ?: 50000; // Default consultation fee
        $subtotal = $medicinesTotal + $consultationFee;
        
        $ppnRate = $prescription->ppn_rate_applied ?: 0.11; // Default 11% PPN
        $ppnAmount = $subtotal * $ppnRate;
        
        $total = $subtotal + $ppnAmount;

        $prescription->update([
            'consultation_fee' => $consultationFee,
            'ppn_rate_applied' => $ppnRate,
            'total_amount' => $total,
        ]);
    }
}
