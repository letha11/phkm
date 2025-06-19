<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $prescription_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property numeric $subtotal_medicines
 * @property numeric $consultation_fee
 * @property numeric $ppn_amount
 * @property numeric $grand_total
 * @property string $payment_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Prescription $prescription
 * @method static \Database\Factories\InvoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereConsultationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePpnAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePrescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotalMedicines($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $stock
 * @property string $type
 * @property string|null $description
 * @property array<array-key, mixed> $dosages
 * @property numeric $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrescriptionItem> $prescriptionItems
 * @property-read int|null $prescription_items_count
 * @method static \Database\Factories\MedicineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereDosages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Medicine withoutTrashed()
 */
	class Medicine extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $date_of_birth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prescription> $prescriptions
 * @property-read int|null $prescriptions_count
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUpdatedAt($value)
 */
	class Patient extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $patient_id
 * @property int $doctor_id
 * @property string $symptom
 * @property string $payment_status
 * @property numeric|null $consultation_fee
 * @property numeric|null $ppn_rate_applied
 * @property numeric|null $total_amount
 * @property numeric|null $paid_amount
 * @property string|null $payment_method
 * @property string|null $notes_pharmacist
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $doctor
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \App\Models\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrescriptionItem> $prescriptionItems
 * @property-read int|null $prescription_items_count
 * @method static \Database\Factories\PrescriptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereConsultationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereNotesPharmacist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription wherePpnRateApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereSymptom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prescription whereUpdatedAt($value)
 */
	class Prescription extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $prescription_id
 * @property int $medicine_id
 * @property string $medicine_dosage_prescribed
 * @property int $medicine_amount_prescribed
 * @property numeric $medicine_price_at_prescription
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\Prescription $prescription
 * @method static \Database\Factories\PrescriptionItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereMedicineAmountPrescribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereMedicineDosagePrescribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereMedicinePriceAtPrescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem wherePrescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrescriptionItem whereUpdatedAt($value)
 */
	class PrescriptionItem extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prescription> $prescriptions
 * @property-read int|null $prescriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

