@extends('reports.pdf.layout')

@section('content')
    @if(isset($chartImageData))
    <div class="chart-container">
        <img src="data:image/png;base64,{{ $chartImageData }}" class="chart-image" alt="Anomaly Report Chart">
    </div>
    @endif
    
    <div class="summary">
        <div class="summary-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Anomalies</div>
                <div class="summary-value">{{ $reportData['summary']['total_anomalies'] }}</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Critical Anomalies</div>
                <div class="summary-value">{{ $reportData['summary']['critical_count'] }}</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Warning Anomalies</div>
                <div class="summary-value">{{ $reportData['summary']['warning_count'] }}</div>
            </div>
        </div>
    </div>

    @if(isset($reportData['anomaly_counts']) && count($reportData['anomaly_counts']['labels']) > 0)
    <h3>Anomaly Distribution</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['anomaly_counts']['labels'] as $index => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $reportData['anomaly_counts']['data'][$index] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <h3>Anomaly Details</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Severity</th>
                <th>Socket</th>
                <th>Reading</th>
                <th>Message</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['details'] as $item)
                <tr>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ ucfirst($item['severity']) }}</td>
                    <td>{{ $item['profile'] }}</td>
                    <td>{{ $item['reading_value'] }}W</td>
                    <td>{{ $item['message'] }}</td>
                    <td>{{ $item['timestamp'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="analysis">
        <h3>Analysis</h3>
        <p>
            This report shows the anomalies detected over the selected period. 
            A total of {{ $reportData['summary']['total_anomalies'] }} anomalies were detected, 
            with {{ $reportData['summary']['critical_count'] }} critical issues requiring immediate attention.
            
            @if(isset($reportData['anomaly_counts']) && count($reportData['anomaly_counts']['labels']) > 0)
                The most common anomaly type was "{{ $reportData['anomaly_counts']['labels'][array_keys($reportData['anomaly_counts']['data'], max($reportData['anomaly_counts']['data']))[0]] }}".
            @endif
        </p>
    </div>
@endsection