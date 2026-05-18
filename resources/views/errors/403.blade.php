@extends('errors.layout')

@section('emoji') 🚫 @endsection
@section('error_code') 403 @endsection
@section('error_title') Akses Ditolak @endsection
@section('error_desc')
    Anda tidak memiliki izin untuk mengakses halaman ini.
    Jika Anda merasa ini adalah kesalahan, silakan hubungi kami.
@endsection

@section('extra_actions')
    @auth
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
            📋 Pesanan Saya
        </a>
    @else
        <a href="{{ route('login') }}" class="btn btn-secondary">
            🔑 Masuk
        </a>
    @endauth
@endsection
