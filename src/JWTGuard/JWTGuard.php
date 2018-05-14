<?php

namespace RBennett\JWTGuard;

use RBennett\JWTGuard\Contracts\HTTPRequestContract;
use RBennett\JWTGuard\Exceptions\JWTHydrationException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\GuardHelpers;
use RBennett\JWTGuard\Contracts\Hydrates;

class JWTGuard implements Guard, Hydrates
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey;

    /**
     * @var
     */
    protected $decodedToken;

    protected $client;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Http\Request $request
     * @param string $inputKey
     * @param HTTPRequestContract $client
     */
    public function __construct(Request $request, $inputKey = 'Authorization', HTTPRequestContract $client)
    {
        $this->request = $request;
        $this->inputKey = $inputKey;
        $this->client = $client;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        if ($this->validateJWTToken($this->getTokenForRequest())) {

           $user = new User([
               'id' => $this->decodedToken->sub,
               'scopes' => $this->decodedToken->scopes
           ]);
        }

        return $this->user = $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $token = $this->request->header($this->inputKey);

        $token = substr($token, 7);

        return $token;
    }

    /**
     * Validate a token.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        if ($this->validateJWTToken($credentials[$this->inputKey])) {
            return true;
        }

        return false;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return $this
     * @throws JWTHydrationException
     */
    public function hydrate()
    {
        $headers =
            [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $this->getTokenForRequest()
            ];

        $decodedResponse = $this->client->request('get', config('jwtguard.passport_oauth_server') . config('jwtguard.hydrate_user_uri'), $headers, [], []);

        if ($decodedResponse['statusCode'] == 200) {
            foreach($decodedResponse['body'] as $property => $value){
                $this->user->{$property} = $value;
            }
        } else {
            throw new JWTHydrationException();
        }

        return $this;
    }

    /**
     * @param $token
     * @return bool
     */
    private function validateJWTToken($token)
    {
        $publicKey = file_get_contents(config('jwtguard.public_key_file'));

        try {
            $decoded = JWT::decode($token, $publicKey, array(config('jwtguard.verification_alg')));
        } catch (\Exception $e) {
            return false;
        }

        $this->decodedToken = $decoded;

        return true;
    }
}
