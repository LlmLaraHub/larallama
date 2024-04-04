<?php 

namespace App\LlmDriver;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\LlmDriver\Requests\MessageInDto;
use App\Events\ChatUiUpdateEvent;
use App\LlmDriver\Functions\FunctionCallDto;
use Facades\App\Domains\Messages\SearchOrSummarizeChatRepo;

class Orchestrate {


    /**
     * 
     * @param MessageInDto[] $messagesArray 
     * @param Chat $chat 
     * @return null|string 
     */
    public function handle(array $messagesArray, Chat $chat) : ?string {

        /**
         * We are looking first for functions / agents / tools
         */
        $functions = LlmDriverFacade::driver($chat->chatable->getDriver())
            ->functionPromptChat($messagesArray);

        if(!empty($functions)) {
            /**
             * @TODO
             * We will deal with multi functions shortly
             * 
             * @TODO 
             * When should messages be made 
             * which class should make them
             * In this case I will assume the user of this class
             * save the Users input as a Message already
             */
            foreach($functions as $function) {
                $functionName = data_get($function, 'name', null);
                
                if(is_null($functionName)) {
                    throw new \Exception("Function name is required");
                }

                ChatUiUpdateEvent::dispatch(
                        $chat->chatable,
                        $chat,
                        sprintf("We are running the agent %s back shortly", 
                        str($functionName)->headline()->toString()
                        )
                    );

                $functionClass = app()->make($functionName);

                $arguments = data_get($function, 'arguments');

                $arguments = is_array($arguments) ? json_encode($arguments) : "";

                $functionDto = FunctionCallDto::from([
                    'arguments' => $arguments,
                    'function_name' => $functionName
                ]);

                $response = $functionClass->handle($messagesArray, $chat, $functionDto);

                $chat->addInput(
                    message: $response->content, 
                    role: RoleEnum::Assistant,
                    show_in_thread: false);

                $messagesArray[] = MessageInDto::from([
                    'role' => 'assistant',
                    'content' => $response->content
                ]);

                ChatUiUpdateEvent::dispatch(
                    $chat->chatable,
                    $chat,
                    "The Agent has completed the task going to the final step now");
                }


                $results = LlmDriverFacade::driver($chat->chatable->getDriver())
                    ->chat($messagesArray);

                
                $chat->addInput(
                    message: $results->content, 
                    role: RoleEnum::Assistant,
                    show_in_thread: true);
                    
                /**
                 * Could just show this in the ui
                */
                ChatUiUpdateEvent::dispatch(
                    $chat->chatable,
                    $chat,
                    $results->content);

                return $results->content;
        } else {
            /**
             * @NOTE 
             * this assumes way too much
             */
            $message = collect($messagesArray)->first(
                function($message) {
                    return $message->role === 'user';
                }
            )->content;

            return SearchOrSummarizeChatRepo::search($chat, $message);
        }
    }
  
}