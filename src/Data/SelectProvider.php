<?php

namespace App\Data;

use App\Entity\Provider;

class SelectProvider
{
    private $providerToKeep;
    private $providerToReplace;


    public function getProviderToKeep(): ?Provider
    {
        return $this->providerToKeep;
    }

    public function setProviderToKeep(?Provider $providerToKeep): self
    {
        $this->providerToKeep = $providerToKeep;

        return $this;
    }

    public function getProviderToReplace(): ?Provider
    {
        return $this->providerToReplace;
    }

    public function setProviderToReplace(?Provider $providerToReplace): self
    {
        $this->providerToReplace = $providerToReplace;

        return $this;
    }
}
