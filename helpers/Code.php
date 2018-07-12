<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/29
 * Time: 下午7:14
 */
namespace app\helpers;

class Code
{

    public static function generateShortCode($input)
    {
        $base32 = array(
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
            "k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
            "u", "v", "w", "x", "y", "z",
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J",
            "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T",
            "U", "V", "W", "X", "Y", "Z"
        );

        $key = 'huogou_';
        $hash = md5($key . $input);
        $len = strlen($hash);
        $output = array();

        #将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
        for ($i = 0; $i < 4; $i++) {
            $hash_piece = substr($hash, $i * $len / 4, $len / 4);
            #将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
            $hex = hexdec($hash_piece) & 0x3fffffff; #此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常

            $code = "";
            #生成6位短连接
            for ($j = 0; $j < 6; $j++) {
                #将得到的值与0x0000003d,3d为61，即$base32的坐标最大值
                $code .= $base32[$hex & 0x0000003d];
                #循环完以后将hex右移5位
                $hex = $hex >> 5;
            }
            $crc = sprintf('%u', crc32($input));
            $code .= $base32[$crc%62]; //末尾加一位
            $output[] = $code;

        }

        return $output;
    }


}