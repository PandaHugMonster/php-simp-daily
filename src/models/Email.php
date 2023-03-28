<?php /** @noinspection RegExpUnnecessaryNonCapturingGroup */

namespace spaf\simputils\daily\models;

use spaf\simputils\attributes\Property;
use spaf\simputils\generic\SimpleObject;
use spaf\simputils\traits\ForOutputsTrait;
use function preg_match;
use function spaf\simputils\basic\pd;
use function spaf\simputils\basic\pr;

/**
 * A single e-mail instance
 *
 * @property ?string $username               First part of e-mail before "@"
 * @property ?string $host                   Host/domain part of e-mail after "@"
 * @property ?string $name                   Name/Surname/Nick part of the string of mailbox format
 * @property bool    $use_mailbox_format     Whether to use mailbox format "for_user" output
 */
class Email extends SimpleObject {
	use ForOutputsTrait;

	// TODO Improve the regexps
	const REGEXP_EMAIL = "/^(\w+[\w._-]*\w+)(?:@([\w._-]+))?$/";
	const REGEXP_MAILBOX = '/^([\w\s."\'_-]*)<(\w+[\w._-]*\w+@[\w._-]+)>$/';

	#[Property]
	protected bool $_use_mailbox_format = false;

	#[Property]
	protected $_name = null;

	#[Property]
	protected $_username = null;

	#[Property]
	protected $_host = null;

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
	) {
		$_username = null;
		$_host = null;

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

		pd($value, $name, $_username, $_host);
	}

	static function isMailboxFormat($value): bool {
		return preg_match(static::REGEXP_MAILBOX, $value);
	}

	static function isEmail($value): bool {
		return preg_match(static::REGEXP_EMAIL, $value);
	}

	#[Property('for_system')]
	protected function getForSystem(): string {
		return "{$this->_username}@{$this->_host}";
	}

	#[Property('for_user')]
	protected function getForUser(): string {
		// TODO: Implement getForUser() method.
	}
}
