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

use Log;

class OrderConversation extends Conversation
{
    public static $decription;
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
                } else {
                    $this->say(Inspiring::quote());
                }
            }
        });
    }

    public function askType(){

            $all_options = Opt::all();

            //Log::info($all_options);

            $question = Question::create('Which Menu option type would you like')
            ->callbackId('game_selection')
            ->addAction(
                Menu::create('Pick option type')
                    ->name('games_list')
                    ->options([
                        [
                            'text' => $all_options[0]->type,
                            'value' => $all_options[0]->description,
                        ],
                        [
                            'text' => $all_options[1]->type,
                            'value' => $all_options[1]->description,
                        ],
                        [
                            'text' => $all_options[2]->type,
                            'value' => $all_options[2]->description,
                        ],
                        [
                            'text' => $all_options[3]->type,
                            'value' => $all_options[3]->description,
                        ],
                       
                    ])
            );

        $this->ask($question, function (Answer $answer) {
            $selectedOption = $answer->getValue();
            
            $this->say('/nThe type you have selected contains: `'.$selectedOption[0]['value'].'`');

            $this->description = $selectedOption[0]['value'];

            $this->askPlaceOrder();
        });
    }

    function askPlaceOrder(){
        $question = Question::create("Would you like to place order for this option?")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('Yes')->value('yes'),
                Button::create('No')->value('no'),
            ]);
            
            return $this->ask($question, function (Answer $answer) {
                if ($answer->isInteractiveMessageReply()) {
                    if ($answer->getValue() === 'yes') {
                        $user_email = $this->bot->getUser()->getinfo()['profile']['email'];
                        $staff = Staff::where('email',$user_email)->first();

                        $menu = Opt::where('description', $this->description)->first();

                        Log::info($menu);
                        //$this->askType();
                    } else {
                        
                    }
                }
            });
    }
}
