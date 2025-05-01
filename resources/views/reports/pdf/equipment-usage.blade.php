@extends('reports.pdf.layout')

@section('content')
    <div class="summary">
        <div class="summary-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Monitored Devices</div>
                <div class="summary-value">{{ $profileName}}</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Average Uptime</div>
                <div class="summary-value">{{ number_format($reportData['summary']['average_uptime'], 1) }}%</div>
            </div>
        </div>
    </div>

    <h3>Equipment Details</h3>
    <table>
        <thead>
            <tr>
                {{-- <th>Socket</th> --}}
                <th>Uptime %</th>
                <th>Downtime %</th>
                <th>Min Power (W)</th>
                <th>Max Power (W)</th>
                <th>Avg Power (W)</th>
                <th>Voltage Range (V)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['equipment_data'] as $item)
                <tr>
                    {{-- <td>{{ $item['profileName'] }}</td> --}}
                    <td>{{ $item['uptime_percentage'] }}%</td>
                    <td>{{ $item['downtime_percentage'] }}%</td>
                    <td>{{ $item['power_fluctuation']['min'] }}</td>
                    <td>{{ $item['power_fluctuation']['max'] }}</td>
                    <td>{{ $item['power_fluctuation']['avg'] }}</td>
                    <td>{{ $item['voltage_fluctuation']['min'] }} - {{ $item['voltage_fluctuation']['max'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="analysis">
        <h3>Analysis</h3>
        <p>
            This report shows the equipment usage statistics over the selected period. 
            The average uptime across all devices was {{ number_format($reportData['summary']['average_uptime'], 1) }}%.
            @if(isset($reportData['equipment_data']) && count($reportData['equipment_data']) > 0)
                The device with the highest uptime was 
                {{ collect($reportData['equipment_data'])->sortByDesc('uptime_percentage')->first()['profile_name'] }}
                at {{ collect($reportData['equipment_data'])->sortByDesc('uptime_percentage')->first()['uptime_percentage'] }}%.
                
                The device with the highest power consumption was
                {{ collect($reportData['equipment_data'])->sortByDesc(function($item) { return $item['power_fluctuation']['max']; })->first()['profile_name'] }}
                with a maximum power of {{ collect($reportData['equipment_data'])->sortByDesc(function($item) { return $item['power_fluctuation']['max']; })->first()['power_fluctuation']['max'] }} W.
            @endif
        </p>
    </div>
@endsection