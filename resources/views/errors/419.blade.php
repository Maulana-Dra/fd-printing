@extends('errors.layout')

@section('emoji') ⏱️ @endsection
@section('error_code') 419 @endsection
@section('error_title') Sesi Kedaluwarsa @endsection
@section('error_desc')
    Sesi Anda telah kedaluwarsa karena tidak ada aktivitas dalam waktu lama,
    atau token keamanan sudah tidak valid. Silakan muat ulang halaman dan
    coba lagi.
@endsection

@section('extra_actions')
    <button onclick="window.history.back()" class="btn btn-secondary">
        🔄 Muat Ulang
    </button>
@endsection
