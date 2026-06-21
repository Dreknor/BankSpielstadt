@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="p-6 border-b-2 border-slate-100">
            <h2 class="text-2xl font-extrabold flex items-center gap-2">
                <i class="fa-solid fa-user-magnifying-glass text-brand-600"></i>
                {{ __('Kunde wählen') }}
            </h2>
        </div>
        <div class="p-6">
            @if (session('status'))
                <div class="rounded-2xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800 p-4 mb-4">
                    {{ session('status') }}
                </div>
            @endif
            <form method="POST" action="{{ url('choose/customer') }}" autocomplete="off">
                @csrf
                <label class="label" for="search">Bitte Name oder Chip scannen</label>
                <input id="search" name="suche" class="field text-xl" autofocus type="text" autocomplete="off" placeholder="z. B. Lisa oder Chip scannen">
            </form>
        </div>
        <div class="p-6 pt-0">
            <ul id="ergebnis" class="divide-y divide-slate-200 bg-white rounded-2xl ring-1 ring-slate-100 hidden">
            </ul>
        </div>
    </div>
</div>
@endsection

@push('js')
<script type="text/javascript">
    (function () {
        const route   = "{{ url('autocomplete-search') }}";
        const baseUrl = "{{ url('choose/customer') }}";
        const input   = document.getElementById('search');
        const list    = document.getElementById('ergebnis');
        let timer;

        function render(items) {
            list.innerHTML = '';
            if (!items || items.length === 0) { list.classList.add('hidden'); return; }
            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'p-4 hover:bg-brand-50 cursor-pointer font-semibold text-lg flex items-center gap-3';
                const keyHint = item.key ? '<span class="text-sm font-normal text-slate-400 ml-1">(Key: ' + item.key + ')</span>' : '';
                li.innerHTML = '<i class="fa-solid fa-user text-brand-600"></i>' + (item.name || item) + keyHint;
                li.addEventListener('click', () => {
                    window.location.href = baseUrl + '/' + item.id;
                });
                list.appendChild(li);
            });
            list.classList.remove('hidden');
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim().toLowerCase();
            if (q.length < 1) { list.innerHTML = ''; list.classList.add('hidden'); return; }
            timer = setTimeout(() => {
                fetch(route + '?query=' + encodeURIComponent(q) + '&name=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(items => {
                        render((items || []).slice(0, 20));
                    })
                    .catch(() => {});
            }, 150);
        });
    })();
</script>
@endpush

