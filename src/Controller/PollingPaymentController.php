<?php
declare(strict_types=1);

namespace FriMi\SDK\Controller;

use FriMi\SDK\APIInfo\Server\APIServerInterface;
use FriMi\SDK\Cache\CacheInterface;
use FriMi\SDK\Credential\ClientCredentials;
use FriMi\SDK\Exception\RuntimeException;
use FriMi\SDK\Handler\PaymentRequestHandler;
use FriMi\SDK\Handler\RefundRequestHandler;
use FriMi\SDK\Handler\StatusRequestHandler;
use FriMi\SDK\Handler\TokenRequestHandler;
use FriMi\SDK\Payment\RequestInterface;
use FriMi\SDK\Response\PaymentResponse;
use FriMi\SDK\Response\PaymentStatusResponse;
use FriMi\SDK\Response\RefundResponse;
use FriMi\SDK\Response\TokenResponse;
use FriMi\SDK\Transport\TransportInterface;

class PollingPaymentController {
	private $server;
	private $credentials;
	private $transport;

	/**
	 * @var TokenResponse
	 */
	private $token;
	private $request;

	/**
	 * @var CacheInterface
	 */
	private $cache;

	public function __construct(APIServerInterface $server, ClientCredentials $credentials, TransportInterface $transport) {
  	$this->server = $server;
		$this->credentials = $credentials;
		$this->transport = $transport;
  }

  public function setPaymentRequest(RequestInterface $request): void {
		$this->request = clone $request; // Make the payment request immutable.
  }

  public function execute(): PaymentResponse {
		$this->ensureToken();

		$payment_handler = new PaymentRequestHandler($this->server, $this->token, $this->transport);
		$payment_handler->setPaymentRequestObject($this->request);
	  $response = $payment_handler->execute();
	  /**
	   * @var PaymentResponse $response
	   */
	  return $response;
  }

	public function poll(): PaymentStatusResponse {
		$this->ensureToken();

		$payment_handler = new StatusRequestHandler($this->server, $this->token, $this->transport);
		$payment_handler->setPaymentRequestObject($this->request);
		$response = $payment_handler->execute();
		/**
		 * @var PaymentStatusResponse $response
		 */
		return $response;
  }

  public function reverse(): RefundResponse {
	  /**
	   * @var \FriMi\SDK\Payment\RefundRequest $request;
	   */
	  $request = $this->request;
	  $request->getOriginalRequestTimestamp();

	  // This check can be made without a token request, so bail-out early.
	  if (!$this->server->isRefundAllowed($request->getOriginalRequestTimestamp())) {
			throw new RuntimeException('Refund for this transaction is no longer allowed because the cut-off time has passed.');
	  }

		$this->ensureToken();
		$payment_handler = new RefundRequestHandler($this->server, $this->token, $this->transport);
		$payment_handler->setPaymentRequestObject($this->request);
	  $response = $payment_handler->execute();
	  /**
	   * @var RefundResponse $response
	   */
	  return $response;
  }

	private function ensureToken(): void {
		if ($this->token && $this->token->isValid()) {
			return;
		}
		$cache_key = $this->server::getName();
		$cache_key = 'frimi_' . $cache_key .'_token';

		if ($this->cache && ($cached_key = $this->cache->get($cache_key)) && is_array($cached_key)) {
			$token = new TokenResponse($cached_key[0], $cached_key[1]);
			if ($token->isValid()) {
				$this->token = $token;
				return;
			}
		}

		$token_request = new TokenRequestHandler($this->server, $this->credentials, $this->transport);
		$this->token = $token_request->execute();

		if ($this->cache) {
			$this->cache->set($cache_key, [$this->token->getToken(), $this->token->getLifeTime()], $this->token->getLifeTime());
		}
	}

	public function setCacheLayer(CacheInterface $cache): void {
		$this->cache = $cache;
	}
}
