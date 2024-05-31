<?php

namespace App\ResponseType\Types;

use App\Models\ResponseType;
use App\ResponseType\BaseResponseType;
use App\ResponseType\Content;
use App\ResponseType\ContentCollection;
use App\ResponseType\ResponseDto;

class [RESOURCE_CLASS_NAME] extends BaseResponseType
{
    public function handle(ResponseType $responseType): ResponseDto
    {

        /**
         * @NOTE you can use the meta_data or prompt_token
         * JSON area for storing encrypted settings
         */
        $token_limit = data_get($responseType->meta_data, 'something', 750);

        $this->response_dto->response->contents->map(function ($document) {
            $document->content = str($document->content)->toString();

            return $document;
        });

        return ResponseDto::from(
            [
                'message' => $this->response_dto->message->refresh(),
                'response' => $this->response_dto->response,
                'filters' => $this->response_dto->filters
            ]
        );
    }
}
