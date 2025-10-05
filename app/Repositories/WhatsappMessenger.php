<?php

namespace App\Repositories;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log as Logger;


class WhatsappMessenger
{
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_AUDIO = 'audio';
    const MEDIA_TYPE_VIDEO = 'video';
    const MEDIA_TYPE_DOCUMENT = 'document';
    const MEDIA_TYPE_STICKER = 'sticker';

    public function __construct()
    {
        $this->url = "https://graph.facebook.com";
        $this->user_access_token = env("WHATSAPP_USER_ACCESS_TOKEN");
        $this->phone_number_id = env("WHATSAPP_PHONE_ID");
        $this->business_account_id = env("WHATSAPP_BUSINESS_ACCOUNT_ID");
        $this->api_version = "v15.0";
    }

    public function getPhoneNumberId()
    {
        try {
            $client = new Client();
            $headers = [
                "Authorization" => "Bearer $this->user_access_token"
            ];
            $url = $this->url . "/{$this->api_version}/{$this->business_account_id}/phone_numbers";
            $request = new Request('GET', $url, $headers);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'data' => $response->data];
        } catch (Exception $e) {
            Logger::info('Whatsapp getPhoneNumberId Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendTemplateMessage($to, $template = "hello_world", $components = [])
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            $payload = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $to,
                "type" => "template",
                "template" => [
                    "name" => $template,
                    "language" => [
                        "code" => "en_US"
                    ],
                ]
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp sendTemplateMessage Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendMediaMessageById($to, $mediaType = 'image', $caption = '', $mediaId)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            switch ($mediaType) {
                case self::MEDIA_TYPE_IMAGE:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "image",
                        "image" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_AUDIO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "audio",
                        "audio" => [
                            "id" => $mediaId
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_VIDEO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "video",
                        "video" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_STICKER:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "sticker",
                        "sticker" => [
                            "id" => $mediaId
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_DOCUMENT:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "document",
                        "document" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                            "filename" => "<DOCUMENT_FILENAME>"
                        ],
                    ];
                    break;
            }

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp sendMediaMessageById Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendMediaMessageByUrl($to, $mediaType = 'image', $caption = '', $mediaUrl)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            switch ($mediaType) {
                case self::MEDIA_TYPE_IMAGE:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "image",
                        "image" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_AUDIO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "audio",
                        "audio" => [
                            "link" => $mediaUrl
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_VIDEO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "video",
                        "video" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_STICKER:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "sticker",
                        "sticker" => [
                            "link" => $mediaUrl
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_DOCUMENT:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "type" => "document",
                        "document" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;
            }

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp sendMediaMessageByUrl Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendPlainMessage($to, $message)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            $payload = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $to,
                "type" => "text",
                "text" => [
                    "preview_url" => true,
                    "body" => $message
                ],
            ];

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp sendPlainMessage Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function replyWithText($messageId, $to, $reply)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            $payload = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $to,
                "context" => [
                    "message_id" => $messageId
                ],
                "type" => "text",
                "text" => [
                    "preview_url" => true,
                    "body" => $reply
                ],
            ];

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp replyWithText Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function replyWithReaction($messageId, $to, $emoji = "😀")
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            $payload = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $to,
                "type" => "reaction",
                "reaction" => [
                    "message_id" => $messageId,
                    "emoji" => $emoji
                ],
            ];

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp replyWithReaction Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function replyWithMediaById($messageId, $to, $mediaType = 'image', $caption = '', $mediaId)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            switch ($mediaType) {
                case self::MEDIA_TYPE_IMAGE:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "image",
                        "image" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_AUDIO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "audio",
                        "audio" => [
                            "id" => $mediaId,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_VIDEO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "video",
                        "video" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_STICKER:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "sticker",
                        "sticker" => [
                            "id" => $mediaId,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_DOCUMENT:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "document",
                        "document" => [
                            "id" => $mediaId,
                            "caption" => $caption,
                            "filename" => "<DOCUMENT_FILENAME>"
                        ],
                    ];
                    break;
            }

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp replyWithMediaById Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function replyWithMediaByUrl($messageId, $to, $mediaType = 'image', $caption = '', $mediaUrl)
    {
        try {
            $client = new Client();
            $headers = [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $this->user_access_token"
            ];

            switch ($mediaType) {
                case self::MEDIA_TYPE_IMAGE:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "image",
                        "image" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_AUDIO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "audio",
                        "audio" => [
                            "link" => $mediaUrl,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_VIDEO:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "video",
                        "video" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_STICKER:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "sticker",
                        "sticker" => [
                            "link" => $mediaUrl,
                        ],
                    ];
                    break;

                case self::MEDIA_TYPE_DOCUMENT:
                    $payload = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" =>  "individual",
                        "to" => $to,
                        "context" => [
                            "message_id" => $messageId
                        ],
                        "type" => "document",
                        "document" => [
                            "link" => $mediaUrl,
                            "caption" => $caption,
                        ],
                    ];
                    break;
            }

            $body = json_encode($payload, JSON_PRETTY_PRINT);
            $url = $this->url . "/{$this->api_version}/{$this->phone_number_id}/messages";
            $request = new Request('POST', $url, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $response = json_decode($res->getBody());

            if (is_null($response)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => 'No response!'];
            } elseif (isset($response->error)) {
                return ['error' => 1, 'statusCode' => 401, 'responseMessage' => $response->error->message, 'errorType' => $response->error->type];
            }
            return ['error' => 0, 'statusCode' => 200, 'ResponseStatus' => 'Successful', 'responseMessage' => 'Message Sent Successfully', 'messaging_product' => $response->messaging_product, 'contacts' => $response->contacts, 'messages' => $response->messages];
        } catch (Exception $e) {
            Logger::info('Whatsapp replyWithMediaByUrl Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendOtpVerification($to, $name, $otp)
    {
        try {
            $template = 'otp_verification';
            $components = [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $name
                        ],
                        [
                            "type" => "text",
                            "text" => $otp
                        ],
                    ]
                ]
            ];

           return $this->sendTemplateMessage($to, $template, $components);
        } catch (Exception $e) {
            Logger::info('Whatsapp sendOtpVerification Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }

    public function sendPaymentSuccessful($to, $buyer_name, $merchant_name,  $amount, $date, $button_param)
    {
        try {
            $template = 'payment_successful';
            $components = [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $buyer_name
                        ],
                        [
                            "type" => "currency",
                            "currency" => [
                                "fallback_value" => "₦" . (string) $amount,
                                "code" => "NGN",
                                "amount_1000" => (int) $amount * 1000
                            ]
                        ],
                        [
                            "type" => "text",
                            "text" => $merchant_name
                        ],
                        [
                            "type" => "text",
                            "text" => $date
                        ]
                    ]
                ],
                [
                    "type" => "button",
                    "sub_type" => "url",
                    "index" => "0",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => $button_param
                        ]
                    ]
                ]
            ];

            return $this->sendTemplateMessage($to, $template, $components);
        } catch (Exception $e) {
            Logger::info('Whatsapp sendPaymentSuccessful Error - Exception', [$e->__toString()]);
            $response = $e->getMessage();
            $code = $e->getCode();
            return ['error' => 1, 'statusCode' => $code, 'responseMessage' => $response];
        }
    }
}
