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
}