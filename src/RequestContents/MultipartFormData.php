<?php declare(strict_types=1);

namespace hollodotme\FastCGI\RequestContents;

use hollodotme\FastCGI\Interfaces\ProvidesRequestContent;
use InvalidArgumentException;
use function base64_encode;
use function basename;
use function chunk_split;
use function file_exists;
use function file_get_contents;
use function implode;
use function sprintf;

final class MultipartFormData implements ProvidesRequestContent
{
	private const BOUNDARY_ID = '__X_FASTCGI_CLIENT_BOUNDARY__';

	/** @var array<string, string> */
	private $formData;

	/** @var array<string, string> */
	private $files;

	/**
	 * @param array<string, string> $formData
	 * @param array<string, string> $files
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $formData, array $files )
	{
		$this->formData = $formData;
		$this->files    = [];

		foreach ( $files as $name => $filePath )
		{
			$this->addFile( (string)$name, (string)$filePath );
		}
	}

	/**
	 * @param string $name
	 * @param string $filePath
	 *
	 * @throws InvalidArgumentException
	 */
	public function addFile( string $name, string $filePath ) : void
	{
		if ( !file_exists( $filePath ) )
		{
			throw new InvalidArgumentException( 'File does not exist: ' . $filePath );
		}

		$this->files[ $name ] = $filePath;
	}

	public function getContentType() : string
	{
		return 'multipart/form-data; boundary=' . self::BOUNDARY_ID;
	}

	public function getContent() : string
	{
		$data = [];

		foreach ( $this->formData as $key => $value )
		{
			$data[] = $this->getFormDataContent( $key, $value );
		}

		foreach ( $this->files as $name => $filePath )
		{
			$data[] = $this->getFileDataContent( $name, $filePath );
		}

		$data[] = '--' . self::BOUNDARY_ID . "--\r\n\r\n";

		return implode( "\r\n", $data );
	}

	private function getFormDataContent( string $key, string $value ) : string
	{
		$data   = ['--' . self::BOUNDARY_ID];
		$data[] = sprintf( "Content-Disposition: form-data; name=\"%s\"\r\n", $key );
		$data[] = $value;

		return implode( "\r\n", $data );
	}

	private function getFileDataContent( string $name, string $filePath ) : string
	{
		$data   = ['--' . self::BOUNDARY_ID];
		$data[] = sprintf(
			'Content-Disposition: form-data; name="%s"; filename="%s"',
			$name,
			basename( $filePath )
		);
		$data[] = 'Content-Type: application/octet-stream';
		$data[] = "Content-Transfer-Encoding: base64\r\n";
		$data[] = trim( chunk_split( base64_encode( (string)file_get_contents( $filePath ) ) ) );

		return implode( "\r\n", $data );
	}
}