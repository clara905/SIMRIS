<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class CaptchaService
{
    private const TTL = 300;
    private const LENGTH = 5;
    private const CHARS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // tanpa 0/O/1/I/L

    /** @return array{key: string, image: string} */
    public function make(): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi PHP GD belum aktif (aktifkan extension=gd di php.ini).');
        }

        $code = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::CHARS[random_int(0, strlen(self::CHARS) - 1)];
        }

        $key = (string) Str::uuid();
        Cache::put($this->cacheKey($key), $code, self::TTL);

        return ['key' => $key, 'image' => $this->render($code)];
    }

    /** Sekali pakai: kode dihapus saat dicek, benar atau salah. */
    public function verify(?string $key, ?string $input): bool
    {
        if (! $key || ! $input) {
            return false;
        }

        $expected = Cache::pull($this->cacheKey($key));

        return is_string($expected) && hash_equals($expected, strtoupper(trim($input)));
    }

    private function cacheKey(string $key): string
    {
        return 'captcha:' . $key;
    }

    private function render(string $code): string
    {
        $w = 100;
        $h = 32;
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 241, 245, 249));

        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($img, random_int(150, 210), random_int(150, 210), random_int(150, 210));
            imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $c);
        }

        $x = 8;
        foreach (str_split($code) as $ch) {
            $c = imagecolorallocate($img, random_int(20, 90), random_int(20, 90), random_int(60, 120));
            imagestring($img, 5, $x, random_int(4, 12), $ch, $c);
            $x += 17;
        }

        for ($i = 0; $i < 60; $i++) {
            imagesetpixel($img, random_int(0, $w - 1), random_int(0, $h - 1),
                imagecolorallocate($img, random_int(100, 200), random_int(100, 200), random_int(100, 200)));
        }

        $big = imagecreatetruecolor(200, 64);
        imagecopyresampled($big, $img, 0, 0, 0, 0, 200, 64, $w, $h);

        ob_start();
        imagepng($big);
        $png = (string) ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($png);
    }
}