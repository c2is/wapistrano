<?php

namespace Wapistrano\ProfileBundle\Security;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

class WapistranoPasswordEncoder extends BasePasswordEncoder
{
    protected function mergePasswordAndSalt($password, $salt)
    {
        return "--".$salt."--".$password."--";
    }

    public function encodePassword($raw, $salt)
    {
        $salted = $this->mergePasswordAndSalt($raw, $salt);

        $digest = hash('sha1', $salted);

        return $digest;
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }

    public function genSalt() {
        $salt = "";
        for($i = 0; $i > 5; $i++) {
            $salt .= chr(rand(48, 90));
        }

        return hash('sha1', $salt);
    }
}