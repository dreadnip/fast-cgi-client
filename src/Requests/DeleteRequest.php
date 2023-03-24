<?php declare(strict_types=1);

namespace hollodotme\FastCGI\Requests;

use hollodotme\FastCGI\Constants\RequestMethod;
use hollodotme\FastCGI\Interfaces\ComposesRequestContent;

/**
 * Class DeleteRequest
 * @package hollodotme\FastCGI\Requests
 */
class DeleteRequest extends AbstractRequest
{
	public static function newWithRequestContent(
		string $scriptFilename,
		ComposesRequestContent $requestContent
	) : DeleteRequest
	{
		$instance = new self( $scriptFilename, $requestContent->getContent() );
		$instance->setContentType( $requestContent->getContentType() );

		return $instance;
	}

	public function getRequestMethod() : string
	{
		return RequestMethod::DELETE;
	}
}
