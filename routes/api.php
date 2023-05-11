<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

Route::resource('applications', ApplicationController::class)->only(['index']);
