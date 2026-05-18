@extends('errors.layout')

@section('emoji') 🔍 @endsection
@section('error_code') 404 @endsection
@section('error_title') Halaman Tidak Ditemukan @endsection
@section('error_desc')
    Maaf, halaman yang Anda cari tidak ditemukan. Mungkin URL salah,
    atau halaman ini sudah dipindahkan / dihapus.
@endsection

@section('extra_actions')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-secondary">
        ← Kembali
    </a>
@endsection
