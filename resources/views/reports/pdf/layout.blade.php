<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 50px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }
        h3 {
            font-size: 15px;
            color: #333;
            margin-bottom: 5;
            font-weight: bold;
        }
        p{
            margin: 5px 0;
            font-size: 12px;
            color: #666;
            
        }
        .meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: left;
        }
        .chart-container {
            width: 100%;
            margin: 20px 0;
            text-align: center;
        }
        .chart-image {
            max-width: 100%;
            height: auto;
        }
        .summary {
            margin: 20px 0;
            padding: 5px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 2px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo.svg') }}" alt="Logo" class="logo">
        <h1>{{ $title }}</h1>
        <div class="meta" style="text-align: left;">
           {{ $companyName }}<br/>
           Period: {{ $reportPeriod }}<br/>
           Device: {{ $profileName }}<br/>
           
        </div>
    </div>
    
    @yield('content')
    
    <div style="margin-top: 30px; font-size: 12px; color: #666;">
         <p>Generated: {{ $generatedAt }}</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Courant Energy Monitoring System</p>
    </div>
</body>
</html>