<?php

namespace App\Dto\Token\AccessToken;

class TwitterAccessToken extends AbstractAccessToken
{
    public function __construct(
        public string $oauthToken,
        public string $oauthTokenSecret
    ) {
    }

    public static function fromString(string $responseString): self
    {
        parse_str($responseString, $params);

        return new self(
            oauthToken: $params['oauth_token'] ?? '',
            oauthTokenSecret: $params['oauth_token_secret'] ?? ''
        );
    }
}
