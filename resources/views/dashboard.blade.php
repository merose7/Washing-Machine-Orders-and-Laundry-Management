@extends('adminlte::page') 

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
@endsection

@section('content')
    <div class="row">
        {{-- Total Machines --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalMachines }}</h3>
                    <p>Total Mesin Cuci</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <a href="{{ route('machines.index') }}" class="small-box-footer">Kelola Mesin <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

    </div>
@endsection
