<?php
/** @noinspection RegExpUnnecessaryNonCapturingGroup */

namespace spaf\simputils\daily\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\traits\ForOutputsTrait;
use function is_null;
use function preg_match;
use function trim;

/**
 * A single e-mail instance
 *
 * @property ?string      $username               First part of e-mail before "@"
 * @property ?string      $host                   Host/domain part of e-mail after "@"
 * @property ?string      $name                   Name/Surname/Nick part of the string of mailbox
 *           format
 * @property ?bool        $use_mailbox_format     Whether to use mailbox format "for_user" output
 * @property-read ?string $orig_value             Original value, that was provided when object was
 * created
 */
class Email extends SimpleObject {
	use ForOutputsTrait;

	// TODO Improve the regexps
	const REGEXP_EMAIL = "/^(\w+[\w._-]*\w+)(?:@([\w._-]+))?$/";
	const REGEXP_MAILBOX = '/^([\w\s."\'_-]*)<(\w+[\w._-]*\w+@[\w._-]+)>$/';

	#[Property(type: 'get')]
	protected ?string $_orig_value = null;

	#[Property]
	protected ?bool $_use_mailbox_format = null;

	#[Property]
	protected $_name = null;

	#[Property]
	protected $_username = null;

	#[Property(type: 'get')]
	protected null|string $_host = null;

	/**
	 * @param ?string $value E-mail or Username part
	 * @param ?string $host  If defined, overrides the host value from $value
	 * @param ?string $name  If defined, overrides the name value from $value (mailbox
	 *                       format)
	 */
	function __construct(
		?string $value = null,
		?string $host = null,
		?string $name = null,
		?bool   $use_mailbox_format = null,
	) {
		$_username = null;
		$_host = null;

		$this->_orig_value = $value;

		if (static::isMailboxFormat($value)) {
			$parts = [];
			preg_match(static::REGEXP_MAILBOX, $value, $parts);
			$name = $name ?? $parts[1];
			$value = $_email = $parts[2];
		}

		if (static::isEmail($value)) {
			$parts = [];
			preg_match(static::REGEXP_EMAIL, $value, $parts);
			$_username = $parts[1];
			$_host = $host ?? $parts[2] ?? null;
		}

		$this->name = trim($name);
		$this->username = trim($_username);
		$this->host = trim($_host);

		$this->use_mailbox_format = $use_mailbox_format;
	}

	static function isMailboxFormat($value): bool {
		return preg_match(static::REGEXP_MAILBOX, $value);
	}

	static function isEmail($value): bool {
		return preg_match(static::REGEXP_EMAIL, $value);
	}

	#[Property('host', type: 'set')]
	protected function setHost($value) {
		if (!is_null($value)) {
			$this->_host = $value;
		}
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return "{$this->_username}@{$this->_host}";
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		if ($this->_use_mailbox_format && !empty($this->_name)) {
			return "{$this->_name} <{$this->_username}@{$this->_host}>";
		}

		return "{$this->_username}@{$this->_host}";
	}
}
