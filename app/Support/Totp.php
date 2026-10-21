<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;

/**
 * Códigos de 6 dígitos que cambian cada 30 segundos (RFC 6238), compatibles con Google Authenticator,
 * Microsoft Authenticator, Authy, etc.
 */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    /** Clave nueva en base32: 32 caracteres de 5 bits = 160 bits, lo que recomienda la RFC. */
    public static function generateSecret(): string
    {
        $secret = '';
        foreach (str_split(random_bytes(32)) as $byte) {
            $secret .= self::ALPHABET[ord($byte) & 31];
        }

        return $secret;
    }

    /**
     * Acepta el código actual y los de 30 s antes o después (relojes desfasados).
     * Un código ya usado no vale dos veces: si alguien lo ve por encima del hombro, no le sirve.
     */
    public static function verify(string $secret, string $code, string $replayKey): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $now = intdiv(time(), self::PERIOD);
        foreach ([0, -1, 1] as $offset) {
            $step = $now + $offset;
            if (hash_equals(self::at($secret, $step), $code)) {
                return Cache::add("totp:{$replayKey}:{$step}", true, self::PERIOD * 3);
            }
        }

        return false;
    }

    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/'.rawurlencode("{$issuer}:{$account}").'?'.http_build_query([
            'secret' => $secret, 'issuer' => $issuer, 'algorithm' => 'SHA1', 'digits' => 6, 'period' => self::PERIOD,
        ]);
    }

    public static function qrSvg(string $uri): string
    {
        return (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($uri);
    }

    private static function at(string $secret, int $step): string
    {
        $hash = hash_hmac('sha1', pack('J', $step), self::base32Decode($secret), true);
        $offset = ord($hash[19]) & 0xF;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $secret): string
    {
        $bits = '';
        foreach (str_split(strtoupper($secret)) as $char) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($byte) => chr(bindec($byte)), str_split(substr($bits, 0, intdiv(strlen($bits), 8) * 8), 8)));
    }
}
