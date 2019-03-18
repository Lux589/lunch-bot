<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\Drivers\Slack\Extensions\Menu;
use App\Opt;
use App\Order;
use App\Staff;
use Carbon\Carbon;

use Log;

class OrderConversation extends Conversation
{
    public static $id;
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askReason();
    }

    public function askReason()
    {
        $question = Question::create("")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('View Order')->value('view'),
                Button::create('Place Order')->value('place'),
                Button::create('Edit Order')->value('edit'),
            ]);
        Log::info('asked reason');
        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'place') {
                    Log::info('selected place order');
                    $this->say('No worries I can help you with that');
                    $this->askType();
                } 
                elseif($answer->getValue() === 'view'){
                    Log::info('user chosen to view order');

                    $start = new Carbon('first day of this month');

                    $end = new Carbon('last day of this month');

                    $user_email = $this->bot->getUser()->getinfo()['profile']['email'];

                    $staff = Staff::where('email',$user_email)->first();

                    $order = Order::where('staff_id',$staff->id)->whereBetween('updated_at',[$start,$end])->first();

                    if(count($order) != 0){
                        $chosen_option = Opt::where('id',$order->opts_id)->first();

                        $this->say('For this month you have chosen the `'.$chosen_option->description.'`');
                    }

                    else {
                        $this->say('Hmmm seems like you have not made an order for this month ');
                        $this->say('Lets me help you place an order.');
                        $this->asktype();
                    }
                }
                else {
                    $this->say('No worries, let us change your current order.');

                    $start = new Carbon('first day of this month');

                    $end = new Carbon('last day of this month');

                    $user_email = $this->bot->getUser()->getinfo()['profile']['email'];

                    $staff = Staff::where('email',$user_email)->first();

                    $order = Order::where('staff_id',$staff->id)->whereBetween('updated_at',[$start,$end])->first()->delete();

                    $this->say('I have just deleted you current order. let us place a new one.');

                    $this->askType();
                }
            }
        });
    }

    public function askType(){
            $start = new Carbon('first day of this month');

            $end = new Carbon('last day of this month');

            $all_options = Opt::whereBetween('updated_at',[$start,$end])->get();

            $options_array = array();

            foreach($all_options as $option){
                $opt = (object)[
                    'text' => $option->type,
                    'value' => $option->id,
                ];

                array_push($options_array,$opt);
            }

           //Log::info($all_opti

            $question = Question::create('Which Menu option type would you like')
            ->callbackId('place_order')
            ->addAction(
                Menu::create('Pick option type')
                    ->name('games_list')
                    ->options($options_array)
            );

        $this->ask($question, function (Answer $answer) {
            $selectedOption = $answer->getValue();

            $option = Opt::where('id',$selectedOption[0]['value'])->first();

            $this->say('The type you have selected contains: `'.$option->description.'`');

            self::$id = $selectedOption[0]['value'];

            $this->askPlaceOrder();
        });
    }

    public function askPlaceOrder(){

        $desc = self::$id;

        $question = Question::create("Would you like to place order for this option?")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('Yes')->value('yes'),
                Button::create('No')->value('no'),
            ]);
            
            return $this->ask($question, function (Answer $answer) use ($desc){

                if ($answer->isInteractiveMessageReply()) {

                    if ($answer->getValue() === 'yes') {
                        $start = new Carbon('first day of this month');

                        $end = new Carbon('last day of this month');

                        $user_email = $this->bot->getUser()->getinfo()['profile']['email'];

                        $staff = Staff::where('email',$user_email)->first();
        
                        $menu = Opt::where('id',$desc)->first();

                        $current_order = Order::where('staff_id',$staff->id)->whereBetween('updated_at', [$start,$end])->first();
                        

                        if(count($current_order) == 0){
                            $place_order = new Order();

                            $place_order->opts_id = $menu->id;

                            $place_order->staff_id = $staff->id;

                            $place_order->save();

                            $this->say('I have successfully placed your order :slightly_smiling_face:');
                        }
                        else {
                            $this->say('Apparently you have already ordered for next month.');

                            $this->say('You ordered the `'.Opt::where('id',$current_order->opts_id)->first()->description.'`');
                        }

                    } else {
                        $this->say('lets order something different for you.');

                        $this->askType();
                    }
                }
            });
    }
}
