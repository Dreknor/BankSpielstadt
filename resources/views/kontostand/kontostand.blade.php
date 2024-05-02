@extends('kontostand.layout')

@section('content')
    <div class="container">
        <!--vertical align on parent using my-auto-->
        <div class="row h-100">
            <div class="col-sm-12 my-auto">
                <br>
                <div class="card bg-info">
                    <div class="card-header m-auto text-white border-bottom mt-5">
                        <h1>
                            Kontostand
                        </h1>
                    </div>
                    <div class="card-body text-white" style="min-height: 75vH">
                        <h4 class="mx-auto text-center" id="hinweis">
                            Bitte Chip scannen
                        </h4>
                        <form action="{{route('kontostand.read_key')}}" method="post" class="form-horizontal"  autocomplete="off">
                            @csrf
                            <input type="password" id="key_input" name="key" class="form-control p-3 w-75 mx-auto" autofocus="autofocus" autocomplete="off"  aria-autocomplete="none" placeholder="Chip scannen">
                        </form>
                        @if ($errors->any())
                            <p>
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            </p>

                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('form').submit(function(e) {
                $('#key_input').hide();
                $('#hinweis').text('Bitte warten...');

            });
        });
    </script>
@endpush
