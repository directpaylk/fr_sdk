<?php
declare(strict_types=1);

namespace FriMi\SDK\Transport;

use FriMi\SDK\Exception\HTTPException;
use FriMi\SDK\Exception\RuntimeException;

class CurlClientTransport implements TransportInterface {
	private const VERSION = 'v0.1';

	private $curl_handler;

	public function request(
		string $method,
		string $url,
		array $headers,
		string $request_body
	): HTTPResponse {
		$this->initCurl();
		$this->setRequest($method, $url, $headers, $request_body);
		$response = $this->execute();
		return new HTTPResponse(\curl_getinfo($this->curl_handler,  CURLINFO_RESPONSE_CODE), $response);
	}

	public function __construct() {
		// @codeCoverageIgnoreStart
	  if (!\function_exists('curl_init')) {
	  	throw new RuntimeException('Curl extension not available.');
	  }
		// @codeCoverageIgnoreEnd
	}

	private function initCurl(): void {
		$this->curl_handler = $curl = \curl_init();

		// @codeCoverageIgnoreStart
		if (!\is_resource($curl)) {
			throw new RuntimeException('Unable to initialize Curl handler.');
		}
		// @codeCoverageIgnoreEnd

		\curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_USERAGENT => 'FriMi PHP SDK ' . self::VERSION,
			CURLOPT_SSL_VERIFYPEER => 'fasle'
		]);
	}

	private function setRequest(string $method, string $url, array $headers = [], string $fields = ''): void {
		\curl_setopt($this->curl_handler, CURLOPT_URL, $url);
		\curl_setopt($this->curl_handler, CURLOPT_CUSTOMREQUEST, $method);

		\Log::info($fields."\n");

		if ($fields) {
			\curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, $fields);
		}

		if ($headers) {
			$curl_headers = [];
			foreach ($headers as $header => $value) {
				$curl_headers[] = "$header: $value";
			}
			\curl_setopt($this->curl_handler, CURLOPT_HTTPHEADER, $curl_headers);
		}
	}

    /**
     * @return string
     */
    private function execute(): string {
		$response = \curl_exec($this->curl_handler);

		if ($err = \curl_error($this->curl_handler)) {
			throw new HTTPException(\sprintf('HTTP Error occurred: "%s"', $err));
		}

		return (string) $response;
	}
}
