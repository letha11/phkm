export interface Patient {
  id: number;
  patient_id: number;
  name: string;
  birthDate: string;
  timeAgo: string;
  complaint: string;
  medications: string;
  status: 'waiting' | 'success' | 'failed';
  payment_status: 'waiting' | 'failed' | 'success';
  doctor_name: string;
  submitted_at: string;
  total_amount?: number;
  consultation_fee?: number;
  ppn_rate_applied?: number;
  paid_amount?: number;
  payment_method?: string;
  notes_pharmacist?: string;
  prescription_items: PrescriptionItem[];
}