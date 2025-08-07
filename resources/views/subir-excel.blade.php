@extends('template')

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
        <div class="card shadow-lg">
            <div class="card-body">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <label for="excel">Selecciona archivo Excel:</label>
                        <input class="form-control" type="file" name="excel" id="excel" required>
                    </div>
                </div>
            </div>
            <div class="py-2 px-3 text-end">
                <button type="submit" class="btn btn-primary">Subir</button>
            </div>
        </div>
    </form>
@endsection
