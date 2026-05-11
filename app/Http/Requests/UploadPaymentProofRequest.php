<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxSizeMb   = config('printing.upload.max_proof_size_mb', 5);
        $maxSizeKb   = $maxSizeMb * 1024;

        return [
            'proof' => [
                'required',
                File::image()
                    ->max($maxSizeKb)
                    ->types(['png', 'jpg', 'jpeg', 'webp', 'pdf']),
            ],

            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],
        ];
    }

    public function messages(): array
    {
        $maxSizeMb = config('printing.upload.max_proof_size_mb', 5);

        return [
            'proof.required' => 'Foto bukti pembayaran wajib disertakan.',
            'proof.max'      => "Ukuran file maksimal {$maxSizeMb} MB.",
            'proof.types'    => 'Format yang diterima: PNG, JPG, WebP, atau PDF.',

            'order_id.required' => 'ID order tidak valid.',
            'order_id.exists'   => 'Order tidak ditemukan.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            if ($this->filled('order_id')) {
                $order = \App\Models\Order::where('id', $this->order_id)
                    ->where('user_id', $this->user()->id)
                    ->first();

                if (! $order) {
                    $v->errors()->add('order_id', 'Order tidak ditemukan atau bukan milik Anda.');
                }
            }
        });
    }
}
