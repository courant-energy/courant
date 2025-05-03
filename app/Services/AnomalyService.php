<?php

namespace App\Services;

class AnomalyService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Check if the reading is an anomaly.
     *
     * @param  \App\Models\Reading  $reading
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function isAnomaly($reading, $user)
    {
        // Check based on your user defined criteria
        if ($reading->power > $reading->profile->max_power_threshold) {
            return true;
        }

        if ($reading->power < $reading->profile->min_power_threshold) {
            return true;
        }
        
        // Check based on the ML service
        


    }

    /**
     * Check if an appliance needs to be flagged for maintenance.
     *
     * @param  \App\Models\Socket  $socket
     * @param \App\Models\Profile  $profile
     * @return bool
     */
    public function needsMaintenance($socket, $profile)
    {
        // Check based on your user defined criteria

        // Check based on the ML service

    }
}
