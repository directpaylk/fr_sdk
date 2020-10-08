<?php
declare(strict_types=1);

namespace FriMi\SDK\Handler;

use FriMi\SDK\Response\PaymentStatusResponse;
use FriMi\SDK\Response\ResponseInterface;

class StatusRequestHandler extends PaymentRequestHandler {
	protected function getResponseObject(): ResponseInterface {
		return new PaymentStatusResponse();
	}
}
