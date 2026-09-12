<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - AnabulMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 border border-slate-800">
        <!-- LOGO & HEADER -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-amber-500/30 text-white text-3xl">
                <i class="fa-solid fa-paw"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">[ADMIN] ANABULMART</h1>
            <p class="text-xs text-gray-400 mt-1">Masukkan kredensial untuk masuk ke dashboard</p>
        </div>

        <!-- PESAN ERROR JIKA GAGAL LOGIN -->
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 text-xs font-semibold flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- FORM LOGIN -->
        <form action="{{ route('admin.login.process') }}" method="POST" class="space-y-5">
            @csrf

            <!-- INPUT EMAIL -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Admin</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 text-sm">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@anabulmart.com" 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                </div>
            </div>

            <!-- INPUT PASSWORD -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                </div>
            </div>

            <!-- TOMBOL SUBMIT -->
            <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Dashboard
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-4">
            <p class="text-[11px] text-gray-400">&copy; {{ date('Y') }} AnabulMart Medan. All rights reserved.</p>
        </div>
    </div>

</body>
</html>