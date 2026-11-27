<?php

namespace App\Support;

use Jenssegers\Agent\Agent;

class DeviceInfo
{
    public static function fromUserAgent(?string $ua): array
    {
        $agent = new Agent();
        $agent->setUserAgent($ua ?? '');

        $platform = $agent->platform();
        $browser  = $agent->browser();

        return [
            'platform'   => $platform,
            'browser'    => $browser,
            'device'     => $agent->device(),
            'is_desktop' => $agent->isDesktop(),
            'is_mobile'  => $agent->isPhone() || $agent->isTablet(),
            'version'    => [
                'platform' => $agent->version($platform) ?: null,
                'browser'  => $agent->version($browser) ?: null,
            ],
        ];
    }
}
