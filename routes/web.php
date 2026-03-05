    <?php

    use App\Http\Controllers\Auth\SproutAuthController;
    use Illuminate\Support\Facades\Route;

        Route::get('/', fn () => view('public.loading'))->name('loading');
        Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
        Route::post('/login', [SproutAuthController::class, 'login'])->name('login');
        Route::get('/dashboard', fn () => view('public.dashboard'))->name('dashboard');