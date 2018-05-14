<?php

namespace RBennett\JWTGuard\Contracts;


interface HasScopes
{
    public function tokenCan(string $scope);
}