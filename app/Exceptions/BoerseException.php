<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Fachlicher Fehler im Börsenhandel (z. B. Race-Condition beim gleichzeitigen
 * Handel, nicht genug Anteile/Bargeld). Wird innerhalb der DB-Transaktion
 * geworfen, damit diese sauber zurückrollt, und außerhalb in eine
 * Nutzer-Meldung übersetzt.
 */
class BoerseException extends RuntimeException
{
}
