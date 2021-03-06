<?php

namespace rikudou\EuQrPayment\Sepa;

/**
 * Supported character sets.
 *
 * @see https://www.europeanpaymentscouncil.eu/sites/default/files/KB/files/EPC069-12%20v2.1%20Quick%20Response%20Code%20-%20Guidelines%20to%20Enable%20the%20Data%20Capture%20for%20the%20Initiation%20of%20a%20SCT.pdf
 */
class CharacterSet
{
    public const UTF_8 = 1;
    public const ISO_8859_1 = 2;
    public const ISO_8859_2 = 3;
    public const ISO_8859_4 = 4;
    public const ISO_8859_5 = 5;
    public const ISO_8859_7 = 6;
    public const ISO_8859_10 = 7;
    public const ISO_8859_15 = 8;
}
