@if(session('success') || session('error') || ($errors ?? false && $errors->any()))
    <div class="p-6">
        @if(session('success'))
            <div class="rounded-2xl bg-green-50 border border-green-200 p-4 text-green-800 mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800 mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(($errors ?? false) && $errors->any())
            <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-800">
                <p class="font-semibold mb-2">Se encontraron los siguientes errores:</p>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
