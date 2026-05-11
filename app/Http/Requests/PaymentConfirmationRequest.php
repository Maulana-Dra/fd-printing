<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class PaymentConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan order milik user yang login
        $order = $this->route('order');
        return Auth::check() && $order && $order->user_id === Auth::id();
    }

    public function rules(): array
    {
        $maxKb = config('printing.upload.max_proof_size_mb', 5) * 1024;

        return [
            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],
            'amount_paid' => [
                'required',
                'numeric',
                'min:1',
            ],
            'transfer_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'proof_image' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])
                    ->max($maxKb),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        $maxMb = config('printing.upload.max_proof_size_mb', 5);

        return [
            'payment_method_id.required' => 'Pilih metode pembayaran yang digunakan.',
            'payment_method_id.exists'   => 'Metode pembayaran tidak valid.',

            'amount_paid.required' => 'Nominal transfer wajib diisi.',
            'amount_paid.numeric'  => 'Nominal transfer harus berupa angka.',
            'amount_paid.min'      => 'Nominal transfer tidak valid.',

            'transfer_date.required'        => 'Tanggal transfer wajib diisi.',
            'transfer_date.date'            => 'Format tanggal tidak valid.',
            'transfer_date.before_or_equal' => 'Tanggal transfer tidak boleh lebih dari hari ini.',

            'proof_image.required' => 'Foto bukti pembayaran wajib dilampirkan.',
            'proof_image.max'      => "Ukuran file maksimal {$maxMb} MB.",
            'proof_image.types'    => 'Format yang diterima: JPG, PNG, WebP, atau PDF.',

            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
