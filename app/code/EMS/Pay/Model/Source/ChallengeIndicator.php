<?php

namespace EMS\Pay\Model\Source;

use EMS\Pay\Gateway\Config\Config;

class ChallengeIndicator
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::CHALLENGE_INDICATOR_1,
                'label' => __('No preference (You have no preference whether a challenge should be performed. This is the default value)')
            ],
            [
                'value' => Config::CHALLENGE_INDICATOR_2,
                'label' => __('No challenge requested (You prefer that no challenge should be performed.)')
            ],
            [
                'value' => Config::CHALLENGE_INDICATOR_3,
                'label' => __('Challenge requested: 3DS Requestor Preference (You prefer that a challenge should be performed)')
            ],
            [
                'value' => Config::CHALLENGE_INDICATOR_4,
                'label' => __('Challenge requested: Mandate (There are local or regional mandates that mean that a challenge must be performed)')
            ]
        ];
    }
}