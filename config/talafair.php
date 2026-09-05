<?php

return [
    // Fallback geofence centre (Barangay San Jose, Iriga City) — replace with your own pin.
    'barangay_lat'   => env('TALAFAIR_BARANGAY_LAT', 13.4262080),
    'barangay_lng'   => env('TALAFAIR_BARANGAY_LNG', 123.3987476),
    'default_radius' => env('TALAFAIR_DEFAULT_RADIUS', 300), // metres

    'address' => [
        'barangay'    => 'San Jose',
        'city'        => 'Iriga City',
        'province'    => 'Camarines Sur',
        'country'     => 'Philippines',
        'postal_code' => '4431',
    ],

    'official_positions' => [
        'barangay_council' => [
            'label'     => 'Barangay Council',
            'positions' => [
                'barangay_captain' => 'Barangay Captain',
                'kagawad'         => 'Kagawad',
                'secretary'       => 'Secretary',
                'treasurer'       => 'Treasurer',
            ],
        ],
        'sangguniang_kabataan' => [
            'label'     => 'Sangguniang Kabataan',
            'positions' => [
                'sk_chairman' => 'SK Chairman',
                'sk_council'  => 'SK Kagawad',
                'secretary'   => 'Secretary',
                'treasurer'   => 'Treasurer',
            ],
        ],
        'personnel' => [
            'label'     => 'Personnel',
            'positions' => [
                'tanod'                   => 'Barangay Tanod',
                'health_worker'           => 'Health Worker',
                'nutrition_scholar'       => 'Barangay Nutrition Scholar',
                'day_care_worker'         => 'Day Care Worker',
                'other_personnel'         => 'Other Personnel',
            ],
        ],
    ],

    'official_position_limits' => [
        'barangay_council' => [
            'barangay_captain' => 1,
            'kagawad' => 7,
            'secretary' => 1,
            'treasurer' => 1,
        ],
        'sangguniang_kabataan' => [
            'sk_chairman' => 1,
            'sk_council' => 7,
            'secretary' => 1,
            'treasurer' => 1,
        ],
    ],
];
