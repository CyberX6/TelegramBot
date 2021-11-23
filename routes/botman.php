<?php

use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('/start|/hey|/baby|start|start(.*)', BotManController::class . '@startConversation')
    ->skipsConversation();

$botman->hears('/stop|stop|stop(.*)', function ($bot) {
    $bot->reply('Bye!');
})->stopsConversation();

$botman->fallback(function ($bot) {
    $bot->reply('Sorry, I am not that good yet, I am just a newborn 👶, I only new several words, like: /start,
    /stop, and when I am bored I also like to hear /hey, or /baby 😊');
});
