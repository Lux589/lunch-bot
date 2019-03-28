<?php
use App\Http\Controllers\BotManController;
use App\Conversations\OrderConversation;
use Carbon\Carbon;
use App\Staff;
use App\Order;
use App\Opt;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

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
        $bot->reply('To begin the order processes just use these keywords `hi`,`i want to order`, `hello`, `order` and `place order` to me then the process will start');

        $new_staff = new Staff();

        $new_staff->name = $user->getInfo()['name'];

        $new_staff->email = $user->getInfo()['profile']['email'];

        $new_staff->slack_uid = $user->getId();

        $new_staff->save();

        $bot->reply('you have now been successfully added to my database. When you want to place an order just say `hi` and i`ll gladly help');
        $bot->startConversation(new OrderConversation);
    }

});

$botman->hears('(show menu|menu|view menu)', function ($bot) {
    $start = new Carbon('first day of this month');

    $end = new Carbon('last day of this month');

    $menu = Opt::whereBetween('updated_at',[$start,$end])->get();

    if(count($menu) == 0){
        $bot->reply('Seems like we do not have a menu for this month yet.');
    }
    else{
        foreach($menu as $option){
            $bot->reply('Type: `'.$option->type.'`'.', Ingredients: `'.$option->description.'`');

            $bot->typesAndWaits(1);
        }
    }
    
});

$botman->hears('(order {type})', function ($bot,$type) {

    $start = new Carbon('first day of this month');
    $end = new Carbon('last day of this month');
    
    $menu_item = Opt::where('type',$type)->whereBetween('updated_at',[$start,$end])->first();

    if(count($menu_item) == 0){
        $bot->reply('Seems like we do not have that food type in our menu, use `view menu` to see the list of items in this months menu');
    }

    $staff = Staff::where('email',$bot->getUser()->getInfo()['profile']['email'])->first();

    $current_order = Order::where('staff_id', $staff->id)->whereBetween('updated_at',[$start,$end])->first();

    if(count($current_order) != 0){
        $bot->reply('Eish seems like you have already ordered for this month');
        $bot->typesAndWaits(2);
        $bot->reply('To see what you ordered use, `view order`');
    }
    else {
        $order = new Order();
    
        $order->staff_id = $staff->id;
        $order->opts_id = $menu_item->id;

        $order->claimed = 0;

        $order->save();

        $bot->reply('order for the `'.$type.'` meal has been place successfully');
    }

});

$botman->hears('view order|show order|my order',function($bot){

    $start = new Carbon('first day of this month');
    $end = new Carbon('last day of this month');

    $staff = Staff::where('email',$bot->getUser()->getInfo()['profile']['email'])->first();

    $order = Order::where('staff_id',$staff->id)->whereBetween('updated_at',[$start,$end])->first();

    if(count($order) == 0){
        $bot->reply('You have not placed an order, unfortunately');
    }
    else {
        $bot->reply('You have ordered the `'.Opt::where('id',$order->opts_id)->whereBetween('updated_at',[$start,$end])->first()->type.'` meal');
    }
});

$botman->hears('view {type} type', function($bot,$type) {
    $start = new Carbon('first day of this month');
    $end = new Carbon('last day of this month');

    $menu_item = Opt::where('type',$type)->whereBetween('updated_at',[$start,$end])->first();

    if(count($menu_item) == 0){
        $bot->reply('We do not have that food type for this month. Please try another type');
    }
    else {
        $bot->reply('The food type contains, `'.$menu_item->description.'`');
    }

});

$botman->hears('delete order|delete current order|remove order', function ($bot){

    $start = new Carbon('first day of this month');
    $end = new Carbon('last day of this month');

    $staff = Staff::where('email',$bot->getUser()->getInfo()['profile']['email'])->first();

    $order = Order::where('staff_id',$staff->id)->whereBetween('updated_at',[$start,$end])->delete();

    //Log::info($order);

    if($order == 1){
        $bot->reply('your order has been deleted successfully');
    }
    else {
        $bot->reply('There was a problem deleting your order, perhaps you have not ordered. Try `view order` to see if you have ordered.');
    }
    
});

$botman->hears('outgoing',function($bot) {
    $message = OutgoingMessage::create('This is an outgoing message');

    $bot->say($message,$bot->getUser()->getId());
});

$botman->hears('send orders',function($bot) {
    $start = new Carbon('first day of this month');
    $end = new Carbon('last day of this month');

    $bot->reply('let me help you quickly');

    $healthy = 'healthy';
    $hearty = 'hearty';
    $vegetarian = 'vegetarian';
    $low_carb = 'low carb';

    $healthy_id = Opt::where('type',$healthy)->whereBetween('updated_at',[$start,$end])->get()->id;

    $hearty_id = Opt::where('type',$hearty)->whereBetween('updated_at',[$start,$end])->get()->id;

    $vegetarian_id = Opt::where('type',$vegetarian)->whereBetween('updated_at',[$start,$end])->get()->id;

    $low_carb_id = Opt::where('type',$low_carb)->whereBetween('updated_at',[$start,$end])->get()->id;

    $healthy_count = Order::where('opts_id',$healthy_id)->whereBetween('updated_at',[$start,$end])->count();

    $hearty_count = Order::where('opts_id',$hearty_id)->whereBetween('updated_at',[$start,$end])->count();

    $vegetarian_count = Order::where('opts_id',$vegetarian_id)->whereBetween('updated_at',[$start,$end])->count();
    
    $low_carb_count = Order::where('opts_id',$low_carb_id)->whereBetween('updated_at',[$start,$end])->count();


    Log::info('Healthy orders'.$healthy_count);

    Log::info('Hearty orders'.$hearty_count);

    Log::info('Vegetarian order'.$vegetarian_count);

    Log::info('Low carb orders'.$low_carb_count);

    $bot->reply('everything is logged');
});



