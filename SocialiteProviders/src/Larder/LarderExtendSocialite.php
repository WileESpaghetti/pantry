<?php

namespace SocialiteProviders\Larder;

use SocialiteProviders\Manager\SocialiteWasCalled;

class LarderExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('larder', __NAMESPACE__.'\Provider');
    }
}
