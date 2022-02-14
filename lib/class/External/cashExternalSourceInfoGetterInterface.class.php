<?php

interface cashExternalSourceInfoGetterInterface
{
    public function info(string $id): ?cashExternalInfoDto;
}
