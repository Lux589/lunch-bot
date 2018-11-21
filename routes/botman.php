<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});

$botman->hears('Whats for {lunch}', function ($bot,$lunch) {
    $bot->reply('Hello!, here is whats for'.$lunch);
});

$botman->hears('Ekse jaylo', function ($bot) {
    $bot->reply('Eita Eita f2 :wave:');
});

$botman->hears('menu', function ($bot) {
    $bot->reply('Hello!, here is whats for');
});

$botman->hears('I want to order', BotManController::class.'@startOrderConversation');
