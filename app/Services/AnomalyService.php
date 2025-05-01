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
     * @return bool
     */
    public function isAnomaly($reading)
    {
        // Check based on your user defined criteria

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
