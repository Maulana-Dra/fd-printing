<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cart tidak butuh auth
    }

    public function rules(): array
    {
        $maxKb = config('printing.upload.max_upload_size_mb', 50) * 1024;
        $mimes = implode(',', config('printing.upload.allowed_mime_types', []));

        return [
            'product_id'           => ['required', 'integer', 'exists:products,id'],
            'quantity'             => ['required', 'integer', 'min:1'],
            'selected_options'     => ['nullable', 'array'],
            'selected_options.*'   => ['integer', 'exists:product_options,id'],
            'design_file'          => ['nullable', 'file', "max:{$maxKb}", "mimes:{$mimes}"],
            'notes'                => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'  => 'Produk tidak valid.',
            'product_id.exists'    => 'Produk tidak ditemukan.',
            'quantity.required'    => 'Jumlah pesanan wajib diisi.',
            'quantity.min'         => 'Jumlah minimal 1.',
            'design_file.max'      => 'Ukuran file maksimal ' . config('printing.upload.max_upload_size_mb') . ' MB.',
            'design_file.mimes'    => 'Format file tidak didukung.',
            'notes.max'            => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
