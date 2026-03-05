    <?php

    use App\Http\Controllers\Auth\SproutAuthController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\TransactionController;

        Route::get('/', fn () => view('public.loading'))->name('loading');
        Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
        Route::post('/login', [SproutAuthController::class, 'login'])->name('login');
        Route::get('/dashboard', fn () => view('public.dashboard'))->name('dashboard');
        Route::get('/signup', [SproutAuthController::class, 'showSignup'])->name('signup');
        Route::post('/signup', [SproutAuthController::class, 'storeSignup'])->name('signup.store');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/api/dashboard', [DashboardController::class, 'data'])->name('dashboard.data');
        Route::post('/api/transactions', [TransactionController::class, 'store'])->name('transactions.store');