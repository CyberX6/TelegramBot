<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('ბონძღი', function ($bot) {
    $bot->reply('რა იყო ქუცუნაaaaa?');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
