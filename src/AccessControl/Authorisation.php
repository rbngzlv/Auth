<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Carbon\Carbon;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;

/**
 * Authorisation state object class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Authorisation implements \JsonSerializable
{
    /** @var string */
    protected $guid;
    /** @var string */
    protected $cookie;
    /** @var \DateTime */
    protected $expiry;
    /** @var AccessToken[] */
    protected $accessTokens;

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     *
     * @return Authorisation
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        if ($this->cookie === null) {
            $this->cookie = Uuid::uuid4()->toString();
        }

        return $this->cookie;
    }

    /**
     * @param string $cookie
     *
     * @return Authorisation
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param \DateTime|string $expiry
     *
     * @return Authorisation
     */
    public function setExpiry($expiry)
    {
        if (is_string($expiry)) {
            $expiry = new Carbon($expiry);
        }
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * @param $provider
     * @param AccessToken $accessToken
     *
     * @return Authorisation
     */
    public function addAccessToken($provider, AccessToken $accessToken)
    {
        $provider = strtolower($provider);
        $this->accessTokens[$provider] = $accessToken;

        return $this;
    }

    /**
     * @param $provider
     *
     * @return AccessToken
     */
    public function getAccessToken($provider)
    {
        $provider = strtolower($provider);

        return $this->accessTokens[$provider];
    }

    /**
     * @return AccessToken
     */
    public function getAccessTokens()
    {
        return $this->accessTokens;
    }

    /**
     * @param AccessToken[] $accessTokens
     *
     * @return Authorisation
     */
    public function setAccessTokens(array $accessTokens)
    {
        $this->accessTokens = $accessTokens;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'guid'         => $this->guid,
            'cookie'       => $this->cookie,
            'expiry'       => $this->expiry,
            'accessTokens' => $this->accessTokens,
        ];
    }

    /**
     * Create an instance from JSON data.
     *
     * @param array|string $data
     *
     * @return Authorisation|null
     */
    public static function createFromJson($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        }
        if (!$data instanceof \stdClass) {
            return null;
        }

        $auth = new self();
        $auth->guid = $data->guid;
        $auth->cookie = $data->cookie;
        if (is_numeric($data->expiry)) {
            $auth->expiry = Carbon::createFromTimestamp($data->expiry);
        } else {
            $auth->expiry = new Carbon(
                $data->expiry->date,
                $data->expiry->timezone
            );
        }

        foreach ((array) $data->accessTokens as $provider => $tokenData) {
            $auth->accessTokens[$provider] = new AccessToken((array) $tokenData);
        }

        return $auth;
    }
}