<?php
use App\Http\Controllers\BotManController;
use App\Conversations\OrderConversation;
use App\Staff;

$botman = resolve('botman');

$botman->hears('(Hi|i want to order|hello|order|place order)', function ($bot) {
    $user = $bot->getUser(); 

    if(Staff::where('email','=',$user->getInfo()['profile']['email'])->first()){
        $bot->reply('Hello!  '.$user->getUsername().' :wave:');

        $bot->reply('what would you like me to help you with?');
        $bot->startConversation(new OrderConversation);
    }
    else {
        $bot->reply('Hello! '.$user->getUsername().' :wave:');
        $bot->reply('Looks like we have never talked before let me give you a quick run through about me');
        $bot->reply('My name is `Lunchbot`. I help you order your monthly lunch. ');
        $bot->reply('To begin the order processes just say `hi` to me then the process will start');

        $new_staff = new Staff();

        $new_staff->name = $user->getInfo()['name'];

        $new_staff->email = $user->getInfo()['profile']['email'];

        $new_staff->save();

        $bot->reply('you have now been successfully added to my database. When you want to place an order just say `hi` and i`ll gladly help');
        $bot->startConversation(new OrderConversation);
    }

});

$botman->hears('delete order', function ($bot,$lunch) {
    $bot->reply('Hello!, that feature is coming soon'.$lunch);
});


//$botman->hears('I want to order', BotManController::class.'@startConversation');

