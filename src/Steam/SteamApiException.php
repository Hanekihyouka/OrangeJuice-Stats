<?php

declare(strict_types=1);

namespace OJStats\Steam;

/**
 * Thrown when the Steam Web API cannot be reached or returns an unusable response.
 */
class SteamApiException extends \RuntimeException
{
}
