<?php
use App\Http\Controllers\BotManController;
use App\Conversations\OrderConversation;
use Carbon\Carbon;
use App\Staff;
use App\Order;
use App\Opt;


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

$botman->hears('(show menu|menu|view menu)', function ($bot) {
    $start = new Carbon('first day of this month');

    $end = new Carbon('last day of this month');

    $menu = Opt::whereBetween('updated_at',[$start,$end])->get();

    foreach($menu as $option){
        $bot->reply('Type: `'.$option->type.'`'.', Ingredients: `'.$option->description.'`');
    }
    
});

$botman->hears('(order {type})', function ($bot,$type) {
    $start = new Carbon('first day of this month');

    $end = new Carbon('last day of this month');
    
    
    $menu_item = Opt::where('type',$type)->whereBetween('updated_at',[$start,$end])->first();

    

    $staff = Staff::where('email',$bot->getUser()->getInfo()['profile']['email'])->first();

    $order = new Order();
    
    $order->staff_id = $staff->id;
    $order->opts_id = $menu_item->id;

    $order->claimed = 0;

    $order->save();

    Log::info($order);


    $bot->reply('order for '.$type.' placed');
});


