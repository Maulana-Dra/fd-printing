<?php

namespace App\Http\Requests;

use App\Enums\DeliveryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $isDelivery = $this->input('delivery_type') === DeliveryType::DELIVERY->value;

        return [
            // ── Step 1: Pengiriman ────────────────────────────────────────────
            'delivery_type' => [
                'required',
                Rule::in([DeliveryType::PICKUP->value, DeliveryType::DELIVERY->value]),
            ],

            // ── Step 2a: Alamat penerima (wajib jika delivery) ────────────────
            'recipient_name' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:150',
            ],
            'recipient_phone' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:20',
            ],
            'shipping_address' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:500',
            ],
            'shipping_city' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:100',
            ],
            'shipping_province' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:100',
            ],
            'shipping_postal_code' => [
                Rule::requiredIf($isDelivery),
                'nullable', 'string', 'max:10',
            ],

            // ── Step 2b: Kurir & Ongkir (diatur manual / COD) ──────────────────
            'courier' => [
                'nullable', 'string', 'max:50',
            ],
            'courier_service' => [
                'nullable', 'string', 'max:100',
            ],
            'shipping_cost' => [
                'nullable', 'numeric', 'min:0',
            ],

            // ── Step 3: Catatan order ─────────────────────────────────────────
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_type.required' => 'Pilih metode pengiriman.',
            'delivery_type.in'       => 'Metode pengiriman tidak valid.',

            'recipient_name.required'     => 'Nama penerima wajib diisi.',
            'recipient_phone.required'    => 'Nomor HP penerima wajib diisi.',
            'shipping_address.required'   => 'Alamat pengiriman wajib diisi.',
            'shipping_city.required'      => 'Kota wajib diisi.',
            'shipping_province.required'  => 'Provinsi wajib diisi.',
            'shipping_postal_code.required' => 'Kode pos wajib diisi.',

            'courier.required'       => 'Pilih jasa kurir.',
            'shipping_cost.required' => 'Estimasi ongkir wajib diisi.',
            'shipping_cost.numeric'  => 'Ongkir harus berupa angka.',
            'shipping_cost.min'      => 'Ongkir tidak boleh negatif.',
        ];
    }

    /**
     * Isi otomatis recipient_name & recipient_phone dari data profil user
     * jika tidak diisi oleh customer (menghemat input).
     */
    protected function prepareForValidation(): void
    {
        $user = Auth::user();

        if ($this->input('delivery_type') === DeliveryType::DELIVERY->value && $user) {
            if (! $this->filled('recipient_name')) {
                $this->merge(['recipient_name' => $user?->name]);
            }
            if (! $this->filled('recipient_phone')) {
                $this->merge(['recipient_phone' => $user?->phone]);
            }
        }
    }
}
