<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses dibatasi hanya user yang sudah login
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxSizeMb    = config('printing.upload.max_upload_size_mb', 50);
        $maxSizeKb    = $maxSizeMb * 1024;

        // Daftar ekstensi dari config/printing.php
        $allowedExts  = config('printing.upload.allowed_extensions', []);

        return [
            'file' => [
                'required',
                File::types($allowedExts)
                    ->max($maxSizeKb),
            ],

            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],

            'replace_path' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        $maxSizeMb   = config('printing.upload.max_upload_size_mb', 50);
        $allowedExts = implode(', ', config('printing.upload.allowed_extensions', []));

        return [
            'file.required' => 'File desain wajib disertakan.',
            'file.max'      => "Ukuran file maksimal {$maxSizeMb} MB.",
            'file.types'    => "Format file tidak didukung. Format yang diterima: {$allowedExts}.",

            'order_id.required' => 'ID order tidak valid.',
            'order_id.integer'  => 'ID order tidak valid.',
            'order_id.exists'   => 'Order tidak ditemukan.',
        ];
    }

    /**
     * Tambahan validasi setelah rules() lolos:
     * verifikasi bahwa order_id memang milik user yang sedang login.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
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
