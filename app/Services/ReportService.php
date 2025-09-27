<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Profile;
use App\Models\Alert;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    /**
     * @var EnergyCalculationService
     */
    protected $energyCalculationService;
    
    /**
     * Create a new service instance.
     *
     * @param EnergyCalculationService $energyCalculationService
     */
    public function __construct(EnergyCalculationService $energyCalculationService)
    {
        $this->energyCalculationService = $energyCalculationService;
    }

    /**
     * Generate energy consumption report data
     *
     * @param string $timeRange daily|weekly|monthly
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $profileId
     * @return array
     */
    public function generateEnergyConsumptionReport(string $timeRange, Carbon $startDate, Carbon $endDate, ?int $profileId = null): array
    {
        $profiles = $this->getUserProfiles($profileId);

        // Structure to hold energy data by date
        $energyByDate = [];
        foreach ($profiles as $profile) {
            // Use the EnergyCalculationService to calculate energy consumption
            $totalEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                $profile,
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            );

            // For daily reporting, we need to split the energy by day
            if ($timeRange === 'daily') {
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
                foreach ($period as $date) {
                    $dayStart = $date->copy()->startOfDay();
                    $dayEnd = $date->copy()->endOfDay();
                    $formattedDate = $dayStart->format('Y-m-d');
                    
                    // Calculate energy for this specific day
                    $dayEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                        $profile,
                        $dayStart->format('Y-m-d H:i:s'),
                        $dayEnd->format('Y-m-d H:i:s')
                    );
                    
                    if (!isset($energyByDate[$formattedDate])) {
                        $energyByDate[$formattedDate] = 0;
                    }
                    $energyByDate[$formattedDate] += $dayEnergy;
                }
            }
        }

        // Convert our energy data into a collection that matches the expected format
        $rawData = collect();
        foreach ($energyByDate as $date => $energy) {
            $rawData->push((object)[
                'date' => $date,
                'total_energy' => $energy
            ]);
        }

        // If not daily or no data yet (for weekly/monthly), use the total for the period
        if (empty($energyByDate)) {
            $totalEnergyAllProfiles = $profiles->sum(function ($profile) use ($startDate, $endDate) {
                return $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $startDate->format('Y-m-d H:i:s'),
                    $endDate->format('Y-m-d H:i:s')
                );
            });
            
            // For weekly or monthly, add a single entry representing the total period
            $rawData->push((object)[
                'date' => $startDate->format('Y-m-d'),
                'total_energy' => $totalEnergyAllProfiles
            ]);
        }
        
        // Format data based on time range
        $formattedData = $this->formatTimeSeriesData($rawData, $timeRange, $startDate, $endDate);
        
        return [
            'labels' => $formattedData['labels'],
            'datasets' => [
                [
                    'label' => 'Energy Consumption (kWh)',
                    'data' => $formattedData['values'],
                ]
            ],
            'summary' => [
                'total_energy' => $rawData->sum('total_energy'),
                'average_daily' => $rawData->count() > 0 ? $rawData->sum('total_energy') / $rawData->count() : 0,
            ]
        ];
    }

    /**
     * Generate cost analysis report data
     *
     * @param string $timeRange daily|monthly
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $profileId
     * @param float $costPerKwh
     * @return array
     */
    public function generateCostAnalysisReport(string $timeRange, Carbon $startDate, Carbon $endDate, float $costPerKwh = 0.12, ?int $profileId = null): array
    {
        $profiles = $this->getUserProfiles($profileId);

        // Structure to hold energy data by date
        $energyByDate = [];
        foreach ($profiles as $profile) {
            // For daily reporting, we need to split the energy by day
            if ($timeRange === 'daily') {
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
                foreach ($period as $date) {
                    $dayStart = $date->copy()->startOfDay();
                    $dayEnd = $date->copy()->endOfDay();
                    $formattedDate = $dayStart->format('Y-m-d');
                    
                    // Calculate energy for this specific day using the service
                    $dayEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                        $profile,
                        $dayStart->format('Y-m-d H:i:s'),
                        $dayEnd->format('Y-m-d H:i:s')
                    );
                    
                    if (!isset($energyByDate[$formattedDate])) {
                        $energyByDate[$formattedDate] = 0;
                    }
                    $energyByDate[$formattedDate] += $dayEnergy;
                }
            } else {
                // For weekly/monthly, calculate the total for the entire period
                $totalEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $startDate->format('Y-m-d H:i:s'),
                    $endDate->format('Y-m-d H:i:s')
                );
                
                $formattedDate = $startDate->format('Y-m-d');
                if (!isset($energyByDate[$formattedDate])) {
                    $energyByDate[$formattedDate] = 0;
                }
                $energyByDate[$formattedDate] += $totalEnergy;
            }
        }

        // Convert energy data to cost data
        $costData = collect();
        foreach ($energyByDate as $date => $energy) {
            $costData->push((object)[
                'date' => $date,
                'energy' => $energy,
                'cost' => $energy * $costPerKwh
            ]);
        }

        // Format data based on time range
        $formattedData = $this->formatTimeSeriesData($costData, $timeRange, $startDate, $endDate, 'cost');
        
        return [
            'labels' => $formattedData['labels'],
            'datasets' => [
                [
                    'label' => 'Energy Cost (PHP)',
                    'data' => $formattedData['values'],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.6)',
                ]
            ],
            'summary' => [
                'total_cost' => $costData->sum('cost'),
                'total_energy' => $costData->sum('energy'),
                'average_daily_cost' => $costData->count() > 0 ? $costData->sum('cost') / $costData->count() : 0,
            ]
        ];
    }

    /**
     * Generate equipment usage report data
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $profileId
     * @return array
     */
    public function generateEquipmentUsageReport(Carbon $startDate, Carbon $endDate, ?int $profileId = null): array
    {
        $userProfileIds = $this->getUserProfileIds($profileId);
        
        $query = Reading::query()
            ->select(
                'profile_id',
                DB::raw('COUNT(*) as reading_count'),
                DB::raw('SUM(CASE WHEN power > 0 THEN 1 ELSE 0 END) as active_readings'),
                DB::raw('MIN(power) as min_power'),
                DB::raw('MAX(power) as max_power'),
                DB::raw('AVG(power) as avg_power'),
                DB::raw('MIN(voltage) as min_voltage'),
                DB::raw('MAX(voltage) as max_voltage'),
                DB::raw('AVG(voltage) as avg_voltage')
            )
            ->whereBetween('reading_time', [$startDate, $endDate])
            ->whereNotNull('profile_id')
            ->whereIn('profile_id', $userProfileIds)
            ->groupBy('profile_id');

        $profileData = $query->get();
        
        // Enrich with profile information
        $profileData->load('socket.profile');
        
        $formattedData = $profileData->map(function ($data) use ($startDate, $endDate) {
            $profile = Profile::find($data->profile_id);
            $totalHours = $startDate->diffInHours($endDate);
            $activePercentage = ($data->active_readings / $data->reading_count) * 100;
            
            return [
                'profile_id' => $data->profile_id,
                'profile_name' => $profile ? $profile->name : 'Unknown',
                'uptime_percentage' => round($activePercentage, 2),
                'downtime_percentage' => round(100 - $activePercentage, 2),
                'power_fluctuation' => [
                    'min' => round($data->min_power, 2),
                    'max' => round($data->max_power, 2),
                    'avg' => round($data->avg_power, 2),
                ],
                'voltage_fluctuation' => [
                    'min' => round($data->min_voltage, 2),
                    'max' => round($data->max_voltage, 2),
                    'avg' => round($data->avg_voltage, 2),
                ],
            ];
        });
        
        return [
            'equipment_data' => $formattedData,
            'summary' => [
                'total_profiles' => $profileData->count(),
                'average_uptime' => ($avgReadingCount = $profileData->avg('reading_count')) > 0 ? round(($profileData->avg('active_readings') / $avgReadingCount) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Generate anomaly report data
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $profileId
     * @return array
     */
    public function generateAnomalyReport(Carbon $startDate, Carbon $endDate, ?int $profileId = null): array
    {
        $userProfileIds = $this->getUserProfileIds($profileId);
        
        $query = Alert::query()
            ->with(['socket.profile'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by user's profiles
        $query->where(function($q) use ($userProfileIds) {
            $q->whereIn('profile_id', $userProfileIds)
              ->orWhereHas('socket.profile', function($subQuery) use ($userProfileIds) {
                  $subQuery->whereIn('id', $userProfileIds);
              });
        });

        $alerts = $query->get();
        
        // Group anomalies by type
        $anomalyTypes = $alerts->groupBy('type');
        $anomalyCounts = $anomalyTypes->map->count();
        
        // Group by severity
        $severityCounts = $alerts->groupBy('severity')->map->count();
        
        // Get anomalies by date
        $anomaliesByDate = $alerts->groupBy(function ($alert) {
            return $alert->created_at->format('Y-m-d');
        })->map->count();
        
        return [
            'anomaly_counts' => [
                'labels' => $anomalyCounts->keys()->toArray(),
                'data' => $anomalyCounts->values()->toArray(),
            ],
            'severity_counts' => [
                'labels' => $severityCounts->keys()->toArray(),
                'data' => $severityCounts->values()->toArray(),
            ],
            'anomalies_by_date' => [
                'labels' => $anomaliesByDate->keys()->toArray(),
                'data' => $anomaliesByDate->values()->toArray(),
            ],
            'details' => $alerts->map(function ($alert) {
                // Get profile either from direct relationship or through socket
                $profile = $alert->profile ?? ($alert->socket->profile ?? null);
                
                // Get reading data safely
                $reading = null;
                $readings = $alert->readings ?? collect(); // Handle null case
                if ($readings->isNotEmpty()) {
                    $reading = $readings->first();
                }
                
                return [
                    'id' => $alert->id,
                    'type' => $alert->type,
                    'severity' => $alert->severity,
                    'message' => $alert->message,
                    'profile' => $profile ? $profile->name : 'Unknown',
                    'reading_value' => $reading->power ?? "N/A ",
                    'timestamp' => $alert->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'summary' => [
                'total_anomalies' => $alerts->count(),
                'critical_count' => $alerts->where('severity', 'critical')->count(),
                'warning_count' => $alerts->where('severity', 'warning')->count(),
                'info_count' => $alerts->where('severity', 'info')->count(),
            ]
        ];
    }

    /**
     * Format time series data based on time range
     *
     * @param Illuminate\Support\Collection $data
     * @param string $timeRange
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $valueField
     * @return array
     */
    protected function formatTimeSeriesData( $data, string $timeRange, Carbon $startDate, Carbon $endDate, string $valueField = 'total_energy'): array
    {
        $labels = [];
        $values = [];
        
        switch ($timeRange) {
            case 'daily':
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
                foreach ($period as $date) {
                    $formattedDate = $date->format('Y-m-d');
                    $labels[] = $date->format('M d');
                    
                    $record = $data->firstWhere('date', $formattedDate);
                    $values[] = $record ? round($record->{$valueField}, 3) : 0;
                }
                break;
                
            case 'weekly':
                $startWeek = $startDate->startOfWeek();
                $endWeek = $endDate->copy()->endOfWeek();
                $period = CarbonPeriod::create($startWeek, '1 week', $endWeek);
                
                foreach ($period as $weekStart) {
                    $weekEnd = $weekStart->copy()->endOfWeek();
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    
                    $weekData = $data->filter(function ($item) use ($weekStart, $weekEnd) {
                        $date = Carbon::parse($item->date);
                        return $date->between($weekStart, $weekEnd);
                    });
                    
                    $values[] = $weekData->sum($valueField) ? round($weekData->sum($valueField), 3) : 0;
                }
                break;
                
            case 'monthly':
                $startMonth = $startDate->copy()->startOfMonth();
                $endMonth = $endDate->copy()->endOfMonth();
                $period = CarbonPeriod::create($startMonth, '1 month', $endMonth);
                
                foreach ($period as $monthStart) {
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    $labels[] = $monthStart->format('M Y');
                    
                    $monthData = $data->filter(function ($item) use ($monthStart, $monthEnd) {
                        $date = Carbon::parse($item->date);
                        return $date->between($monthStart, $monthEnd);
                    });
                    
                    $values[] = $monthData->sum($valueField) ? round($monthData->sum($valueField), 3) : 0;
                }
                break;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get available profiles for report filtering
     *
     * @return array
     */
    public function getAvailableProfiles(): array
    {
        return Profile::query()
            ->where('user_id', Auth::id())
            ->whereHas('sockets')
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'socket_count' => $profile->sockets->count(),
                ];
            })
            ->toArray();
    }

    /**
     * Export report data to CSV format
     *
     * @param array $data
     * @param string $reportType
     * @return string
     */
    public function exportToCsv(array $data, string $reportType): string
    {
        $output = fopen('php://temp', 'r+');
        
        switch ($reportType) {
            case 'energy_consumption':
                fputcsv($output, ['Date', 'Energy Consumption (kWh)']);
                $labels = $data['labels'];
                $values = $data['datasets'][0]['data'];
                
                foreach ($labels as $index => $label) {
                    fputcsv($output, [$label, $values[$index]]);
                }
                break;
                
            case 'cost_analysis':
                fputcsv($output, ['Date', 'Cost (PHP)']);
                $labels = $data['labels'];
                $values = $data['datasets'][0]['data'];
                
                foreach ($labels as $index => $label) {
                    fputcsv($output, [$label, $values[$index]]);
                }
                break;
                
            case 'equipment_usage':
                fputcsv($output, ['Profile', 'Uptime %', 'Downtime %', 'Min Power', 'Max Power', 'Avg Power', 'Min Voltage', 'Max Voltage', 'Avg Voltage']);
                
                foreach ($data['equipment_data'] as $item) {
                    fputcsv($output, [
                        $item['profile_name'],
                        $item['uptime_percentage'],
                        $item['downtime_percentage'],
                        $item['power_fluctuation']['min'],
                        $item['power_fluctuation']['max'],
                        $item['power_fluctuation']['avg'],
                        $item['voltage_fluctuation']['min'],
                        $item['voltage_fluctuation']['max'],
                        $item['voltage_fluctuation']['avg'],
                    ]);
                }
                break;
                
            case 'anomaly':
                fputcsv($output, ['ID', 'Type', 'Severity', 'Message', 'Profile', 'Reading', 'Timestamp']);
                
                foreach ($data['details'] as $item) {
                    fputcsv($output, [
                        $item['id'],
                        $item['type'],
                        $item['severity'],
                        $item['message'],
                        $item['profile'],
                        $item['reading_value'],
                        $item['timestamp'],
                    ]);
                }
                break;
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Get user's profiles based on profileId filter
     *
     * @param int|null $profileId
     * @return Collection
     */
    protected function getUserProfiles(?int $profileId = null): Collection
    {
        $query = Profile::query()->where('user_id', Auth::id());
        
        if ($profileId) {
            $query->where('id', $profileId);
        } else {
            $query->whereHas('sockets');
        }
        
        return $query->get();
    }

    /**
     * Get user's profile IDs based on profileId filter
     *
     * @param int|null $profileId
     * @return array
     */
    protected function getUserProfileIds(?int $profileId = null): array
    {
        $query = Profile::query()->where('user_id', Auth::id());
        
        if ($profileId) {
            $query->where('id', $profileId);
        } else {
            $query->whereHas('sockets');
        }
        
        return $query->pluck('id')->toArray();
    }
}