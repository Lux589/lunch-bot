<?php
use App\Http\Controllers\BotManController;
use App\Conversations\OrderConversation;
use App\Staff;
//use Log;
$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $user = $bot->getUser(); 

    //Log::alert($user->getInfo());

    if(Staff::where('email','=',$user->getInfo()['profile']['email'])->first()){
        $bot->reply('Hello! '.$user->getUsername().' :wave:');
        //$bot->typesAndWaits(2);
        $bot->reply('Seems like we have talked before. what would you like me to help you today?');
        $bot->startConversation(new OrderConversation);
    }
    else {
        $bot->reply('Hello! '.$user->getUsername().' :wave:');
        $bot->reply('Looks like we have never talked before let me give you a quick run through about me');
        $bot->reply('My name is `Lunchbot`. I help you order your weekly lunch. ');
        $bot->reply('To begin the order processes just say `hi` to me then the process will start');

        $new_staff = new Staff();

        $new_staff->name = $user->getInfo()['name'];

        $new_staff->email = $user->getInfo()['profile']['email'];

        $new_staff->save();

        $bot->reply('you have now been successfully added to my database. When you want to place an order just say `hi` and i`ll gladly help');
    }

    //Log::info($user->getInfo()['profile']['email']);
    
    //$bot->reply('What would you like me to help you with?');

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

$botman->hears('I want to order', BotManController::class.'@startConversation');

$botman->hears('Hello', function($bot) {
	$user = $bot->getUser();
	$bot->reply($user->getInfo());
	$bot->reply('Your username is: '.$user->getUsername());
	$bot->reply('Your ID is: '.$user->getId());
});
