@extends('reports.pdf.layout')

@section('content')
    @if(isset($chartImageData))
    <div class="chart-container">
        <img src="data:image/png;base64,{{ $chartImageData }}" class="chart-image" alt="Energy Consumption Chart">
    </div>
    @endif
    
    <div class="summary">
        <div class="summary-title">Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Energy Consumption</div>
                <div class="summary-value">{{ number_format($reportData['summary']['total_energy'], 2) }} kWh</div>
            </div>
            
            <div class="summary-item">
                <div class="summary-label">Average Daily Consumption</div>
                <div class="summary-value">{{ number_format($reportData['summary']['average_daily'], 2) }} kWh</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Energy Consumption (kWh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['labels'] as $index => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($reportData['datasets'][0]['data'][$index], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="analysis">
        <h3>Analysis</h3>
        <p>
            This report shows the energy consumption over the selected period. The total energy consumed was 
            {{ number_format($reportData['summary']['total_energy'], 2) }} kWh with an average daily consumption of 
            {{ number_format($reportData['summary']['average_daily'], 2) }} kWh.
        </p>
    </div>
@endsection