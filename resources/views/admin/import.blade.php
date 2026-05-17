@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card p-6">
        <h2 class="text-2xl font-extrabold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-file-import text-brand-600"></i> Import
        </h2>
        <form action="{{ route('import') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="label">Datei</label>
                <input type="file" name="file" id="file" class="field @error('file') border-rose-400 @enderror">
                @error('file') <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-upload"></i> Importieren
            </button>
        </form>
    </div>
</div>
@endsection
@push('js')

@endpush
