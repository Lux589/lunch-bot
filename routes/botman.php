<?php
use App\Http\Controllers\BotManController;
use App\Opts;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');


$botman->hears('menu', function ($bot) {


    $bot->reply(App\Opts::all);
});