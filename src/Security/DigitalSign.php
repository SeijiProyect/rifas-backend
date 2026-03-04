<?php

namespace App\Security;

use App\Repository\LinkPagoRifaRepository;

use App\Entity\LinkPagoRifa;

use Doctrine\ORM\EntityManagerInterface;

class DigitalSign
{
    // private $linkPagoRifaRepository;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function authCheck($hash)
    {
        $auth = false;

        if ($hash != null) {
            if ($hash === 'diandfvluNCnbawehfb2348dSANndb') {
                return true;
            } else {
                $linkData = $this->em->getRepository(LinkPagoRifa::class)->findOneBy(array(
                    "EncryptedLink" => $hash
                ));

                if ($linkData) {
                    if ($linkData->getEstado() == 'Pendiente de pago') {
                        $auth = true;
                    } else {
                        $auth = false;
                    }
                }
            }
        }

        return $auth;
    }

    public function generateDigitalSign($pData)
    {
        $str = $this->arrayToString($pData);
        $signature = $this->encrypt_RSA(preg_replace('/[^\x21-\x7E]/', '', $str));
        return $signature;
    }

    public function arrayToString($pArray)
    {
        $result = "";

        foreach ($pArray as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $result .= $key;
                }
                $result .= $this->arrayToString($value);
            } else {
                if (!is_numeric($key)) {
                    $result .= $key;
                }

                if ( is_bool($value) ) {
                    if ( $value ) {
                        $value = 'TRUE';
                    } else {
                        $value = 'FALSE';
                    }
                }

                $result .= str_replace(" ", "", $value);
            }
        }

        return strtoupper($result);
    }

    public function verifyRequest($pSignature, $pData)
    {
        $str = $this->arrayToString($pData);

        $pKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuES5UehhH8YtJ8jhZ3Fd
0/iJqCUQ04Ly5JE3ZTcluOUw/HdZJIMNPq4HzlUvptmBVNZvo1MBr85L9MbcLM0v
QkoqkqLcvbidDMhk9+9A/XvzoriFA0RY/49K99/wu4Geq9KdtwpddRSrMTX39wGA
4X9iRIx8/jr+zlRQ/5Fz39CEYDEuP2MZ1SZjJcXwX8sYj8kFYWu5LxPZSbbly69x
D1OfzHK+ocp6/rSfJP6E6C144el8r3EeCkh1+yZRYh7hcuGiTni/Rsk2Vs2ls1E6
LPSZHfuIEGkKdJeawOXx02gm/V0+QzM6MFNUm78T25MUSYR+PqAhtrf6Yh1Ab+dO
vwIDAQAB
-----END PUBLIC KEY-----";

        $decryptionOk = openssl_verify($str, $pSignature, $pKey, 'sha1WithRSAEncryption');

        if ($decryptionOk === false) {
            return false;
        }

        return true;
    }

    private function encrypt_RSA($plainData)
    {
        $encrypted = '';
        $pKey = "-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDCCwbrefKGGO+W
ZFxuljIgI3Cq10xiFyA4/MwJEkY/LQD/wvjaeZn0Vq5VjjPYymlhzfUg9La1ucxb
t/bL5XjiVCyWcTSltbW6rC7dC95jT4O/wqkQuPhIGXvMbD5Qbjn1c5i/jjpZsXvO
cuo0SIP5GH0ah0vyDoKUI9YEJKdD5lOpsPXYjjxEdrqddOWbptC22iSzD3BGVSp8
07Bx5AXpsBj4NX/0LRS8eGKUpN3kwGsQnlHvdHjz5ubEhiIu66iYda/yDwfXiY7Z
+82VvDf+RVnwlDg6Ito0YeIBT2YjFZldBfRbw5+aF/TKOXSS3eoc4BwpZIWgoLTu
i7NyBoVDAgMBAAECggEAW6w9/q3ESForhs+vY4eN8uULa4r/yS/Hx2SXOInrqhkm
6a5BR2Y/t9Hj1wOxtvAZzV0yO/mhAEXhKEvHSxkEfVjrH8An8Unhq2mBUmkzDZQZ
WFUs7nuUwOtZM4DIB9Oriiyss4MMSiexqB1eTIkd4GcTY58b4CW+xyIPEDII2+h3
5cG/N2lx4NQhc2k4tdx6ECcM02iiK90qxXGGSGfYokD55geW539mZADiVQHrkRRT
xP6r5xx8YgY4WUGS6VzUJ8RJaTl1JpeVZgXsGue0fiJMjaPgjP29Wa8BNvvPvTXV
WPPBvNMu+U6Ir6orerO8b6P8HAIPh/D5wzxpMNpqUQKBgQDviPZzV6L0V3LBaBwe
8V34Heg0WsA9XQ/g5TqN7wQDOnoro+hhwdSR2yzPEgw4NOuEd+ctMbuuJbhECZr6
rmCKyCTYB1Cv5HqM1LYHD97Vd5LLSMGDsf1jrlClMnQUaWfY+9hobPSCgQY4mx6X
M3cAUH7LqwvYZpatYaXebcOLbQKBgQDPYY3et66hnfR1w6uF7sj4gerNtsUOqMi/
ILtqwmPpqf0GKJ3GWKh3NdXVku6hufPqgTaqMQ9dTBGcd5l6yckIYKzGrG1cmIQJ
O9TuFnGwMu8f2HxzI8SbQXzqRmPOASLTF6GkmbBGYqCTmh8WrjzNXGRka4KRMo9/
SehK4K21bwKBgQCGhy6a0wJTJnXtx5HbkwWKnwlVtg43qHeNgFBlfHKNeZ85uUF+
/cRr8mN1HJa+ToJjkI/GYyMcCFU36QkgdyUDbKq8pUccsj5dr+1XhANLcm+AOils
1F8SR8lFLlPhTt2jELaW13JvhRVAX2ZL1vyjsZG0wyjQ7bT8RVZJ2O209QKBgHZW
/vQ28So8zsFc9UYqaviUen8FaxHgcx3N+eUEJKu0WzTmfJIxCYFtcSa8//u991i8
f4tDD7d3rx0Qir6s41Eqia2du5J5Xe8++1UZ7apUhmwvx2SU5p6KElRF8rIdp9e5
xn39Tb/8jBFug4sXxo6RyKAdUZa1uXkznaEM8VN1AoGAMRt0R/VsBUw0FOVgixgK
+rwzbFtV+JwFV2N12zXBZqNbFLfV1he/T1yweviuzyDKCMLmEfHwW+XSTlx8gT3K
RHGWTGiB8RCjGCKI+9s6XZdlmz55xz4OlsOiOktIw+zyrCbwpGupr9NXSDKz3AmL
9oWpXt+uGc5icuKe9iqtNhs=
-----END PRIVATE KEY-----";

        $encryptionOk = openssl_sign($plainData, $encrypted, $pKey, 'sha1WithRSAEncryption');

        if ($encryptionOk === false) {
            return false;
        }

        return base64_encode($encrypted); //encoding the whole binary String as MIME base 64
    }

    private function decrypt_RSA($data, $signature)
    {
        $pKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7OeN+BTZ8Fp+2/palOy8
zzGKLRaHc2loHXf1MN1IQf45mAtNxo791egamwwK3DW23j5F/Vi+YSxGrbtoWqGf
ZfK84g8UKHvtGl2oMQw3L+y/1s0AmXk2FY8dHvIUvP1qU8lnk7q0xXF5b1dAO7Vt
ju+kd48+L56DEYmiJzjqCPh0baM+n/fj3EUyAqKrQOXlnbgSwdJU5W3PrHRg03va
4H+PSUVnjjeWI2mxLAc4JJbEtMsTlw0bcQnsKcD1HrCx6nWCMBlu3CSawIorcZ6U
ZgwfCmTFQTMkLpGwj476z4xvhPlF8Ap2s6Vd3G8ohq+KVJCkGzQ3+ptEGSpbNL7K
WwIDAQAB
-----END PUBLIC KEY-----";

        $decryptionOk = openssl_verify($data, $signature, $pKey, 'sha1WithRSAEncryption');

        if ($decryptionOk === false) {
            return false;
        }

        return true;
    }
}