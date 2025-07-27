<!-- resources/views/subir-excel.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>Subir Excel</h1>

    @if(session('status'))
        <div style="color: green">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div style="color: red">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('subir.excel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="excel">Selecciona archivo Excel:</label>
        <input type="file" name="excel" id="excel" required>
        <button type="submit">Subir</button>
    </form>
@endsection
