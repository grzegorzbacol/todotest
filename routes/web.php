<?php

use App\Http\Controllers\TasksController;
use App\Http\Controllers\WeeksController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Test route - return simple response to check if Laravel works
    return response()->json(['status' => 'ok', 'message' => 'Laravel is working']);
    // return redirect('/weeks');
});

// Weeks kanban view
Route::get('/weeks', [WeeksController::class, 'index'])->name('weeks.index');
Route::get('/api/weeks', [WeeksController::class, 'getWeek'])->name('weeks.api');

// Tasks views
Route::get('/inbox', [TasksController::class, 'inbox'])->name('tasks.inbox');
Route::get('/single', [TasksController::class, 'single'])->name('tasks.single');
Route::get('/priorities', [TasksController::class, 'priorities'])->name('tasks.priorities');

// Tasks API
Route::post('/api/tasks', [TasksController::class, 'store'])->name('tasks.store');
Route::patch('/api/tasks/{id}', [TasksController::class, 'update'])->name('tasks.update');

