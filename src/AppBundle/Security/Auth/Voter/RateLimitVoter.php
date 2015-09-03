<?php

namespace AppBundle\Security\Auth\Voter;

use Predis\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * RateLimitVoter
 *
 * Check that the requester is within the
 * acceptable rate limit for the defined ttl.
 *
 * This voter uses redis to implement a quick
 * 'n' dirty 'leaky bucket' strategy to ensure
 * that individual requesters stay within the
 * defined request/time limit.
 *
 * Set a key for each request prefixed by the
 * IP address of the requester. Then set a TTL
 * on that key for the timeout specified.
 *
 * @see parameters.yml{,.dist} for these settings.
 *
 * On each request, do a key search by globbing
 * on the appropriate pattern for this requester.
 *
 * If the limit is not reached, vote as ACCESS_ABSTAIN.
 * This means the request passes this voter, and the
 * controller will be executed.
 *
 * Otherise, the requester that has reached the
 * acceptable rate limit is voted as ACCESS_DENIED,
 * which triggers a `500 Internal Error` response.
 *
 * This voter is only triggered for requests on
 * the POST /trade/message/ route.
 *
 * @see `access_decision_manager` and `access_control`
 * parameters in app/config/security.yml for configuration.
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class RateLimitVoter implements VoterInterface
{
    /**
     * __construct
     *
     * Handle injected parameters
     *
     * @see app/config/services.yml rate_limit_voter
     *
     * @param string $rateLimitMax
     * @param string $rateLimitTtl
     */
    public function __construct(RequestStack $requestStack, Client $redis, $rateLimitMax, $rateLimitTtl)
    {
        $this->request      = $requestStack->getCurrentRequest();
        $this->redis        = $redis;
        $this->rateLimitMax = $rateLimitMax;
        $this->rateLimitTtl = $rateLimitTtl;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $addr = $this->request->server->get('FORWARDED_FOR') ?: $this->request->server->get('REMOTE_ADDR');
        $key  = sprintf('%s_%s', $addr, microtime());

        $this->redis->set($key, 1);
        $this->redis->expire($key, $this->rateLimitTtl);

        $pattern = sprintf('%s_*', $addr);
        $matches = $this->redis->keys($pattern);

        return count($matches) > $this->rateLimitMax
            ? VoterInterface::ACCESS_DENIED
            : VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsAttribute($attributes)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return true;
    }
}
