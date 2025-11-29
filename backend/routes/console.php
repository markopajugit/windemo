<?php

use Illuminate\Support\Facades\Schedule;

// Run winner selection every minute
Schedule::command('lotteries:select-winners')->everyMinute();
