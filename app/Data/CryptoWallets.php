<?php
namespace App\Data;

class CryptoWallets
{
    protected static array $data = [
        [
            'address' => '0x5C2E331E6612Ac676AE06eC126b561B2Fe7ca68C',
        ],
        // [
        //     'address' => '0x81c0E1afC2DbeC4C731Fa255D8Bda4a29acA2Daa',
        // ],
        // [
        //     'address' => '0x598FbFEE20FBCDE5408aF3469eE8FFfbdd491a5C',
        // ],
        // [
        //     'address' => '0x3EB7C5dDB7Ef1726b8A25F8562d814ef448b3AdA',
        // ],
    ];
    public static function all()
    {
        return self::$data;
    }
}
