<h2>Verificar correo</h2>

@if(session('success'))
    <p style="color:green">
        {{ session('success') }}
    </p>
@endif

@if($errors->any())
    <div style="color:red">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form
    method="POST"
    action="{{ route('verify.register.post') }}"
>
    @csrf

    <input
        type="text"
        name="code"
        placeholder="Código"
    >

    <button type="submit">
        Verificar correo
    </button>
</form>