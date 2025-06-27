export interface Medicine {
  id: number;
  name: string;
  stock: number;
  dosages: string[];
  price: number;
  type: string;
  description?: string;
  is_available: boolean;
}



export interface PatientData {
  [key: string]: any;
  id?: number;
  name: string;
  date_of_birth: string;
  existing_id?: number | null;
  formatted_date?: string;
}

export interface PrescriptionMedicine {
  [key: string]: any;
  medicine_id: number;
  medicine_name: string;
  dosage: string;
  amount: number;
  price: number;
} 